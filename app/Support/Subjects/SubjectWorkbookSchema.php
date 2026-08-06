<?php

namespace App\Support\Subjects;

final class SubjectWorkbookSchema
{
    public const DATA_SHEET = 'ข้อมูลรายวิชา';

    public const INSTRUCTIONS_SHEET = 'คำแนะนำ';

    public const MAX_ROWS = 5000;

    public const HEADERS = [
        'รหัสรายวิชา', 'ชื่อรายวิชา (ไทย)', 'ชื่อรายวิชา (อังกฤษ)', 'หน่วยกิตรวม',
        'หน่วยกิตบรรยาย', 'หน่วยกิตปฏิบัติ', 'หน่วยกิตศึกษาด้วยตนเอง',
        'ชั่วโมงบรรยาย', 'ชั่วโมงปฏิบัติ', 'ชั่วโมงศึกษาด้วยตนเอง',
    ];

    public const LEGACY_HEADERS = [
        'รหัสรายวิชา', 'ชื่อรายวิชา (ไทย)', 'ชื่อรายวิชา (อังกฤษ)', 'หน่วยกิตรวม',
        'หน่วยกิตบรรยาย', 'หน่วยกิตปฏิบัติ', 'หน่วยกิตศึกษาด้วยตนเอง',
    ];
}
