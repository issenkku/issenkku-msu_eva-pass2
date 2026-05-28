<?php

namespace App\Imports;

use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use App\Models\User;
use Illuminate\Database\QueryException;
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

    private array $errors = [];

    public function collection(Collection $rows)
    {
        $this->processRows($rows);
    }

    public function processRows(Collection $rows): void
    {
        foreach ($rows->skip(1) as $index => $row) {
            try {
                $rowArray = $row->toArray();

                if ($this->isEmptyRow($rowArray)) {
                    $this->skipped++;

                    continue;
                }

                $mappedData = $this->mapRow($rowArray);

                Log::info('Processing row '.($index + 2), ['mapped_data' => $mappedData]);

                if (! $this->validateRequiredFields($mappedData, $index)) {
                    continue;
                }

                $positionName = trim((string) $mappedData['position']);
                $position = Positions::firstOrCreate(
                    ['name' => $positionName],
                    ['name' => $positionName]
                );

                $departmentName = trim((string) $mappedData['department']);
                $department = Departments::firstOrCreate(
                    ['department_name' => $departmentName],
                    ['department_name' => $departmentName]
                );

                $password = $mappedData['password'] ?? 'password123';
                if (empty(trim((string) $password))) {
                    $password = 'password123';
                }

                $email = strtolower(trim((string) $mappedData['email']));
                $rawPhone = trim((string) $mappedData['phone']);
                $digits = preg_replace('/\D+/', '', $rawPhone);
                $phone = strlen($digits) === 10
                    ? substr($digits, 0, 3).'-'.substr($digits, 3, 3).'-'.substr($digits, 6)
                    : $rawPhone;

                $status = trim((string) ($mappedData['status'] ?? 'active'));
                if (! in_array(strtolower($status), ['active', 'inactive'], true)) {
                    $status = 'active';
                }

                $educationHistory = $this->normalizeEducationHistory([
                    [
                        'graduation_year' => $mappedData['graduation_year'] ?? null,
                        'degree' => $mappedData['degree'] ?? $mappedData['bio'] ?? null,
                        'university' => $mappedData['university'] ?? null,
                    ],
                ]);

                $userData = [
                    'prefix' => trim((string) $mappedData['prefix']),
                    'name' => trim((string) $mappedData['name']),
                    'position_id' => $position->id,
                    'personnel_type' => trim((string) $mappedData['personnel_type']),
                    'department_id' => $department->id,
                    'employee_id' => trim((string) $mappedData['employee_id']),
                    'email' => $email,
                    'phone' => $phone,
                    'password' => Hash::make((string) $password),
                    'bio' => $this->buildEducationBio($educationHistory, $mappedData['bio'] ?? null),
                    'education_history' => $educationHistory,
                    'status' => $status,
                ];

                $userData = array_filter($userData, function ($value, $key) {
                    $nullableFields = ['bio', 'education_history'];

                    if (in_array($key, $nullableFields, true) && is_null($value)) {
                        return true;
                    }

                    return $value !== null && $value !== '';
                }, ARRAY_FILTER_USE_BOTH);

                $existingUser = User::where('employee_id', trim((string) $mappedData['employee_id']))->first();

                if ($existingUser) {
                    $userData['education_history'] = $this->mergeEducationHistory(
                        $existingUser->education_history_entries,
                        $educationHistory
                    );
                    $userData['bio'] = $this->buildEducationBio($userData['education_history']);
                    $existingUser->update($userData);
                    $user = $existingUser;
                    Log::info('Updated user: '.$mappedData['employee_id']);
                    $this->updated++;
                } else {
                    $user = User::create($userData);
                    Log::info('Created user: '.$mappedData['employee_id']);
                    $this->created++;
                }

                $roleNames = collect(preg_split('/[|,]/', (string) ($mappedData['role'] ?? '')))
                    ->map(fn ($role) => trim($role))
                    ->filter()
                    ->unique()
                    ->values();

                if ($roleNames->isNotEmpty()) {
                    $resolvedRoles = $roleNames->map(function ($roleName) {
                        return Role::firstOrCreate(['name' => $roleName])->name;
                    })->all();

                    $user->syncRoles($resolvedRoles);
                    Log::info('Assigned roles '.implode(', ', $resolvedRoles).' to user: '.$mappedData['employee_id']);
                }
            } catch (\Exception $e) {
                $duplicateMessage = $this->buildDuplicateMessage($e, $mappedData ?? [], $index + 2);
                if ($duplicateMessage !== null) {
                    $this->errors[] = [
                        'row' => $index + 2,
                        'error' => $duplicateMessage,
                    ];
                }

                Log::error('Error processing row '.($index + 2).': '.$e->getMessage(), [
                    'row_data' => $row->toArray(),
                    'error' => $e->getTraceAsString(),
                ]);
                $this->skipped++;
            }
        }
    }

    public function stats(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
        ];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function mapRow(array $rowArray): array
    {
        $isNewFormat = count($rowArray) >= 14;

        return [
            'prefix' => $rowArray[0] ?? null,
            'name' => $rowArray[1] ?? null,
            'employee_id' => $rowArray[2] ?? null,
            'department' => $rowArray[3] ?? null,
            'position' => $rowArray[4] ?? null,
            'personnel_type' => $rowArray[5] ?? null,
            'email' => $rowArray[6] ?? null,
            'phone' => $rowArray[7] ?? null,
            'graduation_year' => $isNewFormat ? ($rowArray[8] ?? null) : null,
            'degree' => $isNewFormat ? ($rowArray[9] ?? null) : null,
            'university' => $isNewFormat ? ($rowArray[10] ?? null) : null,
            'bio' => $isNewFormat ? null : ($rowArray[8] ?? null),
            'password' => $isNewFormat ? ($rowArray[11] ?? null) : ($rowArray[9] ?? null),
            'status' => $isNewFormat ? ($rowArray[12] ?? null) : ($rowArray[10] ?? null),
            'role' => $isNewFormat ? ($rowArray[13] ?? null) : ($rowArray[11] ?? null),
        ];
    }

    private function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, function ($value) {
            return ! empty(trim((string) ($value ?? '')));
        }));
    }

    private function validateRequiredFields(array $data, int $index): bool
    {
        $requiredFields = ['prefix', 'email', 'phone', 'employee_id', 'name', 'position', 'department'];

        foreach ($requiredFields as $field) {
            if (empty(trim((string) ($data[$field] ?? '')))) {
                Log::warning("Missing required field '{$field}' in row ".($index + 2), [
                    'field_value' => $data[$field] ?? 'null',
                    'all_data' => $data,
                ]);

                return false;
            }
        }

        return true;
    }

    private function buildDuplicateMessage(\Exception $e, array $mappedData, int $rowNumber): ?string
    {
        if (! ($e instanceof QueryException)) {
            return null;
        }

        $errorInfo = $e->errorInfo;
        $mysqlErrorCode = $errorInfo[1] ?? null;
        if ($mysqlErrorCode !== 1062) {
            return null;
        }

        $message = $e->getMessage();
        $field = null;
        if (str_contains($message, 'users_email_unique')) {
            $field = 'email';
        } elseif (str_contains($message, 'users_phone_unique')) {
            $field = 'phone';
        } elseif (str_contains($message, 'users_employee_id_unique')) {
            $field = 'employee_id';
        }

        if ($field === null) {
            return 'ข้อมูลซ้ำกับรายการที่มีอยู่แล้ว';
        }

        $value = $mappedData[$field] ?? null;
        $employeeId = $mappedData['employee_id'] ?? null;

        if (! empty($employeeId)) {
            return "ข้อมูลซ้ำ: {$field} ({$value}) สำหรับรหัสพนักงาน {$employeeId}";
        }

        return "ข้อมูลซ้ำ: {$field} ({$value})";
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
        if (! empty($educationHistory)) {
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

        return filled($fallbackBio) ? trim((string) $fallbackBio) : null;
    }

    private function mergeEducationHistory(array $existingEntries, ?array $newEntries): ?array
    {
        return $this->normalizeEducationHistory([
            ...$existingEntries,
            ...($newEntries ?? []),
        ]);
    }
}
