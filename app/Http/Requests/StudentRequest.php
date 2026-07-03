<?php

namespace App\Http\Requests;

use App\Models\Student;
use App\Models\StudentGuardian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('id');

        return [
            'student_code' => ['nullable', 'string', 'max:30', Rule::unique('students', 'student_code')->ignore($studentId)],
            'image_base64' => 'nullable|string',
            'name_th' => 'required|string|max:255',
            'name_cn' => 'nullable|string|max:255',
            'citizen_id' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'race_id' => 'nullable|exists:master_options,id',
            'nationality_id' => 'nullable|exists:master_options,id',
            'religion_id' => 'nullable|exists:master_options,id',
            'blood_type_id' => 'nullable|exists:master_options,id',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'chronic_disease' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'status' => 'required|in:' . implode(',', Student::STATUSES),
            'note' => 'nullable|string|max:2000',

            // ที่อยู่ 2 ชุด
            'addresses' => 'nullable|array',
            'addresses.*.house_no' => 'nullable|string|max:50',
            'addresses.*.moo' => 'nullable|string|max:20',
            'addresses.*.subdistrict' => 'nullable|string|max:255',
            'addresses.*.district' => 'nullable|string|max:255',
            'addresses.*.province_id' => 'nullable|exists:master_options,id',
            'addresses.*.postal_code' => 'nullable|string|max:10',

            // ผู้ปกครองหลายคน
            'guardians' => 'nullable|array',
            'guardians.*.guardian_type_id' => 'nullable|exists:master_options,id',
            'guardians.*.name' => 'required_with:guardians|string|max:255',
            'guardians.*.name_cn' => 'nullable|string|max:255',
            'guardians.*.age' => 'nullable|integer|min:0|max:150',
            'guardians.*.race_id' => 'nullable|exists:master_options,id',
            'guardians.*.nationality_id' => 'nullable|exists:master_options,id',
            'guardians.*.religion_id' => 'nullable|exists:master_options,id',
            'guardians.*.living_status' => 'nullable|in:' . implode(',', StudentGuardian::LIVING_STATUSES),
            'guardians.*.address' => 'nullable|string|max:255',
            'guardians.*.phone' => 'nullable|string|max:50',
            'guardians.*.occupation' => 'nullable|string|max:255',
            'guardians.*.workplace_address' => 'nullable|string|max:255',
            'guardians.*.relationship' => 'nullable|string|max:255',
            'primary_guardian' => 'nullable|integer',

            // ประวัติการศึกษา
            'educations' => 'nullable|array',
            'educations.*.school_name' => 'required_with:educations|string|max:255',
            'educations.*.school_location' => 'nullable|string|max:255',
            'educations.*.last_level' => 'nullable|string|max:255',
            'educations.*.gpa' => 'nullable|numeric|min:0|max:4',
            'educations.*.graduated_at' => 'nullable|string|max:20',
            'educations.*.note' => 'nullable|string|max:255',

            // เอกสาร checklist
            'documents' => 'nullable|array',
            'documents.*.is_received' => 'nullable|boolean',
            'documents.*.note' => 'nullable|string|max:255',
            'document_files' => 'nullable|array',
            'document_files.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf',
        ];
    }
}
