<?php

namespace App\Services\Timetable\GeneticAlgorithm;

class Gene
{
    public function __construct(
        public int $openedCourseId,
        public int $teacherId,
        public int $roomId,
        public int $day,
        public int $period,
        public bool $isLocked = false,
    ) {
    }

    public function key(): string
    {
        return "{$this->openedCourseId}_{$this->day}_{$this->period}";
    }

    public function clone(): self
    {
        return new self(
            $this->openedCourseId,
            $this->teacherId,
            $this->roomId,
            $this->day,
            $this->period,
            $this->isLocked,
        );
    }
}
