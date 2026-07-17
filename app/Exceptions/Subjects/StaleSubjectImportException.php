<?php

namespace App\Exceptions\Subjects;

use RuntimeException;

final class StaleSubjectImportException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('ข้อมูลรายวิชาถูกเปลี่ยนหลังสร้าง Preview กรุณาอัปโหลดไฟล์ใหม่');
    }
}
