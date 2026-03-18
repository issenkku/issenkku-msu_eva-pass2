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
        $departments = [
            'สำนักงานเลขานุการ',
            'กลุ่มงานบริหาร',
            'กลุ่มงานวิชาการและพัฒนานิสิต',
            'กลุ่มงานนโยบาย แผนและคลัง',
            'ประธานหลักสูตรสาธารณสุขศาสตรบัณฑิต',
            'หลักสูตร วท.บ.อาชีวอนามัยและความปลอดภัย',
            'หลักสูตร วท.บ.โภชนาการและการกำหนดอาหาร',
            'หลักสูตร วท.บ.อนามัยสิ่งแวดล้อม',
            'หลักสูตรสาธารณสุขศาสตรมหาบัณฑิต',
            'หลักสูตร วท.ม.เทคโนโลยีสุขภาพและความปลอดภัย',
            'หลักสูตรสาธารณสุขศาสตรดุษฎีบัณฑิต',
            'หลักสูตร ปร.ด.เทคโนโลยีสุขภาพและความปลอดภัย',
        ];

        DB::table('departments')->upsert(
            array_map(
                static fn (string $department): array => [
                    'department_name' => $department,
                ],
                $departments
            ),
            ['department_name'],
            ['department_name']
        );
    }
}
