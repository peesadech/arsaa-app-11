<?php

namespace App\Services\Timetable\GeneticAlgorithm;

class SelectionOperator
{
    private int $tournamentSize;

    public function __construct(int $tournamentSize = 3)
    {
        $this->tournamentSize = $tournamentSize;
    }

    /**
     * Tournament selection: pick N random chromosomes, return the best one
     */
    public function select(Population $population): Chromosome
    {
        $candidates = [];
        $size = $population->size();

        for ($i = 0; $i < $this->tournamentSize; $i++) {
            $index = random_int(0, $size - 1);
            $candidates[] = $population->get($index);
        }

        usort($candidates, fn(Chromosome $a, Chromosome $b) => ($b->fitness ?? PHP_FLOAT_MIN) <=> ($a->fitness ?? PHP_FLOAT_MIN));

        return $candidates[0];
    }
}
