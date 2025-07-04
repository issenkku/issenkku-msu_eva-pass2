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
                "employee_id" => "123456789",
                "password" => bcrypt("StrongPass123"),
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
                "name" => "Test User",
                "employee_id" => "999999999",
                "password" => bcrypt("StrongPassword999"),
                "email" => "test.user@kkumail.com",
                "phone" => "0812345678",
                "personnel_type" => "วิชาการ",
                "bio" => null,
                "status" => "active",
                "position_id" => 2,
                "department_id" => 2,
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);
    }
}
