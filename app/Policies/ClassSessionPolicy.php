<?php

namespace App\Policies;

use App\Models\ClassSession;
use App\Models\User;
use App\Services\TeacherAccountService;

class ClassSessionPolicy
{
    /** admin/SuperAdmin จัดการได้ทุกคาบ; ครูจัดการได้เฉพาะคาบของตัวเอง */
    public function manage(User $user, ClassSession $session): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        $teacher = app(TeacherAccountService::class)->teacherForUser($user);

        return $teacher !== null && $teacher->id === $session->teacher_id;
    }

    public function view(User $user, ClassSession $session): bool
    {
        return $this->manage($user, $session);
    }

    private function isAdmin(User $user): bool
    {
        return $user->getRoleNames()
            ->map(fn ($r) => strtoupper($r))
            ->intersect(['ADMIN', 'SUPERADMIN'])
            ->isNotEmpty();
    }
}
