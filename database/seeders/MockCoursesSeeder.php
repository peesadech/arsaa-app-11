<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseType;
use App\Models\Grade;
use App\Models\Semester;
use App\Models\SubjectGroup;
use Illuminate\Database\Seeder;

/**
 * Mock รายวิชา: เติมให้ครบ ระดับชั้นละ 10 วิชา ต่อเทอม (ไม่ทับ/ไม่ซ้ำของเดิม)
 */
class MockCoursesSeeder extends Seeder
{
    const TARGET_PER_TERM = 10;

    public function run(): void
    {
        // subject groups ที่ต้องใช้ (สร้างเพิ่มถ้ายังไม่มี)
        $groups = [];
        $groupNames = [
            'ไทย' => 'Thai',
            'คณิตศาตร์' => 'Mathematics',
            'อังกฤษ' => 'English',
            'ภาษาจีน' => 'Chinese',
            'วิทยาศาสตร์' => 'Science',
            'สังคมศึกษา' => 'Social Studies',
            'ศิลปะ' => 'Arts',
            'สุขศึกษาและพลศึกษา' => 'Health & PE',
            'การงานอาชีพ' => 'Career & Technology',
            'กิจกรรมพัฒนาผู้เรียน' => 'Student Activities',
        ];
        foreach ($groupNames as $th => $en) {
            $groups[$th] = SubjectGroup::firstOrCreate(['name_th' => $th], ['name_en' => $en, 'status' => 1])->id;
        }

        // course types (มีอยู่แล้ว: ทฤษฏี/ปฏิบัติ/กิจกรรม)
        $theory = CourseType::where('name_th', 'like', 'ทฤษ%')->value('id');
        $practice = CourseType::where('name_th', 'like', 'ปฏิบัติ%')->value('id');
        $activity = CourseType::where('name_th', 'like', 'กิจกรรม%')->value('id');

        // แม่แบบ 10 วิชา: [ชื่อ, group, type, คาบ/สัปดาห์, คาบติดกัน/ครั้ง]
        $templates = [
            ['ภาษาไทย', 'ไทย', $theory, 3, 1],
            ['คณิตศาสตร์', 'คณิตศาตร์', $theory, 3, 1],
            ['ภาษาอังกฤษ', 'อังกฤษ', $theory, 3, 1],
            ['ภาษาจีน', 'ภาษาจีน', $theory, 3, 1],
            ['วิทยาศาสตร์', 'วิทยาศาสตร์', $theory, 3, 1],
            ['สังคมศึกษา', 'สังคมศึกษา', $theory, 2, 1],
            ['ศิลปะ', 'ศิลปะ', $practice, 2, 2],
            ['พลศึกษา', 'สุขศึกษาและพลศึกษา', $practice, 2, 1],
            ['การงานอาชีพ', 'การงานอาชีพ', $practice, 2, 2],
            ['กิจกรรมชุมนุม', 'กิจกรรมพัฒนาผู้เรียน', $activity, 1, 1],
        ];

        $grades = Grade::where('status', 1)->get();
        $semesters = Semester::where('status', 1)->get();
        $created = 0;

        foreach ($grades as $grade) {
            foreach ($semesters as $semester) {
                $existingCount = Course::where('grade_id', $grade->id)
                    ->where('semester_id', $semester->id)
                    ->count();

                $existingNames = Course::where('grade_id', $grade->id)
                    ->where('semester_id', $semester->id)
                    ->pluck('name')
                    ->toArray();

                foreach ($templates as [$name, $groupTh, $typeId, $periodsPerWeek, $periodsPerSession]) {
                    if ($existingCount >= self::TARGET_PER_TERM) break;
                    if (in_array($name, $existingNames)) continue;

                    // courses.name เป็น unique ทั้งตาราง — ถ้าชื่อชนกับระดับชั้น/เทอมอื่น ให้ต่อท้ายด้วยชั้น+เทอม
                    $courseName = $name;
                    if (Course::where('name', $courseName)->exists()) {
                        $courseName = "{$name} {$grade->name_th} เทอม {$semester->semester_number}";
                        if (Course::where('name', $courseName)->exists()) continue;
                    }

                    Course::create([
                        'name' => $courseName,
                        'grade_id' => $grade->id,
                        'semester_id' => $semester->id,
                        'subject_group_id' => $groups[$groupTh],
                        'course_type_id' => $typeId,
                        'periods_per_week' => $periodsPerWeek,
                        'periods_per_session' => $periodsPerSession,
                        'status' => 1,
                    ]);

                    $existingCount++;
                    $created++;
                }

                $this->command?->info("{$grade->name_th} / เทอม {$semester->semester_number}: รวม {$existingCount} วิชา");
            }
        }

        $this->command?->info("สร้างวิชาใหม่ {$created} วิชา");
    }
}
