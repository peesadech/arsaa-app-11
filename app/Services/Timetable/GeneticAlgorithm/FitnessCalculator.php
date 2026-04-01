<?php

namespace App\Services\Timetable\GeneticAlgorithm;

use App\Services\Timetable\DataLoader;

class FitnessCalculator
{
    // Hard constraint penalties
    private const HARD_TEACHER_CLASH = -1000;
    private const HARD_ROOM_CLASH = -1000;
    private const HARD_CLASSROOM_CLASH = -1000;
    private const HARD_TEACHER_UNAVAIL = -1000;
    private const HARD_ROOM_UNAVAIL = -1000;
    private const HARD_PERIOD_COUNT = -1000;

    // Soft constraint penalties
    private const SOFT_PREFERRED_DAY = -5;
    private const SOFT_TEACHER_GAP = -10;
    private const SOFT_COURSE_SPREAD = -8;
    private const SOFT_MORNING_CORE = -3;

    public function __construct(private DataLoader $data)
    {
    }

    public function calculate(Chromosome $chromosome): FitnessResult
    {
        $score = 0;
        $hardViolations = 0;
        $softViolations = 0;
        $breakdown = [
            'teacher_clash' => 0,
            'room_clash' => 0,
            'classroom_clash' => 0,
            'teacher_unavailable' => 0,
            'room_unavailable' => 0,
            'period_count' => 0,
            'preferred_day' => 0,
            'teacher_gap' => 0,
            'course_spread' => 0,
            'morning_core' => 0,
        ];

        $genes = $chromosome->getGenes();

        // --- Hard Constraints ---

        // 1. Teacher clash: check via teacherSlots index
        $teacherSlots = $chromosome->getTeacherSlots();
        // Already tracked by Chromosome's index, but we need to count actual clashes
        // Count pairs of conflicting entries per teacher per slot
        $teacherDayPeriodGenes = [];
        foreach ($genes as $gene) {
            $teacherDayPeriodGenes[$gene->teacherId][$gene->day][$gene->period][] = $gene;
        }
        foreach ($teacherDayPeriodGenes as $teacherId => $days) {
            foreach ($days as $day => $periods) {
                foreach ($periods as $period => $genesInSlot) {
                    if (count($genesInSlot) > 1) {
                        $count = count($genesInSlot) - 1;
                        $score += self::HARD_TEACHER_CLASH * $count;
                        $hardViolations += $count;
                        $breakdown['teacher_clash'] += $count;
                    }
                }
            }
        }

        // 2. Room clash
        $roomDayPeriodGenes = [];
        foreach ($genes as $gene) {
            if ($gene->roomId) {
                $roomDayPeriodGenes[$gene->roomId][$gene->day][$gene->period][] = $gene;
            }
        }
        foreach ($roomDayPeriodGenes as $roomId => $days) {
            foreach ($days as $day => $periods) {
                foreach ($periods as $period => $genesInSlot) {
                    if (count($genesInSlot) > 1) {
                        $count = count($genesInSlot) - 1;
                        $score += self::HARD_ROOM_CLASH * $count;
                        $hardViolations += $count;
                        $breakdown['room_clash'] += $count;
                    }
                }
            }
        }

        // 3. Classroom clash
        $classroomSlots = $chromosome->getClassroomSlots();
        $classroomDayPeriodGenes = [];
        foreach ($genes as $gene) {
            $oc = $this->data->getOpenedCourse($gene->openedCourseId);
            if (!$oc) continue;
            $cKey = "{$oc->classroom_id}_{$oc->grade_id}";
            $classroomDayPeriodGenes[$cKey][$gene->day][$gene->period][] = $gene;
        }
        foreach ($classroomDayPeriodGenes as $cKey => $days) {
            foreach ($days as $day => $periods) {
                foreach ($periods as $period => $genesInSlot) {
                    if (count($genesInSlot) > 1) {
                        $count = count($genesInSlot) - 1;
                        $score += self::HARD_CLASSROOM_CLASH * $count;
                        $hardViolations += $count;
                        $breakdown['classroom_clash'] += $count;
                    }
                }
            }
        }

        // 4. Teacher unavailable
        foreach ($genes as $gene) {
            $eduLevel = $this->data->getEducationLevelForOpenedCourse($gene->openedCourseId);
            if ($eduLevel && !$this->data->isTeacherAvailable($gene->teacherId, $eduLevel, $gene->day, $gene->period)) {
                $score += self::HARD_TEACHER_UNAVAIL;
                $hardViolations++;
                $breakdown['teacher_unavailable']++;
            }
        }

        // 5. Room unavailable
        foreach ($genes as $gene) {
            if (!$gene->roomId) continue;
            $eduLevel = $this->data->getEducationLevelForOpenedCourse($gene->openedCourseId);
            if ($eduLevel && !$this->data->isRoomAvailable($gene->roomId, $eduLevel, $gene->day, $gene->period)) {
                $score += self::HARD_ROOM_UNAVAIL;
                $hardViolations++;
                $breakdown['room_unavailable']++;
            }
        }

        // 6. Period count mismatch
        $openedCourses = $this->data->getOpenedCourses();
        foreach ($openedCourses as $oc) {
            $required = $oc->course->periods_per_week ?? 0;
            $actual = $chromosome->getCoursePeriodsCount($oc->id);
            if ($actual !== $required) {
                $diff = abs($actual - $required);
                $score += self::HARD_PERIOD_COUNT * $diff;
                $hardViolations += $diff;
                $breakdown['period_count'] += $diff;
            }
        }

        // --- Soft Constraints ---

        // 7. Preferred days
        foreach ($genes as $gene) {
            $oc = $this->data->getOpenedCourse($gene->openedCourseId);
            if (!$oc) continue;
            $preferredDays = $oc->course->preferred_days ?? null;
            if ($preferredDays && !in_array($gene->day, $preferredDays)) {
                $score += self::SOFT_PREFERRED_DAY;
                $softViolations++;
                $breakdown['preferred_day']++;
            }
        }

        // 8. Teacher gaps - free periods between first and last class per day
        foreach ($teacherDayPeriodGenes as $teacherId => $days) {
            foreach ($days as $day => $periods) {
                $periodNums = array_keys($periods);
                if (count($periodNums) < 2) continue;
                sort($periodNums);
                $min = min($periodNums);
                $max = max($periodNums);
                $gaps = ($max - $min + 1) - count($periodNums);
                if ($gaps > 0) {
                    $score += self::SOFT_TEACHER_GAP * $gaps;
                    $softViolations += $gaps;
                    $breakdown['teacher_gap'] += $gaps;
                }
            }
        }

        // 9. Course spread - avoid >2 periods of same course on same day
        $courseByDay = [];
        foreach ($genes as $gene) {
            $courseByDay[$gene->openedCourseId][$gene->day] = ($courseByDay[$gene->openedCourseId][$gene->day] ?? 0) + 1;
        }
        foreach ($courseByDay as $ocId => $days) {
            foreach ($days as $day => $count) {
                if ($count > 2) {
                    $excess = $count - 2;
                    $score += self::SOFT_COURSE_SPREAD * $excess;
                    $softViolations += $excess;
                    $breakdown['course_spread'] += $excess;
                }
            }
        }

        // 10. Morning preference for core subjects (period <= 4)
        foreach ($genes as $gene) {
            $oc = $this->data->getOpenedCourse($gene->openedCourseId);
            if (!$oc || !$oc->course->subjectGroup) continue;
            // Consider subject groups with id <= 3 as "core" (customizable)
            if ($oc->course->subject_group_id && $oc->course->subject_group_id <= 3 && $gene->period > 4) {
                $score += self::SOFT_MORNING_CORE;
                $softViolations++;
                $breakdown['morning_core']++;
            }
        }

        $chromosome->fitness = $score;
        $chromosome->fitnessBreakdown = $breakdown;

        return new FitnessResult($score, $hardViolations, $softViolations, $breakdown);
    }
}

class FitnessResult
{
    public function __construct(
        public readonly float $score,
        public readonly int $hardViolations,
        public readonly int $softViolations,
        public readonly array $breakdown,
    ) {
    }
}
