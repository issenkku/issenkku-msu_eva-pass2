<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportStructureFromDbSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('criteria_versions')->insert(array (
  0 => 
  array (
    'id' => 1,
    'version_name' => 'เกณฑ์การประเมินกลุ่มอาจารย์ ปี 2568',
    'created_by' => 1,
    'created_at' => '2025-10-17 07:03:03',
    'updated_at' => '2025-10-17 07:03:03',
  ),
  1 => 
  array (
    'id' => 2,
    'version_name' => 'เกณฑ์การประเมินกลุ่มสายสนับสนุน ปี 2568',
    'created_by' => 1,
    'created_at' => '2025-10-17 07:03:03',
    'updated_at' => '2025-10-17 07:03:03',
  ),
  2 => 
  array (
    'id' => 3,
    'version_name' => 'เกณฑ์การประเมินกลุ่มผู้บริหาร ปี 2568',
    'created_by' => 1,
    'created_at' => '2025-10-17 07:03:03',
    'updated_at' => '2025-10-17 07:03:03',
  ),
));

        DB::table('report_datas')->insert(array (
  0 => 
  array (
    'id' => 2,
    'report_title' => 'เกณฑ์การให้คะแนนการประเมินพนักงานสายวิชาการตามมาตรฐานภาระงาน คณะสาธารณสุขศาสตร์ มหาวิทยาลัยมหาสารคาม (กลุ่มสายสนับสนุน)',
    'report_description' => 'การประเมินคุณภาพการปฏิบัติงานของบุคลากรสายสนับสนุน ประจำปีงบประมาณ 2568',
    'assessment_type' => 'กลุ่มสนับสนุน',
    'comment' => 'ประเมินตามเกณฑ์มาตรฐานของมหาวิทยาลัย',
    'criteria_version_id' => 2,
  ),
  1 => 
  array (
    'id' => 3,
    'report_title' => 'เกณฑ์การให้คะแนนการประเมินพนักงานสายวิชาการตามมาตรฐานภาระงาน คณะสาธารณสุขศาสตร์ มหาวิทยาลัยมหาสารคาม (กลุ่มผู้บริหาร)',
    'report_description' => 'การประเมินคุณภาพการปฏิบัติงานของบุคลากรสายบริหาร ประจำปีงบประมาณ 2568',
    'assessment_type' => 'กลุ่มบริหาร',
    'comment' => 'ประเมินตามเกณฑ์มาตรฐานของมหาวิทยาลัย',
    'criteria_version_id' => 3,
  ),
  2 => 
  array (
    'id' => 36,
    'report_title' => 'เกณฑ์การให้คะแนนการประเมินพนักงานสายวิชาการตามมาตรฐานภาระงาน คณะสาธารณสุขศาสตร์ มหาวิทยาลัยมหาสารคาม (กลุ่มอาจารย์)',
    'report_description' => 'การประเมินคุณภาพการปฏิบัติงานของบุคลากรสายวิชาการ ประจำปีงบประมาณ 2568',
    'assessment_type' => 'กลุ่มวิชาการ',
    'comment' => 'ประเมินตามเกณฑ์มาตรฐานของมหาวิทยาลัย',
    'criteria_version_id' => 1,
  ),
));

        DB::table('categories')->insert(array (
  0 => 
  array (
    'id' => 3,
    'main_categories' => 'ผลสัมฤทธิ์ของการปฏิบัติงาน',
    'sub_categories' => '1. ด้านปริมาณผลงาน',
    'sequence' => 1,
    'criteria_version_id' => 2,
  ),
  1 => 
  array (
    'id' => 4,
    'main_categories' => 'ผลสัมฤทธิ์ของการปฏิบัติงาน',
    'sub_categories' => '2. ด้านคุณภาพผลงาน',
    'sequence' => 2,
    'criteria_version_id' => 2,
  ),
  2 => 
  array (
    'id' => 5,
    'main_categories' => 'ผลสัมฤทธิ์ของการปฏิบัติงาน',
    'sub_categories' => '1. ด้านปริมาณผลงาน',
    'sequence' => 1,
    'criteria_version_id' => 3,
  ),
  3 => 
  array (
    'id' => 6,
    'main_categories' => 'ผลสัมฤทธิ์ของการปฏิบัติงาน',
    'sub_categories' => '2. ด้านคุณภาพผลงาน',
    'sequence' => 2,
    'criteria_version_id' => 3,
  ),
  4 => 
  array (
    'id' => 73,
    'main_categories' => 'ผลสัมฤทธิ์ของการปฏิบัติงาน',
    'sub_categories' => '1. ด้านปริมาณผลงาน',
    'sequence' => 1,
    'criteria_version_id' => 1,
  ),
  5 => 
  array (
    'id' => 76,
    'main_categories' => 'ผลสัมฤทธิ์ของการปฏิบัติงาน',
    'sub_categories' => '2. ด้านคุณภาพผลงาน',
    'sequence' => 2,
    'criteria_version_id' => 1,
  ),
));

        DB::table('evaluation_lists')->insert(array (
  0 => 
  array (
    'id' => 9,
    'name' => '1. ด้านปริมาณผลงาน',
    'sum_score' => '40.00',
    'sequence' => 1,
    'annotation' => '',
    'categorie_id' => 3,
    'criteria_version_id' => 2,
  ),
  1 => 
  array (
    'id' => 10,
    'name' => '2.1 ภาระงานในหน้าที่',
    'sum_score' => '15.00',
    'sequence' => 2,
    'annotation' => '',
    'categorie_id' => 4,
    'criteria_version_id' => 2,
  ),
  2 => 
  array (
    'id' => 11,
    'name' => '2.2 ภาระงานด้านการพัฒนาระบบงาน',
    'sum_score' => '3.00',
    'sequence' => 3,
    'annotation' => '',
    'categorie_id' => 4,
    'criteria_version_id' => 2,
  ),
  3 => 
  array (
    'id' => 12,
    'name' => '2.3 ภาระงานด้านการบริการวิชาการ',
    'sum_score' => '0.50',
    'sequence' => 4,
    'annotation' => 'หมายเหตุ 
เมื่อสิ้นสุดในแต่ละกิจกรรม ต้องจัดส่งรายงานแก่หัวหน้างาน
หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในวงรอบถัดไป
',
    'categorie_id' => 4,
    'criteria_version_id' => 2,
  ),
  4 => 
  array (
    'id' => 13,
    'name' => '2.4 ภาระงานด้านการทำนุบำรุงศิลปวัฒนธรรม',
    'sum_score' => '1.50',
    'sequence' => 5,
    'annotation' => 'หมายเหตุ 
งานบุคคลจะเป็นผู้รวบรวมและรายงานผลคะแนน ท่านสามารถตรวจสอบได้ตลอดเวลา และข้อให้ยืนยันก่อนครบกำหนดวันสุดท้ายของวงรอบการประเมิน ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้',
    'categorie_id' => 4,
    'criteria_version_id' => 2,
  ),
  5 => 
  array (
    'id' => 14,
    'name' => '2.5 ภาระงานด้านการพัฒนาตนเอง',
    'sum_score' => '2.00',
    'sequence' => 6,
    'annotation' => 'หมายเหตุ 
1. การพัฒนาตนเองในส่วนที่คณะเป็นผู้จัด ทางงานบุคคลจะเป็นผู้ดำเนินการบันทึกและรายงานผลให้เอง 
2. หากเป็นการศึกษาอบรมผ่านรูปแบบออนไลน์จะต้องมีใบประกาศนียบัตรหรือเทียบเท่ารับรองว่าได้เข้าพัฒนาตนเองครบ ตามหลักสูตรจริงๆ 
3. ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้ (ในรอบถัดไป ให้ระบุด้วยว่ายังไม่เคยใช้ใน  รอบก่อนหน้านี้)',
    'categorie_id' => 4,
    'criteria_version_id' => 2,
  ),
  6 => 
  array (
    'id' => 15,
    'name' => '2.6 ภาระงานด้านผลงานวิจัย',
    'sum_score' => '2.00',
    'sequence' => 7,
    'annotation' => 'หมายเหตุ 
80-100% คือ ส่ง manuscript + ส่งเล่มสมบูรณ์ + เบิกงวด 2 + ส่งรายงานความก้าวหน้า + เบิกงวด 1 60-79% คือ ส่งเล่มสมบูรณ์ + เบิกงวด 2 + ส่งรายงานความก้าวหน้า + เบิกงวด 1 
40-59% คือ เบิกงวด 2 + ส่งรายงานความก้าวหน้า + เบิกงวด 1 
20-39% คือ ส่งรายงานความก้าวหน้า + เบิกงวด 1 
<20% คือ เบิกงวด 1
',
    'categorie_id' => 4,
    'criteria_version_id' => 2,
  ),
  7 => 
  array (
    'id' => 16,
    'name' => '2.7 ภาระงานด้านการบริหารองค์กรสู่ความเป็นเลิศ',
    'sum_score' => '8.00',
    'sequence' => 8,
    'annotation' => 'หมายเหตุ ผลการดำเนินงานเชิงประจักษ์ เช่น มีชื่อเข้าร่วมการประชุมไม่ต่ำกว่าร้อยละ 80 ฯลฯ ',
    'categorie_id' => 4,
    'criteria_version_id' => 2,
  ),
  8 => 
  array (
    'id' => 17,
    'name' => '1. ปริมาณผลงาน',
    'sum_score' => '40.00',
    'sequence' => 1,
    'annotation' => '',
    'categorie_id' => 5,
    'criteria_version_id' => 3,
  ),
  9 => 
  array (
    'id' => 18,
    'name' => '2.1 ภาระงานด้านการสอน',
    'sum_score' => '10.00',
    'sequence' => 1,
    'annotation' => 'ข้อ 2-4 งานบุคคล ให้ขอข้อมูลจากงานวิชาการ',
    'categorie_id' => 6,
    'criteria_version_id' => 3,
  ),
  10 => 
  array (
    'id' => 19,
    'name' => '2.2 ภาระงานด้านการวิจัย',
    'sum_score' => '6.00',
    'sequence' => 2,
    'annotation' => 'ข้อ 2-4 งานบุคคล ให้ขอข้อมูลจากงานวิชาการ',
    'categorie_id' => 6,
    'criteria_version_id' => 3,
  ),
  11 => 
  array (
    'id' => 20,
    'name' => '2.3 ภาระงานด้านการบริการวิชาการ',
    'sum_score' => '3.00',
    'sequence' => 3,
    'annotation' => 'กรณีที่ข้อ 1 ได้ 0 คะแนน แม้ว่ามีการดำเนินในข้อ 2 จำนวนมาก แต่คะแนนสูงสุดจะได้เท่ากับ 2.4 คะแนน 
                                 ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้  (ในรอบถัดไป ให้ระบุด้วยว่ายังไม่เคยใช้ในรอบก่อนหน้านี้)',
    'categorie_id' => 6,
    'criteria_version_id' => 3,
  ),
  12 => 
  array (
    'id' => 21,
    'name' => '2.4 ภาระงานด้านการทำนุบำรุงศิลปวัฒนธรรม',
    'sum_score' => '3.00',
    'sequence' => 4,
    'annotation' => 'ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้  (ในรอบถัดไป ให้ระบุด้วยว่ายังไม่เคยใช้ในรอบก่อนหน้านี้)',
    'categorie_id' => 6,
    'criteria_version_id' => 3,
  ),
  13 => 
  array (
    'id' => 22,
    'name' => '2.5 ภาระงานด้านการพัฒนาตนเอง',
    'sum_score' => '2.00',
    'sequence' => 5,
    'annotation' => 'เมื่อเข้าร่วมพัฒนาตนเองแล้วเสร็จ ต้องจัดส่งรายงานฯแก่หัวหน้าหน่วยงาน  
                                 หากเป็นการศึกษาด้วยตนเอง (ที่ไม่ได้มีหน่วยงานใดจัดประชุมหรืออบรม เช่น ดูจาก You tube หรือศึกษา  ออนไลน์เอง ฯลฯ  ต้องมีใบประกาศนียบัตรหรือเทียบเท่า รับรองว่าได้เข้าพัฒนาตนเองครบตามหลักสูตรจริงๆ  
                                 ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้  (ในรอบถัดไป ให้ระบุด้วยว่ายังไม่เคยใช้ในรอบก่อนหน้านี้)',
    'categorie_id' => 6,
    'criteria_version_id' => 3,
  ),
  14 => 
  array (
    'id' => 23,
    'name' => '2.6 ภาระงานด้านผลงานวิชาการ',
    'sum_score' => '2.00',
    'sequence' => 6,
    'annotation' => 'กรณีที่ข้อ 1 ได้ 0 คะแนน  แม้ว่ามีการดำเนินในข้อ 2 จนครบ แต่คะแนนสูงสุดจะได้เท่ากับ 1.0 คะแนน   
                                 ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้  (ในรอบถัดไป ให้ระบุด้วยว่ายังไม่เคยใช้ในรอบก่อนหน้านี้)',
    'categorie_id' => 6,
    'criteria_version_id' => 3,
  ),
  15 => 
  array (
    'id' => 24,
    'name' => '2.7 ภาระงานด้านการบริหาร',
    'sum_score' => '2.00',
    'sequence' => 7,
    'annotation' => 'ผลการดำเนินงานเชิงประจักษ์ เช่น มีชื่อเข้าร่วมการประชุมไม่ต่ำกว่าร้อยละ 80  ฯลฯ',
    'categorie_id' => 6,
    'criteria_version_id' => 3,
  ),
  16 => 
  array (
    'id' => 25,
    'name' => '2.8 ภาระงานอื่นๆ',
    'sum_score' => '2.00',
    'sequence' => 8,
    'annotation' => 'ผลการดำเนินงานเชิงประจักษ์ เช่น มีการดำเนินงานตามคำสั่ง ภาพกิจกรรม ฯลฯ',
    'categorie_id' => 6,
    'criteria_version_id' => 3,
  ),
  17 => 
  array (
    'id' => 268,
    'name' => '1. ปริมาณผลงาน',
    'sum_score' => '40.00',
    'sequence' => 1,
    'annotation' => NULL,
    'categorie_id' => 73,
    'criteria_version_id' => 1,
  ),
  18 => 
  array (
    'id' => 277,
    'name' => '2.1 ภาระงานด้านการสอน',
    'sum_score' => '6.00',
    'sequence' => 1,
    'annotation' => 'ข้อมูลข้อที่ (1), (2), (3) และ (4) อาจารย์ไม่ต้องแนบ เนื่องจากเอกสารดังกล่าวได้ส่งให้งานวิชาการตามกำหนดแล้ว                                    ข้อมูลที่อาจารย์ต้องแนบ คือ ข้อที่ (5) Link ค่าคะแนนเฉลี่ยจากทุกรายวิชา',
    'categorie_id' => 76,
    'criteria_version_id' => 1,
  ),
  19 => 
  array (
    'id' => 278,
    'name' => '2.2 ภาระงานด้านการวิจัย',
    'sum_score' => '5.00',
    'sequence' => 2,
    'annotation' => '* การดำเนินงานวิจัยตามแผนให้คิดร้อยละความก้าวหน้า ดังนี้                                     80-100% คือ ส่ง manuscript + ส่งเล่มสมบูรณ์ + เบิกงวด 2 + ส่งรายงานความก้าวหน้า + เบิกงวด 1                                     60- 79% คือ ส่งเล่มสมบูรณ์ + เบิกงวด 2 + ส่งรายงานความก้าวหน้า + เบิกงวด 1                                     40-59% คือ เบิกงวด 2 + ส่งรายงานความก้าวหน้า + เบิกงวด 1                                     20-39% คือ ส่งรายงานความก้าวหน้า + เบิกงวด 1                                     <20% คือ เบิกงวด 1                                    ** รายการข้อที่ 4 ใช้เอกสารแบบฟอร์มรายงานการนำไปใช้ประโยชน์ได้ (ที่ใช้ประกอบการปิดโครงการวิจัยของคณะ)',
    'categorie_id' => 76,
    'criteria_version_id' => 1,
  ),
  20 => 
  array (
    'id' => 279,
    'name' => '2.3 ภาระงานด้านการบริการวิชาการ',
    'sum_score' => '3.00',
    'sequence' => 3,
    'annotation' => '1. กรณีที่ข้อ 1 ได้ 0 คะแนน แม้ว่ามีการดำเนินในข้อ 2 จำนวนมาก แต่คะแนนสูงสุดจะได้เท่ากับ 2.4 คะแนน                                     2. ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้ (ในรอบถัดไป ให้ระบุด้วยว่ายังไม่เคยใช้ในรอบ ก่อนหน้านี้)',
    'categorie_id' => 76,
    'criteria_version_id' => 1,
  ),
  21 => 
  array (
    'id' => 280,
    'name' => '2.4 ภาระงานด้านการทำนุบำรุงศิลปวัฒนธรรม',
    'sum_score' => '3.00',
    'sequence' => 4,
    'annotation' => 'งานบุคคลจะเป็นผู้รวบรวมและรายงานผลคะแนน ท่านสามารถตรวจสอบได้ตลอดเวลา และข้อให้ยืนยันก่อนครบกำหนดวันสุดท้ายของวงรอบการประเมิน ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้',
    'categorie_id' => 76,
    'criteria_version_id' => 1,
  ),
  22 => 
  array (
    'id' => 281,
    'name' => '2.5 ภาระงานด้านการพัฒนาตนเอง',
    'sum_score' => '2.00',
    'sequence' => 5,
    'annotation' => '1. การพัฒนาตนเองในส่วนที่คณะเป็นผู้จัด ทางงานบุคคลจะเป็นผู้ดำเนินการบันทึกและรายงานผลให้เอง                                     2. หากเป็นการศึกษาอบรมผ่านรูปแบบออนไลน์จะต้องมีใบประกาศนียบัตรหรือเทียบเท่ารับรองว่าได้เข้าพัฒนาตนเองครบ ตามหลักสูตรจริงๆ                                     3. ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้ (ในรอบถัดไป ให้ระบุด้วยว่ายังไม่เคยใช้ใน  รอบก่อนหน้านี้)',
    'categorie_id' => 76,
    'criteria_version_id' => 1,
  ),
  23 => 
  array (
    'id' => 282,
    'name' => '2.6 ภาระงานด้านผลงานวิชาการ',
    'sum_score' => '3.00',
    'sequence' => 6,
    'annotation' => '1. กรณีที่ข้อ 1 ได้ 0 คะแนน แม้ว่ามีการดำเนินในข้อ 2 จนครบ แต่คะแนนสูงสุดจะได้เท่ากับ 1.5 คะแนน                                     2. ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้ (ในรอบถัดไป ให้ระบุด้วยว่ายังไม่เคยใช้ใน  รอบก่อนหน้านี้)',
    'categorie_id' => 76,
    'criteria_version_id' => 1,
  ),
  24 => 
  array (
    'id' => 283,
    'name' => '2.7 ภาระงานด้านการบริหารองค์กรสู่ความเป็นเลิศ',
    'sum_score' => '8.00',
    'sequence' => 7,
    'annotation' => 'คะแนนในทุกด้านรวมกันไม่เกิน ร้อยละ 50',
    'categorie_id' => 76,
    'criteria_version_id' => 1,
  ),
));

        DB::table('quantity_main_criterias')->insert(array (
  0 => 
  array (
    'id' => 2,
    'name' => '1. ปริมาณผลงาน',
    'tooltips' => '',
    'criteria_version_id' => 2,
  ),
  1 => 
  array (
    'id' => 3,
    'name' => '1. ปริมาณผลงาน',
    'tooltips' => '<p>1. งานสอนรายวิชาศึกษาทั่วไป ไม่ให้น ามาคิดภาระงานในทุกกรณี (ทั้งเชิงปริมาณและเชิงคุณภาพ) 
                                ยกเว้นผู้ที่ไม่ได้เบิกค่าสอนวิชานั้นๆ สามารถน ามาคิดได้เฉพาะเชิงปริมาณ </p><p>
                                2. วงรอบ </p><p style="margin-left: 25px;"> 
                                วงรอบที่ 1 (1 กันยายน 256x – 28 กุมภาพันธ์ 256x)  ใช้วิชาที่สอนในภาคปลาย </p><p style="margin-left: 25px;">
                                วงรอบที่ 2 (1 มีนาคม 256x – 31 สิงหาคม 256x)  
                                ใช้วิชาที่สอนในภาคการศึกษาพิเศษ (ถ้ามี) + ภาคต้น&nbsp;</p>',
    'criteria_version_id' => 3,
  ),
  2 => 
  array (
    'id' => 41,
    'name' => '1. ปริมาณผลงาน',
    'tooltips' => '<p>1. งานสอนรายวิชาศึกษาทั่วไป ไม่ให้น ามาคิดภาระงานในทุกกรณี (ทั้งเชิงปริมาณและเชิงคุณภาพ) 
                                ยกเว้นผู้ที่ไม่ได้เบิกค่าสอนวิชานั้นๆ สามารถน ามาคิดได้เฉพาะเชิงปริมาณ </p><p>2. วงรอบ  
                                วงรอบที่ 1 (1 กันยายน 256x – 28 กุมภาพันธ์ 256x)  ใช้วิชาที่สอนในภาคปลาย 
                                วงรอบที่ 2 (1 มีนาคม 256x – 31 สิงหาคม 256x)  
                                ใช้วิชาที่สอนในภาคการศึกษาพิเศษ (ถ้ามี) + ภาคต้น </p>',
    'criteria_version_id' => 1,
  ),
));

        DB::table('quantity_sub_criterias')->insert(array (
  0 => 
  array (
    'id' => 8,
    'name' => '1.1 ภาระงานในหน้าที่',
    'sequence' => 1,
    'score_a' => '20.00',
    'score_b' => '400.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 2,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 9,
  ),
  1 => 
  array (
    'id' => 9,
    'name' => '1.2 ภาระด้านการพัฒนาระบบงาน',
    'sequence' => 2,
    'score_a' => '4.00',
    'score_b' => '80.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 2,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 9,
  ),
  2 => 
  array (
    'id' => 10,
    'name' => '1.3 ภาระงานบริการวิชาการ',
    'sequence' => 3,
    'score_a' => '1.00',
    'score_b' => '20.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 2,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 9,
  ),
  3 => 
  array (
    'id' => 11,
    'name' => '1.4 ภาระทำนุบำรุงศิลปวัฒนธรรม',
    'sequence' => 4,
    'score_a' => '2.00',
    'score_b' => '40.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 2,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 9,
  ),
  4 => 
  array (
    'id' => 12,
    'name' => '1.5 ภาระงานพัฒนาตนเอง',
    'sequence' => 5,
    'score_a' => '3.00',
    'score_b' => '60.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 2,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 9,
  ),
  5 => 
  array (
    'id' => 13,
    'name' => '1.6 ภาระผลงานด้านวิจัย',
    'sequence' => 6,
    'score_a' => '2.00',
    'score_b' => '40.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 2,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 9,
  ),
  6 => 
  array (
    'id' => 14,
    'name' => '1.7 ภาระงานด้านการบริหารองค์กรสู่ความเป็นเลิศ',
    'sequence' => 7,
    'score_a' => '8.00',
    'score_b' => '160.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 2,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 9,
  ),
  7 => 
  array (
    'id' => 15,
    'name' => '1.1 ภาระงานด้านการสอน',
    'sequence' => 1,
    'score_a' => '10.00',
    'score_b' => '200.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 3,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 17,
  ),
  8 => 
  array (
    'id' => 16,
    'name' => '1.2 ภาระด้านการวิจัย',
    'sequence' => 2,
    'score_a' => '3.00',
    'score_b' => '60.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 3,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 17,
  ),
  9 => 
  array (
    'id' => 17,
    'name' => '1.3 ภาระงานบริการวิชาการ',
    'sequence' => 3,
    'score_a' => '1.50',
    'score_b' => '30.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 3,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 17,
  ),
  10 => 
  array (
    'id' => 18,
    'name' => '1.4 ภาระทำนุบำรุงศิลปวัฒนธรรม',
    'sequence' => 4,
    'score_a' => '1.50',
    'score_b' => '30.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 3,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 17,
  ),
  11 => 
  array (
    'id' => 19,
    'name' => '1.5 ภาระงานพัฒนาตนเอง',
    'sequence' => 5,
    'score_a' => '2.00',
    'score_b' => '40.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 3,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 17,
  ),
  12 => 
  array (
    'id' => 20,
    'name' => '1.6 ภาระผลงานทางวิชาการ',
    'sequence' => 6,
    'score_a' => '2.00',
    'score_b' => '40.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 3,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 17,
  ),
  13 => 
  array (
    'id' => 21,
    'name' => '1.7 ภาระงานเกี่ยวกับการบริหาร',
    'sequence' => 7,
    'score_a' => '20.00',
    'score_b' => '400.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 3,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 17,
  ),
  14 => 
  array (
    'id' => 236,
    'name' => '1.1 ภาระงานด้านการสอน',
    'sequence' => 1,
    'score_a' => '15.00',
    'score_b' => '300.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 41,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 268,
  ),
  15 => 
  array (
    'id' => 250,
    'name' => '1.2 ภาระด้านการวิจัย',
    'sequence' => 2,
    'score_a' => '8.00',
    'score_b' => '160.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 41,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 268,
  ),
  16 => 
  array (
    'id' => 251,
    'name' => '1.3 ภาระงานบริการวิชาการ',
    'sequence' => 3,
    'score_a' => '4.00',
    'score_b' => '80.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 41,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 268,
  ),
  17 => 
  array (
    'id' => 252,
    'name' => '1.4 ภาระทำนุบำรุงศิลปวัฒนธรรม',
    'sequence' => 4,
    'score_a' => '4.00',
    'score_b' => '80.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 41,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 268,
  ),
  18 => 
  array (
    'id' => 253,
    'name' => '1.5 ภาระงานพัฒนาตนเอง',
    'sequence' => 5,
    'score_a' => '3.00',
    'score_b' => '60.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 41,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 268,
  ),
  19 => 
  array (
    'id' => 254,
    'name' => '1.6 ภาระผลงานทางวิชาการ',
    'sequence' => 6,
    'score_a' => '3.00',
    'score_b' => '60.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 41,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 268,
  ),
  20 => 
  array (
    'id' => 255,
    'name' => '1.7 ภาระงานเกี่ยวกับการบริหาร',
    'sequence' => 7,
    'score_a' => '3.00',
    'score_b' => '60.00',
    'description' => NULL,
    'quantity_main_criteria_id' => 41,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 268,
  ),
));

        DB::table('quality_main_criterias')->insert(array (
  0 => 
  array (
    'id' => 18,
    'name' => '(1) มีการวางแผนการทำงานในหน้าที่',
    'ratio' => '25.00',
    'tooltips' => '',
    'sequence' => 1,
    'criteria_version_id' => 2,
  ),
  1 => 
  array (
    'id' => 19,
    'name' => '(2) มีการปฏิบัติตามขั้นตอนแผนงานที่กำหนดในข้อ (1)',
    'ratio' => '25.00',
    'tooltips' => '',
    'sequence' => 2,
    'criteria_version_id' => 2,
  ),
  2 => 
  array (
    'id' => 20,
    'name' => '(3) มีการตรวจสอบและประเมินผลการดำเนินงาน',
    'ratio' => '25.00',
    'tooltips' => '',
    'sequence' => 3,
    'criteria_version_id' => 2,
  ),
  3 => 
  array (
    'id' => 21,
    'name' => '(4) มีการสรุปและแนวทางในการปรับปรุงแก้ไข',
    'ratio' => '25.00',
    'tooltips' => '',
    'sequence' => 4,
    'criteria_version_id' => 2,
  ),
  4 => 
  array (
    'id' => 22,
    'name' => 'ภาระงานด้านการพัฒนาระบบงาน',
    'ratio' => '100.00',
    'tooltips' => '',
    'sequence' => 1,
    'criteria_version_id' => 2,
  ),
  5 => 
  array (
    'id' => 23,
    'name' => 'โครงการบริการวิชาการ (ต่อ 1 โครงการ)',
    'ratio' => '100.00',
    'tooltips' => '',
    'sequence' => 1,
    'criteria_version_id' => 2,
  ),
  6 => 
  array (
    'id' => 24,
    'name' => 'เข้าร่วมงานพิธีที่ดำเนินการโดยคณะ/มหาวิทยาลัย (ต่อครั้ง)',
    'ratio' => '100.00',
    'tooltips' => '',
    'sequence' => 1,
    'criteria_version_id' => 2,
  ),
  7 => 
  array (
    'id' => 25,
    'name' => 'พัฒนาตนเองตามความต้องการของหน่วยงาน (ต่อครั้ง)',
    'ratio' => '100.00',
    'tooltips' => '',
    'sequence' => 1,
    'criteria_version_id' => 2,
  ),
  8 => 
  array (
    'id' => 26,
    'name' => '1. การได้รับทุนสนับสนุนการวิจัย',
    'ratio' => '50.00',
    'tooltips' => '',
    'sequence' => 1,
    'criteria_version_id' => 2,
  ),
  9 => 
  array (
    'id' => 27,
    'name' => '1. งานวิจัยแล้วเสร็จ ตามแผนงานวิจัย',
    'ratio' => '25.00',
    'tooltips' => '(ใช้ได้ถึงกรอบการอนุมัติขยายเวลา)',
    'sequence' => 2,
    'criteria_version_id' => 2,
  ),
  10 => 
  array (
    'id' => 28,
    'name' => '2. งานวิจัยมีการตีพิมพ์เผยแพร่',
    'ratio' => '25.00',
    'tooltips' => 'เฉพาะงานที่สอดคล้องกับเชิงปริมาณใน วงรอบก่อนหน้านี้เท่านั้น 
- ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถ นำไปใช้ในรอบถัดไปได้ (ในรอบถัดไป ให้ระบุ ด้วยว่ายังไม่เคยใช้ในรอบก่อนหน้านี้)',
    'sequence' => 3,
    'criteria_version_id' => 2,
  ),
  11 => 
  array (
    'id' => 29,
    'name' => '1. ภาระงานมุ่งสู่ความเป็นเลิศ',
    'ratio' => '62.50',
    'tooltips' => '',
    'sequence' => 1,
    'criteria_version_id' => 2,
  ),
  12 => 
  array (
    'id' => 30,
    'name' => '2. ภาระงานบริหาร',
    'ratio' => '37.50',
    'tooltips' => '',
    'sequence' => 2,
    'criteria_version_id' => 2,
  ),
  13 => 
  array (
    'id' => 31,
    'name' => '1) ผลการประเมินการสอนจากนิสิต (ทุกวิชา)',
    'ratio' => '25.00',
    'tooltips' => '<p>&nbsp;หาค่าเฉลี่ยจากทุกรายวิชา&nbsp;</p>',
    'sequence' => 1,
    'criteria_version_id' => 3,
  ),
  14 => 
  array (
    'id' => 32,
    'name' => '2) ข้อสอบ (ทุกวิชา)',
    'ratio' => '25.00',
    'tooltips' => '',
    'sequence' => 2,
    'criteria_version_id' => 3,
  ),
  15 => 
  array (
    'id' => 33,
    'name' => '3) มคอ.3 มคอ.5 และส่งเกรด (ทุกวิชา)',
    'ratio' => '25.00',
    'tooltips' => '',
    'sequence' => 3,
    'criteria_version_id' => 3,
  ),
  16 => 
  array (
    'id' => 34,
    'name' => '4) การลงไฟล์สอนใน Classroom (ทุกวิชา)',
    'ratio' => '25.00',
    'tooltips' => '',
    'sequence' => 4,
    'criteria_version_id' => 3,
  ),
  17 => 
  array (
    'id' => 35,
    'name' => '1. แหล่งทุน',
    'ratio' => '10.00',
    'tooltips' => '',
    'sequence' => 1,
    'criteria_version_id' => 3,
  ),
  18 => 
  array (
    'id' => 36,
    'name' => '2. งานวิจัยแล้วเสร็จตามแผนงานวิจัย (ตามปีงบประมาณ ไม่รวมการขอขยายเวลา)',
    'ratio' => '50.00',
    'tooltips' => '<p>80-100%  คือ ส่ง manuscript + ส่งเล่มสมบูรณ์ + เบิกงวด 2 + ส่งรายงานความก้าวหน้า + เบิกงวด 1</p><p> 
                                60-79%  คือ ส่งเล่มสมบูรณ์ + เบิกงวด 2 + ส่งรายงานความก้าวหน้า + เบิกงวด 1 </p><p>
                                40-59%  คือ เบิกงวด 2 + ส่งรายงานความก้าวหน้า + เบิกงวด 1 </p><p>
                                20-39%  คือ ส่งรายงานความก้าวหน้า + เบิกงวด 1 </p><p>
                                &lt;20%  
                                คือ เบิกงวด 1</p>',
    'sequence' => 2,
    'criteria_version_id' => 3,
  ),
  19 => 
  array (
    'id' => 37,
    'name' => '3. งานวิจัยมีการตีพิมพ์เผยแพร่',
    'ratio' => '40.00',
    'tooltips' => '<p>- เฉพาะงานที่สอดคล้องกับเชิงปริมาณใน
                                วงรอบก่อนหน้านี้เท่านั้น </p><p>- ทั้งนี้ หากภาระงานเพียงพอแล้ว สามารถ
                                นำไปใช้ในรอบถัดไปได้  (ในรอบถัดไป ให้ระบุด้วยว่ายังไม่เคยใช้ในรอบก่อนหน้านี้)</p><p><br></p><p><b>สำหรับ ISI:</b> 1st author, Corresponding, ลำดับอื่นๆ</p><p><b>สำหรับ SCOPUS, นานาชาติ อื่นๆ :</b> 1st author, Corresponding, ลำดับอื่นๆ</p><p><b>สำหรับ TCI 1:</b> 1st author, Corresponding, ลำดับอื่นๆ&nbsp;</p><p><b>สำหรับ TCI 2: </b>1st author, Corresponding, ลำดับอื่นๆ&nbsp;</p>',
    'sequence' => 3,
    'criteria_version_id' => 3,
  ),
  20 => 
  array (
    'id' => 38,
    'name' => '1. รูปแบบของการจัดบริการวิชาการ',
    'ratio' => '20.00',
    'tooltips' => '',
    'sequence' => 1,
    'criteria_version_id' => 3,
  ),
  21 => 
  array (
    'id' => 39,
    'name' => '2. ลักษณะของการบริการวิชาการ',
    'ratio' => '80.00',
    'tooltips' => '<p>2.1 โครงการบริการวิชาการ (ที่มีคณะกรรมการ) (ต่อ 1 โครงการ):&nbsp;หัวหน้าโครงการ, ผู้ร่วมโครงการกรรมการที่ได้รับแต่งตั้ง</p><p>2.2 การให้บริการวิชาการลักษณะอื่น</p><p style="margin-left: 25px;">2.2.1 เป็น Peer review วารสารระดับ นานาชาติ (เช่น ISI, SCOPUS) (ต่อ 1 เรื่อง)</p><p style="margin-left: 25px;">2.2.2 เป็น Peer review วารสารระดับชาติ (TCI) (ต่อ 1 เรื่อง)</p><p style="margin-left: 25px;">2.2.3 เป็นวิทยากรบรรยายความรู้/ปฏิบัติ (ต่อครั้ง)</p><p style="margin-left: 25px;">2.2.4 เป็นกรรมการสอบระดับบัณฑิตศึกษาภายนอก มมส. หรือ ภายนอกคณะ (ต่อครั้ง)</p><p style="margin-left: 25px;">2.2.5 เป็นกรรมการตัดสินผลงานวิชาการ (ต่อครั้ง)</p><p style="margin-left: 25px;">2.2.6 เป็นผู้ทรงคุณวุฒิให้หน่วยงานอื่น (ต่อครั้ง)</p>',
    'sequence' => 2,
    'criteria_version_id' => 3,
  ),
  22 => 
  array (
    'id' => 40,
    'name' => '1.1 เข้าร่วมงานตามตารางเวรที่กำหนดล่วงหน้า',
    'ratio' => '10.00',
    'tooltips' => '<p>(หากในวงรอบประเมิน ยังไม่ถึงตารางเวร  
                                สามารถใช้ข้อ 1.2 หรือ 1.3 มาเพิ่มเติมได้)&nbsp;</p>',
    'sequence' => 1,
    'criteria_version_id' => 3,
  ),
  23 => 
  array (
    'id' => 41,
    'name' => '1.2.1  เข้าร่วมวันมหิดล (วงรอบ 1)',
    'ratio' => '10.00',
    'tooltips' => '',
    'sequence' => 2,
    'criteria_version_id' => 3,
  ),
  24 => 
  array (
    'id' => 42,
    'name' => '1.2.2 เข้าร่วมวันคล้ายวันสถาปนาคณะฯ (วงรอบ 2)',
    'ratio' => '10.00',
    'tooltips' => '',
    'sequence' => 3,
    'criteria_version_id' => 3,
  ),
  25 => 
  array (
    'id' => 43,
    'name' => '1.2.3 เข้าร่วมวันปฐมนิเทศนิสิตใหม่ (วงรอบ 2)',
    'ratio' => '10.00',
    'tooltips' => '',
    'sequence' => 4,
    'criteria_version_id' => 3,
  ),
  26 => 
  array (
    'id' => 44,
    'name' => '1.2.4 เข้าร่วมวันไหว้ครู (วงรอบ 2)',
    'ratio' => '10.00',
    'tooltips' => '',
    'sequence' => 5,
    'criteria_version_id' => 3,
  ),
  27 => 
  array (
    'id' => 45,
    'name' => '1.3 เข้าร่วมตามภาระงานหลักของ มมส. ได้แก่ พิธีพระราชทานปริญญาบัตร มมส. (วงรอบ 1)',
    'ratio' => '20.00',
    'tooltips' => '',
    'sequence' => 6,
    'criteria_version_id' => 3,
  ),
  28 => 
  array (
    'id' => 46,
    'name' => '1.4 เข้าร่วมตามหนังสือเชิญอื่นๆ/ งานพิธีที่ดำเนินการโดยคณะ (ต่อครั้ง)',
    'ratio' => '10.00',
    'tooltips' => '',
    'sequence' => 7,
    'criteria_version_id' => 3,
  ),
  29 => 
  array (
    'id' => 47,
    'name' => '2. การสวมใส่ผ้าพื้นเมืองตามนโยบายของรัฐอย่างน้อยสัปดาห์ละ 2 วัน (อังคาร และ ศุกร์)',
    'ratio' => '20.00',
    'tooltips' => '',
    'sequence' => 8,
    'criteria_version_id' => 3,
  ),
  30 => 
  array (
    'id' => 48,
    'name' => '1.1  พัฒนาตนเองตามความต้องการของหน่วยงานเพื่อพัฒนาด้านการเรียนการสอน (ต่อครั้ง)',
    'ratio' => '20.00',
    'tooltips' => '<p>1.&nbsp;จัดโดยหน่วยงานภาครัฐ&nbsp; หรือเอกชน</p><p>2.&nbsp;&nbsp;หากศึกษาด้วยตนเอง&nbsp; (ต้องมี Cert.รับรอง)',
    'sequence' => 1,
    'criteria_version_id' => 3,
  ),
  31 => 
  array (
    'id' => 49,
    'name' => '1.2 พัฒนาตนเองตามความต้องการของหน่วยงานเพื่อพัฒนาทักษะด้านการวิจัย (ต่อครั้ง)',
    'ratio' => '20.00',
    'tooltips' => '<p>1.&nbsp;จัดโดยหน่วยงานภาครัฐ&nbsp; หรือเอกชน</p><p>2.&nbsp;&nbsp;หากศึกษาด้วยตนเอง&nbsp; (ต้องมี Cert.รับรอง)',
    'sequence' => 2,
    'criteria_version_id' => 3,
  ),
  32 => 
  array (
    'id' => 50,
    'name' => '1.3 พัฒนาตนเองตามความต้องการของหน่วยงานเพื่อพัฒนาความเชี่ยวชาญด้านอื่นๆ เช่น  ภาษาต่างประเทศ การใช้เครื่องมือทางวิทยาศาสตร์ ฯลฯ (ต่อครั้ง)',
    'ratio' => '10.00',
    'tooltips' => '<p>1.&nbsp;จัดโดยหน่วยงานภาครัฐ&nbsp; หรือเอกชน</p><p>2.&nbsp;&nbsp;หากศึกษาด้วยตนเอง&nbsp; (ต้องมี Cert.รับรอง)',
    'sequence' => 3,
    'criteria_version_id' => 3,
  ),
  33 => 
  array (
    'id' => 51,
    'name' => '2.1 พัฒนาตนเองตามความสนใจเพื่อพัฒนาด้านการเรียนการสอน (ต่อครั้ง)',
    'ratio' => '20.00',
    'tooltips' => '<p>1.&nbsp;จัดโดยหน่วยงานภาครัฐ&nbsp; หรือเอกชน</p><p>2.&nbsp;&nbsp;หากศึกษาด้วยตนเอง&nbsp; (ต้องมี Cert.รับรอง)',
    'sequence' => 4,
    'criteria_version_id' => 3,
  ),
  34 => 
  array (
    'id' => 52,
    'name' => '2.2 พัฒนาตนเองตามความสนใจเพื่อพัฒนาทักษะด้านการวิจัย (ต่อครั้ง)',
    'ratio' => '20.00',
    'tooltips' => '<p>1.&nbsp;จัดโดยหน่วยงานภาครัฐ&nbsp; หรือเอกชน</p><p>2.&nbsp;&nbsp;หากศึกษาด้วยตนเอง&nbsp; (ต้องมี Cert.รับรอง)',
    'sequence' => 5,
    'criteria_version_id' => 3,
  ),
  35 => 
  array (
    'id' => 53,
    'name' => '2.3 พัฒนาตนเองตามความสนใจเพื่อพัฒนาความเชี่ยวชาญด้านอื่นๆ เช่น  ภาษาต่างประเทศ การใช้เครื่องมือทางวิทยาศาสตร์ ฯลฯ (ต่อครั้ง)',
    'ratio' => '10.00',
    'tooltips' => '<p>1.&nbsp;จัดโดยหน่วยงานภาครัฐ&nbsp; หรือเอกชน</p><p>2.&nbsp;&nbsp;หากศึกษาด้วยตนเอง&nbsp; (ต้องมี Cert.รับรอง)</p>',
    'sequence' => 6,
    'criteria_version_id' => 3,
  ),
  36 => 
  array (
    'id' => 54,
    'name' => '1. การเผยแพร่ผลงานวิชาการ (ต่อเรื่อง) เฉพาะงานในวงรอบเท่านั้น',
    'ratio' => '50.00',
    'tooltips' => '<p><b>สำหรับ จดอนุสิทธิบัตร:</b> 1st author, Corresponding author, ลำดับอื่นๆ&nbsp;</p><p><b>สำหรับ วารสารนานาชาติ เช่น ISI, SCOPUS:</b> 1st author, Corresponding author, ลำดับอื่นๆ&nbsp;</p><p><b>สำหรับ TCI 1:</b> 1st author, Corresponding author, ลำดับอื่นๆ</p><p><b>สำหรับ TCI 2:</b> 1st author, Corresponding author, ลำดับอื่นๆ</p><p><b>สำหรับ Proceeding (full &amp; inter.):</b> 1st author, Corresponding author, ลำดับอื่นๆ&nbsp;</p><p><b>สำหรับ Proceeding</b>: 1st author, Corresponding author, ลำดับอื่นๆ</p><p><b>สำหรับ Abstract book:</b> 1st author, Corresponding author, ลำดับอื่นๆ</p>',
    'sequence' => 1,
    'criteria_version_id' => 3,
  ),
  37 => 
  array (
    'id' => 55,
    'name' => '2. เอกสารและสื่อประกอบการสอน',
    'ratio' => '50.00',
    'tooltips' => '',
    'sequence' => 2,
    'criteria_version_id' => 3,
  ),
  38 => 
  array (
    'id' => 56,
    'name' => '1. กรรมการต่อเนื่องตามคำสั่ง มมส. (ต่อ 1 คำสั่ง)',
    'ratio' => '50.00',
    'tooltips' => '<p>&nbsp;เช่น </p><p>
                                1.1 กรรมการสภามหาวิทยาลัย</p><p>1.2 กรรมการประจำคณะ</p><p> 1.3 สภาคณาจารย์&nbsp;&nbsp;</p>',
    'sequence' => 1,
    'criteria_version_id' => 3,
  ),
  39 => 
  array (
    'id' => 57,
    'name' => '2. กรรมการต่อเนื่องตามคำสั่งคณะ (ต่อ 1 คำสั่ง)',
    'ratio' => '50.00',
    'tooltips' => '<p>เช่น </p><p>
                                2.1 อนุกรรมการวิจัยคณะฯ </p><p>
                                2.2 กรรมการบริหารห้องปฏิบัติการฯ </p><p>
                                2.3 กรรมการวิชาการคณะฯ </p><p>
                                2.4 กรรมการบัณฑิตศึกษาประจำคณะ </p><p>
                                2.5 กรรมการหลักสูตรระยะสั้น, คลังเครดิต </p><p>
                                2.6 กรรมการจัดการมูลฝอยประจำคณะ&nbsp;</p>',
    'sequence' => 2,
    'criteria_version_id' => 3,
  ),
  40 => 
  array (
    'id' => 58,
    'name' => '1. การเข้าร่วมประชุมประจำเดือนของคณะ',
    'ratio' => '20.00',
    'tooltips' => '',
    'sequence' => 1,
    'criteria_version_id' => 3,
  ),
  41 => 
  array (
    'id' => 59,
    'name' => '2. กรรมการตามคำสั่งคณะ หรืองานที่คณบดีมอบหมาย',
    'ratio' => '50.00',
    'tooltips' => '<p>(10 % ต่อ 1 คำสั่ง  รวมสูงสุดได้ 
                                50%) เช่น </p><p>
                                2.1 กรรมการเกี่ยวกับการจัดทำแผนฯ </p><p>
                                2.2 กรรมการเกี่ยวกับครุภัณฑ์ </p><p>
                                2.3 กรรมการเกี่ยวกับการประกันคุณภาพฯ 
                                    เช่น ทวนสอบ ประเมินคุณภาพ ฯลฯ</p>',
    'sequence' => 2,
    'criteria_version_id' => 3,
  ),
  42 => 
  array (
    'id' => 60,
    'name' => '3. อาจารย์ผู้รับผิดชอบหลักสูตร',
    'ratio' => '30.00',
    'tooltips' => '',
    'sequence' => 3,
    'criteria_version_id' => 3,
  ),
  43 => 
  array (
    'id' => 573,
    'name' => '1. ภาระงานมุ่งสู่ความเป็นเลิศ',
    'ratio' => '50.00',
    'tooltips' => '<p>(1.1) การสร้างผลงานทางวิชาการระดับนานาชาติที่เป็นเลิศ (ใช้ได้ 2 วงรอบ)
</p><p>                                    (1.2) การสร้างความร่วมมือด้านวิจัยที่เป็นเลิศ (ใช้ได้ 2 วงรอบ)
</p><p>                                    (1.3) การบริการวิชาการระดับนานาชาติ (ใช้ได้ 2 วงรอบ)
</p><p>                                    (1.4) การขับเคลื่อนองค์กรมุ่งสู่ความเป็นเลิศของคณะฯ</p>',
    'sequence' => 1,
    'criteria_version_id' => 1,
  ),
  44 => 
  array (
    'id' => 574,
    'name' => '2. ภาระงานบริหาร',
    'ratio' => '50.00',
    'tooltips' => '<p>(2.1) กรรมการต่าง ๆ
</p><p>                                    (2.2) การเข้าร่วมประชุมประจำเดือนของคณะ</p>',
    'sequence' => 2,
    'criteria_version_id' => 1,
  ),
  45 => 
  array (
    'id' => 575,
    'name' => '3. การดำเนินงานวิจัยตามแผน*',
    'ratio' => '40.00',
    'tooltips' => '(นับรวมงานวิจัยที่ได้รับการอนุมัติขยายเวลา)',
    'sequence' => 3,
    'criteria_version_id' => 1,
  ),
  46 => 
  array (
    'id' => 576,
    'name' => '4. การนำไปใช้ประโยชน์**',
    'ratio' => '20.00',
    'tooltips' => '<p>อยู่ในรายงานการใช้ประโยชน์งานวิจัย
                                    (4.1) </p><p>ตีพิมพ์เผยแพร่
                                    (4.2) </p><p>การเรียนการสอน
                                    (4.3) </p><p>ถ่ายทอดเทคโนโลยี
                                    (4.4) </p><p>ยื่นจด IP</p>',
    'sequence' => 4,
    'criteria_version_id' => 1,
  ),
  47 => 
  array (
    'id' => 577,
    'name' => '(5) ผลการประเมินการสอนจากนิสิต (ทุกวิชา)**',
    'ratio' => '20.00',
    'tooltips' => 'หาค่าเฉลี่ยจากทุกรายวิชา',
    'sequence' => 5,
    'criteria_version_id' => 1,
  ),
));

        DB::table('quality_sub_criterias')->insert(array (
  0 => 
  array (
    'id' => 45,
    'name' => '(1) มีการวางแผนการทำงานในหน้าที่',
    'sequence' => 1,
    'num_score' => '3.75',
    'description' => 'คะแนนที่ได้ 3.75',
    'quality_main_criteria_id' => 18,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 10,
  ),
  1 => 
  array (
    'id' => 46,
    'name' => '(2) มีการปฏิบัติตามขั้นตอนแผนงานที่กำหนดในข้อ (1)',
    'sequence' => 2,
    'num_score' => '3.75',
    'description' => 'คะแนนที่ได้ 3.75',
    'quality_main_criteria_id' => 19,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 10,
  ),
  2 => 
  array (
    'id' => 47,
    'name' => '(3) มีการตรวจสอบและประเมินผลการดำเนินงาน',
    'sequence' => 3,
    'num_score' => '3.75',
    'description' => 'คะแนนที่ได้ 3.75',
    'quality_main_criteria_id' => 20,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 10,
  ),
  3 => 
  array (
    'id' => 48,
    'name' => '(4) มีการสรุปและแนวทางในการปรับปรุงแก้ไข',
    'sequence' => 4,
    'num_score' => '3.75',
    'description' => 'คะแนนที่ได้ 3.75',
    'quality_main_criteria_id' => 21,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 10,
  ),
  4 => 
  array (
    'id' => 49,
    'name' => '(1) มีการวิเคราะห์ปัญหาระบบ (ต่อ 1 ชิ้นงาน)',
    'sequence' => 1,
    'num_score' => '1.00',
    'description' => 'คะแนนที่ได้ 1.00',
    'quality_main_criteria_id' => 22,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 11,
  ),
  5 => 
  array (
    'id' => 50,
    'name' => '(2) มีการกำหนดตัวชี้วัดประสิทธิภาพงาน (ต่อ 1 ชิ้นงาน)',
    'sequence' => 2,
    'num_score' => '1.00',
    'description' => 'คะแนนที่ได้ 1.00',
    'quality_main_criteria_id' => 22,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 11,
  ),
  6 => 
  array (
    'id' => 51,
    'name' => '(3) มีการใช้เทคโนโลยีเข้ามาช่วยเพิ่มประสิทธิภาพงาน (เช่น AI) (ต่อ 1 ชิ้นงาน)',
    'sequence' => 3,
    'num_score' => '1.00',
    'description' => 'คะแนนที่ได้ 1.00',
    'quality_main_criteria_id' => 22,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 11,
  ),
  7 => 
  array (
    'id' => 52,
    'name' => '(4) มีการลดขั้นตอนการทำงาน (ต่อ 1 ชิ้นงาน',
    'sequence' => 4,
    'num_score' => '1.00',
    'description' => 'คะแนนที่ได้ 1.00',
    'quality_main_criteria_id' => 22,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 11,
  ),
  8 => 
  array (
    'id' => 53,
    'name' => '(5) มีการสร้างคู่มือปฏิบัติงานฉบับปรับปรุง (ต่อ 1 ชิ้นงาน)',
    'sequence' => 5,
    'num_score' => '1.00',
    'description' => 'คะแนนที่ได้ 1.00',
    'quality_main_criteria_id' => 22,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 11,
  ),
  9 => 
  array (
    'id' => 54,
    'name' => '(6) มีแนวทางในการจัดการข้อร้องเรียน (ต่อ 1 ชิ้นงาน)',
    'sequence' => 6,
    'num_score' => '1.00',
    'description' => 'คะแนนที่ได้ 1.00',
    'quality_main_criteria_id' => 22,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 11,
  ),
  10 => 
  array (
    'id' => 55,
    'name' => '(7) มีกระบวนการส่ง-มอบข้อมูล (ต่อ 1 ชิ้นงาน)',
    'sequence' => 7,
    'num_score' => '1.00',
    'description' => 'คะแนนที่ได้ 1.00',
    'quality_main_criteria_id' => 22,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 11,
  ),
  11 => 
  array (
    'id' => 56,
    'name' => '(8) มีการรายงานข้อมูลสรุปด้านคุณภาพงาน (ต่อ 1 ชิ้นงาน)',
    'sequence' => 8,
    'num_score' => '1.00',
    'description' => 'คะแนนที่ได้ 1.00',
    'quality_main_criteria_id' => 22,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 11,
  ),
  12 => 
  array (
    'id' => 57,
    'name' => 'โครงการบริการวิชาการ (ต่อ 1 โครงการ)',
    'sequence' => 1,
    'num_score' => '0.50',
    'description' => 'คะแนนที่ได้ 0.50',
    'quality_main_criteria_id' => 23,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 12,
  ),
  13 => 
  array (
    'id' => 58,
    'name' => '1.1 วันมหิดล (วงรอบ 1)',
    'sequence' => 1,
    'num_score' => '0.50',
    'description' => 'คะแนนที่ได้ 0.50',
    'quality_main_criteria_id' => 24,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 13,
  ),
  14 => 
  array (
    'id' => 59,
    'name' => '1.2 วันคล้ายวันสถาปนาคณะฯ (วงรอบ 2)',
    'sequence' => 2,
    'num_score' => '0.50',
    'description' => 'คะแนนที่ได้ 0.50',
    'quality_main_criteria_id' => 24,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 13,
  ),
  15 => 
  array (
    'id' => 60,
    'name' => '1.3 วันปฐมนิเทศนิสิตใหม่ (วงรอบ 2)',
    'sequence' => 3,
    'num_score' => '0.50',
    'description' => 'คะแนนที่ได้ 0.50',
    'quality_main_criteria_id' => 24,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 13,
  ),
  16 => 
  array (
    'id' => 61,
    'name' => '1.4 วันไหว้ครู (วงรอบ 2)',
    'sequence' => 4,
    'num_score' => '0.50',
    'description' => 'คะแนนที่ได้ 0.50',
    'quality_main_criteria_id' => 24,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 13,
  ),
  17 => 
  array (
    'id' => 62,
    'name' => '1.5 พิธีพระราชทานปริญญาบัตร มมส.',
    'sequence' => 5,
    'num_score' => '0.50',
    'description' => 'คะแนนที่ได้ 0.50',
    'quality_main_criteria_id' => 24,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 13,
  ),
  18 => 
  array (
    'id' => 63,
    'name' => '1.6 กิจกรรมวันปีใหม่ (วงรอบ 1)',
    'sequence' => 6,
    'num_score' => '0.50',
    'description' => 'คะแนนที่ได้ 0.50',
    'quality_main_criteria_id' => 24,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 13,
  ),
  19 => 
  array (
    'id' => 64,
    'name' => '1.7 กิจกรรมวันสงกรานต์ (วงรอบ 2)',
    'sequence' => 7,
    'num_score' => '0.50',
    'description' => 'คะแนนที่ได้ 0.50',
    'quality_main_criteria_id' => 24,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 13,
  ),
  20 => 
  array (
    'id' => 65,
    'name' => '1.8 กิจกรรมวันบุญเผวด (วงรอบ 1)',
    'sequence' => 8,
    'num_score' => '0.50',
    'description' => 'คะแนนที่ได้ 0.50',
    'quality_main_criteria_id' => 24,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 13,
  ),
  21 => 
  array (
    'id' => 66,
    'name' => '1.9 กิจกรรมวันคล้ายวันสถาปนามหาวิทยาลัย',
    'sequence' => 9,
    'num_score' => '0.50',
    'description' => 'คะแนนที่ได้ 0.50',
    'quality_main_criteria_id' => 24,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 13,
  ),
  22 => 
  array (
    'id' => 67,
    'name' => '1.10 เข้าร่วมกิจกรรมอื่น ๆ ตามหนังสือเชิญอื่นๆ',
    'sequence' => 10,
    'num_score' => '0.50',
    'description' => 'คะแนนที่ได้ 0.50',
    'quality_main_criteria_id' => 24,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 13,
  ),
  23 => 
  array (
    'id' => 68,
    'name' => '1.1 การพัฒนาองค์กรสู่ความเป็นเลิศ เช่น จัดทำแผน',
    'sequence' => 1,
    'num_score' => '0.50',
    'description' => 'คะแนนที่ได้ 0.50',
    'quality_main_criteria_id' => 25,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 14,
  ),
  24 => 
  array (
    'id' => 69,
    'name' => '1.2 ด้านการพัฒนาคุณภาพงาน',
    'sequence' => 2,
    'num_score' => '0.50',
    'description' => 'คะแนนที่ได้ 0.50',
    'quality_main_criteria_id' => 25,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 14,
  ),
  25 => 
  array (
    'id' => 70,
    'name' => '1.3 ด้านการปรับปรุงประสิทธิภาพงาน',
    'sequence' => 3,
    'num_score' => '0.50',
    'description' => 'คะแนนที่ได้ 0.50',
    'quality_main_criteria_id' => 25,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 14,
  ),
  26 => 
  array (
    'id' => 71,
    'name' => '1.4 ความเชี่ยวชาญด้านอื่นๆ เช่น ภาษาต่างประเทศ การใช้เครื่องมือทางวิทยาศาสตร์ ฯลฯ (ต่อครั้ง)',
    'sequence' => 4,
    'num_score' => '0.50',
    'description' => 'คะแนนที่ได้ 0.50',
    'quality_main_criteria_id' => 25,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 14,
  ),
  27 => 
  array (
    'id' => 72,
    'name' => '1.5 ด้านการจัดการความรู้ (KM) เพื่อเพิ่มประสิทธิภาพและคุณภาพงานในองค์กร',
    'sequence' => 5,
    'num_score' => '0.50',
    'description' => '<p>ผู้จัดการหลัก = 0.50 </p><p>ผู้ร่วม = 0.25</p>',
    'quality_main_criteria_id' => 25,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 14,
  ),
  28 => 
  array (
    'id' => 73,
    'name' => '1. การได้รับทุนสนับสนุนการวิจัย',
    'sequence' => 1,
    'num_score' => '1.00',
    'description' => '<p>หัวหน้าได้ 1.0 </p><p>ผู้ร่วมได้ 0.5</p>',
    'quality_main_criteria_id' => 26,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 15,
  ),
  29 => 
  array (
    'id' => 74,
    'name' => '1. งานวิจัยแล้วเสร็จ ตามแผนงานวิจัย',
    'sequence' => 1,
    'num_score' => '0.50',
    'description' => '<p>80-100% ได้ 0.5
</p><p>                                60-79% 
                                ได้ 0.40
</p><p>                                40-59% 
                                ได้ 0.30
</p><p>                                20-39% 
                                ได้ 0.20
</p><p>                                &lt;20% 
                                ได้ 0</p>',
    'quality_main_criteria_id' => 27,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 15,
  ),
  30 => 
  array (
    'id' => 75,
    'name' => 'TCI 1 ขึ้นไป',
    'sequence' => 1,
    'num_score' => '0.50',
    'description' => '<p>1st author 0.5 </p><p>Corresponding 0.5 </p><p>ลำดับอื่นๆ 0.25</p>',
    'quality_main_criteria_id' => 28,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 15,
  ),
  31 => 
  array (
    'id' => 76,
    'name' => 'TCI 2',
    'sequence' => 2,
    'num_score' => '0.40',
    'description' => '<p>1st author 0.4 </p><p>Corresponding 0.4 </p><p>ลำดับอื่นๆ 0.20</p>',
    'quality_main_criteria_id' => 28,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 15,
  ),
  32 => 
  array (
    'id' => 77,
    'name' => 'Proceeding(Full-text)',
    'sequence' => 3,
    'num_score' => '0.30',
    'description' => '<p>1st author 0.3 </p><p>Corresponding 0.3 </p><p>ลำดับอื่นๆ 0.15</p>',
    'quality_main_criteria_id' => 28,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 15,
  ),
  33 => 
  array (
    'id' => 78,
    'name' => 'Proceeding(Abstract)',
    'sequence' => 4,
    'num_score' => '0.20',
    'description' => '<p>1st author 0.2 </p><p>Corresponding 0.2 </p><p>ลำดับอื่นๆ 0.1</p>',
    'quality_main_criteria_id' => 28,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 15,
  ),
  34 => 
  array (
    'id' => 79,
    'name' => '(1.1) การขับเคลื่อนองค์กรมุ่งสู่ความเป็นเลิศ',
    'sequence' => 1,
    'num_score' => '1.50',
    'description' => '<p>ผู้ดำเนินการหลัก 1.5 </p><p>ผู้ร่วม 1.5</p>',
    'quality_main_criteria_id' => 29,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 16,
  ),
  35 => 
  array (
    'id' => 80,
    'name' => '(1.2) การพัฒนาคุณภาพงานสู่แนวปฏิบัติที่ดี',
    'sequence' => 2,
    'num_score' => '1.50',
    'description' => '<p>เสร็จสมบูรณ์ 1.5 </p><p>ฉบับร่าง 1.0</p>',
    'quality_main_criteria_id' => 29,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 16,
  ),
  36 => 
  array (
    'id' => 81,
    'name' => '(1.3) การบริการที่เป็นเลิศ',
    'sequence' => 3,
    'num_score' => '2.00',
    'description' => '<p>80% ขึ้นไป ดีเยี่ยม 2
</p><p>                                70-79% ดีมาก 1.5
</p><p>                                60-69% ดี 1.0
</p><p>                                50-59% ปานกลาง 0.5
</p><p>                                &lt;50% ต้องปรับปรุง 0</p>',
    'quality_main_criteria_id' => 29,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 16,
  ),
  37 => 
  array (
    'id' => 82,
    'name' => '(2.1) กรรมการต่อเนื่องตามคำสั่งคณะ หรือ มหาวิทยาลัย (ต่อ 1 คำสั่ง)',
    'sequence' => 1,
    'num_score' => '0.50',
    'description' => '<p>มีคำสั่งและมีการดำเนินงาน 0.5
</p><p>                มีคำสั่ง
0.25
</p>',
    'quality_main_criteria_id' => 30,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 16,
  ),
  38 => 
  array (
    'id' => 83,
    'name' => '(2.2) กรรมการตามคำสั่งคณะ หรือ งานที่คณบดี มอบหมาย',
    'sequence' => 2,
    'num_score' => '0.50',
    'description' => '<p>มีคำสั่งและมีการดำเนินงาน 0.5
</p><p>                มีคำสั่ง
0.25
</p>',
    'quality_main_criteria_id' => 30,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 16,
  ),
  39 => 
  array (
    'id' => 84,
    'name' => '(2.3) การเข้าร่วมประชุมประจำเดือนของคณะ',
    'sequence' => 3,
    'num_score' => '0.50',
    'description' => '<p>เข้าร่วมร้อยละ 60-100 ได้ 1.0
</p><p>เข้าร่วม&lt;ร้อยละ 60 ได้ 0.5</p>',
    'quality_main_criteria_id' => 30,
    'criteria_version_id' => 2,
    'evaluation_list_id' => 16,
  ),
  40 => 
  array (
    'id' => 85,
    'name' => '1) ผลการประเมินการสอนจากนิสิต (ทุกวิชา)',
    'sequence' => 1,
    'num_score' => '2.50',
    'description' => '<ul><li>มากที่สุด ได้ 2.5</li><li>มาก ได้ 2.0</li><li>ปานกลาง ได้ 1.5</li><li>น้อย ได้ 1.0</li></ul>',
    'quality_main_criteria_id' => 31,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 18,
  ),
  41 => 
  array (
    'id' => 86,
    'name' => '2.1 ส่งข้อสอบตรงเวลาที่กำหนด',
    'sequence' => 1,
    'num_score' => '1.25',
    'description' => '',
    'quality_main_criteria_id' => 32,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 18,
  ),
  42 => 
  array (
    'id' => 87,
    'name' => '2.2 วิเคราะห์ข้อสอบของปีที่ผ่านมา (ถ้าวิชาใหม่ จะนับในปีถัดไป)',
    'sequence' => 2,
    'num_score' => '1.25',
    'description' => '',
    'quality_main_criteria_id' => 32,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 18,
  ),
  43 => 
  array (
    'id' => 88,
    'name' => '3.1 ส่ง มคอ.3 ตรงเวลาที่กำหนด',
    'sequence' => 1,
    'num_score' => '1.25',
    'description' => '',
    'quality_main_criteria_id' => 33,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 18,
  ),
  44 => 
  array (
    'id' => 89,
    'name' => '3.2 ส่ง มคอ.5 และเกรดตรงเวลาที่กำหนด',
    'sequence' => 2,
    'num_score' => '1.25',
    'description' => '',
    'quality_main_criteria_id' => 33,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 18,
  ),
  45 => 
  array (
    'id' => 90,
    'name' => '4.1 ลงไฟล์สอนก่อนเปิดภาคเรียน',
    'sequence' => 1,
    'num_score' => '1.25',
    'description' => '',
    'quality_main_criteria_id' => 34,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 18,
  ),
  46 => 
  array (
    'id' => 91,
    'name' => '4.2 ลงไฟล์สอนครบถ้วนตาม มคอ.3',
    'sequence' => 2,
    'num_score' => '1.25',
    'description' => '',
    'quality_main_criteria_id' => 34,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 18,
  ),
  47 => 
  array (
    'id' => 92,
    'name' => '1. แหล่งทุน',
    'sequence' => 1,
    'num_score' => '0.60',
    'description' => '<p>-&nbsp;ภายนอก มมส. (ต้องมีหลักฐานการหัก 
10% ให้ มมส.)   ได้ 0.6</p><p>-&nbsp;ภายใน มมส. 
ได้ 0.3 </p>',
    'quality_main_criteria_id' => 35,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 19,
  ),
  48 => 
  array (
    'id' => 93,
    'name' => '2. งานวิจัยแล้วเสร็จ ตามแผนงานวิจัย',
    'sequence' => 1,
    'num_score' => '3.00',
    'description' => '<p style="outline-color: oklab(0.869595 0.0000396371 0.0000174046 / 0.5); --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;">80-100% ได้ 3.0</p><p style="outline-color: oklab(0.869595 0.0000396371 0.0000174046 / 0.5); --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;">60-79% ได้ 2.5</p><p style="outline-color: oklab(0.869595 0.0000396371 0.0000174046 / 0.5); --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;">40-59% ได้ 2.0</p><p style="outline-color: oklab(0.869595 0.0000396371 0.0000174046 / 0.5); --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;">20-39% ได้ 1.5</p><p style="outline-color: oklab(0.869595 0.0000396371 0.0000174046 / 0.5); --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;">&lt;20% ได้ 0</p>',
    'quality_main_criteria_id' => 36,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 19,
  ),
  49 => 
  array (
    'id' => 94,
    'name' => '3. งานวิจัยมีการตีพิมพ์เผยแพร่',
    'sequence' => 1,
    'num_score' => '2.40',
    'description' => '<table class="table table-bordered" style="text-align: center;"><tbody><tr><td><b>องค์ประกอบการพิจารณา</b></td><td><b>1st author</b></td><td><p><b>Corresponding author</b></p></td><td><b>ลำดับอื่นๆ</b></td></tr><tr><td>ISI</td><td>&nbsp;ได้ 2.4</td><td>ได้ 2.4</td><td>ได้ 2.0</td></tr><tr><td>SCOPUS, นานาชาติ อื่นๆ</td><td>ได้ 2.0</td><td>ได้ 2.0</td><td>ได้ 1.8</td></tr><tr><td>TCI 1</td><td>ได้ 1.8</td><td>ได้ 1.8</td><td>ได้ 1.2</td></tr><tr><td>TCI 2</td><td>ได้ 1.2</td><td>ได้ 1.2</td><td>ได้ 0.6</td></tr></tbody></table>',
    'quality_main_criteria_id' => 37,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 19,
  ),
  50 => 
  array (
    'id' => 95,
    'name' => 'ก่อให้เกิดรายได้ให้แก่ มมส.',
    'sequence' => 1,
    'num_score' => '0.60',
    'description' => '',
    'quality_main_criteria_id' => 38,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 20,
  ),
  51 => 
  array (
    'id' => 96,
    'name' => 'ไม่ก่อให้เกิดรายได้ให้ มมส.',
    'sequence' => 2,
    'num_score' => '0.00',
    'description' => '',
    'quality_main_criteria_id' => 38,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 20,
  ),
  52 => 
  array (
    'id' => 97,
    'name' => '2. ลักษณะของการบริการวิชาการ',
    'sequence' => 1,
    'num_score' => '2.40',
    'description' => '<table class="table table-bordered" style="text-align: center;"><tbody><tr><td style="text-align: left; "><b>2. ลักษณะของการบริการวิชาการ</b></td><td><b>หัวหน้าโครงการ</b></td><td><p><b>ผู้ร่วมโครงการ</b></p><p><b> กรรมการที่ได้รับแต่งตั้ง</b></p></td><td><p><b>อื่นๆ เช่น ได้รับเชิญ</b></p><p><b>แบบระบุชื่อ ฯลฯ</b></p></td></tr><tr><td style="text-align: left;">2.1 โครงการบริการวิชาการ (ที่มีคณะกรรมการ) 
(ต่อ 1 โครงการ)</td><td>ได้ 2.4</td><td>ได้ 1.8</td><td>-</td></tr><tr><td style="text-align: left;">2.2 การให้บริการวิชาการลักษณะอื่น</td><td><br></td><td><br></td><td><br></td></tr><tr><td style="text-align: left;"><p style="margin-left: 25px;">2.2.1 เป็น Peer review วารสารระดับ
 นานาชาติ </p><p style="margin-left: 25px;">(เช่น ISI, SCOPUS) (ต่อ 1 เรื่อง)</p></td><td>-</td><td>-</td><td>ได้ 1.2</td></tr><tr><td style="text-align: left;"><p style="margin-left: 25px;">2.2.2 เป็น Peer review วารสารระดับชาติ 
(TCI) (ต่อ 1 เรื่อง)</p></td><td>-</td><td>-</td><td>ได้ 0.6</td></tr><tr><td style="text-align: left;"><p style="margin-left: 25px;">2.2.3 เป็นวิทยากรบรรยายความรู้/ปฏิบัติ 
(ต่อครั้ง)</p></td><td>-</td><td>-</td><td>ได้ 0.6</td></tr><tr><td style="text-align: left;"><p style="margin-left: 25px;">2.2.4 เป็นกรรมการสอบระดับบัณฑิตศึกษา
</p><p style="margin-left: 25px;"> ภายนอก มมส. หรือ ภายนอกคณะ (ต่อครั้ง)</p></td><td>-</td><td>-</td><td>ได้ 0.6</td></tr><tr><td style="text-align: left;"><p style="margin-left: 25px;">2.2.5 เป็นกรรมการตัดสินผลงานวิชาการ  
(ต่อครั้ง)</p></td><td>-</td><td>-</td><td>ได้ 0.6</td></tr><tr><td style="text-align: left;"><p style="margin-left: 25px;">2.2.6 เป็นผู้ทรงคุณวุฒิให้หน่วยงานอื่น  
(ต่อครั้ง)</p></td><td>-</td><td>-</td><td>ได้ 0.6</td></tr></tbody></table>',
    'quality_main_criteria_id' => 39,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 20,
  ),
  53 => 
  array (
    'id' => 98,
    'name' => 'เข้าร่วม',
    'sequence' => 1,
    'num_score' => '0.15',
    'description' => '',
    'quality_main_criteria_id' => 40,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  54 => 
  array (
    'id' => 99,
    'name' => 'จัดส่งรายงาน แก่หัวหน้าหน่วยงาน',
    'sequence' => 2,
    'num_score' => '0.15',
    'description' => '',
    'quality_main_criteria_id' => 40,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  55 => 
  array (
    'id' => 100,
    'name' => 'เข้าร่วม',
    'sequence' => 1,
    'num_score' => '0.15',
    'description' => '',
    'quality_main_criteria_id' => 41,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  56 => 
  array (
    'id' => 101,
    'name' => 'จัดส่งรายงาน แก่หัวหน้าหน่วยงาน',
    'sequence' => 2,
    'num_score' => '0.15',
    'description' => '',
    'quality_main_criteria_id' => 41,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  57 => 
  array (
    'id' => 102,
    'name' => 'เข้าร่วม',
    'sequence' => 1,
    'num_score' => '0.15',
    'description' => '',
    'quality_main_criteria_id' => 42,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  58 => 
  array (
    'id' => 103,
    'name' => 'จัดส่งรายงาน แก่หัวหน้าหน่วยงาน',
    'sequence' => 2,
    'num_score' => '0.15',
    'description' => '',
    'quality_main_criteria_id' => 42,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  59 => 
  array (
    'id' => 104,
    'name' => 'เข้าร่วม',
    'sequence' => 1,
    'num_score' => '0.15',
    'description' => '',
    'quality_main_criteria_id' => 43,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  60 => 
  array (
    'id' => 105,
    'name' => 'จัดส่งรายงาน แก่หัวหน้าหน่วยงาน',
    'sequence' => 2,
    'num_score' => '0.15',
    'description' => '',
    'quality_main_criteria_id' => 43,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  61 => 
  array (
    'id' => 106,
    'name' => 'เข้าร่วม',
    'sequence' => 1,
    'num_score' => '0.15',
    'description' => '',
    'quality_main_criteria_id' => 44,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  62 => 
  array (
    'id' => 107,
    'name' => 'จัดส่งรายงาน แก่หัวหน้าหน่วยงาน',
    'sequence' => 2,
    'num_score' => '0.15',
    'description' => '',
    'quality_main_criteria_id' => 44,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  63 => 
  array (
    'id' => 108,
    'name' => 'เข้าร่วม',
    'sequence' => 1,
    'num_score' => '0.30',
    'description' => '',
    'quality_main_criteria_id' => 45,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  64 => 
  array (
    'id' => 109,
    'name' => 'จัดส่งรายงาน แก่หัวหน้าหน่วยงาน',
    'sequence' => 2,
    'num_score' => '0.30',
    'description' => '',
    'quality_main_criteria_id' => 45,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  65 => 
  array (
    'id' => 110,
    'name' => 'เข้าร่วม',
    'sequence' => 1,
    'num_score' => '0.15',
    'description' => '',
    'quality_main_criteria_id' => 46,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  66 => 
  array (
    'id' => 111,
    'name' => 'จัดส่งรายงาน แก่หัวหน้าหน่วยงาน',
    'sequence' => 2,
    'num_score' => '0.15',
    'description' => '',
    'quality_main_criteria_id' => 46,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  67 => 
  array (
    'id' => 112,
    'name' => 'ใส่',
    'sequence' => 1,
    'num_score' => '0.60',
    'description' => '',
    'quality_main_criteria_id' => 47,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  68 => 
  array (
    'id' => 113,
    'name' => 'ไม่ใส่',
    'sequence' => 2,
    'num_score' => '0.00',
    'description' => '',
    'quality_main_criteria_id' => 47,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 21,
  ),
  69 => 
  array (
    'id' => 114,
    'name' => '1.1  พัฒนาตนเองตามความต้องการของหน่วยงานเพื่อพัฒนาด้านการเรียนการสอน (ต่อครั้ง)',
    'sequence' => 1,
    'num_score' => '0.40',
    'description' => '<p>จัดโดยหน่วยงานภาครัฐ&nbsp; หรือเอกชน ได้ 0.4</p><p>หากศึกษาด้วยตนเอง&nbsp; (ต้องมี Cert.รับรอง) ได้ 0.2</p>',
    'quality_main_criteria_id' => 48,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 22,
  ),
  70 => 
  array (
    'id' => 115,
    'name' => '1.2 พัฒนาตนเองตามความต้องการของหน่วยงานเพื่อพัฒนาทักษะด้านการวิจัย (ต่อครั้ง)',
    'sequence' => 1,
    'num_score' => '0.40',
    'description' => '<p>จัดโดยหน่วยงานภาครัฐ&nbsp; หรือเอกชน ได้ 0.4</p><p>หากศึกษาด้วยตนเอง&nbsp; (ต้องมี Cert.รับรอง) ได้ 0.2</p>',
    'quality_main_criteria_id' => 49,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 22,
  ),
  71 => 
  array (
    'id' => 116,
    'name' => '1.3 พัฒนาตนเองตามความต้องการของหน่วยงานเพื่อพัฒนาความเชี่ยวชาญด้านอื่นๆ เช่น  ภาษาต่างประเทศ การใช้เครื่องมือทางวิทยาศาสตร์ ฯลฯ (ต่อครั้ง)',
    'sequence' => 1,
    'num_score' => '0.20',
    'description' => '<p>จัดโดยหน่วยงานภาครัฐ&nbsp; หรือเอกชน ได้ 0.2</p><p>หากศึกษาด้วยตนเอง&nbsp; (ต้องมี Cert.รับรอง) ได้ 0.1</p>',
    'quality_main_criteria_id' => 50,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 22,
  ),
  72 => 
  array (
    'id' => 117,
    'name' => '2.1 พัฒนาตนเองตามความสนใจเพื่อพัฒนาด้านการเรียนการสอน (ต่อครั้ง)',
    'sequence' => 1,
    'num_score' => '0.40',
    'description' => '<p>จัดโดยหน่วยงานภาครัฐ&nbsp; หรือเอกชน ได้ 0.4</p><p>หากศึกษาด้วยตนเอง&nbsp; (ต้องมี Cert.รับรอง) ได้ 0.2</p>',
    'quality_main_criteria_id' => 51,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 22,
  ),
  73 => 
  array (
    'id' => 118,
    'name' => '2.2 พัฒนาตนเองตามความสนใจเพื่อพัฒนาทักษะด้านการวิจัย (ต่อครั้ง)',
    'sequence' => 1,
    'num_score' => '0.40',
    'description' => '<p>จัดโดยหน่วยงานภาครัฐ&nbsp; หรือเอกชน ได้ 0.4</p><p>หากศึกษาด้วยตนเอง&nbsp; (ต้องมี Cert.รับรอง) ได้ 0.2</p>',
    'quality_main_criteria_id' => 52,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 22,
  ),
  74 => 
  array (
    'id' => 119,
    'name' => '2.3 พัฒนาตนเองตามความสนใจเพื่อพัฒนาความเชี่ยวชาญด้านอื่นๆ เช่น  ภาษาต่างประเทศ การใช้เครื่องมือทางวิทยาศาสตร์ ฯลฯ (ต่อครั้ง)',
    'sequence' => 1,
    'num_score' => '0.20',
    'description' => '<p>จัดโดยหน่วยงานภาครัฐ&nbsp; หรือเอกชน ได้ 0.2</p><p>หากศึกษาด้วยตนเอง&nbsp; (ต้องมี Cert.รับรอง) ได้ 0.1</p>',
    'quality_main_criteria_id' => 53,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 22,
  ),
  75 => 
  array (
    'id' => 120,
    'name' => '1. การเผยแพร่ผลงานวิชาการ (ต่อเรื่อง)  เฉพาะงานในวงรอบเท่านั้น',
    'sequence' => 1,
    'num_score' => '1.00',
    'description' => '<table class="table table-bordered" style="text-align: center;"><tbody><tr><td style="text-align: center; "><b>องค์ประกอบการพิจารณา</b></td><td style="text-align: center; "><b>1st author</b></td><td style="text-align: center; "><b>Corresponding author</b></td><td style="text-align: center; "><b>ลำดับอื่นๆ</b></td></tr><tr><td style="text-align: center; ">จดอนุสิทธิบัตร</td><td style="text-align: center;">ได้ 1.0</td><td style="text-align: center; ">ได้ 1.0</td><td style="text-align: center;">ได้ 0.75</td></tr><tr><td style="text-align: center;">วารสารนานาชาติ เช่น ISI, SCOPUS</td><td style="text-align: center;">ได้ 1.0</td><td style="text-align: center;">ได้ 1.0</td><td style="text-align: center;">ได้ 0.75</td></tr><tr><td style="text-align: center;">TCI 1</td><td style="text-align: center;">ได้ 0.75</td><td style="text-align: center;">ได้ 0.75</td><td style="text-align: center;">ได้ 0.5</td></tr><tr><td style="text-align: center;">TCI 2</td><td style="text-align: center;">ได้ 0.5</td><td style="text-align: center;">ได้ 0.5</td><td style="text-align: center;">ได้ 0.25</td></tr><tr><td style="text-align: center;">Proceeding (full &amp; inter.)</td><td style="text-align: center;">ได้ 0.5</td><td style="text-align: center;">ได้ 0.5</td><td style="text-align: center;">ได้ 0.25</td></tr><tr><td style="text-align: center;">Proceeding</td><td style="text-align: center;">ได้ 0.25</td><td style="text-align: center;">ได้ 0.25</td><td style="text-align: center;">ได้ 0.1</td></tr><tr><td style="text-align: center;">Abstract book</td><td style="text-align: center;">ได้ 0.25</td><td style="text-align: center;">ได้ 0.25</td><td style="text-align: center;">ได้ 0.1</td></tr></tbody></table>',
    'quality_main_criteria_id' => 54,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 23,
  ),
  76 => 
  array (
    'id' => 121,
    'name' => '2.1 ตำรา/ หนังสือ (1 วิชาใช้ได้ 2 ปีงบประมาณ)',
    'sequence' => 1,
    'num_score' => '0.40',
    'description' => '<p>มี peer review 
                                    ได้ 0.4
</p><p>                                    ไม่มี peer review 
                                    ได้ 0.2
                                    </p>',
    'quality_main_criteria_id' => 55,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 23,
  ),
  77 => 
  array (
    'id' => 122,
    'name' => '2.2 เอกสารประกอบการสอน (1 วิชาใช้ได้1 ปีงบประมาณ)',
    'sequence' => 2,
    'num_score' => '0.20',
    'description' => '<p>มี peer review 
                                    ได้ 0.2
</p><p>                                    ไม่มี peer review 
                                    ได้ 0.1
                                    </p>',
    'quality_main_criteria_id' => 55,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 23,
  ),
  78 => 
  array (
    'id' => 123,
    'name' => '2.3 ไฟล์สอน ใน Classroom (1 วิชา ต่อครั้งของการประเมิน)',
    'sequence' => 3,
    'num_score' => '0.20',
    'description' => '<p>ทันเวลาและครบถ้วน 
                                    ได้ 0.2
</p><p>                                    ทันเวลาหรือครบถ้วน 
                                    ได้ 0.1
                                    </p>',
    'quality_main_criteria_id' => 55,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 23,
  ),
  79 => 
  array (
    'id' => 124,
    'name' => '2.4 คลิป วีดิทัศน์ หรือสื่อการสอนรูปแบบอื่น (1 วิชา ต่อครั้งของการประเมิน) ซึ่งเป็นส่วนเสริม ข้อ 2.3',
    'sequence' => 4,
    'num_score' => '1.00',
    'description' => '',
    'quality_main_criteria_id' => 55,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 23,
  ),
  80 => 
  array (
    'id' => 125,
    'name' => 'มีคำสั่งแต่งตั้ง',
    'sequence' => 1,
    'num_score' => '0.50',
    'description' => '',
    'quality_main_criteria_id' => 56,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 24,
  ),
  81 => 
  array (
    'id' => 126,
    'name' => 'มีผลการดำเนินงาน',
    'sequence' => 2,
    'num_score' => '0.50',
    'description' => '',
    'quality_main_criteria_id' => 56,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 24,
  ),
  82 => 
  array (
    'id' => 127,
    'name' => 'มีคำสั่งแต่งตั้ง',
    'sequence' => 1,
    'num_score' => '0.50',
    'description' => '',
    'quality_main_criteria_id' => 57,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 24,
  ),
  83 => 
  array (
    'id' => 128,
    'name' => 'มีผลการดำเนินงาน',
    'sequence' => 2,
    'num_score' => '0.50',
    'description' => '',
    'quality_main_criteria_id' => 57,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 24,
  ),
  84 => 
  array (
    'id' => 129,
    'name' => 'เข้าร่วมร้อยละ 80-100',
    'sequence' => 1,
    'num_score' => '0.40',
    'description' => '',
    'quality_main_criteria_id' => 58,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 25,
  ),
  85 => 
  array (
    'id' => 130,
    'name' => 'เข้าร่วมต่ำกว่าร้อยละ 20',
    'sequence' => 2,
    'num_score' => '0.00',
    'description' => '',
    'quality_main_criteria_id' => 58,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 25,
  ),
  86 => 
  array (
    'id' => 131,
    'name' => 'กรรมการตามคำสั่งคณะ หรืองานที่คณบดีมอบหมาย',
    'sequence' => 1,
    'num_score' => '1.00',
    'description' => '<p>มีคำสั่งแต่งตั้งและมีการดำเนินงานตามคำสั่ง* </p><p>
(ได้ 0.2 คะแนน ต่อ 1 คำสั่ง) 
รวมสูงสุดได้ 1 คะแนน</p>',
    'quality_main_criteria_id' => 59,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 25,
  ),
  87 => 
  array (
    'id' => 132,
    'name' => 'อาจารย์ผู้รับผิดชอบหลักสูตร',
    'sequence' => 1,
    'num_score' => '0.60',
    'description' => '<p>มีคำสั่งแต่งตั้งและมีการดำเนินงานตามคำสั่ง*</p>',
    'quality_main_criteria_id' => 60,
    'criteria_version_id' => 3,
    'evaluation_list_id' => 25,
  ),
  88 => 
  array (
    'id' => 2528,
    'name' => 'มีครบ 4',
    'sequence' => 1,
    'num_score' => '1.20',
    'description' => NULL,
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 277,
  ),
  89 => 
  array (
    'id' => 2529,
    'name' => 'มี 3 ข้อ',
    'sequence' => 2,
    'num_score' => '1.00',
    'description' => NULL,
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 277,
  ),
  90 => 
  array (
    'id' => 2530,
    'name' => 'มี 2 ข้อ',
    'sequence' => 3,
    'num_score' => '0.80',
    'description' => NULL,
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 277,
  ),
  91 => 
  array (
    'id' => 2531,
    'name' => 'มี 1 ข้อ',
    'sequence' => 4,
    'num_score' => '0.60',
    'description' => NULL,
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 277,
  ),
  92 => 
  array (
    'id' => 2532,
    'name' => '2.1 มีสื่อการสอนมากกว่า 1 รูปแบบ',
    'sequence' => 1,
    'num_score' => '1.20',
    'description' => NULL,
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 277,
  ),
  93 => 
  array (
    'id' => 2533,
    'name' => '2.2 มีการใช้สื่อการเรียนการสอนภาษาอังกฤษร่วมด้วย',
    'sequence' => 2,
    'num_score' => '0.60',
    'description' => NULL,
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 277,
  ),
  94 => 
  array (
    'id' => 2534,
    'name' => 'มีครบ 2 ข้อ',
    'sequence' => 1,
    'num_score' => '1.20',
    'description' => NULL,
    'quality_main_criteria_id' => 575,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 277,
  ),
  95 => 
  array (
    'id' => 2535,
    'name' => 'มี 1 ข้อ',
    'sequence' => 2,
    'num_score' => '0.60',
    'description' => NULL,
    'quality_main_criteria_id' => 575,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 277,
  ),
  96 => 
  array (
    'id' => 2536,
    'name' => 'มีครบ 2 ข้อ',
    'sequence' => 1,
    'num_score' => '1.20',
    'description' => NULL,
    'quality_main_criteria_id' => 576,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 277,
  ),
  97 => 
  array (
    'id' => 2537,
    'name' => 'มี 1 ข้อ',
    'sequence' => 2,
    'num_score' => '0.60',
    'description' => NULL,
    'quality_main_criteria_id' => 576,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 277,
  ),
  98 => 
  array (
    'id' => 2538,
    'name' => '>4.49',
    'sequence' => 1,
    'num_score' => '1.20',
    'description' => NULL,
    'quality_main_criteria_id' => 577,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 277,
  ),
  99 => 
  array (
    'id' => 2539,
    'name' => '4.00– 4.49',
    'sequence' => 2,
    'num_score' => '1.00',
    'description' => NULL,
    'quality_main_criteria_id' => 577,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 277,
  ),
  100 => 
  array (
    'id' => 2540,
    'name' => '3.50 –3.99',
    'sequence' => 3,
    'num_score' => '0.80',
    'description' => NULL,
    'quality_main_criteria_id' => 577,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 277,
  ),
  101 => 
  array (
    'id' => 2541,
    'name' => '3.00-3.49',
    'sequence' => 4,
    'num_score' => '0.60',
    'description' => NULL,
    'quality_main_criteria_id' => 577,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 277,
  ),
  102 => 
  array (
    'id' => 2622,
    'name' => 'ทุนวิจัยจากต่างประเทศ',
    'sequence' => 1,
    'num_score' => '1.00',
    'description' => '<p><br></p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 278,
  ),
  103 => 
  array (
    'id' => 2623,
    'name' => 'ทุนวิจัยภายนอกมหาวิทยาลัย',
    'sequence' => 2,
    'num_score' => '0.80',
    'description' => '<p><br></p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 278,
  ),
  104 => 
  array (
    'id' => 2624,
    'name' => 'ทุนวิจัยภายใน มหาวิทยาลัย',
    'sequence' => 3,
    'num_score' => '0.50',
    'description' => '<p><br></p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 278,
  ),
  105 => 
  array (
    'id' => 2625,
    'name' => '> 300,000',
    'sequence' => 1,
    'num_score' => '1.00',
    'description' => '<p><br></p>',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 278,
  ),
  106 => 
  array (
    'id' => 2626,
    'name' => '100,001 – 300,000',
    'sequence' => 2,
    'num_score' => '0.80',
    'description' => '<p><br></p>',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 278,
  ),
  107 => 
  array (
    'id' => 2627,
    'name' => '70,000 -100,000',
    'sequence' => 3,
    'num_score' => '0.50',
    'description' => '<p><br></p>',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 278,
  ),
  108 => 
  array (
    'id' => 2628,
    'name' => '80-100%',
    'sequence' => 1,
    'num_score' => '2.00',
    'description' => '<p><br></p>',
    'quality_main_criteria_id' => 575,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 278,
  ),
  109 => 
  array (
    'id' => 2629,
    'name' => '60-79%',
    'sequence' => 2,
    'num_score' => '1.80',
    'description' => '<p><br></p>',
    'quality_main_criteria_id' => 575,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 278,
  ),
  110 => 
  array (
    'id' => 2630,
    'name' => '40-59%',
    'sequence' => 3,
    'num_score' => '1.60',
    'description' => '<p><br></p>',
    'quality_main_criteria_id' => 575,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 278,
  ),
  111 => 
  array (
    'id' => 2631,
    'name' => '20-39%',
    'sequence' => 4,
    'num_score' => '1.40',
    'description' => '<p><br></p>',
    'quality_main_criteria_id' => 575,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 278,
  ),
  112 => 
  array (
    'id' => 2632,
    'name' => '<20%',
    'sequence' => 5,
    'num_score' => '1.20',
    'description' => '<p><br></p>',
    'quality_main_criteria_id' => 575,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 278,
  ),
  113 => 
  array (
    'id' => 2633,
    'name' => 'มี',
    'sequence' => 1,
    'num_score' => '1.00',
    'description' => '<p><br></p>',
    'quality_main_criteria_id' => 576,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 278,
  ),
  114 => 
  array (
    'id' => 2634,
    'name' => 'ไม่มี',
    'sequence' => 2,
    'num_score' => '0.00',
    'description' => '<p><br></p>',
    'quality_main_criteria_id' => 576,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 278,
  ),
  115 => 
  array (
    'id' => 2635,
    'name' => 'เกิดรายได้',
    'sequence' => 1,
    'num_score' => '0.60',
    'description' => '1.1 เป็นโครงการที่สร้างรายได้ให้กับมหาวิทยาลัย',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 279,
  ),
  116 => 
  array (
    'id' => 2636,
    'name' => 'เกิดผลกระทบ (ประโยชน์)',
    'sequence' => 2,
    'num_score' => '0.60',
    'description' => '1.2 เป็นโครงการที่สร้างผลตอบแทนที่เป็นประโยชน์ทางสังคม (SROI)',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 279,
  ),
  117 => 
  array (
    'id' => 2637,
    'name' => '2.1 โครงการบริการวิชาการ (ที่มีคณะกรรมการ) (ต่อ 1 โครงการ)',
    'sequence' => 1,
    'num_score' => '2.00',
    'description' => '<br>',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 279,
  ),
  118 => 
  array (
    'id' => 2638,
    'name' => '2.2.1 เป็น Peer review วารสารระดับ นานาชาติ (เช่น ISI, SCOPUS) (ต่อ 1 เรื่อง)',
    'sequence' => 2,
    'num_score' => '1.20',
    'description' => '2.2 การให้บริการวิชาการลักษณะอื่น',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 279,
  ),
  119 => 
  array (
    'id' => 2639,
    'name' => '2.2.2 เป็น Peer review วารสารระดับชาติ  (TCI) (ต่อ 1 เรื่อง)',
    'sequence' => 3,
    'num_score' => '0.60',
    'description' => '2.2 การให้บริการวิชาการลักษณะอื่น',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 279,
  ),
  120 => 
  array (
    'id' => 2640,
    'name' => '2.2.3 เป็นวิทยากรบรรยายความรู้/ปฏิบัติ (ต่อครั้ง)',
    'sequence' => 4,
    'num_score' => '0.60',
    'description' => '2.2 การให้บริการวิชาการลักษณะอื่น',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 279,
  ),
  121 => 
  array (
    'id' => 2641,
    'name' => '2.2.4 เป็นกรรมการสอบระดับบัณฑิตศึกษา ภายนอก มมส. หรือ ภายนอกคณะ (ต่อครั้ง)',
    'sequence' => 5,
    'num_score' => '0.60',
    'description' => '2.2 การให้บริการวิชาการลักษณะอื่น',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 279,
  ),
  122 => 
  array (
    'id' => 2642,
    'name' => '2.2.5 เป็นกรรมการตัดสินผลงานวิชาการระดับชาติ (ต่อครั้ง)',
    'sequence' => 6,
    'num_score' => '0.60',
    'description' => '2.2 การให้บริการวิชาการลักษณะอื่น',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 279,
  ),
  123 => 
  array (
    'id' => 2643,
    'name' => '2.2.6 เป็นผู้ทรงคุณวุฒิให้หน่วยงานอื่น (ต่อครั้ง)',
    'sequence' => 7,
    'num_score' => '0.60',
    'description' => '<p>2.2 การให้บริการวิชาการลักษณะอื่น</p>',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 279,
  ),
  124 => 
  array (
    'id' => 2644,
    'name' => 'วันมหิดล (วงรอบ 1)',
    'sequence' => 1,
    'num_score' => '1.00',
    'description' => '<br>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 280,
  ),
  125 => 
  array (
    'id' => 2645,
    'name' => 'วันคล้ายวันสถาปนาคณะฯ (วงรอบ 2)',
    'sequence' => 2,
    'num_score' => '1.00',
    'description' => '<br>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 280,
  ),
  126 => 
  array (
    'id' => 2646,
    'name' => 'วันปฐมนิเทศนิสิตใหม่ (วงรอบ 2)',
    'sequence' => 3,
    'num_score' => '1.00',
    'description' => '<br>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 280,
  ),
  127 => 
  array (
    'id' => 2647,
    'name' => 'วันไหว้ครู (วงรอบ 2)',
    'sequence' => 4,
    'num_score' => '1.00',
    'description' => '<br>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 280,
  ),
  128 => 
  array (
    'id' => 2648,
    'name' => 'พิธีพระราชทานปริญญาบัตร มมส.',
    'sequence' => 5,
    'num_score' => '1.00',
    'description' => '<br>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 280,
  ),
  129 => 
  array (
    'id' => 2649,
    'name' => 'กิจกรรมวันปีใหม่ (วงรอบ 1)',
    'sequence' => 6,
    'num_score' => '1.00',
    'description' => '<br>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 280,
  ),
  130 => 
  array (
    'id' => 2650,
    'name' => 'กิจกรรมวันสงกรานต์ (วงรอบ 2)',
    'sequence' => 7,
    'num_score' => '1.00',
    'description' => '<br>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 280,
  ),
  131 => 
  array (
    'id' => 2651,
    'name' => 'กิจกรรมวันบุญเผวด (วงรอบ 1)',
    'sequence' => 8,
    'num_score' => '1.00',
    'description' => '<br>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 280,
  ),
  132 => 
  array (
    'id' => 2652,
    'name' => 'กิจกรรมวันคล้ายวันสถาปนามหาวิทยาลัย',
    'sequence' => 9,
    'num_score' => '1.00',
    'description' => '<br>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 280,
  ),
  133 => 
  array (
    'id' => 2653,
    'name' => 'เข้าร่วมกิจกรรมอื่น ๆ ตามหนังสือเชิญอื่นๆ',
    'sequence' => 10,
    'num_score' => '1.00',
    'description' => '<br>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 280,
  ),
  134 => 
  array (
    'id' => 2654,
    'name' => '1.1 การพัฒนาองค์กรสู่ความเป็นเลิศ เช่น จัดทำแผน ฯลฯ',
    'sequence' => 1,
    'num_score' => '0.50',
    'description' => '<br>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 281,
  ),
  135 => 
  array (
    'id' => 2655,
    'name' => '1.2 ด้านการเรียนการสอน',
    'sequence' => 2,
    'num_score' => '0.50',
    'description' => '<br>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 281,
  ),
  136 => 
  array (
    'id' => 2656,
    'name' => '1.3 ด้านการวิจัย',
    'sequence' => 3,
    'num_score' => '0.50',
    'description' => '<br>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 281,
  ),
  137 => 
  array (
    'id' => 2657,
    'name' => '1.4 ความเชี่ยวชาญด้านอื่นๆ เช่น ภาษา  ต่างประเทศ การใช้เครื่องมือทางวิทยาศาสตร์ ฯลฯ (ต่อครั้ง)',
    'sequence' => 4,
    'num_score' => '0.50',
    'description' => '<br>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 281,
  ),
  138 => 
  array (
    'id' => 2658,
    'name' => '1.5 ด้านวิชาชีพ',
    'sequence' => 5,
    'num_score' => '0.50',
    'description' => '<br>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 281,
  ),
  139 => 
  array (
    'id' => 2659,
    'name' => '1.6 ด้านการจัดการความรู้ (KM) เพื่อเพิ่มประสิทธิภาพและคุณภาพงานในองค์กร',
    'sequence' => 6,
    'num_score' => '0.50',
    'description' => '<p>ผู้จัดการหลัก</p><p><br></p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 281,
  ),
  140 => 
  array (
    'id' => 2660,
    'name' => '1.6 ด้านการจัดการความรู้ (KM) เพื่อเพิ่มประสิทธิภาพและคุณภาพงานในองค์กร',
    'sequence' => 7,
    'num_score' => '0.25',
    'description' => '<p>ผู้ร่วม</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 281,
  ),
  141 => 
  array (
    'id' => 2661,
    'name' => '1.1 การจดทะเบียน IP - สิทธิบัตร',
    'sequence' => 1,
    'num_score' => '1.50',
    'description' => '<p>สิทธิบัตร 
                                    ผู้ถือสิทธิ์หลัก</p><p><br></p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  142 => 
  array (
    'id' => 2662,
    'name' => '1.1 การจดทะเบียน IP - สิทธิบัตร',
    'sequence' => 2,
    'num_score' => '1.20',
    'description' => '<p style="--tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;">สิทธิบัตร ผู้ถือสิทธิ์ร่วม&nbsp;</p><p style="--tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;"><br></p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  143 => 
  array (
    'id' => 2663,
    'name' => '1.1 การจดทะเบียน IP - อนุสิทธิบัตร',
    'sequence' => 3,
    'num_score' => '1.00',
    'description' => '<p style="--tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;">อนุสิทธิบัตร ผู้ถือสิทธิ์หลัก</p><p style="--tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;"><br></p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  144 => 
  array (
    'id' => 2664,
    'name' => '1.1 การจดทะเบียน IP - อนุสิทธิบัตร',
    'sequence' => 4,
    'num_score' => '0.75',
    'description' => '<p>อนุสิทธิบัตร&nbsp;ผู้ถือสิทธิ์ร่วม</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  145 => 
  array (
    'id' => 2665,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย - ISI/SCOPUS Q1-2',
    'sequence' => 5,
    'num_score' => '1.50',
    'description' => '<p>First/Corresponding Author</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  146 => 
  array (
    'id' => 2666,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย - ISI/SCOPUS Q1-2',
    'sequence' => 6,
    'num_score' => '1.25',
    'description' => '<p>First Co-contributor/Colleagues</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  147 => 
  array (
    'id' => 2667,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย - SCOPUS Q 3-4',
    'sequence' => 7,
    'num_score' => '1.00',
    'description' => '<p>&nbsp;First/Corresponding Author&nbsp;</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  148 => 
  array (
    'id' => 2668,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย - SCOPUS Q 3-4',
    'sequence' => 8,
    'num_score' => '0.75',
    'description' => '<p>Co-contributor/Colleagues&nbsp;</p><p><br></p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  149 => 
  array (
    'id' => 2669,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย-TCI 1',
    'sequence' => 9,
    'num_score' => '0.75',
    'description' => '<p>&nbsp;First/Corresponding Author&nbsp;</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  150 => 
  array (
    'id' => 2670,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย-TCI 1',
    'sequence' => 10,
    'num_score' => '0.50',
    'description' => '<p>Co-contributor/Colleagues&nbsp;</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  151 => 
  array (
    'id' => 2671,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย-TCI 2',
    'sequence' => 11,
    'num_score' => '0.50',
    'description' => '<p>&nbsp;First/Corresponding Author&nbsp;</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  152 => 
  array (
    'id' => 2672,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย-TCI 2',
    'sequence' => 12,
    'num_score' => '0.25',
    'description' => '<p>Co-contributor/Colleagues&nbsp;</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  153 => 
  array (
    'id' => 2673,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย - Proceeding   (full & inter.)',
    'sequence' => 13,
    'num_score' => '0.50',
    'description' => '<p>&nbsp;First/Corresponding Author&nbsp;</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  154 => 
  array (
    'id' => 2674,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย - Proceeding   (full & inter.)',
    'sequence' => 14,
    'num_score' => '0.25',
    'description' => '<p>Co-contributor/Colleagues&nbsp;</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  155 => 
  array (
    'id' => 2675,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย - Proceeding',
    'sequence' => 15,
    'num_score' => '0.25',
    'description' => '<p>&nbsp;First/Corresponding Author&nbsp;</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  156 => 
  array (
    'id' => 2676,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย - Proceeding',
    'sequence' => 16,
    'num_score' => '0.10',
    'description' => '<p>Co-contributor/Colleagues&nbsp;</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  157 => 
  array (
    'id' => 2677,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย - Abstract book',
    'sequence' => 17,
    'num_score' => '0.25',
    'description' => '<p>&nbsp;First/Corresponding Author&nbsp;</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  158 => 
  array (
    'id' => 2678,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย - Abstract book',
    'sequence' => 18,
    'num_score' => '0.10',
    'description' => '<p>Co-contributor/Colleagues&nbsp;</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  159 => 
  array (
    'id' => 2679,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย - Citation เฉพาะวารสารนานาชาติ',
    'sequence' => 19,
    'num_score' => '1.00',
    'description' => '<p>10 citations</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  160 => 
  array (
    'id' => 2680,
    'name' => '1.2 บทความวิชาการ/บทความวิจัย - Citation เฉพาะวารสารนานาชาติ',
    'sequence' => 20,
    'num_score' => '0.10',
    'description' => '<p>1 citations</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  161 => 
  array (
    'id' => 2681,
    'name' => '2.1 ตำรา/หนังสือ (มี peer review)',
    'sequence' => 1,
    'num_score' => '1.50',
    'description' => NULL,
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  162 => 
  array (
    'id' => 2682,
    'name' => '2.1 ตำรา/หนังสือ (ไม่มี peer review)',
    'sequence' => 2,
    'num_score' => '1.00',
    'description' => '<p><br></p>',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  163 => 
  array (
    'id' => 2683,
    'name' => '2.2 เอกสารประกอบการสอน(มี peer review)',
    'sequence' => 3,
    'num_score' => '0.20',
    'description' => '<p><br></p>',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  164 => 
  array (
    'id' => 2684,
    'name' => '2.2 เอกสารประกอบการสอน(ไม่มี peer review )',
    'sequence' => 4,
    'num_score' => '0.10',
    'description' => '<p><br></p><div><br></div>',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  165 => 
  array (
    'id' => 2685,
    'name' => '2.3 สื่อการเรียนรู้ด้วยตนเอง (Self-study) ที่จัดทำขึ้นเองและเผยแพร่บน Platform ที่นิสิตสามารถเรียนได้ด้วยตนเองตลอด อย่างน้อย 1 ภาคการศึกษา (ต่อ 1 หน่วยกิต)',
    'sequence' => 5,
    'num_score' => '1.00',
    'description' => '<p>มีการเผยแพร่และมีการประเมินผลความพึงพอใจจากผู้เรียน</p><div><br></div>',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  166 => 
  array (
    'id' => 2686,
    'name' => '2.3 สื่อการเรียนรู้ด้วยตนเอง (Self-study) ที่จัดทำขึ้นเองและเผยแพร่บน Platform ที่นิสิตสามารถเรียนได้ด้วยตนเองตลอด อย่างน้อย 1 ภาคการศึกษา (ต่อ 1 หน่วยกิต)',
    'sequence' => 6,
    'num_score' => '0.50',
    'description' => '<p>มีการเผยแพร่</p>',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 282,
  ),
  167 => 
  array (
    'id' => 2687,
    'name' => '1.1 การสร้างผลงานทางวิชาการระดับนานาชาติที่เป็นเลิศ',
    'sequence' => 1,
    'num_score' => '4.00',
    'description' => '<p>Principle conductor/Corresponding Author/First Author</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 283,
  ),
  168 => 
  array (
    'id' => 2688,
    'name' => '1.1 การสร้างผลงานทางวิชาการระดับนานาชาติที่เป็นเลิศ(Co-contributor/Colleagues)',
    'sequence' => 2,
    'num_score' => '3.00',
    'description' => '<p>Co-contributor/Colleagues&nbsp;</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 283,
  ),
  169 => 
  array (
    'id' => 2689,
    'name' => '1.2 การสร้างความร่วมมือด้านวิจัยที่เป็นเลิศ(Principle conductor/Corresponding Author/First Author)',
    'sequence' => 3,
    'num_score' => '4.00',
    'description' => '<p>Principle conductor/Corresponding Author/First Author&nbsp;</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 283,
  ),
  170 => 
  array (
    'id' => 2690,
    'name' => '1.2 การสร้างความร่วมมือด้านวิจัยที่เป็นเลิศ(Co-contributor/Colleagues)',
    'sequence' => 4,
    'num_score' => '3.00',
    'description' => '<p>Co-contributor/Colleagues</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 283,
  ),
  171 => 
  array (
    'id' => 2691,
    'name' => '1.3 การบริการวิชาการระดับนานาชาติ (Principle conductor/Corresponding Author/First Author)',
    'sequence' => 5,
    'num_score' => '4.00',
    'description' => '<p>Principle conductor/Corresponding Author/First Author</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 283,
  ),
  172 => 
  array (
    'id' => 2692,
    'name' => '1.3 การบริการวิชาการระดับนานาชาติ (Co-contributor/Colleagues)',
    'sequence' => 6,
    'num_score' => '3.00',
    'description' => '<p>Co-contributor/Colleagues&nbsp;</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 283,
  ),
  173 => 
  array (
    'id' => 2693,
    'name' => '1.4 การขับเคลื่อนองค์กรมุ่งสู่ความเป็นเลิศของคณะฯ(Principle conductor/Corresponding Author/First Autho)',
    'sequence' => 7,
    'num_score' => '2.00',
    'description' => '<p>Principle conductor/Corresponding Author/First Author</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 283,
  ),
  174 => 
  array (
    'id' => 2694,
    'name' => '1.4 การขับเคลื่อนองค์กรมุ่งสู่ความเป็นเลิศของคณะฯ(Co-contributor/Colleagues)',
    'sequence' => 8,
    'num_score' => '2.00',
    'description' => '<p>Co-contributor/Colleagues&nbsp;</p>',
    'quality_main_criteria_id' => 573,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 283,
  ),
  175 => 
  array (
    'id' => 2695,
    'name' => '(2.1) กรรมการต่าง ๆ(มีคำสั่งและมีการดำเนินงาน)',
    'sequence' => 1,
    'num_score' => '1.00',
    'description' => '<p>กรรมการต่อเนื่องตามคำสั่งคณะ หรือ มหาวิทยาลัย (ต่อ 1 คำสั่ง) (มีกำหนดการประชุมหรือการดำเนินงานที่ชัดเจน)</p>',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 283,
  ),
  176 => 
  array (
    'id' => 2696,
    'name' => '(2.1) กรรมการต่าง ๆ(มีคำสั่ง)',
    'sequence' => 2,
    'num_score' => '0.50',
    'description' => '<p>กรรมการต่อเนื่องตามคำสั่งคณะ หรือ มหาวิทยาลัย (ต่อ 1 คำสั่ง) (มีกำหนดการประชุมหรือการดำเนินงานที่ชัดเจน)</p>',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 283,
  ),
  177 => 
  array (
    'id' => 2697,
    'name' => '(2.1) กรรมการต่าง ๆ(มีคำสั่งและมีการดำเนินงาน)',
    'sequence' => 3,
    'num_score' => '0.20',
    'description' => '<p>กรรมการตามคำสั่งคณะ หรือ งานที่คณบดี มอบหมาย&nbsp;</p>',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 283,
  ),
  178 => 
  array (
    'id' => 2698,
    'name' => '(2.1) กรรมการต่าง ๆ(มีคำสั่ง)',
    'sequence' => 4,
    'num_score' => '0.10',
    'description' => '<p>กรรมการตามคำสั่งคณะ หรือ งานที่คณบดี มอบหมาย&nbsp;</p>',
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 283,
  ),
  179 => 
  array (
    'id' => 2699,
    'name' => '(2.2) การเข้าร่วมประชุมประจำเดือนของคณะ(เข้าร่วมร้อยละ 75-100)',
    'sequence' => 5,
    'num_score' => '0.40',
    'description' => NULL,
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 283,
  ),
  180 => 
  array (
    'id' => 2700,
    'name' => '(2.2) การเข้าร่วมประชุมประจำเดือนของคณะ(เข้าร่วมร้อยละ 50-75)',
    'sequence' => 6,
    'num_score' => '0.20',
    'description' => NULL,
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 283,
  ),
  181 => 
  array (
    'id' => 2701,
    'name' => '(2.2) การเข้าร่วมประชุมประจำเดือนของคณะ(เข้าร่วม<ร้อยละ 50 )',
    'sequence' => 7,
    'num_score' => '0.00',
    'description' => NULL,
    'quality_main_criteria_id' => 574,
    'criteria_version_id' => 1,
    'evaluation_list_id' => 283,
  ),
));

        DB::table('formulas')->insert(array (
  0 => 
  array (
    'id' => 41,
    'condition' => 'D = A × C / B',
    'quantity_main_criteria_id' => 41,
    'created_at' => '2026-02-18 06:46:48',
    'updated_at' => '2026-02-18 06:46:48',
  ),
));

    }
}
