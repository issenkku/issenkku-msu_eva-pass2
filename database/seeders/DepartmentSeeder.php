<?php

namespace Database\Seeders;

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
                'department_name' => 'สำนักงานเลขานุการ',
            ],
            [
                'department_name' => 'กลุ่มงานบริหาร',
            ],
            [
                'department_name' => 'กลุ่มงานนโยบายแผนและคลัง',
            ],
            [
                'department_name' => 'กลุ่มงานวิชาการและพัฒนานิสิต',
            ],
            [
                'department_name' => 'ศูนย์บริการวิชาการ',
            ],
            [
                'department_name' => 'สาธารณสุขศาสตรบัณฑิต',
            ],
            [
                'department_name' => 'สาขาอนามัยสิ่งแวดล้อม',
            ],
            [
                'department_name' => 'สาขาโภชนาการและการกำหนดอาหาร',
            ],
            [
                'department_name' => 'สาขาอาชีวอนามัยและความปลอดภัย',
            ],
            [
                'department_name' => 'สาธารณสุขศาสตรมหาบัณฑิต',
            ],
            [
                'department_name' => 'วิทยาศาสตรมหาบัณฑิต สาขาเทคโนโลยีทางสุขภาพและความปลอดภัย',
            ],
            [
                'department_name' => 'สาธารณสุขศาสตรดุษฎีบัณฑิต',
            ],
            [
                'department_name' => 'ปรัชญาดุษฎีบัณฑิต สาขาเทคโนโลยีทางสุขภาพและความปลอดภัย',
            ],
        ]);
    }
}
