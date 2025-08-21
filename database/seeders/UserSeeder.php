<?php

namespace Database\Seeders;

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
                'name' => 'Yanasorn Wongpakdee',
                'employee_id' => '001',
                'password' => Hash::make('password001'),
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
                'name' => 'กอ ขอ',
                'employee_id' => '002',
                'password' => Hash::make('password002'),
                'email' => 'evaluator001@gmail.com',
                'phone' => '098-521-1821',
                'personnel_type' => 'วิชาการ',
                'bio' => null,
                'status' => 'active',
                'position_id' => 4,
                'department_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prefix' => 'นาย',
                'name' => 'test evaluatee1',
                'employee_id' => '003',
                'password' => Hash::make('password003'),
                'email' => 'evaluatee001@gmail.com',
                'phone' => '084-515-5454',
                'personnel_type' => 'วิชาการ',
                'bio' => null,
                'status' => 'active',
                'position_id' => 3,
                'department_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prefix' => 'นาง',
                'name' => 'test evaluatee2',
                'employee_id' => '004',
                'password' => Hash::make('password004'),
                'email' => 'evaluatee002@gmail.com',
                'phone' => '084-632-3284',
                'personnel_type' => 'วิชาการ',
                'bio' => null,
                'status' => 'active',
                'position_id' => 2,
                'department_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prefix' => 'นาง',
                'name' => 'test director',
                'employee_id' => '005',
                'password' => Hash::make('password005'),
                'email' => 'dircector001@gmail.com',
                'phone' => '088-724-7534',
                'personnel_type' => 'วิชาการ',
                'bio' => null,
                'status' => 'active',
                'position_id' => 1,
                'department_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prefix' => 'นาย',
                'name' => 'test committee',
                'employee_id' => '006',
                'password' => Hash::make('password006'),
                'email' => 'committee001@gmail.com',
                'phone' => '088-724-7532',
                'personnel_type' => 'วิชาการ',
                'bio' => null,
                'status' => 'active',
                'position_id' => 2,
                'department_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
