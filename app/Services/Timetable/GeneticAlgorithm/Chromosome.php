<?php

namespace App\Services\Timetable\GeneticAlgorithm;

class Chromosome
{
    /** @var array<string, Gene> keyed by gene key */
    private array $genes = [];

    /** @var array<int, array<int, array<int, int>>> teacherSlots[teacherId][day][period] = openedCourseId */
    private array $teacherSlots = [];

    /** @var array<int, array<int, array<int, int>>> roomSlots[roomId][day][period] = openedCourseId */
    private array $roomSlots = [];

    /** @var array<string, array<int, array<int, int>>> classroomSlots["classroomId_gradeId"][day][period] = openedCourseId */
    private array $classroomSlots = [];

    /** @var array<int, int> coursePeriodsCount[openedCourseId] = count */
    private array $coursePeriodsCount = [];

    /** @var array<int, int> classroomMap[openedCourseId] = classroomId */
    private array $classroomMap = [];

    /** @var array<int, int> gradeMap[openedCourseId] = gradeId */
    private array $gradeMap = [];

    public ?float $fitness = null;
    public ?array $fitnessBreakdown = null;

    public function setClassroomGradeMap(int $openedCourseId, int $classroomId, int $gradeId): void
    {
        $this->classroomMap[$openedCourseId] = $classroomId;
        $this->gradeMap[$openedCourseId] = $gradeId;
    }

    public function placeGene(Gene $gene): void
    {
        $key = $gene->key();

        // Remove existing gene at this key if any
        if (isset($this->genes[$key])) {
            $this->removeGene($key);
        }

        $this->genes[$key] = $gene;

        // Update teacher index
        $this->teacherSlots[$gene->teacherId][$gene->day][$gene->period] = $gene->openedCourseId;

        // Update room index
        if ($gene->roomId) {
            $this->roomSlots[$gene->roomId][$gene->day][$gene->period] = $gene->openedCourseId;
        }

        // Update classroom index
        $classroomId = $this->classroomMap[$gene->openedCourseId] ?? 0;
        $gradeId = $this->gradeMap[$gene->openedCourseId] ?? 0;
        $cKey = "{$classroomId}_{$gradeId}";
        $this->classroomSlots[$cKey][$gene->day][$gene->period] = $gene->openedCourseId;

        // Update period count
        $this->coursePeriodsCount[$gene->openedCourseId] = ($this->coursePeriodsCount[$gene->openedCourseId] ?? 0) + 1;
    }

    public function removeGene(string $key): void
    {
        if (!isset($this->genes[$key])) return;

        $gene = $this->genes[$key];

        // Remove from teacher index
        unset($this->teacherSlots[$gene->teacherId][$gene->day][$gene->period]);

        // Remove from room index
        if ($gene->roomId) {
            unset($this->roomSlots[$gene->roomId][$gene->day][$gene->period]);
        }

        // Remove from classroom index
        $classroomId = $this->classroomMap[$gene->openedCourseId] ?? 0;
        $gradeId = $this->gradeMap[$gene->openedCourseId] ?? 0;
        $cKey = "{$classroomId}_{$gradeId}";
        unset($this->classroomSlots[$cKey][$gene->day][$gene->period]);

        // Update period count
        $this->coursePeriodsCount[$gene->openedCourseId] = max(0, ($this->coursePeriodsCount[$gene->openedCourseId] ?? 1) - 1);

        unset($this->genes[$key]);
    }

    public function hasTeacherConflict(int $teacherId, int $day, int $period): bool
    {
        return isset($this->teacherSlots[$teacherId][$day][$period]);
    }

    public function hasRoomConflict(int $roomId, int $day, int $period): bool
    {
        return isset($this->roomSlots[$roomId][$day][$period]);
    }

    public function hasClassroomConflict(int $classroomId, int $gradeId, int $day, int $period): bool
    {
        $cKey = "{$classroomId}_{$gradeId}";
        return isset($this->classroomSlots[$cKey][$day][$period]);
    }

    public function getTeacherAtSlot(int $teacherId, int $day, int $period): ?int
    {
        return $this->teacherSlots[$teacherId][$day][$period] ?? null;
    }

    /** @return Gene[] */
    public function getGenes(): array
    {
        return $this->genes;
    }

    public function getGene(string $key): ?Gene
    {
        return $this->genes[$key] ?? null;
    }

    /** @return Gene[] */
    public function getGenesForOpenedCourse(int $openedCourseId): array
    {
        return array_filter($this->genes, fn(Gene $g) => $g->openedCourseId === $openedCourseId);
    }

    /** @return Gene[] */
    public function getGenesForClassroom(int $classroomId, int $gradeId): array
    {
        return array_filter($this->genes, function (Gene $g) use ($classroomId, $gradeId) {
            return ($this->classroomMap[$g->openedCourseId] ?? 0) === $classroomId
                && ($this->gradeMap[$g->openedCourseId] ?? 0) === $gradeId;
        });
    }

    public function getCoursePeriodsCount(int $openedCourseId): int
    {
        return $this->coursePeriodsCount[$openedCourseId] ?? 0;
    }

    public function getTeacherSlots(): array
    {
        return $this->teacherSlots;
    }

    public function getClassroomSlots(): array
    {
        return $this->classroomSlots;
    }

    /** @return Gene[] unlocked genes */
    public function getUnlockedGenes(): array
    {
        return array_filter($this->genes, fn(Gene $g) => !$g->isLocked);
    }

    public function cloneChromosome(): self
    {
        $new = new self();
        $new->classroomMap = $this->classroomMap;
        $new->gradeMap = $this->gradeMap;

        foreach ($this->genes as $gene) {
            $new->placeGene($gene->clone());
        }

        return $new;
    }
}
