<?php

namespace App\Imports;

use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Spatie\Permission\Models\Role;

class UsersImport implements ToCollection
{
    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;

    public function collection(Collection $rows)
    {
        // Keep compatibility with Maatwebsite's ToCollection contract
        $this->processRows($rows);
    }

    // Expose row processing so controllers can feed rows from any worksheet
    public function processRows(Collection $rows): void
    {
        foreach ($rows->skip(1) as $index => $row) {
            try {
                $rowArray = $row->toArray();

                if ($this->isEmptyRow($rowArray)) {
                    $this->skipped++;
                    continue;
                }

                $mappedData = [
                    'prefix' => $rowArray[0] ?? null,           // คำนำหน้า
                    'name' => $rowArray[1] ?? null,             // ชื่อ-สกุล
                    'employee_id' => $rowArray[2] ?? null,      // รหัสพนักงาน
                    'department' => $rowArray[3] ?? null,       // สาขาวิชา
                    'position' => $rowArray[4] ?? null,         // ตำแหน่ง
                    'personnel_type' => $rowArray[5] ?? null,   // ประเภทบุคลากร
                    'email' => $rowArray[6] ?? null,            // อีเมล
                    'phone' => $rowArray[7] ?? null,            // เบอร์โทร
                    'bio' => $rowArray[8] ?? null,              // ประวัติการศึกษา
                    'password' => $rowArray[9] ?? null,         // รหัสผ่าน
                    'status' => $rowArray[10] ?? null,          // สถานะ
                    'role' => $rowArray[11] ?? null,            // บทบาท
                ];

                Log::info('Processing row '.($index + 2), ['mapped_data' => $mappedData]);

                // Validate required fields
                if (! $this->validateRequiredFields($mappedData, $index)) {
                    continue;
                }

                // Handle position
                $positionName = trim($mappedData['position']);
                $position = Positions::firstOrCreate(
                    ['name' => $positionName],
                    ['name' => $positionName]
                );

                // Handle department
                $departmentName = trim($mappedData['department']);
                $department = Departments::firstOrCreate(
                    ['department_name' => $departmentName],
                    ['department_name' => $departmentName]
                );

                // Handle password
                $password = $mappedData['password'] ?? 'password123';
                if (empty(trim($password))) {
                    $password = 'password123';
                }

                // Normalize and sanitize
                $email = strtolower(trim($mappedData['email']));
                $rawPhone = trim($mappedData['phone']);
                $digits = preg_replace('/\D+/', '', $rawPhone);
                // Format to 3-3-4 if 10 digits, else keep original
                if (strlen($digits) === 10) {
                    $phone = substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6);
                } else {
                    $phone = $rawPhone;
                }

                $status = trim($mappedData['status'] ?? 'active');
                if (!in_array(strtolower($status), ['active', 'inactive'])) {
                    $status = 'active';
                }

                $userData = [
                    'prefix' => trim($mappedData['prefix']),
                    'name' => trim($mappedData['name']),
                    'position_id' => $position->id,
                    'personnel_type' => trim($mappedData['personnel_type']),
                    'department_id' => $department->id,
                    'employee_id' => trim($mappedData['employee_id']),
                    'email' => $email,
                    'phone' => $phone,
                    'password' => Hash::make($password),
                    'bio' => ! empty(trim($mappedData['bio'] ?? '')) ? trim($mappedData['bio']) : null,
                    'status' => $status,
                ];

                // Only remove completely empty values, but keep null values for nullable fields
                $userData = array_filter($userData, function ($value, $key) {
                    $nullableFields = ['bio'];
                    if (in_array($key, $nullableFields) && is_null($value)) {
                        return true;
                    }

                    return $value !== null && $value !== '';
                }, ARRAY_FILTER_USE_BOTH);

                $existingUser = User::where('employee_id', trim($mappedData['employee_id']))->first();

                if ($existingUser) {
                    $existingUser->update($userData);
                    $user = $existingUser;
                    Log::info('Updated user: '.$mappedData['employee_id']);
                    $this->updated++;
                } else {
                    $user = User::create($userData);
                    Log::info('Created user: '.$mappedData['employee_id']);
                    $this->created++;
                }

                // Handle role assignment
                $roleName = trim($mappedData['role'] ?? '');
                if (! empty($roleName)) {
                    $role = Role::firstOrCreate(['name' => $roleName]);
                    $user->syncRoles([$role->name]);
                    Log::info("Assigned role {$roleName} to user: ".$mappedData['employee_id']);
                }

            } catch (\Exception $e) {
                Log::error('Error processing row '.($index + 2).': '.$e->getMessage(), [
                    'row_data' => $row->toArray(),
                    'error' => $e->getTraceAsString(),
                ]);
                $this->skipped++;
                continue;
            }
        }
    }

    // Optional import stats to show in UI
    public function stats(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
        ];
    }

    /**
     * Check if row is empty
     */
    private function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, function ($value) {
            return ! empty(trim($value ?? ''));
        }));
    }

    /**
     * Validate required fields
     */
    private function validateRequiredFields(array $data, int $index): bool
    {
        $requiredFields = ['prefix', 'email', 'phone', 'employee_id', 'name', 'position', 'department'];

        foreach ($requiredFields as $field) {
            if (empty(trim($data[$field] ?? ''))) {
                Log::warning("Missing required field '{$field}' in row ".($index + 2), [
                    'field_value' => $data[$field] ?? 'null',
                    'all_data' => $data,
                ]);

                return false;
            }
        }

        return true;
    }
}
