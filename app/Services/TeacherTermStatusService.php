<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\TeacherTermStatus;
use App\Models\TeacherTermStatusLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherTermStatusService
{
    public function getOrCreate(int $teacherId, int $yearId, int $semesterId): TeacherTermStatus
    {
        return TeacherTermStatus::firstOrCreate(
            [
                'teacher_id' => $teacherId,
                'academic_year_id' => $yearId,
                'semester_id' => $semesterId,
            ],
            [
                'status' => TeacherTermStatus::STATUS_AVAILABLE,
                'can_be_scheduled' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]
        );
    }

    public function updateStatus(
        TeacherTermStatus $termStatus,
        string $newStatus,
        ?bool $canBeScheduled = null,
        ?string $reason = null,
        array $extra = [],
    ): TeacherTermStatus {
        return DB::transaction(function () use ($termStatus, $newStatus, $canBeScheduled, $reason, $extra) {
            $oldStatus = $termStatus->status;
            $oldCanBeScheduled = $termStatus->can_be_scheduled;

            // Auto-derive can_be_scheduled if not explicitly set
            if ($canBeScheduled === null) {
                $canBeScheduled = in_array($newStatus, TeacherTermStatus::SCHEDULABLE_STATUSES);
            }

            $termStatus->update(array_merge([
                'status' => $newStatus,
                'can_be_scheduled' => $canBeScheduled,
                'updated_by' => Auth::id(),
            ], $extra));

            // Write audit log
            TeacherTermStatusLog::create([
                'teacher_term_status_id' => $termStatus->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'old_can_be_scheduled' => $oldCanBeScheduled,
                'new_can_be_scheduled' => $canBeScheduled,
                'reason' => $reason,
                'changed_by' => Auth::id(),
                'changed_at' => now(),
            ]);

            return $termStatus->fresh();
        });
    }

    public function bulkInitialize(int $yearId, int $semesterId): int
    {
        $existingTeacherIds = TeacherTermStatus::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->pluck('teacher_id');

        $teachersToInit = Teacher::where('status', 1)
            ->whereNotIn('id', $existingTeacherIds)
            ->pluck('id');

        if ($teachersToInit->isEmpty()) return 0;

        $now = now();
        $userId = Auth::id();

        $records = $teachersToInit->map(fn($id) => [
            'teacher_id' => $id,
            'academic_year_id' => $yearId,
            'semester_id' => $semesterId,
            'status' => TeacherTermStatus::STATUS_AVAILABLE,
            'can_be_scheduled' => true,
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ])->toArray();

        TeacherTermStatus::insert($records);

        return count($records);
    }

    public function getSchedulableTeacherIds(int $yearId, int $semesterId): array
    {
        // Active teachers who either have no term record (default available)
        // or have can_be_scheduled = true
        $nonSchedulableIds = TeacherTermStatus::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->where('can_be_scheduled', false)
            ->pluck('teacher_id');

        return Teacher::where('status', 1)
            ->whereNotIn('id', $nonSchedulableIds)
            ->pluck('id')
            ->toArray();
    }

    public function getTermStatusSummary(int $yearId, int $semesterId): array
    {
        $counts = TeacherTermStatus::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalActive = Teacher::where('status', 1)->count();
        $totalWithRecord = array_sum($counts);

        return [
            'total_active_teachers' => $totalActive,
            'no_term_record' => $totalActive - $totalWithRecord,
            'by_status' => $counts,
        ];
    }
}
