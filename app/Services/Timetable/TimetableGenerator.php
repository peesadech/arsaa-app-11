<?php

namespace App\Services\Timetable;

use App\Models\TimetableConflict;
use App\Models\TimetableEntry;
use App\Models\TimetableGeneration;
use App\Models\TimetableSolution;
use App\Services\Timetable\GeneticAlgorithm\Chromosome;
use App\Services\Timetable\GeneticAlgorithm\CrossoverOperator;
use App\Services\Timetable\GeneticAlgorithm\FitnessCalculator;
use App\Services\Timetable\GeneticAlgorithm\Gene;
use App\Services\Timetable\GeneticAlgorithm\MutationOperator;
use App\Services\Timetable\GeneticAlgorithm\Population;
use App\Services\Timetable\GeneticAlgorithm\SelectionOperator;

class TimetableGenerator
{
    private FitnessCalculator $fitness;
    private SelectionOperator $selection;
    private CrossoverOperator $crossover;
    private MutationOperator $mutation;

    public function __construct(private DataLoader $data)
    {
        $this->fitness = new FitnessCalculator($data);
        $this->selection = new SelectionOperator(3);
        $this->crossover = new CrossoverOperator($data, 0.8);
        $this->mutation = new MutationOperator($data, 0.05);
    }

    public function generate(TimetableGeneration $generation): void
    {
        $populationSize = $generation->population_size;
        $maxGenerations = $generation->max_generations;
        $solutionsRequested = $generation->solutions_requested;

        // Load locked genes from existing selected solution if incremental
        $lockedGenes = $this->loadLockedGenes($generation);

        // Create initial population
        $population = $this->createInitialPopulation($populationSize, $lockedGenes);

        // Evaluate initial fitness
        foreach ($population->all() as $chromosome) {
            $this->fitness->calculate($chromosome);
        }

        // GA main loop
        for ($gen = 1; $gen <= $maxGenerations; $gen++) {
            $newChromosomes = [];

            // Elitism: keep the best
            $best = $population->getBest();
            if ($best) {
                $newChromosomes[] = $best->cloneChromosome();
            }

            // Create new generation
            while (count($newChromosomes) < $populationSize) {
                $parent1 = $this->selection->select($population);
                $parent2 = $this->selection->select($population);
                $child = $this->crossover->crossover($parent1, $parent2);
                $this->mutation->mutate($child);
                $this->fitness->calculate($child);
                $newChromosomes[] = $child;
            }

            $population->replace($newChromosomes);

            // Progress update every 10 generations
            if ($gen % 10 === 0) {
                $bestNow = $population->getBest();
                $generation->update([
                    'current_generation' => $gen,
                    'config' => array_merge($generation->config ?? [], [
                        'best_fitness' => $bestNow?->fitness,
                        'best_hard_violations' => $bestNow?->fitnessBreakdown['teacher_clash']
                            + ($bestNow?->fitnessBreakdown['room_clash'] ?? 0)
                            + ($bestNow?->fitnessBreakdown['classroom_clash'] ?? 0)
                            + ($bestNow?->fitnessBreakdown['teacher_unavailable'] ?? 0)
                            + ($bestNow?->fitnessBreakdown['room_unavailable'] ?? 0)
                            + ($bestNow?->fitnessBreakdown['period_count'] ?? 0),
                    ]),
                ]);
            }

            // Early termination: no hard violations
            $bestNow = $population->getBest();
            if ($bestNow && $this->countHardViolations($bestNow) === 0) {
                break;
            }
        }

        // Save top N solutions
        $topChromosomes = $population->getTopN($solutionsRequested);
        foreach ($topChromosomes as $rank => $chromosome) {
            $this->saveSolution($generation, $chromosome, $rank + 1);
        }

        $generation->update([
            'current_generation' => min($gen ?? $maxGenerations, $maxGenerations),
        ]);
    }

    private function loadLockedGenes(TimetableGeneration $generation): array
    {
        $lockedGenes = [];

        if (!$generation->scope) return $lockedGenes;

        // Find existing selected solution for this year/semester
        $existingSolution = TimetableSolution::whereHas('generation', function ($q) use ($generation) {
            $q->where('academic_year_id', $generation->academic_year_id)
                ->where('semester_id', $generation->semester_id)
                ->where('status', 'completed');
        })->where('is_selected', true)->first();

        if (!$existingSolution) return $lockedGenes;

        // Get locked entries from existing solution
        $entries = TimetableEntry::where('solution_id', $existingSolution->id)
            ->where('is_locked', true)
            ->get();

        foreach ($entries as $entry) {
            $lockedGenes[] = new Gene(
                $entry->opened_course_id,
                $entry->teacher_id ?? 0,
                $entry->room_id ?? 0,
                $entry->day,
                $entry->period,
                true,
            );
        }

        return $lockedGenes;
    }

