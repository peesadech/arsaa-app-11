<?php

namespace App\Services\Timetable\GeneticAlgorithm;

class Population
{
    /** @var Chromosome[] */
    private array $chromosomes = [];

    public function add(Chromosome $chromosome): void
    {
        $this->chromosomes[] = $chromosome;
    }

    /** @return Chromosome[] */
    public function all(): array
    {
        return $this->chromosomes;
    }

    public function size(): int
    {
        return count($this->chromosomes);
    }

    public function get(int $index): ?Chromosome
    {
        return $this->chromosomes[$index] ?? null;
    }

    public function getBest(): ?Chromosome
    {
        if (empty($this->chromosomes)) return null;

        return collect($this->chromosomes)->sortByDesc(fn(Chromosome $c) => $c->fitness ?? PHP_FLOAT_MIN)->first();
    }

    /** @return Chromosome[] sorted by fitness descending */
    public function getTopN(int $n): array
    {
        return collect($this->chromosomes)
            ->sortByDesc(fn(Chromosome $c) => $c->fitness ?? PHP_FLOAT_MIN)
            ->take($n)
            ->values()
            ->all();
    }

    public function replace(array $newChromosomes): void
    {
        $this->chromosomes = $newChromosomes;
    }
}
