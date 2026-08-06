<?php

namespace App\Exports\Subjects;

use App\Support\Subjects\SubjectWorkbookSchema;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class SubjectInstructionsSheet implements FromArray, WithColumnWidths, WithStyles, WithTitle
{
    public function array(): array
    {
        return [
            ['ฟิลด์', 'ตัวอย่าง', 'กติกา'],
            ['รหัสรายวิชา', 'CS101', 'บังคับ; ระบบตัดช่องว่างและแปลงอักษรอังกฤษเป็นตัวพิมพ์ใหญ่'],
            ['ชื่อรายวิชา (ไทย)', 'วิทยาการคอมพิวเตอร์', 'ไม่บังคับแยกช่อง; ต้องมีชื่อไทยหรืออังกฤษอย่างน้อยหนึ่งช่อง และรองรับข้อความทุกภาษา'],
            ['ชื่อรายวิชา (อังกฤษ)', 'Computer Science', 'ไม่บังคับแยกช่อง; ต้องมีชื่อไทยหรืออังกฤษอย่างน้อยหนึ่งช่อง และช่องว่างหมายถึงล้างค่าเดิม'],
            ['หน่วยกิต', '3 / 3 / 0 / 6', 'จำนวนเต็มตั้งแต่ 0 ขึ้นไป; ทุกค่าเป็นอิสระต่อกัน; ช่องว่างถือเป็น 0'],
            ['ชั่วโมง', '3 / 2 / 1', 'จำนวนเต็มตั้งแต่ 0 ขึ้นไป; ทุกค่าเป็นอิสระต่อกัน; ช่องว่างถือเป็น 0'],
            ['ข้อจำกัดไฟล์', '.xlsx', 'ไม่เกิน 10 MB และ 5,000 แถว; ห้ามใช้ Formula'],
        ];
    }

    public function title(): string
    {
        return SubjectWorkbookSchema::INSTRUCTIONS_SHEET;
    }

    public function columnWidths(): array
    {
        return ['A' => 28, 'B' => 28, 'C' => 72];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
