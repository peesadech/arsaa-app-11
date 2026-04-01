<?php

namespace App\Jobs;

use App\Models\TimetableGeneration;
use App\Services\Timetable\DataLoader;
use App\Services\Timetable\TimetableGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunTimetableGeneration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 1;

    public function __construct(private int $generationId)
    {
    }

    public function handle(): void
    {
        $generation = TimetableGeneration::findOrFail($this->generationId);
        $generation->update(['status' => 'running', 'started_at' => now()]);

        try {
            $data = new DataLoader(
                $generation->academic_year_id,
                $generation->semester_id,
                $generation->scope,
            );

            $generator = new TimetableGenerator($data);
            $generator->generate($generation);

            $generation->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $generation->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }
}
