<?php

namespace Database\Seeders;

use App\Models\MasterOption;
use App\Models\Student;
use App\Models\StudentAddress;
use App\Models\StudentEducationHistory;
use App\Models\StudentGuardian;
use App\Models\Teacher;
use App\Services\TeacherAccountService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Mock data สำหรับทดสอบระบบ: นักเรียน 20 คน + ครู 5 คน
 * รันซ้ำได้ (idempotent) — เช็คจาก student_code / email ก่อนสร้าง
 *
 * php artisan db:seed --class=MockStudentTeacherSeeder
 */
class MockStudentTeacherSeeder extends Seeder
{
    public function run(): void
    {
        // master data ต้องมีก่อน (firstOrCreate ทั้งหมด รันซ้ำได้)
        $this->call(StudentMasterDataSeeder::class);

        $this->seedTeachers();
        $this->seedStudents();
    }

    private function seedTeachers(): void
    {
        $teachers = [
            ['name' => 'ครูสมชาย ใจดี',       'email' => 'somchai@arsaa.test',  'phone' => '081-111-1001'],
            ['name' => 'ครูวิภา เรืองศรี',      'email' => 'wipa@arsaa.test',     'phone' => '081-111-1002'],
            ['name' => 'ครูหลี่ เหม่ยหลิง',     'email' => 'meiling@arsaa.test',  'phone' => '081-111-1003'],
            ['name' => 'ครูประวิทย์ แซ่ตั้ง',   'email' => 'prawit@arsaa.test',   'phone' => '081-111-1004'],
            ['name' => 'ครูสุนีย์ พงษ์พานิช',   'email' => 'sunee@arsaa.test',    'phone' => '081-111-1005'],
        ];

        $accountService = app(TeacherAccountService::class);

        foreach ($teachers as $data) {
            $teacher = Teacher::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => Hash::make('password'),
                    'status' => 1,
                ]
            );

