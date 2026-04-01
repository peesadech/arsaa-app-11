<?php

namespace App\Services\Timetable\GeneticAlgorithm;

use App\Services\Timetable\DataLoader;

class CrossoverOperator
{
    public function __construct(
        private DataLoader $data,
        private float $crossoverRate = 0.8,
    ) {
    }

    /**
     * Uniform crossover at classroom level:
     * For each classroom, randomly pick which parent supplies that classroom's genes
     */
    public function crossover(Chromosome $parent1, Chromosome $parent2): Chromosome
    {
        if (mt_rand(0, 100) / 100 > $this->crossoverRate) {
            // No crossover, return clone of better parent
            return ($parent1->fitness >= $parent2->fitness)
                ? $parent1->cloneChromosome()
                : $parent2->cloneChromosome();
        }

        $child = new Chromosome();

        // Build classroom-to-openedCourse mapping
        $classroomGroups = [];
        foreach ($this->data->getOpenedCourses() as $oc) {
            $cKey = "{$oc->classroom_id}_{$oc->grade_id}";
            $classroomGroups[$cKey][] = $oc->id;
            $child->setClassroomGradeMap($oc->id, $oc->classroom_id, $oc->grade_id);
        }

        // For each classroom group, pick from parent1 or parent2
        foreach ($classroomGroups as $cKey => $openedCourseIds) {
            $source = (mt_rand(0, 1) === 0) ? $parent1 : $parent2;

            foreach ($openedCourseIds as $ocId) {
                $genes = $source->getGenesForOpenedCourse($ocId);
                foreach ($genes as $gene) {
                    $child->placeGene($gene->clone());
                }
            }
        }

        return $child;
    }
}
