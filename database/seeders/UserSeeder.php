<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                "prefix" => "นาย",
                "name" => "Admin User",
                "employee_id" => "001",
                "password" => Hash::make("admin001"),
                "email" => "admin1@kkumail.com",
                "phone" => "087-084-0715",
                "personnel_type" => "วิชาการ",
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
