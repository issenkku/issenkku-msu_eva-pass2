<?php

namespace Database\Seeders;

use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();

        $users = [
            [
                'prefix' => 'นางสาว',
                'name' => 'Admin',
                'employee_id' => '001',
                'password' => Hash::make('password'),
                'email' => 'admin1@kkumail.com',
                'phone' => '087-084-0715',
                'personnel_type' => 'สนับสนุน',
                'bio' => null,
                'status' => 'active',
                'position_name' => 'คณบดี',
                'department_name' => 'สำนักงานเลขานุการคณะ',
            ],
            [
                'prefix' => 'นาย',
                'name' => 'ผู้ประเมิน 1',
                'employee_id' => '002',
                'password' => Hash::make('password'),
                'email' => 'evaluator1@gmail.com',
                'phone' => '098-521-1823',
                'personnel_type' => 'วิชาการ',
                'bio' => null,
                'status' => 'active',
                'position_name' => 'อาจารย์',
                'department_name' => 'กลุ่มงานวิชาการและพัฒนานิสิต',
            ],
            [
                'prefix' => 'นาย',
                'name' => 'ผู้ประเมิน 2',
                'employee_id' => '003',
                'password' => Hash::make('password'),
                'email' => 'evaluator2@gmail.com',
                'phone' => '098-521-1814',
                'personnel_type' => 'สนับสนุน',
                'bio' => null,
                'status' => 'active',
                'position_name' => 'อาจารย์',
                'department_name' => 'กลุ่มงานวิชาการและพัฒนานิสิต',
            ],
            [
                'prefix' => 'นาย',
                'name' => 'ผู้ถูกประเมิน 1',
                'employee_id' => '004',
                'password' => Hash::make('password'),
                'email' => 'evaluatee1@gmail.com',
                'phone' => '084-515-5445',
                'personnel_type' => 'วิชาการ',
                'bio' => null,
                'status' => 'active',
                'position_name' => 'ผู้ช่วยศาสตราจารย์',
                'department_name' => 'กลุ่มงานวิชาการและพัฒนานิสิต',
            ],
            [
                'prefix' => 'นาง',
                'name' => 'ผู้ถูกประเมิน 2',
                'employee_id' => '005',
                'password' => Hash::make('password'),
                'email' => 'evaluatee2@gmail.com',
                'phone' => '084-632-3846',
                'personnel_type' => 'สนับสนุน',
                'bio' => null,
                'status' => 'active',
                'position_name' => 'ผู้ช่วยศาสตราจารย์',
                'department_name' => 'กลุ่มงานวิชาการและพัฒนานิสิต',
            ],
            [
                'prefix' => 'นาง',
                'name' => 'ผู้บริหาร',
                'employee_id' => '006',
                'password' => Hash::make('password'),
                'email' => 'manager@gmail.com',
                'phone' => '084-632-3287',
                'personnel_type' => 'สนับสนุน',
                'bio' => null,
                'status' => 'active',
                'position_name' => 'หัวหน้ากลุ่มงานบริหาร',
                'department_name' => 'กลุ่มงานบริหาร',
            ],
            [
                'prefix' => 'นาง',
                'name' => 'กรรมการ',
                'employee_id' => '007',
                'password' => Hash::make('password'),
                'email' => 'director@gmail.com',
                'phone' => '095-741-6415',
                'personnel_type' => 'สนับสนุน',
                'bio' => null,
                'status' => 'active',
                'position_name' => 'รองคณบดีฝ่ายบริหารและแผนงาน',
                'department_name' => 'กลุ่มงานบริหาร',
            ],
        ];

        $payload = collect($users)->map(function (array $user) use ($timestamp) {
            return [
                'prefix' => $user['prefix'],
                'name' => $user['name'],
                'employee_id' => $user['employee_id'],
                'password' => $user['password'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'personnel_type' => $user['personnel_type'],
                'bio' => $user['bio'],
                'status' => $user['status'],
                'position_id' => $this->resolvePositionId($user['position_name']),
                'department_id' => $this->resolveDepartmentId($user['department_name']),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        })->all();

        DB::table('users')->upsert(
            $payload,
            ['email'],
            [
                'prefix',
                'name',
                'employee_id',
                'password',
                'phone',
                'personnel_type',
                'bio',
                'status',
                'position_id',
                'department_id',
                'updated_at',
            ]
        );
    }

    private function resolvePositionId(string $name): int
    {
        return Positions::firstOrCreate(['name' => $name])->id;
    }

    private function resolveDepartmentId(string $name): int
    {
        return Departments::firstOrCreate(['department_name' => $name])->id;
    }
}
