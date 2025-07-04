<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                "prefix" => "นางสาว",
                "name" => "Yanasorn Wongpakdee",
                "employee_id" => "123456789",
                "password" => Hash::make("StrongPass123"), // เข้ารหัสรหัสผ่าน
                "email" => "yanasorn.w@kkumail.com",
                "phone" => "0871593293",
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
                "employee_id" => "0415647161",
                "password" => Hash::make("StrongPass123"),
                "email" => "darknightsch@gmail.com",
                "phone" => "0941528156",
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
                "name" => "ใหญ่ ใหญ่",
                "employee_id" => "0415647161",
                "password" => Hash::make("StrongPass123"),
                "email" => "bigbig@example.com",
                "phone" => "0941528156",
                "personnel_type" => "สนับสนุน",
                "bio" => null,
                "status" => "active",
                "position_id" => 1,
                "department_id" => 1,
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);
    }
}
