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
                'department_name' => 'สาธารณสุขศาสตร์',
                'faculty' => 'คณะสาธารณสุข'
            ],
            [
                'department_name' => 'โภชนศาสตร์ การกำหนดอาหาร และอาหารปลอดภัย',
                'faculty' => 'คณะสาธารณสุข'
            ],
            [
                'department_name' => 'อนามัยสิ่งแวดล้อม',
                'faculty' => 'คณะสาธารณสุข'
            ],
            [
                'department_name' => 'อาชีวอนามัยและความปลอดภัย',
                'faculty' => 'คณะสาธารณสุข'
            ],
        ]);
    }
}
