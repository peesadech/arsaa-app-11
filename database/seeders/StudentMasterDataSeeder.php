<?php

namespace Database\Seeders;

use App\Models\GradeSetting;
use App\Models\MasterOption;
use Illuminate\Database\Seeder;

class StudentMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $sets = [
            MasterOption::TYPE_NATIONALITY => [
                ['ไทย', 'Thai', '泰国'],
                ['จีน', 'Chinese', '中国'],
                ['เมียนมา', 'Myanmar', '缅甸'],
                ['ลาว', 'Lao', '老挝'],
                ['กัมพูชา', 'Cambodian', '柬埔寨'],
                ['อื่น ๆ', 'Other', '其他'],
            ],
            MasterOption::TYPE_RELIGION => [
                ['พุทธ', 'Buddhism', '佛教'],
                ['คริสต์', 'Christianity', '基督教'],
                ['อิสลาม', 'Islam', '伊斯兰教'],
                ['อื่น ๆ', 'Other', '其他'],
            ],
            MasterOption::TYPE_BLOOD_TYPE => [
                ['A', 'A', 'A'],
                ['B', 'B', 'B'],
                ['AB', 'AB', 'AB'],
                ['O', 'O', 'O'],
            ],
            MasterOption::TYPE_GUARDIAN_TYPE => [
                ['บิดา', 'Father', '父亲'],
                ['มารดา', 'Mother', '母亲'],
                ['ผู้ปกครอง', 'Guardian', '监护人'],
                ['อื่น ๆ', 'Other', '其他'],
            ],
            MasterOption::TYPE_DOCUMENT_TYPE => [
                ['รูปถ่าย 1.5 นิ้ว', 'Photo 1.5"', '1.5寸照片'],
                ['สำเนาบัตรประชาชน / สูติบัตรนักเรียน', 'Student ID card / birth certificate copy', '学生身份证/出生证复印件'],
                ['สำเนาทะเบียนบ้านนักเรียน', 'Student house registration copy', '学生户口本复印件'],
                ['สำเนาบัตรประชาชนผู้ปกครอง', 'Guardian ID card copy', '监护人身份证复印件'],
                ['สำเนาทะเบียนบ้านผู้ปกครอง', 'Guardian house registration copy', '监护人户口本复印件'],
                ['เอกสารแสดงผลการเรียนล่าสุด', 'Latest academic record', '最新成绩单'],
                ['อื่น ๆ', 'Other', '其他'],
            ],
            MasterOption::TYPE_PROVINCE => array_map(fn($p) => [$p, null, null], [
                'กรุงเทพมหานคร', 'กระบี่', 'กาญจนบุรี', 'กาฬสินธุ์', 'กำแพงเพชร', 'ขอนแก่น', 'จันทบุรี',
                'ฉะเชิงเทรา', 'ชลบุรี', 'ชัยนาท', 'ชัยภูมิ', 'ชุมพร', 'เชียงราย', 'เชียงใหม่', 'ตรัง',
                'ตราด', 'ตาก', 'นครนายก', 'นครปฐม', 'นครพนม', 'นครราชสีมา', 'นครศรีธรรมราช', 'นครสวรรค์',
                'นนทบุรี', 'นราธิวาส', 'น่าน', 'บึงกาฬ', 'บุรีรัมย์', 'ปทุมธานี', 'ประจวบคีรีขันธ์',
                'ปราจีนบุรี', 'ปัตตานี', 'พระนครศรีอยุธยา', 'พะเยา', 'พังงา', 'พัทลุง', 'พิจิตร',
                'พิษณุโลก', 'เพชรบุรี', 'เพชรบูรณ์', 'แพร่', 'ภูเก็ต', 'มหาสารคาม', 'มุกดาหาร',
                'แม่ฮ่องสอน', 'ยโสธร', 'ยะลา', 'ร้อยเอ็ด', 'ระนอง', 'ระยอง', 'ราชบุรี', 'ลพบุรี',
                'ลำปาง', 'ลำพูน', 'เลย', 'ศรีสะเกษ', 'สกลนคร', 'สงขลา', 'สตูล', 'สมุทรปราการ',
                'สมุทรสงคราม', 'สมุทรสาคร', 'สระแก้ว', 'สระบุรี', 'สิงห์บุรี', 'สุโขทัย', 'สุพรรณบุรี',
                'สุราษฎร์ธานี', 'สุรินทร์', 'หนองคาย', 'หนองบัวลำภู', 'อ่างทอง', 'อำนาจเจริญ',
                'อุดรธานี', 'อุตรดิตถ์', 'อุทัยธานี', 'อุบลราชธานี',
            ]),
        ];

        foreach ($sets as $type => $items) {
            foreach ($items as $i => [$th, $en, $cn]) {
                MasterOption::firstOrCreate(
                    ['type' => $type, 'name_th' => $th],
                    ['name_en' => $en, 'name_cn' => $cn, 'sort_order' => $i + 1, 'status' => 1]
                );
            }
        }

        // เกณฑ์เกรดเริ่มต้น
        $grades = [
            ['A', 80, 100, true],
            ['B', 70, 79.99, true],
            ['C', 60, 69.99, true],
            ['D', 50, 59.99, true],
            ['F', 0, 49.99, false],
        ];

        foreach ($grades as $i => [$grade, $min, $max, $pass]) {
            GradeSetting::firstOrCreate(
                ['grade' => $grade],
                ['min_score' => $min, 'max_score' => $max, 'is_pass' => $pass, 'sort_order' => $i + 1]
            );
        }
    }
}
