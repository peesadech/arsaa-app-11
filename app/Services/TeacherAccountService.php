<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\User;
use Spatie\Permission\Models\Role;

class TeacherAccountService
{
    const ROLE_NAME = 'Teacher';

    private function teacherRole(): Role
    {
        return Role::firstOrCreate(['name' => self::ROLE_NAME, 'guard_name' => 'web']);
    }

    /**
     * สร้าง/ผูกบัญชี user ให้ครู — password รับเป็น hash แล้ว (copy จาก teachers.password ได้เลย)
     */
    public function syncAccount(Teacher $teacher): User
    {
        $user = $teacher->user_id ? User::find($teacher->user_id) : null;

        // ถ้ายังไม่ผูก แต่มี user email เดียวกันอยู่แล้ว → ผูกกับคนนั้น (ไม่สร้างซ้ำ)
        if (!$user) {
            $user = User::where('email', $teacher->email)->first();
        }

        if ($user) {
            $user->name = $teacher->name;
            $user->email = $teacher->email;
            $user->password = $teacher->password; // hash เดียวกับตาราง teachers
            $user->save();
        } else {
            $user = new User();
            $user->name = $teacher->name;
            $user->email = $teacher->email;
            $user->password = $teacher->password;
            $user->save();
        }

        $user->assignRole($this->teacherRole());

        if ($teacher->user_id !== $user->id) {
            $teacher->user_id = $user->id;
            $teacher->save();
        }

        return $user;
    }

    public function deleteAccount(Teacher $teacher): void
    {
        if (!$teacher->user_id) return;

        $user = User::find($teacher->user_id);
        if ($user) {
            $user->delete();
        }
    }

    /**
     * สร้างบัญชีให้ครูทุกคนที่ยังไม่มี (backfill ครูเก่า) — คืนจำนวนที่สร้าง
     */
    public function createMissingAccounts(): int
    {
        $count = 0;

        Teacher::whereNull('user_id')->where('status', 1)->get()->each(function (Teacher $teacher) use (&$count) {
            $this->syncAccount($teacher);
            $count++;
        });

        return $count;
    }

    /**
     * หา record ครูของ user ที่ login อยู่
     */
    public function teacherForUser(?User $user): ?Teacher
    {
        if (!$user) return null;

        return Teacher::where('user_id', $user->id)->first();
    }
}
