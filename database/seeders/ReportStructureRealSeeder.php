<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportStructureRealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('criteria_versions')->insert([
            [
                'id' => 1,
                'version_name' => 'เกณฑ์การประเมินกลุ่มอาจารย์ ปี 2568',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'version_name' => 'เกณฑ์การประเมินกลุ่มสายสนับสนุน ปี 2568',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('report_datas')->insert([
            [
                'id' => 1,
                'report_title' => 'เกณฑ์การให้คะแนนการประเมินพนักงานสายวิชาการตามมาตรฐานภาระงาน คณะสาธารณสุขศาสตร์ มหาวิทยาลัยมหาสารคาม (กลุ่มอาจารย์)',
                'report_description' => 'การประเมินคุณภาพการปฏิบัติงานของบุคลากรสายวิชาการ ประจำปีงบประมาณ 2568',
                'assessment_type' => 'กลุ่มวิชาการ',
                'comment' => 'ประเมินตามเกณฑ์มาตรฐานของมหาวิทยาลัย',
                'criteria_version_id' => 1,
            ],
            [
                'id' => 2,
                'report_title' => 'เกณฑ์การให้คะแนนการประเมินพนักงานสายวิชาการตามมาตรฐานภาระงาน คณะสาธารณสุขศาสตร์ มหาวิทยาลัยมหาสารคาม (กลุ่มสายสนับสนุน)',
                'report_description' => 'การประเมินคุณภาพการปฏิบัติงานของบุคลากรสายสนับสนุน ประจำปีงบประมาณ 2568',
                'assessment_type' => 'กลุ่มสนับสนุน',
                'comment' => 'ประเมินตามเกณฑ์มาตรฐานของมหาวิทยาลัย',
                'criteria_version_id' => 2,
            ],
        ]);

        DB::table('categories')->insert([
            [
                'id' => 1,
                'main_categories' => 'ผลสัมฤทธิ์ของการปฏิบัติงาน',
                'sub_categories' => '1. ด้านปริมาณผลงาน',
                'sequence' => 1,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 2,
                'main_categories' => 'ผลสัมฤทธิ์ของการปฏิบัติงาน',
                'sub_categories' => '2. ด้านคุณภาพผลงาน',
                'sequence' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 3,
                'main_categories' => 'ผลสัมฤทธิ์ของการปฏิบัติงาน',
                'sub_categories' => '1. ด้านปริมาณผลงาน',
                'sequence' => 1,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 4,
                'main_categories' => 'ผลสัมฤทธิ์ของการปฏิบัติงาน',
                'sub_categories' => '2. ด้านคุณภาพผลงาน',
                'sequence' => 2,
                'criteria_version_id' => 2,
            ],
        ]);

        DB::table('evaluation_lists')->insert([
            [
                'id' => 1,
                'name' => '1. ปริมาณผลงาน',
                'sum_score' => 40.00,
                'sequence' => 1,
                'annotation' => '',
                'categorie_id' => 1,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 2,
                'name' => '2.1 ภาระงานด้านการสอน',
                'sum_score' => 6.00,
                'sequence' => 2,
                'annotation' => 'ข้อมูลข้อที่ (1), (2), (3) และ (4) อาจารย์ไม่ต้องแนบ เนื่องจากเอกสารดังกล่าวได้ส่งให้งานวิชาการตามกำหนดแล้ว
                                    ข้อมูลที่อาจารย์ต้องแนบ คือ ข้อที่ (5) Link ค่าคะแนนเฉลี่ยจากทุกรายวิชา ',
                'categorie_id' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 3,
                'name' => '2.2 ภาระงานด้านการวิจัย',
                'sum_score' => 5.00,
                'sequence' => 3,
                'annotation' => '* การดำเนินงานวิจัยตามแผนให้คิดร้อยละความก้าวหน้า ดังนี้ 
                                    80-100% คือ ส่ง manuscript + ส่งเล่มสมบูรณ์ + เบิกงวด 2 + ส่งรายงานความก้าวหน้า + เบิกงวด 1 
                                    60- 79% คือ ส่งเล่มสมบูรณ์ + เบิกงวด 2 + ส่งรายงานความก้าวหน้า + เบิกงวด 1 
                                    40-59% คือ เบิกงวด 2 + ส่งรายงานความก้าวหน้า + เบิกงวด 1 
                                    20-39% คือ ส่งรายงานความก้าวหน้า + เบิกงวด 1 
                                    <20% คือ เบิกงวด 1
                                    ** รายการข้อที่ 4 ใช้เอกสารแบบฟอร์มรายงานการนำไปใช้ประโยชน์ได้ (ที่ใช้ประกอบการปิดโครงการวิจัยของคณะ)',
                'categorie_id' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 4,
                'name' => '2.3 ภาระงานด้านการบริการวิชาการ',
                'sum_score' => 3.00,
                'sequence' => 4,
                'annotation' => '1. กรณีที่ข้อ 1 ได้ 0 คะแนน แม้ว่ามีการดำเนินในข้อ 2 จำนวนมาก แต่คะแนนสูงสุดจะได้เท่ากับ 2.4 คะแนน 
                                    2. ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้ (ในรอบถัดไป ให้ระบุด้วยว่ายังไม่เคยใช้ในรอบ ก่อนหน้านี้)',
                'categorie_id' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 5,
                'name' => '2.4 ภาระงานด้านการทำนุบำรุงศิลปวัฒนธรรม',
                'sum_score' => 3.00,
                'sequence' => 5,
                'annotation' => 'งานบุคคลจะเป็นผู้รวบรวมและรายงานผลคะแนน ท่านสามารถตรวจสอบได้ตลอดเวลา และข้อให้ยืนยันก่อนครบกำหนดวันสุดท้ายของวงรอบการประเมิน ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้',
                'categorie_id' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 6,
                'name' => '2.5 ภาระงานด้านการพัฒนาตนเอง',
                'sum_score' => 2.00,
                'sequence' => 6,
                'annotation' => '1. การพัฒนาตนเองในส่วนที่คณะเป็นผู้จัด ทางงานบุคคลจะเป็นผู้ดำเนินการบันทึกและรายงานผลให้เอง 
                                    2. หากเป็นการศึกษาอบรมผ่านรูปแบบออนไลน์จะต้องมีใบประกาศนียบัตรหรือเทียบเท่ารับรองว่าได้เข้าพัฒนาตนเองครบ ตามหลักสูตรจริงๆ 
                                    3. ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้ (ในรอบถัดไป ให้ระบุด้วยว่ายังไม่เคยใช้ใน  รอบก่อนหน้านี้)',
                'categorie_id' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 7,
                'name' => '2.6 ภาระงานด้านผลงานวิชาการ',
                'sum_score' => 3.00,
                'sequence' => 7,
                'annotation' => '1. กรณีที่ข้อ 1 ได้ 0 คะแนน แม้ว่ามีการดำเนินในข้อ 2 จนครบ แต่คะแนนสูงสุดจะได้เท่ากับ 1.5 คะแนน 
                                    2. ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้ (ในรอบถัดไป ให้ระบุด้วยว่ายังไม่เคยใช้ใน  รอบก่อนหน้านี้)',
                'categorie_id' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 8,
                'name' => '2.7 ภาระงานด้านการบริหารองค์กรสู่ความเป็นเลิศ',
                'sum_score' => 8.00,
                'sequence' => 8,
                'annotation' => 'คะแนนในทุกด้านรวมกันไม่เกิน ร้อยละ 50',
                'categorie_id' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 9,
                'name' => '1. ด้านปริมาณผลงาน',
                'sum_score' => 40.00,
                'sequence' => 1,
                'annotation' => '',
                'categorie_id' => 3,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 10,
                'name' => '2.1 ภาระงานในหน้าที่',
                'sum_score' => 15.00,
                'sequence' => 2,
                'annotation' => '',
                'categorie_id' => 4,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 11,
                'name' => '2.2 ภาระงานด้านการพัฒนาระบบงาน',
                'sum_score' => 3.00,
                'sequence' => 3,
                'annotation' => '',
                'categorie_id' => 4,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 12,
                'name' => '2.3 ภาระงานด้านการบริการวิชาการ',
                'sum_score' => 0.50,
                'sequence' => 4,
                'annotation' => 'หมายเหตุ 
เมื่อสิ้นสุดในแต่ละกิจกรรม ต้องจัดส่งรายงานแก่หัวหน้างาน
หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในวงรอบถัดไป
',
                'categorie_id' => 4,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 13,
                'name' => '2.4 ภาระงานด้านการทำนุบำรุงศิลปวัฒนธรรม',
                'sum_score' => 1.50,
                'sequence' => 5,
                'annotation' => 'หมายเหตุ 
งานบุคคลจะเป็นผู้รวบรวมและรายงานผลคะแนน ท่านสามารถตรวจสอบได้ตลอดเวลา และข้อให้ยืนยันก่อนครบกำหนดวันสุดท้ายของวงรอบการประเมิน ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้',
                'categorie_id' => 4,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 14,
                'name' => '2.5 ภาระงานด้านการพัฒนาตนเอง',
                'sum_score' => 2.00,
                'sequence' => 6,
                'annotation' => 'หมายเหตุ 
1. การพัฒนาตนเองในส่วนที่คณะเป็นผู้จัด ทางงานบุคคลจะเป็นผู้ดำเนินการบันทึกและรายงานผลให้เอง 
2. หากเป็นการศึกษาอบรมผ่านรูปแบบออนไลน์จะต้องมีใบประกาศนียบัตรหรือเทียบเท่ารับรองว่าได้เข้าพัฒนาตนเองครบ ตามหลักสูตรจริงๆ 
3. ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถนำไปใช้ในรอบถัดไปได้ (ในรอบถัดไป ให้ระบุด้วยว่ายังไม่เคยใช้ใน  รอบก่อนหน้านี้)',
                'categorie_id' => 4,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 15,
                'name' => '2.6 ภาระงานด้านผลงานวิจัย',
                'sum_score' => 2.00,
                'sequence' => 7,
                'annotation' => 'หมายเหตุ 
80-100% คือ ส่ง manuscript + ส่งเล่มสมบูรณ์ + เบิกงวด 2 + ส่งรายงานความก้าวหน้า + เบิกงวด 1 60-79% คือ ส่งเล่มสมบูรณ์ + เบิกงวด 2 + ส่งรายงานความก้าวหน้า + เบิกงวด 1 
40-59% คือ เบิกงวด 2 + ส่งรายงานความก้าวหน้า + เบิกงวด 1 
20-39% คือ ส่งรายงานความก้าวหน้า + เบิกงวด 1 
<20% คือ เบิกงวด 1
',
                'categorie_id' => 4,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 16,
                'name' => '2.7 ภาระงานด้านการบริหารองค์กรสู่ความเป็นเลิศ',
                'sum_score' => 8.00,
                'sequence' => 8,
                'annotation' => 'หมายเหตุ ผลการดำเนินงานเชิงประจักษ์ เช่น มีชื่อเข้าร่วมการประชุมไม่ต่ำกว่าร้อยละ 80 ฯลฯ ',
                'categorie_id' => 4,
                'criteria_version_id' => 2,
            ],
        ]);

        DB::table('quantity_main_criterias')->insert([
            [
                'id' => 1,
                'name' => '1. ปริมาณผลงาน',
                'tooltips' => '1. งานสอน รายวิชาศึกษาทั่วไป ไม่ให้นำมาคิดภาระงานในทุกกรณี (ทั้งเชิงปริมาณและเชิงคุณภาพ) ยกเว้นผู้ที่ไม่ได้เบิกค่าสอนวิชานั้นๆ สามารถนำมาคิดได้เฉพาะเชิงปริมาณ 
                                    2. วงรอบ 
                                    วงรอบที่ 1 (1 กันยายน 256x – 28 กุมภาพันธ์ 256x) ใช้วิชาที่สอนในภาคปลาย 
                                    วงรอบที่ 2 (1 มีนาคม 256x – 31 สิงหาคม 256x) ใช้วิชาที่สอนในภาคการศึกษาพิเศษ (ถ้ามี) + ภาคต้น 
                                    3. ภาระงานสอนในส่วนของวิชาที่ได้รับมอบหมายให้กรอกเฉพาะตัวเลข สรุปภาระงานตามคำสั่งมอบหมายภาระงานสอน (ไม่ต้องแนบไฟล์) ส่วนภาระงานอื่น ๆ ให้กรอกในระบบด้วยตนเอง โดยแขวนลิงค์ในช่องหลักฐาน',
                'criteria_version_id' => 1,
            ],
            [
                'id' => 2,
                'name' => '1. ปริมาณผลงาน',
                'tooltips' => '',
                'criteria_version_id' => 2,
            ],
        ]);

        DB::table('quantity_sub_criterias')->insert([
            [
                'id' => 1,
                'name' => '1.1 ภาระงานด้านการสอน ',
                'sequence' => 1,
                'score_a' => 15.00,
                'score_b' => 300.00,
                'quantity_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 1,
            ],
            [
                'id' => 2,
                'name' => '1.2 ภาระด้านการวิจัย',
                'sequence' => 2,
                'score_a' => 8.00,
                'score_b' => 160.00,
                'quantity_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 1,
            ],
            [
                'id' => 3,
                'name' => '1.3 ภาระงานบริการวิชาการ',
                'sequence' => 3,
                'score_a' => 4.00,
                'score_b' => 80.00,
                'quantity_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 1,
            ],
            [
                'id' => 4,
                'name' => '1.4 ภาระทำนุบำรุงศิลปวัฒนธรรม',
                'sequence' => 4,
                'score_a' => 4.00,
                'score_b' => 80.00,
                'quantity_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 1,
            ],
            [
                'id' => 5,
                'name' => '1.5 ภาระงานพัฒนาตนเอง',
                'sequence' => 5,
                'score_a' => 3.00,
                'score_b' => 60.00,
                'quantity_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 1,
            ],
            [
                'id' => 6,
                'name' => '1.6 ภาระผลงานทางวิชาการ',
                'sequence' => 6,
                'score_a' => 3.00,
                'score_b' => 60.00,
                'quantity_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 1,
            ],
            [
                'id' => 7,
                'name' => '1.7 ภาระงานด้านบริหารองค์กรสู่ความเป็นเลิศ ',
                'sequence' => 7,
                'score_a' => 3.00,
                'score_b' => 60.00,
                'quantity_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 1,
            ],
            [
                'id' => 8,
                'name' => '1.1 ภาระงานในหน้าที่',
                'sequence' => 1,
                'score_a' => 20.00,
                'score_b' => 400.00,
                'quantity_main_criteria_id' => 2,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 9,
            ],
            [
                'id' => 9,
                'name' => '1.2 ภาระด้านการพัฒนาระบบงาน',
                'sequence' => 2,
                'score_a' => 4.00,
                'score_b' => 80.00,
                'quantity_main_criteria_id' => 2,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 9,
            ],
            [
                'id' => 10,
                'name' => '1.3 ภาระงานบริการวิชาการ',
                'sequence' => 3,
                'score_a' => 1.00,
                'score_b' => 20.00,
                'quantity_main_criteria_id' => 2,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 9,
            ],
            [
                'id' => 11,
                'name' => '1.4 ภาระทำนุบำรุงศิลปวัฒนธรรม',
                'sequence' => 4,
                'score_a' => 2.00,
                'score_b' => 40.00,
                'quantity_main_criteria_id' => 2,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 9,
            ],
            [
                'id' => 12,
                'name' => '1.5 ภาระงานพัฒนาตนเอง',
                'sequence' => 5,
                'score_a' => 3.00,
                'score_b' => 60.00,
                'quantity_main_criteria_id' => 2,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 9,
            ],
            [
                'id' => 13,
                'name' => '1.6 ภาระผลงานด้านวิจัย',
                'sequence' => 6,
                'score_a' => 2.00,
                'score_b' => 40.00,
                'quantity_main_criteria_id' => 2,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 9,
            ],
            [
                'id' => 14,
                'name' => '1.7 ภาระงานด้านการบริหารองค์กรสู่ความเป็นเลิศ',
                'sequence' => 7,
                'score_a' => 8.00,
                'score_b' => 160.00,
                'quantity_main_criteria_id' => 2,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 9,
            ],
        ]);

        DB::table('quality_main_criterias')->insert([
            [
                'id' => 1,
                'name' => '(1) การเตรียมแผนการจัดการเรียนการสอน',
                'ratio' => 20,
                'tooltips' => '<p>  1.1 ส่ง มคอ.3/4 ตามเวลาที่กำหนด (ทุกวิชาที่สอน) </p><p>
                                    1.2 มีแผนการจัดการเรียนการสอนแบบ Active Learning
</p><p>                                    1.3 มีการกำหนดเกณฑ์ในการประเมินที่สอดคล้องกับ CLOs
                                    1.4 มีกำหนดช่องทางและช่วงเวลาในการสื่อสารไปยังผู้เรียน</p>',
                'sequence' => 1,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 2,
                'name' => '(2) คุณภาพสื่อการเรียนการสอน',
                'ratio' => 20,
                'tooltips' => '<p>2.1 มีสื่อการสอนมากกว่า 1 รูปแบบ
</p><p>                                    2.2 มีการใช้สื่อการเรียนการสอนภาษาอังกฤษร่วมด้วย</p>',
                'sequence' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 3,
                'name' => '(3) การประเมินผลและการวัดผล',
                'ratio' => 20,
                'tooltips' => '<p>3.1 ส่งข้อสอบตรงเวลาที่กำหนด (ทุกวิชาที่สอน)
</p><p>                                    3.2 ข้อสอบหรือแบบทดสอบสอดคล้องกับการบรรลุ CLOs</p>',
                'sequence' => 3,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 4,
                'name' => '(4) การรายงานผล',
                'ratio' => 20,
                'tooltips' => '<p>4.1 ส่งเกรดตรงเวลาที่กำหนด(ทุกวิชาที่สอน)
</p><p>                                    4.2 ส่ง มคอ.5/6 ตามเวลาที่กำหนด (ทุกวิชาที่สอน)</p>',
                'sequence' => 4,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 5,
                'name' => '(5) ผลการประเมินการสอนจากนิสิต (ทุกวิชา)**',
                'ratio' => 20,
                'tooltips' => 'หาค่าเฉลี่ยจากทุกรายวิชา',
                'sequence' => 5,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 6,
                'name' => '1. แหล่งทุน',
                'ratio' => 20,
                'tooltips' => 'งานวิจัยรวบรวมสรุปให้',
                'sequence' => 1,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 7,
                'name' => '2. งบประมาณ (บาท/ปี)',
                'ratio' => 20,
                'tooltips' => '(ไม่ต้องคิดสัดส่วนความรับผิดชอบ)',
                'sequence' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 8,
                'name' => '3. การดำเนินงานวิจัยตามแผน*',
                'ratio' => 40,
                'tooltips' => '(นับรวมงานวิจัยที่ได้รับการอนุมัติขยายเวลา)',
                'sequence' => 3,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 9,
                'name' => '4. การนำไปใช้ประโยชน์**',
                'ratio' => 20,
                'tooltips' => '<p>อยู่ในรายงานการใช้ประโยชน์งานวิจัย
                                    (4.1) </p><p>ตีพิมพ์เผยแพร่
                                    (4.2) </p><p>การเรียนการสอน
                                    (4.3) </p><p>ถ่ายทอดเทคโนโลยี
                                    (4.4) </p><p>ยื่นจด IP</p>',
                'sequence' => 4,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 10,
                'name' => '1. รูปแบบของการจัดบริการวิชาการ',
                'ratio' => 20,
                'tooltips' => '<p>1.1 เป็นโครงการที่สร้างรายได้ให้กับมหาวิทยาลัย
</p><p>                                    1.2 เป็นโครงการที่สร้างผลตอบแทนที่เป็นประโยชน์ทางสังคม (SROI)</p>',
                'sequence' => 1,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 11,
                'name' => '2. ลักษณะของการบริการวิชาการ',
                'ratio' => 80,
                'tooltips' => '<p>2.1 โครงการบริการวิชาการ (ที่มีคณะกรรมการ) (ต่อ 1 โครงการ)
</p><ul><li>                                    2.2 การให้บริการวิชาการลักษณะอื่น
</li></ul><p>                                    2.2.1 เป็น Peer review วารสารระดับ นานาชาติ (เช่น ISI, SCOPUS) (ต่อ 1 เรื่อง)
</p><p>                                    2.2.2 เป็น Peer review วารสารระดับชาติ  (TCI) (ต่อ 1 เรื่อง)
</p><p>                                    2.2.3 เป็นวิทยากรบรรยายความรู้/ปฏิบัติ (ต่อครั้ง)
</p><p>                                    2.2.4 เป็นกรรมการสอบระดับบัณฑิตศึกษา ภายนอก มมส. หรือ ภายนอกคณะ (ต่อครั้ง)
</p><p>                                    2.2.5 เป็นกรรมการตัดสินผลงานวิชาการระดับชาติ (ต่อครั้ง)
</p><p>                                    2.2.6 เป็นผู้ทรงคุณวุฒิให้หน่วยงานอื่น (ต่อครั้ง)</p>',
                'sequence' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 12,
                'name' => 'เข้าร่วมงานพิธีที่ดำเนินการโดยคณะ/มหาวิทยาลัย (ต่อครั้ง)',
                'ratio' => 100,
                'tooltips' => 'งานบุคคลจะเป็นผู้รวบรวมข้อมูล',
                'sequence' => 1,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 13,
                'name' => 'พัฒนาตนเองตามความต้องการของหน่วยงาน (ต่อครั้ง)',
                'ratio' => 100,
                'tooltips' => 'งานบุคคลจะเป็นผู้รวบรวมข้อมูล',
                'sequence' => 1,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 14,
                'name' => '1. การเผยแพร่ผลงานวิชาการ (ต่อเรื่อง) ',
                'ratio' => 50,
                'tooltips' => '<p>1.1 การจดทะเบียน IP
</p><p>                                    1.2 บทความวิชาการ/บทความวิจัย</p>',
                'sequence' => 1,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 15,
                'name' => '2. เอกสารและสื่อประกอบการสอน',
                'ratio' => 50,
                'tooltips' => '<p>2.1 ตำรา/หนังสือ (1 วิชาใช้ได้ 2 ปีงบประมาณ)
</p><p>                                    2.2 เอกสารประกอบการสอน (1 วิชาใช้ได้ 1 ปีงบประมาณ)
</p><p>                                    2.3 สื่อการเรียนรู้ด้วยตนเอง (Self-study) ที่จัดทำขึ้นเองและเผยแพร่บน Platform ที่นิสิตสามารถเรียนได้ด้วยตนเองตลอด อย่างน้อย 1 ภาคการศึกษา (ต่อ 1 หน่วยกิต)</p>',
                'sequence' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 16,
                'name' => '1. ภาระงานมุ่งสู่ความเป็นเลิศ',
                'ratio' => 50,
                'tooltips' => '<p>(1.1) การสร้างผลงานทางวิชาการระดับนานาชาติที่เป็นเลิศ (ใช้ได้ 2 วงรอบ)
</p><p>                                    (1.2) การสร้างความร่วมมือด้านวิจัยที่เป็นเลิศ (ใช้ได้ 2 วงรอบ)
</p><p>                                    (1.3) การบริการวิชาการระดับนานาชาติ (ใช้ได้ 2 วงรอบ)
</p><p>                                    (1.4) การขับเคลื่อนองค์กรมุ่งสู่ความเป็นเลิศของคณะฯ</p>',
                'sequence' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 17,
                'name' => '2. ภาระงานบริหาร',
                'ratio' => 50,
                'tooltips' => '<p>(2.1) กรรมการต่าง ๆ
</p><p>                                    (2.2) การเข้าร่วมประชุมประจำเดือนของคณะ</p>',
                'sequence' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 18,
                'name' => '(1) มีการวางแผนการทำงานในหน้าที่',
                'ratio' => 25,
                'tooltips' => '',
                'sequence' => 1,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 19,
                'name' => '(2) มีการปฏิบัติตามขั้นตอนแผนงานที่กำหนดในข้อ (1)',
                'ratio' => 25,
                'tooltips' => '',
                'sequence' => 2,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 20,
                'name' => '(3) มีการตรวจสอบและประเมินผลการดำเนินงาน',
                'ratio' => 25,
                'tooltips' => '',
                'sequence' => 3,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 21,
                'name' => '(4) มีการสรุปและแนวทางในการปรับปรุงแก้ไข',
                'ratio' => 25,
                'tooltips' => '',
                'sequence' => 4,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 22,
                'name' => 'ภาระงานด้านการพัฒนาระบบงาน',
                'ratio' => 100,
                'tooltips' => '',
                'sequence' => 1,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 23,
                'name' => 'โครงการบริการวิชาการ (ต่อ 1 โครงการ)',
                'ratio' => 100,
                'tooltips' => '',
                'sequence' => 1,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 24,
                'name' => 'เข้าร่วมงานพิธีที่ดำเนินการโดยคณะ/มหาวิทยาลัย (ต่อครั้ง)',
                'ratio' => 100,
                'tooltips' => '',
                'sequence' => 1,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 25,
                'name' => 'พัฒนาตนเองตามความต้องการของหน่วยงาน (ต่อครั้ง)',
                'ratio' => 100,
                'tooltips' => '',
                'sequence' => 1,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 26,
                'name' => '1. การได้รับทุนสนับสนุนการวิจัย',
                'ratio' => 50,
                'tooltips' => '',
                'sequence' => 1,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 27,
                'name' => '1. งานวิจัยแล้วเสร็จ ตามแผนงานวิจัย',
                'ratio' => 25,
                'tooltips' => '(ใช้ได้ถึงกรอบการอนุมัติขยายเวลา)',
                'sequence' => 2,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 28,
                'name' => '2. งานวิจัยมีการตีพิมพ์เผยแพร่',
                'ratio' => 25,
                'tooltips' => 'เฉพาะงานที่สอดคล้องกับเชิงปริมาณใน วงรอบก่อนหน้านี้เท่านั้น 
- ทั้งนี้หากภาระงานเพียงพอแล้ว สามารถ นำไปใช้ในรอบถัดไปได้ (ในรอบถัดไป ให้ระบุ ด้วยว่ายังไม่เคยใช้ในรอบก่อนหน้านี้)',
                'sequence' => 3,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 29,
                'name' => '1. ภาระงานมุ่งสู่ความเป็นเลิศ',
                'ratio' => 62.5,
                'tooltips' => '',
                'sequence' => 1,
                'criteria_version_id' => 2,
            ],
            [
                'id' => 30,
                'name' => '2. ภาระงานบริหาร',
                'ratio' => 37.5,
                'tooltips' => '',
                'sequence' => 2,
                'criteria_version_id' => 2,
            ],

        ]);

        DB::table('quality_sub_criterias')->insert([
            [
                'id' => 1,
                'name' => '(1) การเตรียมแผนการจัดการเรียนการสอน',
                'sequence' => 1,
                'num_score' => 1.20,
                'description' => '<p>มีครบ 4 ข้อได้ 1.2
</p><p>                                        มี 3 ข้อ 1.0
</p><p>                                        มี 2 ข้อ 0.8
</p><p>                                        มี 1 ข้อ 0.6</p>',
                'quality_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 2,
            ],
            [
                'id' => 2,
                'name' => '(2) คุณภาพสื่อการเรียนการสอน',
                'sequence' => 2,
                'num_score' => 1.20,
                'description' => '<p>มีครบ 2 ข้อได้ 1.2</p><p>                                        มี 1 ข้อ 0.6</p>',
                'quality_main_criteria_id' => 2,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 2,
            ],
            [
                'id' => 3,
                'name' => '(3) คุณภาพสื่อการเรียนการสอน',
                'sequence' => 3,
                'num_score' => 1.20,
                'description' => '<p>มีครบ 2 ข้อได้ 1.2
</p><p>                                        มี 1 ข้อ 0.6</p>',
                'quality_main_criteria_id' => 3,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 2,
            ],
            [
                'id' => 4,
                'name' => '(4) การรายงานผล',
                'sequence' => 4,
                'num_score' => 1.20,
                'description' => '<p>มีครบ 2 ข้อได้ 1.2
</p><p>                                        มี 1 ข้อ 0.6</p>',
                'quality_main_criteria_id' => 4,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 2,
            ],
            [
                'id' => 5,
                'name' => 'ผลการประเมินการสอนจากนิสิต (ทุกวิชา)**',
                'sequence' => 5,
                'num_score' => 1.20,
                'description' => '<p>&gt;4.49 ได้ 1.2</p><p>
                                        4.00– 4.49 ได้ 1.0
</p><p>                                        3.50 –3.99 ได้ 0.8
</p><p>                                        3.00-3.49 ได้ 0.6</p>',
                'quality_main_criteria_id' => 5,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 2,
            ],
            [
                'id' => 6,
                'name' => '1. แหล่งทุน',
                'sequence' => 1,
                'num_score' => 1.00,
                'description' => '<p>ทุนวิจัยจากต่างประเทศ
                                        ได้ 1.0
</p><p>                                        ทุนวิจัยภายนอกมหาวิทยาลัย
                                        ได้ 0.8
</p><p>                                        ทุนวิจัยภายใน
                                        มหาวิทยาลัย
                                        ได้ 0.5
                                        </p>',
                'quality_main_criteria_id' => 6,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 3,
            ],
            [
                'id' => 7,
                'name' => '2. งบประมาณ (บาท/ปี) (ไม่ต้องคิดสัดส่วนความรับผิดชอบ)',
                'sequence' => 2,
                'num_score' => 1.00,
                'description' => '<p>&gt; 300,000
                                        ได้ 1.0
</p><p>                                        100,001 – 300,000
                                        ได้ 0.8</p><p>70,000 -100,000
                                        ได้ 0.5
                                        </p>',
                'quality_main_criteria_id' => 7,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 3,
            ],
            [
                'id' => 8,
                'name' => '3. การดำเนินงานวิจัยตามแผน*(นับรวมงานวิจัยที่ได้รับการอนุมัติขยายเวลา)',
                'sequence' => 3,
                'num_score' => 2.00,
                'description' => '<p>80-100% 
                                    ได้ 2.0
</p><p>                                    60-79% 
                                    ได้ 1.8
</p><p>                                    40-59% 
                                    ได้ 1.6
</p><p>                                    20-39% 
                                    ได้ 1.4
</p><p>                                    &lt;20% 
                                    ได้ 1.2
                                    </p>',
                'quality_main_criteria_id' => 8,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 3,
            ],
            [
                'id' => 9,
                'name' => '4. การนำไปใช้ประโยชน์**',
                'sequence' => 4,
                'num_score' => 1.00,
                'description' => '<p>มี
                                    ได้ 1.0
</p><p>                                    ไม่มี
                                    ได้ 0
                                    </p>',
                'quality_main_criteria_id' => 9,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 3,
            ],
            [
                'id' => 10,
                'name' => '1.1 เป็นโครงการที่สร้างรายได้ให้กับมหาวิทยาลัย',
                'sequence' => 1,
                'num_score' => 0.60,
                'description' => 'เกิดรายได้ ได้ 0.6',
                'quality_main_criteria_id' => 10,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 4,
            ],
            [
                'id' => 11,
                'name' => '1.2 เป็นโครงการที่สร้างผลตอบแทนที่เป็นประโยชน์ทางสังคม (SROI)',
                'sequence' => 2,
                'num_score' => 0.60,
                'description' => 'เกิดผลกระทบ (ประโยชน์)ได้ 0.6',
                'quality_main_criteria_id' => 10,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 4,
            ],
            [
                'id' => 12,
                'name' => '2.1 โครงการบริการวิชาการ (ที่มีคณะกรรมการ) (ต่อ 1 โครงการ)',
                'sequence' => 1,
                'num_score' => 2.00,
                'description' => 'ได้ 2.0',
                'quality_main_criteria_id' => 11,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 4,
            ],
            [
                'id' => 13,
                'name' => '2.2.1 เป็น Peer review วารสารระดับ นานาชาติ (เช่น ISI, SCOPUS) (ต่อ 1 เรื่อง)',
                'sequence' => 2,
                'num_score' => 1.20,
                'description' => 'ได้ 1.2',
                'quality_main_criteria_id' => 11,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 4,
            ],
            [
                'id' => 14,
                'name' => '2.2.2 เป็น Peer review วารสารระดับชาติ  (TCI) (ต่อ 1 เรื่อง)',
                'sequence' => 3,
                'num_score' => 0.60,
                'description' => 'ได้ 0.6',
                'quality_main_criteria_id' => 11,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 4,
            ],
            [
                'id' => 15,
                'name' => '2.2.3 เป็นวิทยากรบรรยายความรู้/ปฏิบัติ (ต่อครั้ง)',
                'sequence' => 4,
                'num_score' => 0.60,
                'description' => 'ได้ 0.6',
                'quality_main_criteria_id' => 11,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 4,
            ],
            [
                'id' => 16,
                'name' => '2.2.4 เป็นกรรมการสอบระดับบัณฑิตศึกษา ภายนอก มมส. หรือ ภายนอกคณะ (ต่อครั้ง)',
                'sequence' => 5,
                'num_score' => 0.60,
                'description' => 'ได้ 0.6',
                'quality_main_criteria_id' => 11,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 4,
            ],
            [
                'id' => 17,
                'name' => '2.2.5 เป็นกรรมการตัดสินผลงานวิชาการระดับชาติ (ต่อครั้ง)',
                'sequence' => 6,
                'num_score' => 0.60,
                'description' => 'ได้ 0.6',
                'quality_main_criteria_id' => 11,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 4,
            ],
            [
                'id' => 18,
                'name' => 'วันมหิดล (วงรอบ 1)',
                'sequence' => 1,
                'num_score' => 1.00,
                'description' => 'ได้ 1.0',
                'quality_main_criteria_id' => 12,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 5,
            ],
            [
                'id' => 19,
                'name' => 'วันคล้ายวันสถาปนาคณะฯ (วงรอบ 2) ',
                'sequence' => 2,
                'num_score' => 1.00,
                'description' => 'ได้ 1.0',
                'quality_main_criteria_id' => 12,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 5,
            ],
            [
                'id' => 20,
                'name' => 'วันปฐมนิเทศนิสิตใหม่ (วงรอบ 2) ',
                'sequence' => 3,
                'num_score' => 1.00,
                'description' => 'ได้ 1.0',
                'quality_main_criteria_id' => 12,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 5,
            ],
            [
                'id' => 21,
                'name' => 'วันไหว้ครู (วงรอบ 2) ',
                'sequence' => 4,
                'num_score' => 1.00,
                'description' => 'ได้ 1.0',
                'quality_main_criteria_id' => 12,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 5,
            ],
            [
                'id' => 22,
                'name' => 'พิธีพระราชทานปริญญาบัตร มมส.',
                'sequence' => 5,
                'num_score' => 1.00,
                'description' => 'ได้ 1.0',
                'quality_main_criteria_id' => 12,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 5,
            ],
            [
                'id' => 23,
                'name' => 'กิจกรรมวันปีใหม่ (วงรอบ 1)',
                'sequence' => 6,
                'num_score' => 1.00,
                'description' => 'ได้ 1.0',
                'quality_main_criteria_id' => 12,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 5,
            ],
            [
                'id' => 24,
                'name' => 'กิจกรรมวันสงกรานต์ (วงรอบ 2)',
                'sequence' => 7,
                'num_score' => 1.00,
                'description' => 'ได้ 1.0',
                'quality_main_criteria_id' => 12,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 5,
            ],
            [
                'id' => 25,
                'name' => 'กิจกรรมวันบุญเผวด (วงรอบ 1)',
                'sequence' => 8,
                'num_score' => 1.00,
                'description' => 'ได้ 1.0',
                'quality_main_criteria_id' => 12,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 5,
            ],
            [
                'id' => 26,
                'name' => 'กิจกรรมวันคล้ายวันสถาปนามหาวิทยาลัย',
                'sequence' => 9,
                'num_score' => 1.00,
                'description' => 'ได้ 1.0',
                'quality_main_criteria_id' => 12,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 5,
            ],
            [
                'id' => 27,
                'name' => 'เข้าร่วมกิจกรรมอื่น ๆ ตามหนังสือเชิญอื่นๆ',
                'sequence' => 10,
                'num_score' => 1.00,
                'description' => 'ได้ 1.0',
                'quality_main_criteria_id' => 12,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 5,
            ],
            [
                'id' => 28,
                'name' => '1.1 การพัฒนาองค์กรสู่ความเป็นเลิศ เช่น จัดทำแผน ฯลฯ',
                'sequence' => 1,
                'num_score' => 0.50,
                'description' => 'ได้ 0.50',
                'quality_main_criteria_id' => 13,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 6,
            ],
            [
                'id' => 29,
                'name' => '1.2 ด้านการเรียนการสอน ',
                'sequence' => 2,
                'num_score' => 0.50,
                'description' => 'ได้ 0.50',
                'quality_main_criteria_id' => 13,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 6,
            ],
            [
                'id' => 30,
                'name' => '1.3 ด้านการวิจัย',
                'sequence' => 3,
                'num_score' => 0.50,
                'description' => 'ได้ 0.50',
                'quality_main_criteria_id' => 13,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 6,
            ],
            [
                'id' => 31,
                'name' => '1.4 ความเชี่ยวชาญด้านอื่นๆ เช่น ภาษา  ต่างประเทศ การใช้เครื่องมือทางวิทยาศาสตร์ ฯลฯ (ต่อครั้ง)',
                'sequence' => 4,
                'num_score' => 0.50,
                'description' => 'ได้ 0.50',
                'quality_main_criteria_id' => 13,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 6,
            ],
            [
                'id' => 32,
                'name' => '1.5 ด้านวิชาชีพ',
                'sequence' => 5,
                'num_score' => 0.50,
                'description' => 'ได้ 0.50',
                'quality_main_criteria_id' => 13,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 6,
            ],
            [
                'id' => 33,
                'name' => '1.6 ด้านการจัดการความรู้ (KM) เพื่อเพิ่มประสิทธิภาพและคุณภาพงานในองค์กร',
                'sequence' => 6,
                'num_score' => 0.50,
                'description' => '<p>ผู้จัดการหลัก = 0.50 </p><p>ผู้ร่วม = 0.25</p>',
                'quality_main_criteria_id' => 13,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 6,
            ],
            [
                'id' => 34,
                'name' => '1.1 การจดทะเบียน IP',
                'sequence' => 1,
                'num_score' => 0.50,
                'description' => '<p>สิทธิบัตร 
                                    ผู้ถือสิทธิ์หลัก ได้ 1.5
</p><p>                                    ผู้ถือสิทธิ์ร่วม ได้ 1.25
</p><p>                                    อนุสิทธิบัตร
                                    ผู้ถือสิทธิ์หลัก ได้ 1.0
</p><p>                                    ผู้ถือสิทธิ์ร่วม ได้ 0.75</p>',
                'quality_main_criteria_id' => 14,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 7,
            ],
            [
                'id' => 35,
                'name' => '1.2 บทความวิชาการ/บทความวิจัย',
                'sequence' => 2,
                'num_score' => 1.50,
                'description' => '<p>ISI/SCOPUS Q1-2 
                                    First/Corresponding Author ได้ 1.5
</p><p>                                    Co-contributor/Colleagues ได้ 1.25
</p><p>                                    SCOPUS Q 3-4
                                    First/Corresponding Author ได้ 1.5
</p><p>                                    Co-contributor/Colleagues ได้ 0.75
</p><p>                                    TCI 1
                                    First/Corresponding Author ได้ 1.5
</p><p>                                    Co-contributor/Colleagues ได้ 0.75
</p><p>                                    TCI2
                                    First/Corresponding Author ได้ 1.5
</p><p>                                    Co-contributor/Colleagues ได้ 0.75
</p><p>                                    Proceeding(full &amp; inter.)
                                    First/Corresponding Author ได้ 1.5
</p><p>                                    Co-contributor/Colleagues ได้ 0.75
</p><p>                                    Proceeding
                                    First/Corresponding Author ได้ 1.5
</p><p>                                    Co-contributor/Colleagues ได้ 0.75
</p><p>                                    Abstract book
                                    First/Corresponding Author ได้ 1.5
</p><p>                                    Co-contributor/Colleagues ได้ 0.75
</p><p>                                    Citation เฉพาะวารสารนานาชาติ
                                    1 </p><p>citation = 0.1
</p><p>                                    10 citations = 1.0</p>',
                'quality_main_criteria_id' => 14,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 7,
            ],
            [
                'id' => 36,
                'name' => '2.1 ตำรา/หนังสือ (1 วิชาใช้ได้ 2 ปีงบประมาณ)',
                'sequence' => 1,
                'num_score' => 1.50,
                'description' => '<p>มี peer review 
                                    ได้ 1.5
</p><p>                                    ไม่มี peer review 
                                    ได้ 1.0
                                    </p>',
                'quality_main_criteria_id' => 15,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 7,
            ],
            [
                'id' => 37,
                'name' => '2.2 เอกสารประกอบการสอน (1 วิชาใช้ได้ 1 ปีงบประมาณ)',
                'sequence' => 2,
                'num_score' => 0.20,
                'description' => '<p>มี peer review 
                                    ได้ 0.2
</p><p>                                    ไม่มี peer review 
                                    ได้ 0.1
                                    </p>',
                'quality_main_criteria_id' => 15,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 7,
            ],
            [
                'id' => 38,
                'name' => '2.3 สื่อการเรียนรู้ด้วยตนเอง (Self-study) ที่จัดทำขึ้นเองและเผยแพร่บน Platform ที่นิสิตสามารถเรียนได้ด้วยตนเองตลอด อย่างน้อย 1 ภาคการศึกษา (ต่อ 1 หน่วยกิต)',
                'sequence' => 3,
                'num_score' => 1.00,
                'description' => '<p>มีการเผยแพร่และมีการประเมินผลความพึงพอใจจากผู้เรียน
                                    ได้ 1.0
</p><p>                                    มีการเผยแพร่
                                    ได้ 0.5
                                    </p>',
                'quality_main_criteria_id' => 15,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 7,
            ],
            [
                'id' => 39,
                'name' => '(1.1) การสร้างผลงานทางวิชาการระดับนานาชาติที่เป็นเลิศ (ใช้ได้ 2 วงรอบ)',
                'sequence' => 1,
                'num_score' => 4.00,
                'description' => '<p>Principle conductor/Corresponding Author/First Author
                                    ได้ 4.0
</p><p>                                    Co-contributor/Colleagues
                                    ได้ 3.0</p>',
                'quality_main_criteria_id' => 16,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 8,
            ],
            [
                'id' => 40,
                'name' => '(1.2) การสร้างความร่วมมือด้านวิจัยที่เป็นเลิศ (ใช้ได้ 2 วงรอบ)',
                'sequence' => 2,
                'num_score' => 4.00,
                'description' => '<p>Principle conductor/Corresponding Author/First Author
                                    ได้ 4.0
</p><p>                                    Co-contributor/Colleagues
                                    ได้ 3.0</p>',
                'quality_main_criteria_id' => 16,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 8,
            ],
            [
                'id' => 41,
                'name' => '(1.3) การบริการวิชาการระดับนานาชาติ (ใช้ได้ 2 วงรอบ)',
                'sequence' => 3,
                'num_score' => 4.00,
                'description' => '<p>Principle conductor/Corresponding Author/First Author
                                    ได้ 4.0
</p><p>                                    Co-contributor/Colleagues
                                    ได้ 3.0</p>',
                'quality_main_criteria_id' => 16,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 8,
            ],
            [
                'id' => 42,
                'name' => '(1.4) การขับเคลื่อนองค์กรมุ่งสู่ความเป็นเลิศของคณะฯ',
                'sequence' => 4,
                'num_score' => 4.00,
                'description' => '<p>Principle conductor/Corresponding Author/First Author
                                    ได้ 4.0
</p><p>                                    Co-contributor/Colleagues
                                    ได้ 3.0</p>',
                'quality_main_criteria_id' => 16,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 8,
            ],
            [
                'id' => 43,
                'name' => '(2.1) กรรมการต่าง ๆ',
                'sequence' => 1,
                'num_score' => 1.00,
                'description' => '<p>มีคำสั่งและมีการดำเนินงาน
                                    ได้ 1.0
</p><p>                                    มีคำสั่ง
                                    ได้ 0.5</p>',
                'quality_main_criteria_id' => 17,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 8,
            ],
            [
                'id' => 44,
                'name' => '(2.2) การเข้าร่วมประชุมประจำเดือนของคณะ',
                'sequence' => 2,
                'num_score' => 0.40,
                'description' => '<p>เข้าร่วมร้อยละ 75-100
                                    ได้ 0.4
</p><p>                                    เข้าร่วมร้อยละ 50-75
                                    ได้ 0.2
</p><p>                                    เข้าร่วม&lt;ร้อยละ 50 ได้ 0.0</p>',
                'quality_main_criteria_id' => 17,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 8,
            ],
            [
                'id' => 45,
                'name' => '(1) มีการวางแผนการทำงานในหน้าที่',
                'sequence' => 1,
                'num_score' => 3.75,
                'description' => 'คะแนนที่ได้ 3.75',
                'quality_main_criteria_id' => 18,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 10,
            ],
            [
                'id' => 46,
                'name' => '(2) มีการปฏิบัติตามขั้นตอนแผนงานที่กำหนดในข้อ (1)',
                'sequence' => 2,
                'num_score' => 3.75,
                'description' => 'คะแนนที่ได้ 3.75',
                'quality_main_criteria_id' => 19,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 10,
            ],
            [
                'id' => 47,
                'name' => '(3) มีการตรวจสอบและประเมินผลการดำเนินงาน',
                'sequence' => 3,
                'num_score' => 3.75,
                'description' => 'คะแนนที่ได้ 3.75',
                'quality_main_criteria_id' => 20,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 10,
            ],
            [
                'id' => 48,
                'name' => '(4) มีการสรุปและแนวทางในการปรับปรุงแก้ไข',
                'sequence' => 4,
                'num_score' => 3.75,
                'description' => 'คะแนนที่ได้ 3.75',
                'quality_main_criteria_id' => 21,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 10,
            ],
            [
                'id' => 49,
                'name' => '(1) มีการวิเคราะห์ปัญหาระบบ (ต่อ 1 ชิ้นงาน)',
                'sequence' => 1,
                'num_score' => 1.00,
                'description' => 'คะแนนที่ได้ 1.00',
                'quality_main_criteria_id' => 22,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 11,
            ],
            [
                'id' => 50,
                'name' => '(2) มีการกำหนดตัวชี้วัดประสิทธิภาพงาน (ต่อ 1 ชิ้นงาน)',
                'sequence' => 2,
                'num_score' => 1.00,
                'description' => 'คะแนนที่ได้ 1.00',
                'quality_main_criteria_id' => 22,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 11,
            ],
            [
                'id' => 51,
                'name' => '(3) มีการใช้เทคโนโลยีเข้ามาช่วยเพิ่มประสิทธิภาพงาน (เช่น AI) (ต่อ 1 ชิ้นงาน)',
                'sequence' => 3,
                'num_score' => 1.00,
                'description' => 'คะแนนที่ได้ 1.00',
                'quality_main_criteria_id' => 22,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 11,
            ],
            [
                'id' => 52,
                'name' => '(4) มีการลดขั้นตอนการทำงาน (ต่อ 1 ชิ้นงาน',
                'sequence' => 4,
                'num_score' => 1.00,
                'description' => 'คะแนนที่ได้ 1.00',
                'quality_main_criteria_id' => 22,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 11,
            ],
            [
                'id' => 53,
                'name' => '(5) มีการสร้างคู่มือปฏิบัติงานฉบับปรับปรุง (ต่อ 1 ชิ้นงาน)',
                'sequence' => 5,
                'num_score' => 1.00,
                'description' => 'คะแนนที่ได้ 1.00',
                'quality_main_criteria_id' => 22,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 11,
            ],
            [
                'id' => 54,
                'name' => '(6) มีแนวทางในการจัดการข้อร้องเรียน (ต่อ 1 ชิ้นงาน)',
                'sequence' => 6,
                'num_score' => 1.00,
                'description' => 'คะแนนที่ได้ 1.00',
                'quality_main_criteria_id' => 22,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 11,
            ],
            [
                'id' => 55,
                'name' => '(7) มีกระบวนการส่ง-มอบข้อมูล (ต่อ 1 ชิ้นงาน)',
                'sequence' => 7,
                'num_score' => 1.00,
                'description' => 'คะแนนที่ได้ 1.00',
                'quality_main_criteria_id' => 22,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 11,
            ],
            [
                'id' => 56,
                'name' => '(8) มีการรายงานข้อมูลสรุปด้านคุณภาพงาน (ต่อ 1 ชิ้นงาน)',
                'sequence' => 8,
                'num_score' => 1.00,
                'description' => 'คะแนนที่ได้ 1.00',
                'quality_main_criteria_id' => 22,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 11,
            ],
            [
                'id' => 57,
                'name' => 'โครงการบริการวิชาการ (ต่อ 1 โครงการ)',
                'sequence' => 1,
                'num_score' => 0.50,
                'description' => 'คะแนนที่ได้ 0.50',
                'quality_main_criteria_id' => 23,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 12,
            ],
            [
                'id' => 58,
                'name' => '1.1 วันมหิดล (วงรอบ 1)',
                'sequence' => 1,
                'num_score' => 0.50,
                'description' => 'คะแนนที่ได้ 0.50',
                'quality_main_criteria_id' => 24,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 13,
            ],
            [
                'id' => 59,
                'name' => '1.2 วันคล้ายวันสถาปนาคณะฯ (วงรอบ 2)',
                'sequence' => 2,
                'num_score' => 0.50,
                'description' => 'คะแนนที่ได้ 0.50',
                'quality_main_criteria_id' => 24,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 13,
            ],
            [
                'id' => 60,
                'name' => '1.3 วันปฐมนิเทศนิสิตใหม่ (วงรอบ 2)',
                'sequence' => 3,
                'num_score' => 0.50,
                'description' => 'คะแนนที่ได้ 0.50',
                'quality_main_criteria_id' => 24,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 13,
            ],
            [
                'id' => 61,
                'name' => '1.4 วันไหว้ครู (วงรอบ 2)',
                'sequence' => 4,
                'num_score' => 0.50,
                'description' => 'คะแนนที่ได้ 0.50',
                'quality_main_criteria_id' => 24,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 13,
            ],
            [
                'id' => 62,
                'name' => '1.5 พิธีพระราชทานปริญญาบัตร มมส.',
                'sequence' => 5,
                'num_score' => 0.50,
                'description' => 'คะแนนที่ได้ 0.50',
                'quality_main_criteria_id' => 24,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 13,
            ],
            [
                'id' => 63,
                'name' => '1.6 กิจกรรมวันปีใหม่ (วงรอบ 1)',
                'sequence' => 6,
                'num_score' => 0.50,
                'description' => 'คะแนนที่ได้ 0.50',
                'quality_main_criteria_id' => 24,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 13,
            ],
            [
                'id' => 64,
                'name' => '1.7 กิจกรรมวันสงกรานต์ (วงรอบ 2)',
                'sequence' => 7,
                'num_score' => 0.50,
                'description' => 'คะแนนที่ได้ 0.50',
                'quality_main_criteria_id' => 24,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 13,
            ],
            [
                'id' => 65,
                'name' => '1.8 กิจกรรมวันบุญเผวด (วงรอบ 1)',
                'sequence' => 8,
                'num_score' => 0.50,
                'description' => 'คะแนนที่ได้ 0.50',
                'quality_main_criteria_id' => 24,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 13,
            ],
            [
                'id' => 66,
                'name' => '1.9 กิจกรรมวันคล้ายวันสถาปนามหาวิทยาลัย',
                'sequence' => 9,
                'num_score' => 0.50,
                'description' => 'คะแนนที่ได้ 0.50',
                'quality_main_criteria_id' => 24,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 13,
            ],
            [
                'id' => 67,
                'name' => '1.10 เข้าร่วมกิจกรรมอื่น ๆ ตามหนังสือเชิญอื่นๆ',
                'sequence' => 10,
                'num_score' => 0.50,
                'description' => 'คะแนนที่ได้ 0.50',
                'quality_main_criteria_id' => 24,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 13,
            ],
            [
                'id' => 68,
                'name' => '1.1 การพัฒนาองค์กรสู่ความเป็นเลิศ เช่น จัดทำแผน',
                'sequence' => 1,
                'num_score' => 0.50,
                'description' => 'คะแนนที่ได้ 0.50',
                'quality_main_criteria_id' => 25,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 14,
            ],
            [
                'id' => 69,
                'name' => '1.2 ด้านการพัฒนาคุณภาพงาน',
                'sequence' => 2,
                'num_score' => 0.50,
                'description' => 'คะแนนที่ได้ 0.50',
                'quality_main_criteria_id' => 25,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 14,
            ],
            [
                'id' => 70,
                'name' => '1.3 ด้านการปรับปรุงประสิทธิภาพงาน',
                'sequence' => 3,
                'num_score' => 0.50,
                'description' => 'คะแนนที่ได้ 0.50',
                'quality_main_criteria_id' => 25,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 14,
            ],
            [
                'id' => 71,
                'name' => '1.4 ความเชี่ยวชาญด้านอื่นๆ เช่น ภาษาต่างประเทศ การใช้เครื่องมือทางวิทยาศาสตร์ ฯลฯ (ต่อครั้ง)',
                'sequence' => 4,
                'num_score' => 0.50,
                'description' => 'คะแนนที่ได้ 0.50',
                'quality_main_criteria_id' => 25,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 14,
            ],
            [
                'id' => 72,
                'name' => '1.5 ด้านการจัดการความรู้ (KM) เพื่อเพิ่มประสิทธิภาพและคุณภาพงานในองค์กร',
                'sequence' => 5,
                'num_score' => 0.50,
                'description' => '<p>ผู้จัดการหลัก = 0.50 </p><p>ผู้ร่วม = 0.25</p>',
                'quality_main_criteria_id' => 25,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 14,
            ],
            [
                'id' => 73,
                'name' => '1. การได้รับทุนสนับสนุนการวิจัย',
                'sequence' => 1,
                'num_score' => 1.00,
                'description' => '<p>หัวหน้าได้ 1.0 </p><p>ผู้ร่วมได้ 0.5</p>',
                'quality_main_criteria_id' => 26,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 15,
            ],
            [
                'id' => 74,
                'name' => '1. งานวิจัยแล้วเสร็จ ตามแผนงานวิจัย',
                'sequence' => 1,
                'num_score' => 0.50,
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
            ],
            [
                'id' => 75,
                'name' => 'TCI 1 ขึ้นไป',
                'sequence' => 1,
                'num_score' => 0.50,
                'description' => '<p>1st author 0.5 </p><p>Corresponding 0.5 </p><p>ลำดับอื่นๆ 0.25</p>',
                'quality_main_criteria_id' => 28,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 15,
            ],
            [
                'id' => 76,
                'name' => 'TCI 2',
                'sequence' => 2,
                'num_score' => 0.40,
                'description' => '<p>1st author 0.4 </p><p>Corresponding 0.4 </p><p>ลำดับอื่นๆ 0.20</p>',
                'quality_main_criteria_id' => 28,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 15,
            ],
            [
                'id' => 77,
                'name' => 'Proceeding(Full-text)',
                'sequence' => 3,
                'num_score' => 0.30,
                'description' => '<p>1st author 0.3 </p><p>Corresponding 0.3 </p><p>ลำดับอื่นๆ 0.15</p>',
                'quality_main_criteria_id' => 28,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 15,
            ],
            [
                'id' => 78,
                'name' => 'Proceeding(Abstract)',
                'sequence' => 4,
                'num_score' => 0.20,
                'description' => '<p>1st author 0.2 </p><p>Corresponding 0.2 </p><p>ลำดับอื่นๆ 0.1</p>',
                'quality_main_criteria_id' => 28,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 15,
            ],
            [
                'id' => 79,
                'name' => '(1.1) การขับเคลื่อนองค์กรมุ่งสู่ความเป็นเลิศ',
                'sequence' => 1,
                'num_score' => 1.50,
                'description' => '<p>ผู้ดำเนินการหลัก 1.5 </p><p>ผู้ร่วม 1.5</p>',
                'quality_main_criteria_id' => 29,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 16,
            ],
            [
                'id' => 80,
                'name' => '(1.2) การพัฒนาคุณภาพงานสู่แนวปฏิบัติที่ดี',
                'sequence' => 2,
                'num_score' => 1.50,
                'description' => '<p>เสร็จสมบูรณ์ 1.5 </p><p>ฉบับร่าง 1.0</p>',
                'quality_main_criteria_id' => 29,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 16,
            ],
            [
                'id' => 81,
                'name' => '(1.3) การบริการที่เป็นเลิศ',
                'sequence' => 3,
                'num_score' => 2.00,
                'description' => '<p>80% ขึ้นไป ดีเยี่ยม 2
</p><p>                                70-79% ดีมาก 1.5
</p><p>                                60-69% ดี 1.0
</p><p>                                50-59% ปานกลาง 0.5
</p><p>                                &lt;50% ต้องปรับปรุง 0</p>',
                'quality_main_criteria_id' => 29,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 16,
            ],
            [
                'id' => 82,
                'name' => '(2.1) กรรมการต่อเนื่องตามคำสั่งคณะ หรือ มหาวิทยาลัย (ต่อ 1 คำสั่ง)',
                'sequence' => 1,
                'num_score' => 0.50,
                'description' => '<p>มีคำสั่งและมีการดำเนินงาน 0.5
</p><p>                มีคำสั่ง
0.25
</p>',
                'quality_main_criteria_id' => 30,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 16,
            ],
            [
                'id' => 83,
                'name' => '(2.2) กรรมการตามคำสั่งคณะ หรือ งานที่คณบดี มอบหมาย',
                'sequence' => 2,
                'num_score' => 0.50,
                'description' => '<p>มีคำสั่งและมีการดำเนินงาน 0.5
</p><p>                มีคำสั่ง
0.25
</p>',
                'quality_main_criteria_id' => 30,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 16,
            ],
            [
                'id' => 84,
                'name' => '(2.3) การเข้าร่วมประชุมประจำเดือนของคณะ',
                'sequence' => 3,
                'num_score' => 0.50,
                'description' => '<p>เข้าร่วมร้อยละ 60-100 ได้ 1.0
</p><p>เข้าร่วม&lt;ร้อยละ 60 ได้ 0.5</p>',
                'quality_main_criteria_id' => 30,
                'criteria_version_id' => 2,
                'evaluation_list_id' => 16,
            ],

        ]);
    }
}
