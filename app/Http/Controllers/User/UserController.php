<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Imports\UsersImport;
use App\Models\Setting\Departments;
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
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class UserController extends Controller
{
    // create page to add use
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'prefix' => 'required|string|max:10',
            'name' => 'required|string|max:100|unique:users,name',
            'employee_id' => 'required|max:20|unique:users,employee_id',
            'password' => ['required', 'max:50'],
            'email' => 'required|string|lowercase|email:rfc|max:50|unique:users,email',
            'phone' => 'required|max:20|unique:users,phone',
            'personnel_type' => 'required|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'status' => 'required|max:20',
            'position_id' => 'required|exists:positions,id',
            'department_id' => 'required|exists:departments,id',
            'role' => 'nullable|string',
        ], [
            'name.unique' => 'ชื่อ-นามสกุลนี้ถูกใช้ไปแล้ว',
            'employee_id.unique' => 'รหัสพนักงานนี้ถูกใช้ไปแล้ว',
            'email.unique' => 'อีเมลนี้ถูกใช้ไปแล้ว',
            'phone.unique' => 'เบอร์โทรนี้ถูกใช้ไปแล้ว',
        ]);

        $user = User::create([
            'prefix' => $request->prefix,
            'name' => $request->name,
            'employee_id' => $request->employee_id,
            'password' => Hash::make($request->password),
            'email' => $request->email,
            'phone' => $request->phone,
            'personnel_type' => $request->personnel_type,
            'bio' => $request->bio,
            'status' => $request->status,
            'position_id' => $request->position_id,
            'department_id' => $request->department_id,
        ]);

        // ป้องกัน assignRole ถ้าไม่มีค่า role
        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route('users.index')->with('success', 'เพิ่มผู้ใช้เรียบร้อยแล้ว');
    }

    // Add this method to your UserController

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

    public function import(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            ]);

            // Start database transaction
            DB::beginTransaction();
            $import = new UsersImport;
            Excel::import($import, $request->file('import_file'));
            DB::commit();

            return redirect()->route('users.index')->with('success', 'เพิ่มผู้ใช้เรียบร้อยแล้ว');
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

                public function __construct($data, $headers, $options)
                {
                    $this->data = $data;
                    $this->headers = $headers;
                    $this->options = $options;
                }

                public function sheets(): array
                {
                    return [
                        // Sheet 1: Template with sample users
                        new class($this->data, $this->headers) implements FromArray, WithHeadings, WithStyles, WithColumnWidths  {
                            private $data;
                            private $headers;

                            public function __construct($data, $headers)
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

                            public function __construct($options)
                            {
                                $this->options = $options;
                            }

                            public function array(): array
                            {
                                return $this->options;
                            }

                            public function styles(Worksheet $sheet)
                            {
                                $sheet->setTitle('ตัวเลือก');
                                $sheet->getStyle('A1:E100')->getFont()->setName('TH Sarabun New')->setSize(14);
                                $sheet->getStyle('A1:E1')->getFont()->setBold(true);
                            }

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

    public function index(Request $request)
    {
        // เริ่มต้น Query Builder พร้อมกับ Eager Loading ที่จำเป็น
        $query = User::with(['position', 'roles']);

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
        if ($request->filled('personnel_type')) {
            $query->whereIn('personnel_type', (array) $request->personnel_type);
        }
        if ($request->filled('status')) {
            $query->whereIn('status', (array) $request->status);
        }
        // -------------------------

        // ดึงข้อมูลพร้อม Pagination และส่งต่อ Query String ทั้งหมด
        $users = $query->latest()->paginate(10)->withQueryString();

        // ดึงข้อมูลสำหรับ Dropdown/Filter
        $departments = Departments::all();
        $positions = Positions::all();
        $roles = Role::all();
        $user = null; // สำหรับฟอร์มสร้างผู้ใช้ใหม่

        return view('user.management.index', compact('users', 'departments', 'positions', 'roles', 'user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $rules = [
            'prefix' => 'required|string|max:10',
            'name' => 'required|string|max:100',
            'phone' => [
                'required',
                'max:20',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'personnel_type' => 'required|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'status' => 'required|max:20',
            'position_id' => 'required|exists:positions,id',
            'department_id' => 'required|exists:departments,id',
            'role' => 'nullable|string',
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

        $user->fill(collect($validated)->except('password')->toArray());

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();
        // syncRoles เพื่อบันทึกบทบาทที่เลือกไว้
        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route('users.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'ลบเรียบร้อยแล้ว');
    }
}