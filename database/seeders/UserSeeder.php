<?php

namespace Database\Seeders;

use App\Models\User;
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
        DB::table('users')->insert([
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
                'position_id' => 1,
                'department_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
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
                'position_id' => 7,
                'department_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
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
                'position_id' => 7,
                'department_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
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
                'position_id' => 18,
                'department_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
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
                'position_id' => 18,
                'department_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
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
                'position_id' => 1,
                'department_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
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
                'position_id' => 2,
                'department_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // User::factory()->count(50)->create();
    }
}
