<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                "prefix" => "นางสาว",
                "name" => "Yanasorn Wongpakdee",
                "employee_id" => "001",
                "password" => Hash::make("admin001"),
                "email" => "admin1@kkumail.com",
                "phone" => "087-084-0715",
                "personnel_type" => "สนับสนุน",
                "bio" => null,
                "status" => "active",
                "position_id" => 1,
                "department_id" => 1,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "prefix" => "นาย",
                "name" => "กอ ขอ",
                "employee_id" => "002",
                "password" => Hash::make("evaluator001"),
                "email" => "evaluator001@gmail.com",
                "phone" => "098-521-1821",
                "personnel_type" => "สนับสนุน",
                "bio" => null,
                "status" => "active",
                "position_id" => 1,
                "department_id" => 1,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "prefix" => "นาย",
                "name" => "test evaluatee1",
                "employee_id" => "003",
                "password" => Hash::make("evaluatee001"),
                "email" => "evaluatee001@gmail.com",
                "phone" => "084-515-5454",
                "personnel_type" => "วิชาการ",
                "bio" => null,
                "status" => "active",
                "position_id" => 2,
                "department_id" => 3,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "prefix" => "นาง",
                "name" => "test evaluatee2",
                "employee_id" => "004",
                "password" => Hash::make("evaluatee002"),
                "email" => "evaluatee002@gmail.com",
                "phone" => "084-632-3284",
                "personnel_type" => "วิชาการ",
                "bio" => null,
                "status" => "active",
                "position_id" => 3,
                "department_id" => 3,
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);
    }
}
