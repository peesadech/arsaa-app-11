<?php

namespace App\Services\Timetable\GeneticAlgorithm;

use App\Services\Timetable\DataLoader;

class MutationOperator
{
    public function __construct(
        private DataLoader $data,
        private float $mutationRate = 0.05,
    ) {
    }

    /**
     * Mutate a chromosome in-place
     * Types: move to empty slot, swap two genes, reassign teacher/room
     */
    public function mutate(Chromosome $chromosome): void
    {
        $unlocked = $chromosome->getUnlockedGenes();
        if (empty($unlocked)) return;

        foreach ($unlocked as $gene) {
            if (mt_rand(0, 1000) / 1000 > $this->mutationRate) continue;

            $type = mt_rand(1, 3);
            match ($type) {
                1 => $this->moveToEmptySlot($chromosome, $gene),
                2 => $this->swapWithinCourse($chromosome, $gene),
                3 => $this->reassignResource($chromosome, $gene),
            };
        }
    }

    private function moveToEmptySlot(Chromosome $chromosome, Gene $gene): void
    {
        $eduLevel = $this->data->getEducationLevelForOpenedCourse($gene->openedCourseId);
        if (!$eduLevel) return;

        $validSlots = $this->data->getValidSlots($eduLevel);
        if (empty($validSlots)) return;

        $oc = $this->data->getOpenedCourse($gene->openedCourseId);
        if (!$oc) return;

        // Try up to 10 random slots
        for ($i = 0; $i < 10; $i++) {
            $slot = $validSlots[array_rand($validSlots)];
            $day = $slot['day'];
            $period = $slot['period'];

            // Skip if classroom already occupied
            if ($chromosome->hasClassroomConflict($oc->classroom_id, $oc->grade_id, $day, $period)) {
                continue;
            }

            // Move the gene
            $oldKey = $gene->key();
            $chromosome->removeGene($oldKey);
            $gene->day = $day;
            $gene->period = $period;
            $chromosome->placeGene($gene);
            return;
        }
    }

    private function swapWithinCourse(Chromosome $chromosome, Gene $gene): void
    {
        $courseGenes = $chromosome->getGenesForOpenedCourse($gene->openedCourseId);
        $unlocked = array_filter($courseGenes, fn(Gene $g) => !$g->isLocked && $g->key() !== $gene->key());

        if (empty($unlocked)) return;

        $other = $unlocked[array_rand($unlocked)];

        // Swap day/period
        $chromosome->removeGene($gene->key());
        $chromosome->removeGene($other->key());

        [$gene->day, $other->day] = [$other->day, $gene->day];
        [$gene->period, $other->period] = [$other->period, $gene->period];

        $chromosome->placeGene($gene);
        $chromosome->placeGene($other);
    }

    private function reassignResource(Chromosome $chromosome, Gene $gene): void
    {
        $oc = $this->data->getOpenedCourse($gene->openedCourseId);
        if (!$oc) return;

        $action = mt_rand(0, 1);

        if ($action === 0) {
            // Reassign teacher
            $teachers = $this->data->getTeachersForCourse($oc->course_id);
            if (count($teachers) > 1) {
                $newTeacher = $teachers[array_rand($teachers)];
                $chromosome->removeGene($gene->key());
                $gene->teacherId = $newTeacher;
                $chromosome->placeGene($gene);
            }
        } else {
            // Reassign room
            $rooms = $this->data->getRoomsForCourse($oc->course_id);
            if (count($rooms) > 1) {
                $newRoom = $rooms[array_rand($rooms)];
                $chromosome->removeGene($gene->key());
                $gene->roomId = $newRoom;
                $chromosome->placeGene($gene);
            }
        }
    }
}