    private function createInitialPopulation(int $size, array $lockedGenes): Population
    {
        $population = new Population();
        $openedCourses = $this->data->getOpenedCourses();

        for ($i = 0; $i < $size; $i++) {
            $chromosome = new Chromosome();

            // Set classroom/grade map for all opened courses
            foreach ($openedCourses as $oc) {
                $chromosome->setClassroomGradeMap($oc->id, $oc->classroom_id, $oc->grade_id);
            }

            // Place locked genes first
            foreach ($lockedGenes as $gene) {
                $chromosome->placeGene($gene->clone());
            }

            // Sort opened courses by most-constrained-first for first chromosome (greedy)
            $sortedCourses = $openedCourses->sortBy(function ($oc) {
                $teacherCount = count($this->data->getTeachersForCourse($oc->course_id));
                $roomCount = count($this->data->getRoomsForCourse($oc->course_id));
                return $teacherCount + $roomCount; // fewer options = more constrained
            });

            // Use greedy for first chromosome, random for rest
            $coursesToPlace = ($i === 0) ? $sortedCourses : $openedCourses->shuffle();

            foreach ($coursesToPlace as $oc) {
                $periodsNeeded = $oc->course->periods_per_week ?? 1;
                $periodsPlaced = $chromosome->getCoursePeriodsCount($oc->id);
                $remaining = $periodsNeeded - $periodsPlaced;

                if ($remaining <= 0) continue;

                $eduLevel = $this->data->getEducationLevelForOpenedCourse($oc->id);
                if (!$eduLevel) continue;

                $validSlots = $this->data->getValidSlots($eduLevel);
                if (empty($validSlots)) continue;

                $teachers = $this->data->getTeachersForCourse($oc->course_id);
                $rooms = $this->data->getRoomsForCourse($oc->course_id);

                if (empty($teachers)) continue;

                $teacher = $teachers[array_rand($teachers)];
                $room = !empty($rooms) ? $rooms[array_rand($rooms)] : 0;

                // Prefer preferred_days for greedy (first chromosome)
                $preferredDays = $oc->course->preferred_days ?? [];
                if ($i === 0 && !empty($preferredDays)) {
                    usort($validSlots, function ($a, $b) use ($preferredDays) {
                        $aPreferred = in_array($a['day'], $preferredDays) ? 0 : 1;
                        $bPreferred = in_array($b['day'], $preferredDays) ? 0 : 1;
                        return $aPreferred <=> $bPreferred ?: $a['period'] <=> $b['period'];
                    });
                } else {
                    shuffle($validSlots);
                }

                $placed = 0;
                foreach ($validSlots as $slot) {
                    if ($placed >= $remaining) break;

                    $day = $slot['day'];
                    $period = $slot['period'];

                    // Skip if classroom already occupied
                    if ($chromosome->hasClassroomConflict($oc->classroom_id, $oc->grade_id, $day, $period)) {
                        continue;
                    }

                    // For greedy, also check teacher/room conflicts
                    if ($i === 0) {
                        if ($chromosome->hasTeacherConflict($teacher, $day, $period)) continue;
                        if ($room && $chromosome->hasRoomConflict($room, $day, $period)) continue;
                        if ($eduLevel && !$this->data->isTeacherAvailable($teacher, $eduLevel, $day, $period)) continue;
                        if ($room && $eduLevel && !$this->data->isRoomAvailable($room, $eduLevel, $day, $period)) continue;
                    }

                    $gene = new Gene($oc->id, $teacher, $room, $day, $period);
                    $chromosome->placeGene($gene);
                    $placed++;
                }
            }

            $population->add($chromosome);
        }

        return $population;
    }

    private function countHardViolations(Chromosome $chromosome): int
    {
        $bd = $chromosome->fitnessBreakdown ?? [];
        return ($bd['teacher_clash'] ?? 0)
            + ($bd['room_clash'] ?? 0)
            + ($bd['classroom_clash'] ?? 0)
            + ($bd['teacher_unavailable'] ?? 0)
            + ($bd['room_unavailable'] ?? 0)
            + ($bd['period_count'] ?? 0);
    }

    private function saveSolution(TimetableGeneration $generation, Chromosome $chromosome, int $rank): void
    {
        $result = $this->fitness->calculate($chromosome);

        $solution = TimetableSolution::create([
            'generation_id' => $generation->id,
            'rank' => $rank,
            'fitness_score' => $result->score,
            'hard_violations' => $result->hardViolations,
            'soft_violations' => $result->softViolations,
            'fitness_breakdown' => $result->breakdown,
            'is_selected' => $rank === 1,
        ]);

        // Bulk insert entries
        $entries = [];
        $now = now();
        foreach ($chromosome->getGenes() as $gene) {
            $entries[] = [
                'solution_id' => $solution->id,
                'opened_course_id' => $gene->openedCourseId,
                'teacher_id' => $gene->teacherId ?: null,
                'room_id' => $gene->roomId ?: null,
                'day' => $gene->day,
                'period' => $gene->period,
                'is_locked' => $gene->isLocked,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert in chunks to avoid query size limits
        foreach (array_chunk($entries, 500) as $chunk) {
            TimetableEntry::insert($chunk);
        }

        // Save conflicts
        $checker = new ConstraintChecker($this->data);
        $conflicts = $checker->findAllConflicts($solution->id);
        $conflictRecords = [];
        foreach ($conflicts as $conflict) {
            $conflictRecords[] = [
                'solution_id' => $solution->id,
                'type' => $conflict['type'],
                'severity' => $conflict['severity'],
                'day' => $conflict['day'],
                'period' => $conflict['period'],
                'details' => json_encode($conflict['details']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($conflictRecords)) {
            foreach (array_chunk($conflictRecords, 500) as $chunk) {
                TimetableConflict::insert($chunk);
            }
        }
    }
}
