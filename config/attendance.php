<?php

return [
    /*
     * เกณฑ์เปอร์เซ็นต์การเข้าเรียนขั้นต่ำ (สำหรับแจ้งเตือน / สิทธิ์สอบ)
     * นักเรียนที่เข้าเรียนต่ำกว่าค่านี้จะถูกไฮไลต์ว่า "เสี่ยงหมดสิทธิ์สอบ"
     * ใช้ร่วมกับ flag is_count_as_present ของ Attendance Status Master (ไม่ hardcode ชื่อสถานะ)
     * ปรับได้ผ่าน env: ATTENDANCE_MIN_PERCENT
     */
    'min_percent' => (float) env('ATTENDANCE_MIN_PERCENT', 80),
];
