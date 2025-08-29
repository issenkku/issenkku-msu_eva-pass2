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
            // [
            //     'id' => 2,
            //     'version_name' => 'เกณฑ์การประเมินกลุ่มสายสนับสนุน ปี 2568',
            //     'created_by' => 1,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
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
            // [
            //     'id' => 2,
            //     'report_title' => 'เกณฑ์การให้คะแนนการประเมินพนักงานสายวิชาการตามมาตรฐานภาระงาน
            //                         คณะสาธารณสุขศาสตร์ มหาวิทยาลัยมหาสารคาม
            //                         (กลุ่มสายสนับสนุน)
            //                         ',
            //     'report_description' => 'การประเมินคุณภาพการปฏิบัติงานของบุคลากรสายสนับสนุน ประจำปีงบประมาณ 2568',
            //     'assessment_type' => 'สนับสนุน',
            //     'comment' => 'ประเมินตามเกณฑ์มาตรฐานของมหาวิทยาลัย',
            //     'criteria_version_id' => 1,
            // ],
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
        ]);

        DB::table('quality_main_criterias')->insert([
            [
                'id' => 1,
                'name' => '(1) การเตรียมแผนการจัดการเรียนการสอน',
                'ratio' => 20,
                'tooltips' => '  1.1 ส่ง มคอ.3/4 ตามเวลาที่กำหนด (ทุกวิชาที่สอน) 
                                    1.2 มีแผนการจัดการเรียนการสอนแบบ Active Learning
                                    1.3 มีการกำหนดเกณฑ์ในการประเมินที่สอดคล้องกับ CLOs
                                    1.4 มีกำหนดช่องทางและช่วงเวลาในการสื่อสารไปยังผู้เรียน',
                'sequence' => 1,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 2,
                'name' => '(2) คุณภาพสื่อการเรียนการสอน',
                'ratio' => 20,
                'tooltips' => '2.1 มีสื่อการสอนมากกว่า 1 รูปแบบ
                                    2.2 มีการใช้สื่อการเรียนการสอนภาษาอังกฤษร่วมด้วย',
                'sequence' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 3,
                'name' => '(3) การประเมินผลและการวัดผล',
                'ratio' => 20,
                'tooltips' => '3.1 ส่งข้อสอบตรงเวลาที่กำหนด (ทุกวิชาที่สอน)
                                    3.2 ข้อสอบหรือแบบทดสอบสอดคล้องกับการบรรลุ CLOs',
                'sequence' => 3,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 4,
                'name' => '(4) การรายงานผล',
                'ratio' => 20,
                'tooltips' => '4.1 ส่งเกรดตรงเวลาที่กำหนด(ทุกวิชาที่สอน)
                                    4.2 ส่ง มคอ.5/6 ตามเวลาที่กำหนด (ทุกวิชาที่สอน) ',
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
                'tooltips' => 'อยู่ในรายงานการใช้ประโยชน์งานวิจัย
                                    (4.1) ตีพิมพ์เผยแพร่
                                    (4.2) การเรียนการสอน
                                    (4.3) ถ่ายทอดเทคโนโลยี
                                    (4.4) ยื่นจด IP',
                'sequence' => 4,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 10,
                'name' => '1. รูปแบบของการจัดบริการวิชาการ',
                'ratio' => 20,
                'tooltips' => '1.1 เป็นโครงการที่สร้างรายได้ให้กับมหาวิทยาลัย
                                    1.2 เป็นโครงการที่สร้างผลตอบแทนที่เป็นประโยชน์ทางสังคม (SROI)',
                'sequence' => 1,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 11,
                'name' => '2. ลักษณะของการบริการวิชาการ',
                'ratio' => 80,
                'tooltips' => '2.1 โครงการบริการวิชาการ (ที่มีคณะกรรมการ) (ต่อ 1 โครงการ)
                                    2.2 การให้บริการวิชาการลักษณะอื่น
                                    2.2.1 เป็น Peer review วารสารระดับ นานาชาติ (เช่น ISI, SCOPUS) (ต่อ 1 เรื่อง)
                                    2.2.2 เป็น Peer review วารสารระดับชาติ  (TCI) (ต่อ 1 เรื่อง)
                                    2.2.3 เป็นวิทยากรบรรยายความรู้/ปฏิบัติ (ต่อครั้ง)
                                    2.2.4 เป็นกรรมการสอบระดับบัณฑิตศึกษา ภายนอก มมส. หรือ ภายนอกคณะ (ต่อครั้ง)
                                    2.2.5 เป็นกรรมการตัดสินผลงานวิชาการระดับชาติ (ต่อครั้ง)
                                    2.2.6 เป็นผู้ทรงคุณวุฒิให้หน่วยงานอื่น (ต่อครั้ง)',
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
                'tooltips' => '1.1 การจดทะเบียน IP
                                    1.2 บทความวิชาการ/บทความวิจัย',
                'sequence' => 1,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 15,
                'name' => '2. เอกสารและสื่อประกอบการสอน',
                'ratio' => 50,
                'tooltips' => '2.1 ตำรา/หนังสือ (1 วิชาใช้ได้ 2 ปีงบประมาณ)
                                    2.2 เอกสารประกอบการสอน (1 วิชาใช้ได้ 1 ปีงบประมาณ)
                                    2.3 สื่อการเรียนรู้ด้วยตนเอง (Self-study) ที่จัดทำขึ้นเองและเผยแพร่บน Platform ที่นิสิตสามารถเรียนได้ด้วยตนเองตลอด อย่างน้อย 1 ภาคการศึกษา (ต่อ 1 หน่วยกิต)',
                'sequence' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 16,
                'name' => '1. ภาระงานมุ่งสู่ความเป็นเลิศ',
                'ratio' => 50,
                'tooltips' => '(1.1) การสร้างผลงานทางวิชาการระดับนานาชาติที่เป็นเลิศ (ใช้ได้ 2 วงรอบ)
                                    (1.2) การสร้างความร่วมมือด้านวิจัยที่เป็นเลิศ (ใช้ได้ 2 วงรอบ)
                                    (1.3) การบริการวิชาการระดับนานาชาติ (ใช้ได้ 2 วงรอบ)
                                    (1.4) การขับเคลื่อนองค์กรมุ่งสู่ความเป็นเลิศของคณะฯ',
                'sequence' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 17,
                'name' => '2. ภาระงานบริหาร',
                'ratio' => 50,
                'tooltips' => '(2.1) กรรมการต่าง ๆ
                                    (2.2) การเข้าร่วมประชุมประจำเดือนของคณะ',
                'sequence' => 2,
                'criteria_version_id' => 1,
            ],

        ]);

        DB::table('quality_sub_criterias')->insert([
            [
                'id' => 1,
                'name' => '(1) การเตรียมแผนการจัดการเรียนการสอน',
                'sequence' => 1,
                'num_score' => 1.20,
                'description' => 'มีครบ 4 ข้อได้ 1.2
                                        มี 3 ข้อ 1.0
                                        มี 2 ข้อ 0.8
                                        มี 1 ข้อ 0.6',
                'quality_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 2,
            ],
            [
                'id' => 2,
                'name' => '(2) คุณภาพสื่อการเรียนการสอน',
                'sequence' => 2,
                'num_score' => 1.20,
                'description' => 'มีครบ 2 ข้อได้ 1.2
                                        มี 1 ข้อ 0.6',
                'quality_main_criteria_id' => 2,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 2,
            ],
            [
                'id' => 3,
                'name' => '(3) คุณภาพสื่อการเรียนการสอน',
                'sequence' => 3,
                'num_score' => 1.20,
                'description' => 'มีครบ 2 ข้อได้ 1.2
                                        มี 1 ข้อ 0.6',
                'quality_main_criteria_id' => 3,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 2,
            ],
            [
                'id' => 4,
                'name' => '(4) การรายงานผล',
                'sequence' => 4,
                'num_score' => 1.20,
                'description' => 'มีครบ 2 ข้อได้ 1.2
                                        มี 1 ข้อ 0.6',
                'quality_main_criteria_id' => 4,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 2,
            ],
            [
                'id' => 5,
                'name' => 'ผลการประเมินการสอนจากนิสิต (ทุกวิชา)**',
                'sequence' => 5,
                'num_score' => 1.20,
                'description' => '>4.49 ได้ 1.2
                                        4.00– 4.49 ได้ 1.0
                                        3.50 –3.99 ได้ 0.8
                                        3.00-3.49 ได้ 0.6',
                'quality_main_criteria_id' => 5,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 2,
            ],
            [
                'id' => 6,
                'name' => '1. แหล่งทุน',
                'sequence' => 1,
                'num_score' => 1.00,
                'description' => 'ทุนวิจัยจากต่างประเทศ
                                        ได้ 1.0
                                        ทุนวิจัยภายนอกมหาวิทยาลัย
                                        ได้ 0.8
                                        ทุนวิจัยภายใน
                                        มหาวิทยาลัย
                                        ได้ 0.5
                                        ',
                'quality_main_criteria_id' => 6,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 3,
            ],
            [
                'id' => 7,
                'name' => '2. งบประมาณ (บาท/ปี) (ไม่ต้องคิดสัดส่วนความรับผิดชอบ)',
                'sequence' => 2,
                'num_score' => 1.00,
                'description' => '> 300,000
                                        ได้ 1.0
                                        100,001 – 300,000
                                        ได้ 0.870,000 -100,000
                                        ได้ 0.5
                                        ',
                'quality_main_criteria_id' => 7,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 3,
            ],
            [
                'id' => 8,
                'name' => '3. การดำเนินงานวิจัยตามแผน*(นับรวมงานวิจัยที่ได้รับการอนุมัติขยายเวลา)',
                'sequence' => 3,
                'num_score' => 2.00,
                'description' => '80-100% 
                                    ได้ 2.0
                                    60-79% 
                                    ได้ 1.8
                                    40-59% 
                                    ได้ 1.6
                                    20-39% 
                                    ได้ 1.4
                                    <20% 
                                    ได้ 1.2
                                    ',
                'quality_main_criteria_id' => 8,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 3,
            ],
            [
                'id' => 9,
                'name' => '4. การนำไปใช้ประโยชน์**',
                'sequence' => 4,
                'num_score' => 1.00,
                'description' => 'มี
                                    ได้ 1.0
                                    ไม่มี
                                    ได้ 0
                                    ',
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
                'description' => 'ผู้จัดการหลัก = 0.50 ผู้ร่วม = 0.25',
                'quality_main_criteria_id' => 13,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 6,
            ],
            [
                'id' => 34,
                'name' => '1.1 การจดทะเบียน IP',
                'sequence' => 1,
                'num_score' => 0.50,
                'description' => 'สิทธิบัตร 
                                    ผู้ถือสิทธิ์หลัก ได้ 1.5
                                    ผู้ถือสิทธิ์ร่วม ได้ 1.25
                                    อนุสิทธิบัตร
                                    ผู้ถือสิทธิ์หลัก ได้ 1.0
                                    ผู้ถือสิทธิ์ร่วม ได้ 0.75',
                'quality_main_criteria_id' => 14,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 7,
            ],
            [
                'id' => 35,
                'name' => '1.2 บทความวิชาการ/บทความวิจัย',
                'sequence' => 2,
                'num_score' => 1.50,
                'description' => 'ISI/SCOPUS Q1-2 
                                    First/Corresponding Author ได้ 1.5
                                    Co-contributor/Colleagues ได้ 1.25
                                    SCOPUS Q 3-4
                                    First/Corresponding Author ได้ 1.5
                                    Co-contributor/Colleagues ได้ 0.75
                                    TCI 1
                                    First/Corresponding Author ได้ 1.5
                                    Co-contributor/Colleagues ได้ 0.75
                                    TCI2
                                    First/Corresponding Author ได้ 1.5
                                    Co-contributor/Colleagues ได้ 0.75
                                    Proceeding(full & inter.)
                                    First/Corresponding Author ได้ 1.5
                                    Co-contributor/Colleagues ได้ 0.75
                                    Proceeding
                                    First/Corresponding Author ได้ 1.5
                                    Co-contributor/Colleagues ได้ 0.75
                                    Abstract book
                                    First/Corresponding Author ได้ 1.5
                                    Co-contributor/Colleagues ได้ 0.75
                                    Citation เฉพาะวารสารนานาชาติ
                                    1 citation = 0.1
                                    10 citations = 1.0',
                'quality_main_criteria_id' => 14,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 7,
            ],
            [
                'id' => 36,
                'name' => '2.1 ตำรา/หนังสือ (1 วิชาใช้ได้ 2 ปีงบประมาณ)',
                'sequence' => 1,
                'num_score' => 1.50,
                'description' => 'มี peer review 
                                    ได้ 1.5
                                    ไม่มี peer review 
                                    ได้ 1.0
                                    ',
                'quality_main_criteria_id' => 15,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 7,
            ],
            [
                'id' => 37,
                'name' => '2.2 เอกสารประกอบการสอน (1 วิชาใช้ได้ 1 ปีงบประมาณ)',
                'sequence' => 2,
                'num_score' => 0.20,
                'description' => 'มี peer review 
                                    ได้ 0.2
                                    ไม่มี peer review 
                                    ได้ 0.1
                                    ',
                'quality_main_criteria_id' => 15,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 7,
            ],
            [
                'id' => 38,
                'name' => '2.3 สื่อการเรียนรู้ด้วยตนเอง (Self-study) ที่จัดทำขึ้นเองและเผยแพร่บน Platform ที่นิสิตสามารถเรียนได้ด้วยตนเองตลอด อย่างน้อย 1 ภาคการศึกษา (ต่อ 1 หน่วยกิต)',
                'sequence' => 3,
                'num_score' => 1.00,
                'description' => 'มีการเผยแพร่และมีการประเมินผลความพึงพอใจจากผู้เรียน
                                    ได้ 1.0
                                    มีการเผยแพร่
                                    ได้ 0.5
                                    ',
                'quality_main_criteria_id' => 15,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 7,
            ],
            [
                'id' => 39,
                'name' => '(1.1) การสร้างผลงานทางวิชาการระดับนานาชาติที่เป็นเลิศ (ใช้ได้ 2 วงรอบ)',
                'sequence' => 1,
                'num_score' => 4.00,
                'description' => 'Principle conductor/Corresponding Author/First Author
                                    ได้ 4.0
                                    Co-contributor/Colleagues
                                    ได้ 3.0',
                'quality_main_criteria_id' => 16,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 8,
            ],
            [
                'id' => 40,
                'name' => '(1.2) การสร้างความร่วมมือด้านวิจัยที่เป็นเลิศ (ใช้ได้ 2 วงรอบ)',
                'sequence' => 2,
                'num_score' => 4.00,
                'description' => 'Principle conductor/Corresponding Author/First Author
                                    ได้ 4.0
                                    Co-contributor/Colleagues
                                    ได้ 3.0',
                'quality_main_criteria_id' => 16,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 8,
            ],
            [
                'id' => 41,
                'name' => '(1.3) การบริการวิชาการระดับนานาชาติ (ใช้ได้ 2 วงรอบ)',
                'sequence' => 3,
                'num_score' => 4.00,
                'description' => 'Principle conductor/Corresponding Author/First Author
                                    ได้ 4.0
                                    Co-contributor/Colleagues
                                    ได้ 3.0',
                'quality_main_criteria_id' => 16,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 8,
            ],
            [
                'id' => 42,
                'name' => '(1.4) การขับเคลื่อนองค์กรมุ่งสู่ความเป็นเลิศของคณะฯ',
                'sequence' => 4,
                'num_score' => 4.00,
                'description' => 'Principle conductor/Corresponding Author/First Author
                                    ได้ 4.0
                                    Co-contributor/Colleagues
                                    ได้ 3.0',
                'quality_main_criteria_id' => 16,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 8,
            ],
            [
                'id' => 43,
                'name' => '(2.1) กรรมการต่าง ๆ',
                'sequence' => 1,
                'num_score' => 1.00,
                'description' => 'มีคำสั่งและมีการดำเนินงาน
                                    ได้ 1.0
                                    มีคำสั่ง
                                    ได้ 0.5
                                    ',
                'quality_main_criteria_id' => 17,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 8,
            ],
            [
                'id' => 44,
                'name' => '(2.2) การเข้าร่วมประชุมประจำเดือนของคณะ',
                'sequence' => 2,
                'num_score' => 0.40,
                'description' => 'เข้าร่วมร้อยละ 75-100
                                    ได้ 0.4
                                    เข้าร่วมร้อยละ 50-75
                                    ได้ 0.2
                                    เข้าร่วม<ร้อยละ 50 ได้ 0.0',
                'quality_main_criteria_id' => 17,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 8,
            ],

        ]);
    }
}
