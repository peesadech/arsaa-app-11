<?php

namespace Database\Seeders;

use App\Models\AttendanceStatus;
use Illuminate\Database\Seeder;

class AttendanceStatusSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'PRESENT',        'name_th' => 'มา',                 'name_en' => 'Present',        'status_type' => 'PRESENT',     'is_count_as_present' => true,  'color' => '#22c55e'],
            ['code' => 'LATE',           'name_th' => 'สาย',                'name_en' => 'Late',           'status_type' => 'LATE',        'is_count_as_present' => true,  'is_late' => true, 'color' => '#f59e0b'],
            ['code' => 'LEAVE',          'name_th' => 'ลา',                 'name_en' => 'Leave',          'status_type' => 'LEAVE',       'is_leave' => true,             'is_require_remark' => true, 'color' => '#3b82f6'],
            ['code' => 'ABSENT',         'name_th' => 'ขาด',                'name_en' => 'Absent',         'status_type' => 'ABSENT',      'is_count_as_absent' => true,  'color' => '#ef4444'],
            ['code' => 'ACTIVITY',       'name_th' => 'ไปกิจกรรมโรงเรียน',   'name_en' => 'School Activity', 'status_type' => 'ACTIVITY',    'is_count_as_present' => true,  'is_require_remark' => true, 'color' => '#0ea5e9'],
            ['code' => 'COMPETITION',    'name_th' => 'ไปแข่งขัน',           'name_en' => 'Competition',    'status_type' => 'COMPETITION', 'is_count_as_present' => true,  'is_require_remark' => true, 'color' => '#6366f1'],
            ['code' => 'SICK',           'name_th' => 'ป่วย',               'name_en' => 'Sick',           'status_type' => 'SICK',        'is_leave' => true,             'color' => '#f97316'],
            ['code' => 'PERSONAL_LEAVE', 'name_th' => 'ลากิจ',              'name_en' => 'Personal Leave', 'status_type' => 'PERSONAL_LEAVE', 'is_leave' => true,          'color' => '#14b8a6'],
            ['code' => 'ONLINE',         'name_th' => 'เรียนออนไลน์',        'name_en' => 'Online',         'status_type' => 'ONLINE',      'is_count_as_present' => true,  'color' => '#8b5cf6'],
            ['code' => 'OTHER',          'name_th' => 'อื่น ๆ',             'name_en' => 'Other',          'status_type' => 'OTHER',       'is_require_remark' => true,    'color' => '#64748b'],
        ];

        foreach ($rows as $i => $row) {
            AttendanceStatus::updateOrCreate(
                ['code' => $row['code']],
                array_merge([
                    'is_count_as_present' => false,
                    'is_count_as_absent'  => false,
                    'is_late'             => false,
                    'is_leave'            => false,
                    'is_require_remark'   => false,
                    'sort_order'          => $i + 1,
                    'is_active'           => true,
                ], $row)
            );
        }
    }
}
