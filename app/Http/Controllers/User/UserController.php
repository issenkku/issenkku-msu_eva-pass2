<?php

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Imports\UsersImport;
use App\Models\Setting\Departments;
use App\Models\Setting\JobLevel;
use App\Models\Setting\Positions;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class UserController extends Controller
{
    // create page to add use
    /**
     * เมธอด: store
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ บันทึกข้อมูล User และเปลี่ยนเส้นทางไปที่ route users.index
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: Redirect ไปที่ route users.index
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'prefix' => 'required|string|max:50',
            'name' => 'required|string|max:100|unique:users,name',
            'employee_id' => 'required|max:20|unique:users,employee_id',
            'password' => ['required', 'max:50'],
            'email' => 'required|string|lowercase|email:rfc|max:50|unique:users,email',
            'phone' => 'required|max:20|unique:users,phone',
            'personnel_type' => 'required|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'education_history' => 'nullable|array',
            'education_history.*.graduation_year' => 'nullable|digits:4',
            'education_history.*.degree' => 'nullable|string|max:255',
            'education_history.*.university' => 'nullable|string|max:255',
            'status' => 'required|max:20',
            'position_id' => 'required|exists:positions,id',
            'job_level_id' => 'nullable|exists:job_levels,id',
            'department_id' => 'required|exists:departments,id',
            'roles' => 'nullable|array',
            'roles.*' => 'string|exists:roles,name',
        ], [
            'name.unique' => 'ชื่อ-นามสกุลนี้ถูกใช้ไปแล้ว',
            'employee_id.unique' => 'รหัสพนักงานนี้ถูกใช้ไปแล้ว',
            'email.unique' => 'อีเมลนี้ถูกใช้ไปแล้ว',
            'phone.unique' => 'เบอร์โทรนี้ถูกใช้ไปแล้ว',
        ]);

        $educationHistory = $this->normalizeEducationHistory($request->input('education_history', []));

        $user = User::create([
            'prefix' => $request->prefix,
            'name' => $request->name,
            'employee_id' => $request->employee_id,
            'password' => Hash::make($request->password),
            'email' => $request->email,
            'phone' => $request->phone,
            'personnel_type' => $request->personnel_type,
            'bio' => $this->buildEducationBio($educationHistory, $request->bio),
            'education_history' => $educationHistory,
            'status' => $request->status,
            'position_id' => $request->position_id,
            'job_level_id' => $request->job_level_id,
            'department_id' => $request->department_id,
        ]);

        // ป้องกัน assignRole ถ้าไม่มีค่า role
        $user->syncRoles($request->input('roles', []));

        return redirect()->route('users.index')->with('success', 'เพิ่มผู้ใช้เรียบร้อยแล้ว');
    }

    // Add this method to your UserController

    /**
     * เมธอด: checkUnique
     * จุดประสงค์: ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ข้อมูล JSON
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function checkUnique(Request $request)
    {
        $field = $request->input('field');
        $value = $request->input('value');
        $userId = $request->input('user_id'); // Exclude current user when editing

        // Validate the field name to prevent SQL injection
        $allowedFields = ['employee_id', 'email', 'phone'];
        
        if (!in_array($field, $allowedFields)) {
            return response()->json(['error' => 'Invalid field'], 400);
        }

        // Clean phone number for comparison (remove dashes)
        if ($field === 'phone') {
            $value = preg_replace('/\D/', '', $value);
        }

        // Build query
        $query = User::where($field, $value);

        // Exclude current user when editing
        if ($userId) {
            $query->where('id', '!=', $userId);
        }

        $exists = $query->exists();

        return response()->json([
            'unique' => !$exists,
            'field' => $field,
            'message' => $exists ? 'ข้อมูลนี้ถูกใช้งานแล้ว' : 'ข้อมูลสามารถใช้ได้'
        ]);
    }

    /**
     * เมธอด: import
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ บันทึกข้อมูล UsersImport และเปลี่ยนเส้นทางไปที่ route users.index
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: Redirect ไปที่ route users.index
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function import(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            ]);

            // Start database transaction
            DB::beginTransaction();
            $import = new UsersImport();

            // Load all worksheets to ensure we process the sheet that contains the data,
            // even if it is not the first sheet in the workbook.
            $uploaded = $request->file('import_file');
            $filePath = $uploaded->getRealPath();

            $spreadsheet = IOFactory::load($filePath);
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $rowsArray = $sheet->toArray(null, false, false, false);
                $rowsCollection = collect($rowsArray)->map(function ($row) {
                    return collect($row);
                });
                $import->processRows($rowsCollection);
            }

            DB::commit();

            if (method_exists($import, 'stats')) {
                $stats = $import->stats();
                $total = ($stats['created'] ?? 0) + ($stats['updated'] ?? 0);
                $importErrors = method_exists($import, 'errors') ? $import->errors() : [];
                $redirect = redirect()->route('users.index')->with('import_stats', $stats);
                if (!empty($importErrors)) {
                    $redirect->with('import_errors', $importErrors);
                }

                if ($total === 0) {
                    return $redirect->with('warning', 'นำเข้าเสร็จแล้ว แต่ไม่มี?รายการที่ถูกต้อง จึงไม่ได้เพิ่ม/อัปเดตข้อมูล');
                }

                return $redirect->with('success', "นำเข้าสำเร็จ: เพิ่ม {$stats['created']} รายการ, อัปเดต {$stats['updated']} รายการ, ข้าม {$stats['skipped']} รายการ");
            }

            return redirect()->route('users.index')->with('success', 'นำเข้าข้อมูลผู้ใช้สำเร็จ');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            Log::error('Excel validation error', ['errors' => $e->errors()]);

            return redirect()->back()
                ->with('error', 'ข้อมูลในไฟล์ไม่ถูกต้อง')
                ->with('validation_errors', $e->errors());
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาดในการนำเข้าข้อมูล: '.$e->getMessage());
        }
    }

    /**
     * เมธอด: downloadTemplate
     * จุดประสงค์: ส่งไฟล์สำหรับดาวน์โหลด
     * อินพุต: ไม่มี
     * เอาต์พุต: ไฟล์ดาวน์โหลด
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function downloadTemplate()
    {
        $headers = [
            'prefix' => 'คำนำหน้า',
            'name' => 'ชื่อ-สกุล',
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

        // Create sample data
        $sampleData = [
            [
                'คำนำหน้า' => 'นาย',
                'ชื่อ-สกุล' => 'สมชาย ใจดี',
                'รหัสพนักงาน' => 'EMP001',
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
            [
                'คำนำหน้า' => 'นาง',
                'ชื่อ-สกุล' => 'สมหญิง ใจดี',
                'รหัสพนักงาน' => 'EMP002',
                'สาขาวิชา' => 'สาธารณสุขศาสตรมหาบัณฑิต',
                'ตำแหน่ง' => 'คณบดี',
                'ประเภทบุคลากร' => 'สนับสนุน',
                'อีเมล' => 'somying@university.ac.th',
                'เบอร์โทร' => '097-535-7378',
                'ประวัติการศึกษา' => 'ปริญญาเอก สาขาวิทยาการคอมพิวเตอร์',
                'รหัสผ่าน' => '123456',
                'สถานะ' => 'active',
                'บทบาท' => 'ผู้บริหาร',
            ],
            [
                'คำนำหน้า' => 'นางสาว',
                'ชื่อ-สกุล' => 'พรุ่งนี้ ใจดี',
                'รหัสพนักงาน' => 'EMP003',
                'สาขาวิชา' => 'สาธารณสุขศาสตรมหาบัณฑิต',
                'ตำแหน่ง' => 'ผู้ช่วยคณบดีฝ่ายวิเทศสัมพันธ์',
                'ประเภทบุคลากร' => 'วิชาการ',
                'อีเมล' => 'tomorrow@university.ac.th',
                'เบอร์โทร' => '089-761-1745',
                'ประวัติการศึกษา' => 'ปริญญาเอก สาขาวิทยาการคอมพิวเตอร์',
                'รหัสผ่าน' => '123456',
                'สถานะ' => 'active',
                'บทบาท' => 'ผู้ประเมิน',
            ],
            [
                'คำนำหน้า' => 'นาย',
                'ชื่อ-สกุล' => 'วันนี้ ใจดี',
                'รหัสพนักงาน' => 'EMP004',
                'สาขาวิชา' => 'สำนักงานเลขานุการ',
                'ตำแหน่ง' => 'หัวหน้าสำนักงานเลขานุการ',
                'ประเภทบุคลากร' => 'สนับสนุน',
                'อีเมล' => 'today@university.ac.th',
                'เบอร์โทร' => '089-875-7564',
                'ประวัติการศึกษา' => 'ปริญญาเอก สาขาวิทยาการคอมพิวเตอร์',
                'รหัสผ่าน' => '123456',
                'สถานะ' => 'active',
                'บทบาท' => 'ผู้รับการประเมิน',
            ],
            [
                'คำนำหน้า' => 'นาย',
                'ชื่อ-สกุล' => 'ชีวิต ใจดี',
                'รหัสพนักงาน' => 'EMP005',
                'สาขาวิชา' => 'กลุ่มงานบริหาร',
                'ตำแหน่ง' => 'รองคณบดีฝ่ายบริหารและแผน',
                'ประเภทบุคลากร' => 'วิชาการ',
                'อีเมล' => 'life@university.ac.th',
                'เบอร์โทร' => '083-329-7451',
                'ประวัติการศึกษา' => 'ปริญญาเอก สาขาวิทยาการคอมพิวเตอร์',
                'รหัสผ่าน' => '123456',
                'สถานะ' => 'active',
                'บทบาท' => 'กรรมการ',
            ],
        ];

        $options = [
            ['สาขาวิชา', 'ตำแหน่ง', 'ประเภทบุคลากร', 'สถานะ', 'บทบาท'],
            ['สำนักงานเลขานุการ', 'คณบดี', 'วิชาการ', 'active', 'admin'],
            ['กลุ่มงานบริหาร', 'รองคณบดีฝ่ายบริหารและแผน', 'สนับสนุน', 'inactive', 'ผู้บริหาร'],
            ['กลุ่มงานนโยบายแผนและคลัง', 'รองคณบดีฝ่ายวิชาการและนวัตกรรมการเรียนรู้', 'บริหาร', '', 'ผู้ประเมิน'],
            ['กลุ่มงานวิชาการและพัฒนานิสิต', 'รองคณบดีฝ่ายวิจัยและประกันคุณภาพ', '', '', 'ผู้รับการประเมิน'],
            ['ศูนย์บริการวิชาการ', 'รองคณบดีฝ่ายพัฒนานิสิตและบัณฑิตศึกษา', '', '', 'กรรมการ'],
            ['สาธารณสุขศาสตรบัณฑิต', 'รองคณบดีฝ่ายเทคโนโลยีสารสนเทศและโครงสร้างพื้นฐาน', '', '', ''],
            ['สาขาอนามัยสิ่งแวดล้อม', 'ผู้ช่วยคณบดีฝ่ายวิเทศสัมพันธ์', '', '', ''],
            ['สาขาโภชนาการและการกำหนดอาหาร', 'ผู้ช่วยคณบดีฝ่ายกิจการพิเศษและภาพลักษณ์องค์กร', '', '', ''],
            ['สาขาอาชีวอนามัยและความปลอดภัย', 'หัวหน้าสำนักงานเลขานุการ', '', '', ''],
            ['สาธารณสุขศาสตรมหาบัณฑิต', 'หัวหน้ากลุ่มงานบริหาร', '', '', ''],
            ['วิทยาศาสตรมหาบัณฑิต สาขาเทคโนโลยีทางสุขภาพและความปลอดภัย', 'หัวหน้ากลุ่มงานนโยบายแผนและคลัง', '', '', ''],
            ['สาธารณสุขศาสตรดุษฎีบัณฑิต', 'หัวหน้ากลุ่มงานวิชาการและพัฒนานิสิต', '', '', ''],
            ['ปรัชญาดุษฎีบัณฑิต สาขาเทคโนโลยีทางสุขภาพและความปลอดภัย', 'ผู้อำนวยการศูนย์บริการวิชาการ', '', '', ''],
            ['','หัวหน้ากลุ่มงานบริการวิชาการ', '', '', ''],
            ['', 'หัวหน้าสาขาอนามัยสิ่งแวดล้อม', '', '', ''],
            ['', 'หัวหน้าสาขาโภชนาการและการกำหนดอาหาร', '', '', ''],
            ['', 'หัวหน้าสาขาอาชีวอนามัยและความปลอดภัย', '', '', ''],
            ['', 'หัวหน้าสาขาเทคโนโลยีทางสุขภาพและความปลอดภัย', '', '', ''],
            ['', 'อาจารย์', '', '', ''],
            ['', 'เจ้าหน้าที่', '', '', ''],
        ];

        return Excel::download(
            new class($sampleData, $headers, $options) implements WithMultipleSheets
            {
                private $data;
                private $headers;
                private $options;

                /**
                 * เมธอด: __construct
                 * จุดประสงค์: ประมวลผลคำขอ
                 * อินพุต: พารามิเตอร์ $data, พารามิเตอร์ $headers, พารามิเตอร์ $options
                 * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
                 * @param mixed $data ค่าที่รับเข้ามา
                 * @param mixed $headers ค่าที่รับเข้ามา
                 * @param mixed $options ค่าที่รับเข้ามา
                 * @return mixed ผลลัพธ์ของการทำงาน
                 */
                public function __construct($data, $headers, $options)
                {
                    $this->data = $data;
                    $this->headers = $headers;
                    $this->options = $options;
                }

                /**
                 * เมธอด: sheets
                 * จุดประสงค์: ประมวลผลคำขอ
                 * อินพุต: ไม่มี
                 * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
                 * @param void ไม่มีพารามิเตอร์
                 * @return mixed ผลลัพธ์ของการทำงาน
                 */
                public function sheets(): array
                {
                    return [
                        // Sheet 1: Template with sample users
                        new class($this->data, $this->headers) implements FromArray, WithHeadings, WithStyles, WithColumnWidths  {
                            private $data;
                            private $headers;

                            /**
                             * เมธอด: __construct
                             * จุดประสงค์: ประมวลผลคำขอ
                             * อินพุต: พารามิเตอร์ $data, พารามิเตอร์ $headers
                             * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
                             * @param mixed $data ค่าที่รับเข้ามา
                             * @param mixed $headers ค่าที่รับเข้ามา
                             * @return mixed ผลลัพธ์ของการทำงาน
                             */
                            public function __construct($data, $headers)
                            {
                                $this->data = $data;
                                $this->headers = $headers;
                            }

                            /**
                             * เมธอด: array
                             * จุดประสงค์: ประมวลผลคำขอ
                             * อินพุต: ไม่มี
                             * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
                             * @param void ไม่มีพารามิเตอร์
                             * @return mixed ผลลัพธ์ของการทำงาน
                             */
                            public function array(): array
                            {
                                return $this->data;
                            }

                            /**
                             * เมธอด: headings
                             * จุดประสงค์: ประมวลผลคำขอ
                             * อินพุต: ไม่มี
                             * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
                             * @param void ไม่มีพารามิเตอร์
                             * @return mixed ผลลัพธ์ของการทำงาน
                             */
                            public function headings(): array
                            {
                                return array_values($this->headers);
                            }

                            /**
                             * เมธอด: styles
                             * จุดประสงค์: ประมวลผลคำขอ
                             * อินพุต: โมเดล Worksheet
                             * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
                             * @param Worksheet $sheet ค่าที่รับเข้ามา
                             * @return mixed ผลลัพธ์ของการทำงาน
                             */
                            public function styles(Worksheet $sheet)
                            {
                                $sheet->getStyle('A1:Z100')->getFont()->setName('TH Sarabun New')->setSize(14);
                                $sheet->getStyle('A1:Z1')->getFont()->setBold(true);
                            }

                            /**
                             * เมธอด: columnWidths
                             * จุดประสงค์: ประมวลผลคำขอ
                             * อินพุต: ไม่มี
                             * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
                             * @param void ไม่มีพารามิเตอร์
                             * @return mixed ผลลัพธ์ของการทำงาน
                             */
                            public function columnWidths(): array
                            {
                                return [
                                    'B' => 25,
                                    'C' => 12,
                                    'D' => 30,
                                    'E' => 25,
                                    'G' => 22,
                                    'H' => 15,
                                    'I' => 25,
                                ];
                            }
                        },

                        // Sheet 2: Options list
                        new class($this->options) implements FromArray, WithStyles, WithColumnWidths  {
                            private $options;

                            /**
                             * เมธอด: __construct
                             * จุดประสงค์: ประมวลผลคำขอ
                             * อินพุต: พารามิเตอร์ $options
                             * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
                             * @param mixed $options ค่าที่รับเข้ามา
                             * @return mixed ผลลัพธ์ของการทำงาน
                             */
                            public function __construct($options)
                            {
                                $this->options = $options;
                            }

                            /**
                             * เมธอด: array
                             * จุดประสงค์: ประมวลผลคำขอ
                             * อินพุต: ไม่มี
                             * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
                             * @param void ไม่มีพารามิเตอร์
                             * @return mixed ผลลัพธ์ของการทำงาน
                             */
                            public function array(): array
                            {
                                return $this->options;
                            }

                            /**
                             * เมธอด: styles
                             * จุดประสงค์: ประมวลผลคำขอ
                             * อินพุต: โมเดล Worksheet
                             * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
                             * @param Worksheet $sheet ค่าที่รับเข้ามา
                             * @return mixed ผลลัพธ์ของการทำงาน
                             */
                            public function styles(Worksheet $sheet)
                            {
                                $sheet->setTitle('ตัวเลือก');
                                $sheet->getStyle('A1:E100')->getFont()->setName('TH Sarabun New')->setSize(14);
                                $sheet->getStyle('A1:E1')->getFont()->setBold(true);
                            }

                            /**
                             * เมธอด: columnWidths
                             * จุดประสงค์: ประมวลผลคำขอ
                             * อินพุต: ไม่มี
                             * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
                             * @param void ไม่มีพารามิเตอร์
                             * @return mixed ผลลัพธ์ของการทำงาน
                             */
                            public function columnWidths(): array
                            {
                                return [
                                    'A' => 30,
                                    'B' => 30,
                                    'E' => 15,
                                ];
                            }
                        },
                    ];
                }
            },
            'user_import_template.xlsx'
        );
    }

    /**
     * เมธอด: index
     * จุดประสงค์: แสดงหน้า user.management.index
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: หน้า user.management.index
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function index(Request $request)
    {
        // เริ่มต้น Query Builder พร้อมกับ Eager Loading ที่จำเป็น
        $query = User::with(['position', 'jobLevel', 'roles']);

        // --- เพิ่ม Logic การค้นหา ---
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            // กรองข้อมูลจากคอลัมน์ 'name' และสามารถเพิ่มคอลัมน์อื่นได้
            // เช่น ค้นหาจากรหัสพนักงานด้วย
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%'.$searchTerm.'%')
                    ->orWhere('employee_id', 'like', '%'.$searchTerm.'%');
            });
        }
        // -------------------------

        // --- Filter ที่มีอยู่เดิม ---
        if ($request->filled('department_id')) {
            $query->whereIn('department_id', (array) $request->department_id);
        }
        if ($request->filled('position_id')) {
            $query->whereIn('position_id', (array) $request->position_id);
        }
        if ($request->filled('job_level_id')) {
            $query->whereIn('job_level_id', (array) $request->job_level_id);
        }
        if ($request->filled('personnel_type')) {
            $query->whereIn('personnel_type', (array) $request->personnel_type);
        }
        if ($request->filled('status')) {
            $query->whereIn('status', (array) $request->status);
        }
        // -------------------------

        // ดึงข้อมูลพร้อม Pagination และส่งต่อ Query String ทั้งหมด
        $users = $query->latest()->paginate(10)->withQueryString();
        $users->getCollection()->transform(function ($user) {
            $array = $user->toArray();
            $array['role_names'] = $user->roles->pluck('name')->toArray();
            $array['education_history_entries'] = $user->education_history_entries;
            return $array;
        });

        // ดึงข้อมูลสำหรับ Dropdown/Filter
        $departments = Departments::all();
        $positions = Positions::all();
        $jobLevels = JobLevel::all();
        $roles = Role::all();
        $user = null; // สำหรับฟอร์มสร้างผู้ใช้ใหม่

        return view('user.management.index', compact('users', 'departments', 'positions', 'jobLevels', 'roles', 'user'));
    }

    /**
     * เมธอด: update
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ บันทึกข้อมูล และเปลี่ยนเส้นทางไปที่ route users.index
     * อินพุต: ข้อมูลจากคำขอ, โมเดล User
     * เอาต์พุต: Redirect ไปที่ route users.index
     * @param Request $request ค่าที่รับเข้ามา
     * @param User $user ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $rules = [
            'prefix' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'phone' => [
                'required',
                'max:20',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'personnel_type' => 'required|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'education_history' => 'nullable|array',
            'education_history.*.graduation_year' => 'nullable|digits:4',
            'education_history.*.degree' => 'nullable|string|max:255',
            'education_history.*.university' => 'nullable|string|max:255',
            'status' => 'required|max:20',
            'position_id' => 'required|exists:positions,id',
            'job_level_id' => 'nullable|exists:job_levels,id',
            'department_id' => 'required|exists:departments,id',
            'roles' => 'nullable|array',
            'roles.*' => 'string|exists:roles,name',
            'employee_id' => [
                'required',
                'max:20',
                Rule::unique('users', 'employee_id')->ignore($user->id),
            ],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:50',
                Rule::unique('users')->ignore($user->id),
            ],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', 'max:50'];
        }

        $validated = $request->validate($rules);
        $educationHistory = $this->normalizeEducationHistory($validated['education_history'] ?? []);
        $validated['education_history'] = $educationHistory;
        $validated['bio'] = $this->buildEducationBio($educationHistory, $validated['bio'] ?? null);

        $user->fill(collect($validated)->except('password')->toArray());

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();
        // syncRoles เพื่อบันทึกบทบาทที่เลือกไว้
        $user->syncRoles($request->input('roles', []));

        return redirect()->route('users.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * เมธอด: destroy
     * จุดประสงค์: ลบข้อมูล และเปลี่ยนเส้นทางไปที่ route users.index
     * อินพุต: โมเดล User
     * เอาต์พุต: Redirect ไปที่ route users.index
     * @param User $user ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'ลบเรียบร้อยแล้ว');
    }
    private function normalizeEducationHistory(array $entries): ?array
    {
        $normalized = collect($entries)
            ->filter(fn ($entry) => is_array($entry))
            ->map(fn (array $entry) => [
                'graduation_year' => filled($entry['graduation_year'] ?? null) ? (string) $entry['graduation_year'] : null,
                'degree' => filled($entry['degree'] ?? null) ? trim((string) $entry['degree']) : null,
                'university' => filled($entry['university'] ?? null) ? trim((string) $entry['university']) : null,
            ])
            ->filter(fn (array $entry) => filled($entry['graduation_year']) || filled($entry['degree']) || filled($entry['university']))
            ->values()
            ->all();

        return $normalized === [] ? null : $normalized;
    }

    private function buildEducationBio(?array $educationHistory, ?string $fallbackBio = null): ?string
    {
        if (!empty($educationHistory)) {
            return collect($educationHistory)
                ->map(function (array $entry) {
                    return collect([
                        $entry['graduation_year'] ?? null,
                        $entry['degree'] ?? null,
                        $entry['university'] ?? null,
                    ])->filter(fn ($value) => filled($value))->implode(' ');
                })
                ->filter(fn ($line) => filled($line))
                ->implode(PHP_EOL);
        }

        return filled($fallbackBio) ? trim($fallbackBio) : null;
    }
}
