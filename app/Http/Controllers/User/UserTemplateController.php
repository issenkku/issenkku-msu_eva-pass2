<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\Permission\Models\Role;

class UserTemplateController extends Controller
{
    public function download(): BinaryFileResponse
    {
        $headers = [
            'prefix' => 'คำนำหน้า',
            'name' => 'ชื่อ-นามสกุล',
            'employee_id' => 'รหัสพนักงาน',
            'department' => 'สาขาวิชา',
            'position' => 'ตำแหน่ง',
            'personnel_type' => 'ประเภทบุคลากร',
            'email' => 'อีเมล',
            'phone' => 'เบอร์โทร',
            'bio' => 'ประวัติการศึกษา',
            'password' => 'รหัสผ่าน',
            'status' => 'สถานะ',
            'role' => 'บทบาท',
        ];

        $sampleData = [
            [
                'คำนำหน้า' => 'นาย',
                'ชื่อ-นามสกุล' => 'สมชาย ใจดี',
                'รหัสพนักงาน' => '00001',
                'สาขาวิชา' => 'หน่วยห้องสมุด',
                'ตำแหน่ง' => 'กลุ่มงานบริหาร',
                'ประเภทบุคลากร' => 'สนับสนุน',
                'อีเมล' => 'somchai@university.ac.th',
                'เบอร์โทร' => '081-234-5678',
                'ประวัติการศึกษา' => 'ปริญญาเอก สาขาวิทยาการคอมพิวเตอร์',
                'รหัสผ่าน' => '123456',
                'สถานะ' => 'active',
                'บทบาท' => 'admin',
            ],
        ];

        $roles = Role::pluck('name')->toArray();
        $options = [
            ['ฟิลด์', 'ตัวเลือก (ถ้ามี)', 'ตัวอย่าง/หมายเหตุ'],
            ['คำนำหน้า', 'นาย | นาง | นางสาว', 'บังคับกรอก'],
            ['ประเภทบุคลากร', 'สายวิชาการ | สนับสนุน', 'บังคับกรอก'],
            ['สถานะ', 'active | inactive', 'ค่าเริ่มต้น active'],
            ['บทบาท', implode(' | ', $roles), 'ปล่อยว่างได้ หากไม่ต้องการกำหนด'],
            ['หมายเหตุ', '', 'หัวตารางบรรทัด 1 ข้อมูลเริ่มบรรทัด 2'],
        ];

        return Excel::download(
            new class($sampleData, $headers, $options) implements WithMultipleSheets {
                private array $data;
                private array $headers;
                private array $options;

                public function __construct(array $data, array $headers, array $options)
                {
                    $this->data = $data;
                    $this->headers = $headers;
                    $this->options = $options;
                }

                public function sheets(): array
                {
                    return [
                        // Sheet 1: Template
                        new class($this->data, $this->headers) implements FromArray, WithHeadings, WithStyles, WithColumnWidths {
                            private array $data;
                            private array $headers;

                            public function __construct(array $data, array $headers)
                            {
                                $this->data = $data;
                                $this->headers = $headers;
                            }

                            public function array(): array
                            {
                                return $this->data;
                            }

                            public function headings(): array
                            {
                                return array_values($this->headers);
                            }

                            public function styles(Worksheet $sheet)
                            {
                                $sheet->getStyle('A1:Z100')->getFont()->setName('TH Sarabun New')->setSize(14);
                                $sheet->getStyle('A1:Z1')->getFont()->setBold(true);
                            }

                            public function columnWidths(): array
                            {
                                return [
                                    'B' => 25,
                                    'C' => 15,
                                    'D' => 30,
                                    'E' => 25,
                                    'G' => 24,
                                    'H' => 18,
                                    'I' => 35,
                                ];
                            }
                        },

                        // Sheet 2: Options / Help
                        new class($this->options) implements FromArray, WithStyles, WithColumnWidths {
                            private array $options;

                            public function __construct(array $options)
                            {
                                $this->options = $options;
                            }

                            public function array(): array
                            {
                                return $this->options;
                            }

                            public function styles(Worksheet $sheet)
                            {
                                // Sanitize sheet title: Excel forbids \\ / * ? : [ ] and length > 31
                                $rawTitle = 'ตัวเลือก/คำอธิบาย';
                                $safeTitle = preg_replace('/[\\\\\/*?:\[\]]/u', '-', $rawTitle);
                                if (function_exists('mb_substr')) {
                                    $safeTitle = mb_substr($safeTitle, 0, 31);
                                } else {
                                    $safeTitle = substr($safeTitle, 0, 31);
                                }
                                $sheet->setTitle($safeTitle);
                                $sheet->getStyle('A1:E100')->getFont()->setName('TH Sarabun New')->setSize(14);
                                $sheet->getStyle('A1:E1')->getFont()->setBold(true);
                            }

                            public function columnWidths(): array
                            {
                                return [
                                    'A' => 24,
                                    'B' => 50,
                                    'C' => 45,
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