            $accountService->syncAccount($teacher);
        }

        $this->command?->info('Teachers: ' . count($teachers) . ' คน (password เริ่มต้น: password)');
    }

    private function seedStudents(): void
    {
        $options = fn (string $type) => MasterOption::ofType($type)->get();

        $nationalities = $options(MasterOption::TYPE_NATIONALITY);
        $religions = $options(MasterOption::TYPE_RELIGION);
        $bloodTypes = $options(MasterOption::TYPE_BLOOD_TYPE);
        $guardianTypes = $options(MasterOption::TYPE_GUARDIAN_TYPE);
        $provinces = $options(MasterOption::TYPE_PROVINCE);

        $thai = $nationalities->firstWhere('name_th', 'ไทย');
        $buddhism = $religions->firstWhere('name_th', 'พุทธ');
        $father = $guardianTypes->firstWhere('name_th', 'บิดา');
        $mother = $guardianTypes->firstWhere('name_th', 'มารดา');
        $chiangmai = $provinces->firstWhere('name_th', 'เชียงใหม่') ?? $provinces->first();

        // [ชื่อไทย, ชื่อจีน, เพศ(m/f), ปีเกิด ค.ศ.]
        $students = [
            ['ด.ช.กิตติพงศ์ วัฒนชัย',   '李伟强', 'm', 2014],
            ['ด.ญ.ณัฐธิดา ศรีสุวรรณ',   '王美丽', 'f', 2014],
            ['ด.ช.ธนกร แซ่ลิ้ม',         '林俊杰', 'm', 2013],
            ['ด.ญ.พิมพ์ชนก จันทร์เพ็ญ', '陈小婷', 'f', 2013],
            ['ด.ช.ภูริภัทร ตั้งตระกูล',   '张志明', 'm', 2014],
            ['ด.ญ.กัญญาณัฐ แก้วมณี',    '刘雅婷', 'f', 2015],
            ['ด.ช.ศุภกฤต พูลสวัสดิ์',    '黄文杰', 'm', 2015],
            ['ด.ญ.อริสรา บุญประเสริฐ',  '吴佳琪', 'f', 2014],
            ['ด.ช.ปัณณวิชญ์ แซ่โง้ว',    '吴俊宏', 'm', 2013],
            ['ด.ญ.ชญานิศ วงศ์สกุล',     '郑雪儿', 'f', 2013],
            ['ด.ช.จิรายุ รัตนโชติ',       '赵天宇', 'm', 2014],
            ['ด.ญ.นภัสสร ทองสุข',       '孙丽华', 'f', 2015],
            ['ด.ช.กฤตภาส ชัยวัฒน์',     '周建国', 'm', 2015],
            ['ด.ญ.ปุณยวีร์ ศิริวัฒนา',    '徐静怡', 'f', 2014],
            ['ด.ช.ธีรเดช แซ่จิว',         '朱伟明', 'm', 2013],
            ['ด.ญ.ขวัญข้าว พรหมเมศ',   '马小红', 'f', 2014],
            ['ด.ช.อัครวินท์ เลิศวิริยะ',   '胡志强', 'm', 2015],
            ['ด.ญ.ธัญชนก แสงทอง',      '郭美玲', 'f', 2013],
            ['ด.ช.รัชชานนท์ กิจเจริญ',   '何家豪', 'm', 2014],
            ['ด.ญ.ลลิตภัทร มั่งมีศรี',     '高小燕', 'f', 2015],
        ];

        $occupations = ['ค้าขาย', 'รับจ้าง', 'เกษตรกร', 'พนักงานบริษัท', 'ธุรกิจส่วนตัว', 'รับราชการ'];
        $schools = [
            ['โรงเรียนบ้านสันกำแพง', 'อ.สันกำแพง จ.เชียงใหม่'],
            ['โรงเรียนอนุบาลเชียงใหม่', 'อ.เมือง จ.เชียงใหม่'],
            ['โรงเรียนบ้านแม่โจ้', 'อ.สันทราย จ.เชียงใหม่'],
            ['โรงเรียนวัดดอนจั่น', 'อ.เมือง จ.เชียงใหม่'],
        ];
        $lastNamesCn = ['李', '王', '林', '陈', '张', '刘', '黄', '吴', '郑', '赵'];

        $created = 0;

        foreach ($students as $i => [$nameTh, $nameCn, $sex, $birthYear]) {
            if (Student::where('name_th', $nameTh)->exists()) {
                continue;
            }

            $student = Student::create([
                'student_code' => Student::generateCode(),
                'name_th' => $nameTh,
                'name_cn' => $nameCn,
                'citizen_id' => '1509901' . str_pad((string) (100000 + $i), 6, '0', STR_PAD_LEFT),
                'birth_date' => sprintf('%d-%02d-%02d', $birthYear, ($i % 12) + 1, ($i % 27) + 1),
                'race_id' => $thai?->id,
                'nationality_id' => $thai?->id,
                'religion_id' => $buddhism?->id,
                'blood_type_id' => $bloodTypes[$i % $bloodTypes->count()]?->id,
                'height' => 115 + ($i % 30),
                'weight' => 20 + ($i % 18),
                'mobile' => '09' . str_pad((string) (10000000 + $i * 137), 8, '0', STR_PAD_LEFT),
                'status' => Student::STATUS_STUDYING,
                'note' => 'mock data',
            ]);

            // ที่อยู่ 2 ชุด (current / registered ใช้ที่เดียวกัน)
            foreach ([StudentAddress::TYPE_CURRENT, StudentAddress::TYPE_REGISTERED] as $type) {
                $student->addresses()->create([
                    'type' => $type,
                    'house_no' => (string) (($i + 1) * 7) . '/' . ($i + 1),
                    'moo' => (string) (($i % 9) + 1),
                    'subdistrict' => 'ต.สุเทพ',
                    'district' => 'อ.เมือง',
                    'province_id' => $chiangmai?->id,
                    'postal_code' => '50200',
                ]);
            }

            // ผู้ปกครอง: พ่อ + แม่ (แม่เป็นผู้ปกครองหลัก)
            $surname = mb_substr($nameTh, mb_strpos($nameTh, ' ') + 1);
            $cnSurname = $lastNamesCn[$i % count($lastNamesCn)];
            $student->guardians()->createMany([
                [
                    'guardian_type_id' => $father?->id,
                    'name' => 'นายสมศักดิ์ ' . $surname,
                    'name_cn' => $cnSurname . '先生',
                    'age' => 38 + ($i % 12),
                    'race_id' => $thai?->id,
                    'nationality_id' => $thai?->id,
                    'religion_id' => $buddhism?->id,
                    'living_status' => 'together',
                    'phone' => '08' . str_pad((string) (20000000 + $i * 211), 8, '0', STR_PAD_LEFT),
                    'occupation' => $occupations[$i % count($occupations)],
                    'relationship' => 'บิดา',
                    'is_primary' => false,
                ],
                [
                    'guardian_type_id' => $mother?->id,
                    'name' => 'นางสมหญิง ' . $surname,
                    'name_cn' => $cnSurname . '太太',
                    'age' => 35 + ($i % 12),
                    'race_id' => $thai?->id,
                    'nationality_id' => $thai?->id,
                    'religion_id' => $buddhism?->id,
                    'living_status' => 'together',
                    'phone' => '08' . str_pad((string) (30000000 + $i * 307), 8, '0', STR_PAD_LEFT),
                    'occupation' => $occupations[($i + 2) % count($occupations)],
                    'relationship' => 'มารดา',
                    'is_primary' => true,
                ],
            ]);

            // ประวัติการศึกษา (จีน) เดิม 1 รายการ
            [$school, $location] = $schools[$i % count($schools)];
            $student->educationHistories()->create([
                'school_name' => $school,
                'school_location' => $location,
                'last_level' => 'ระดับ ' . (($i % 3) + 1),
                'gpa' => round(2.5 + ($i % 15) * 0.1, 2),
                'graduated_at' => '03/' . (2566 + ($i % 3)),
            ]);

            $created++;
        }

        $this->command?->info("Students: สร้างใหม่ {$created} คน (ทั้งหมด " . Student::count() . ' คนในระบบ)');
    }
}
