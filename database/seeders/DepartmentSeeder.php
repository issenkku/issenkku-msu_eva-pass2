<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('departments')->insert([
            [
                'department_name' => 'หน่วยจัดการศึกษา',
                'faculty' => 'คณะสาธารณสุข'
            ],
            [
                'department_name' => 'หน่วยจัดการงานทั่วไป',
                'faculty' => 'คณะสาธารณสุข'
            ],
            [
                'department_name' => 'หน่วยห้องสมุด',
                'faculty' => 'คณะสาธารณสุข'
            ],
            [
                'department_name' => 'หน่วยการต่างประเทศ',
                'faculty' => 'คณะสาธารณสุข'
            ],
        ]);
    }
}
