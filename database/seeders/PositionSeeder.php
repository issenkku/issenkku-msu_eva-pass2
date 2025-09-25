<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('positions')->insert([
            [
                'name' => 'คณบดี',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'รองคณบดีฝ่ายบริหารและแผน',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'รองคณบดีฝ่ายวิชาการและนวัตกรรมการเรียนรู้',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'รองคณบดีฝ่ายวิจัยและประกันคุณภาพ',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'รองคณบดีฝ่ายพัฒนานิสิตและบัณฑิตศึกษา',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'รองคณบดีฝ่ายเทคโนโลยีสารสนเทศและโครงสร้างพื้นฐาน',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ผู้ช่วยคณบดีฝ่ายวิเทศสัมพันธ์',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ผู้ช่วยคณบดีฝ่ายกิจการพิเศษและภาพลักษณ์องค์กร',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'หัวหน้าสำนักงานเลขานุการ',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'หัวหน้ากลุ่มงานบริหาร',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'หัวหน้ากลุ่มงานนโยบายแผนและคลัง',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'หัวหน้ากลุ่มงานวิชาการและพัฒนานิสิต',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'หัวหน้ากลุ่มงานบริการวิชาการ',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ผู้อำนวยการศูนย์บริการวิชาการ',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'หัวหน้าสาขาอนามัยสิ่งแวดล้อม',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'หัวหน้าสาขาโภชนาการและการกำหนดอาหาร',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'หัวหน้าสาขาอาชีวอนามัยและความปลอดภัย',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'หัวหน้าสาขาเทคโนโลยีทางสุขภาพและความปลอดภัย',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'อาจารย์',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'เจ้าหน้าที่',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
