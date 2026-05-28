<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserTemplateController extends Controller
{
    public function download(): BinaryFileResponse
    {
        $headers = [
            'คำนำหน้า',
            'ชื่อ-นามสกุล',
            'รหัสพนักงาน',
            'สาขาวิชา',
            'ตำแหน่ง',
            'ประเภทบุคลากร',
            'อีเมล',
            'เบอร์โทร',
            'ปีที่จบ',
            'วุฒิการศึกษา',
            'มหาวิทยาลัยที่จบ',
            'รหัสผ่าน',
            'สถานะ',
            'บทบาท',
        ];

        $sampleData = [[
            'นาย',
            'สมชาย ใจดี',
            '00001',
            'สาขาสาธารณสุขศาสตร์',
            'อาจารย์',
            'วิชาการ',
            'somchai@university.ac.th',
            '081-234-5678',
            '2562',
            'ปรัชญาดุษฎีบัณฑิต',
            'มหาวิทยาลัยมหาสารคาม',
            '123456',
            'active',
            'admin',
        ]];

        $roles = Role::pluck('name')->implode(' | ');
        $helpRows = [
            ['ฟิลด์', 'ตัวอย่าง/ตัวเลือก', 'หมายเหตุ'],
            ['คำนำหน้า', 'นาย | นาง | นางสาว', 'บังคับกรอก'],
            ['ประเภทบุคลากร', 'วิชาการ | สนับสนุน | บริหาร', 'บังคับกรอก'],
            ['สถานะ', 'active | inactive', 'ถ้าไม่กรอกจะใช้ active'],
            ['บทบาท', $roles, 'ใส่ได้หลายบทบาทโดยคั่นด้วย | หรือ ,'],
            ['ประวัติการศึกษา', '1 แถวต่อ 1 วุฒิ', 'ถ้ามีหลายวุฒิให้เพิ่มหลายแถวโดยใช้ข้อมูลบุคลากรคนเดิม'],
        ];

        return Excel::download(
            new class($sampleData, $headers, $helpRows) implements WithMultipleSheets
            {
                public function __construct(
                    private array $sampleData,
                    private array $headers,
                    private array $helpRows,
                ) {}

                public function sheets(): array
                {
                    return [
                        new class($this->sampleData, $this->headers) implements FromArray, WithColumnWidths, WithHeadings, WithStyles
                        {
                            public function __construct(
                                private array $sampleData,
                                private array $headers,
                            ) {}

                            public function array(): array
                            {
                                return $this->sampleData;
                            }

                            public function headings(): array
                            {
                                return $this->headers;
                            }

                            public function styles(Worksheet $sheet)
                            {
                                $sheet->setTitle('Template');
                                $sheet->getStyle('A1:N100')->getFont()->setName('TH Sarabun New')->setSize(14);
                                $sheet->getStyle('A1:N1')->getFont()->setBold(true);
                            }

                            public function columnWidths(): array
                            {
                                return [
                                    'A' => 14,
                                    'B' => 28,
                                    'C' => 16,
                                    'D' => 24,
                                    'E' => 22,
                                    'F' => 18,
                                    'G' => 26,
                                    'H' => 18,
                                    'I' => 12,
                                    'J' => 24,
                                    'K' => 28,
                                    'L' => 16,
                                    'M' => 12,
                                    'N' => 20,
                                ];
                            }
                        },
                        new class($this->helpRows) implements FromArray, WithColumnWidths, WithStyles
                        {
                            public function __construct(private array $helpRows) {}

                            public function array(): array
                            {
                                return $this->helpRows;
                            }

                            public function styles(Worksheet $sheet)
                            {
                                $sheet->setTitle('คำอธิบาย');
                                $sheet->getStyle('A1:C100')->getFont()->setName('TH Sarabun New')->setSize(14);
                                $sheet->getStyle('A1:C1')->getFont()->setBold(true);
                            }

                            public function columnWidths(): array
                            {
                                return [
                                    'A' => 18,
                                    'B' => 40,
                                    'C' => 42,
                                ];
                            }
                        },
                    ];
                }
            },
            'user_import_template.xlsx'
        );
    }
}
