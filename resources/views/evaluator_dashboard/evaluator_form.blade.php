@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="header">
            <h1>แบบประเมินผลงาน</h1>
        </div>

        <!-- ข้อมูลพื้นฐาน -->
        {{-- <div class="form-section">
            <div class="section-header">
                <div class="section-title">ข้อมูลพื้นฐาน</div>
            </div>
            <div class="section-content">
                <div class="form-row">
                    <label class="form-label">ชื่อ-นามสกุล:</label>
                    <input type="text" class="form-input" placeholder="กรุณากรอกชื่อ-นามสกุล">
                </div>
                <div class="form-row">
                    <label class="form-label">ตำแหน่ง:</label>
                    <input type="text" class="form-input" placeholder="กรุณากรอกตำแหน่ง">
                </div>
                <div class="form-row">
                    <label class="form-label">หน่วยงาน:</label>
                    <input type="text" class="form-input" placeholder="กรุณากรอกหน่วยงาน">
                </div>
            </div>
        </div> --}}

        <!-- ด้านปริมาณ -->
        {{-- <div class="form-section">
            <div class="section-header">
                <div class="section-title">ด้านปริมาณ คะแนนที่ได้รวม 40 </div>
            </div>
            <div class="section-content">

                <table class="evaluation-table">
                    <thead>
                        <tr>
                            <th>รายการประเมิน</th>
                            <th>ค่าน้ำหนัก (A)</th>
                            <th>หน่วยภาระงานมาตรฐาน (B)</th>
                            <th>หน่วยภาระงานที่ทำได้ (ตาม TOR) (C)</th>
                            <th>คำนวณหาค่าน้ำหนักคะแนน (D) D = (A*C)/B</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1.1 ภาระงานในหน้าที่</td>
                            <td class="weight">20</td>
                            <td class="max-score">400</td>
                            <td>ดึงข้อมูล db</td>
                            <td>ดึงข้อมูล db</td>
                        </tr>
                        <tr>
                            <td>1.2 ภาระด้านการพัฒนาระบบงาน</td>
                            <td class="weight">4</td>
                            <td class="max-score">80</td>
                            <td>ดึงข้อมูล db</td>
                            <td>ดึงข้อมูล db</td>
                        </tr>
                        <tr>
                            <td>1.3 ภาระงานบริการวิชาการ</td>
                            <td class="weight">1</td>
                            <td class="max-score">20</td>
                            <td>ดึงข้อมูล db</td>
                            <td>ดึงข้อมูล db</td>
                        </tr>
                        <tr>
                            <td>1.4 ภาระทำนุบำรุงศิลปวัฒนธรรม</td>
                            <td class="weight">2</td>
                            <td class="max-score">40</td>
                            <td>ดึงข้อมูล db</td>
                            <td>ดึงข้อมูล db</td>
                        </tr>
                        <tr>
                            <td>1.5 ภาระงานพัฒนาตนเอง</td>
                            <td class="weight">3</td>
                            <td class="max-score">60</td>
                            <td>ดึงข้อมูล db</td>
                            <td>ดึงข้อมูล db</td>
                        </tr>
                        <tr>
                            <td>1.6 ภาระผลงานด้านวิจัย</td>
                            <td class="weight">2</td>
                            <td class="max-score">40</td>
                            <<td>ดึงข้อมูล db</td>
                                <td>ดึงข้อมูล db</td>
                        </tr>
                        <tr>
                            <td>1.7 ภาระงานด้านการบริหารองค์กรสู่ความเป็นเลิศ</td>
                            <td class="weight">8</td>
                            <td class="max-score">160</td>
                            <td>ดึงข้อมูล db</td>
                            <td>ดึงข้อมูล db</td>
                        </tr>
                    </tbody>

                </table>

                <div class="score-summary">
                    <div class="form-row">
                        <label class="form-label">ลิงก์หลักฐาน (ถ้ามี):</label>
                        <p>ดึงข้อมูล db</p>
                    </div>
                    <div class="score-row">
                        <span>ค่าน้ำหนักรวม (D) :</span>
                        <span id="sum-quantity-input">0</span>
                    </div>
                    <div class="score-row">
                        <span>คะแนนรวมด้านปริมาณ:</span>
                        <span id="quantity-summary">0/800</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- ด้านคุณภาพ -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-title">ด้านคุณภาพผลงาน 30 คะแนน </div>
                <div class="">2.1ภาระงานในหน้าที่ (ค่าน้ำหนัก 15 คะแนน) มีองค์ประกอบการพิจารณา ดังนี้</div>

                <button onclick="toggleTip('tip-box-1')">คำแนะนำการกรอกคะแนน</button>
                <div id="tip-box-1"
                    style="display: none; margin-top: 8px; background: #fef3c7; padding: 10px; border-radius: 6px; color: #92400e;">
                    - กรอกคะแนนแต่ละข้อไม่เกิน <strong>3.75</strong><br>
                    - รวมคะแนนทั้งหมดไม่เกิน <strong>15</strong><br>
                    - กรอกได้ทีละ <strong>0.25</strong> เช่น 1.00, 1.25
                </div>
            </div>
            <div class="section-content">
                <table class="evaluation-table">
                    <thead>
                        <tr>
                            <th>องค์ประกอบการพิจารณา</th>
                            <th>หลักฐาน</th>
                            <th>คะแนนที่ได้ (หน่วย : คะแนน)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1. มีการวางแผนการทำงานในหน้าที่</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td> 2. มีการปฏิบัติตามขั้นตอนแผนงานที่กำหนดในข้อ (1)</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td>3. มีการตรวจสอบและประเมินผลการดำเนินงาน</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td>4. มีการสรุปและแนวทางในการปรับปรุงแก้ไข</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="score-summary">
                    <div class="score-row">
                        <span>คะแนนรวม:</span>
                        <span id="quality-total">0/15</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-section">
            <div class="section-header">
                <div>2.2 ภาระงานในหน้าที่ (ค่าน้ำหนัก 15 คะแนน) มีองค์ประกอบการพิจารณาดังนี้</div>

                <button onclick="toggleTip('tip-box-2')">คำแนะนำการกรอกคะแนน</button>
                <div id="tip-box-2"
                    style="display:none; margin-top:8px; background:#fef3c7; padding:10px; border-radius:6px; color:#92400e;">
                    - กรอกคะแนนแต่ละข้อไม่เกิน <strong>3.75</strong><br>
                    - รวมคะแนนทั้งหมดไม่เกิน <strong>15</strong><br>
                    - กรอกได้ทีละ <strong>0.25</strong> เช่น 1.00, 1.25
                </div>
            </div>
            <div class="section-content">
                <table class="evaluation-table">
                    <thead>
                        <tr>
                            <th>องค์ประกอบการพิจารณา</th>
                            <th>หลักฐาน</th>
                            <th>คะแนนที่ได้ (หน่วย : คะแนน)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1.มีการวิเคราะห์ปัญหาระบบ (ต่อ 1 ชิ้นงาน)</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td>2.มีการกำหนดตัวชี้วัดประสิทธิภาพงาน (ต่อ 1 ชิ้นงาน) (1)</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td>3.มีการใช้เทคโนโลยีเข้ามาช่วยเพิ่มประสิทธิภาพงาน (เช่น AI) (ต่อ 1 ชิ้นงาน)</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td> 4.มีการลดขั้นตอนการทำงาน (ต่อ 1 ชิ้นงาน)</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td> 5.มีการสร้างคู่มือปฏิบัติงานฉบับปรับปรุง (ต่อ 1 ชิ้นงาน)</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td>6.มีแนวทางในการจัดการข้อร้องเรียน (ต่อ 1 ชิ้นงาน)</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td> 7.มีกระบวนการส่ง-มอบข้อมูล (ต่อ 1 ชิ้นงาน)</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td> 8.มีการรายงานข้อมูลสรุปด้านคุณภาพงาน (ต่อ 1 ชิ้นงาน)</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="score-summary">
                    <div class="score-row">
                        <span>คะแนนรวม:</span>
                        <span id="quality-total">0/3</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-section">
            <div class="section-header">
                <div class="">2.3 ภาระงานด้านการพัฒนาระบบงาน (ค่าน้ำหนัก 3 คะแนน) มีองค์ประกอบการพิจารณา ดังนี้</div>

                <button onclick="toggleTip('tip-box-3')">คำแนะนำการกรอกคะแนน</button>
                <div id="tip-box-3"
                    style="display: none; margin-top: 8px; background: #fef3c7; padding: 10px; border-radius: 6px; color: #92400e;">
                    - กรอกคะแนนแต่ละข้อไม่เกิน <strong>3.75</strong><br>
                    - รวมคะแนนทั้งหมดไม่เกิน <strong>15</strong><br>
                    - กรอกได้ทีละ <strong>0.25</strong> เช่น 1.00, 1.25
                </div>
            </div>
            <div class="section-content">
                <table class="evaluation-table">
                    <thead>
                        <tr>
                            <th>องค์ประกอบการพิจารณา</th>
                            <th>หลักฐาน</th>
                            <th>คะแนนที่ได้ (หน่วย : คะแนน)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>โครงการบริการวิชาการ (ต่อ 1 โครงการ)</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>

                    </tbody>
                </table>
                <div class="score-summary">
                    <div class="score-row">
                        <span>คะแนนรวม:</span>
                        <span id="quality-total">0/0.5</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-section">
            <div class="section-header">
                <div>2.4 ภาระงานด้านการทำนุบำรุงศิลปวัฒนธรรม (ค่าน้ำหนัก 1.5 คะแนน) มีองค์ประกอบการพิจารณา ดังนี้ </div>

                <button onclick="toggleTip('tip-box-4')">คำแนะนำการกรอกคะแนน</button>
                <div id="tip-box-4"
                    style="display:none; margin-top:8px; background:#fef3c7; padding:10px; border-radius:6px; color:#92400e;">
                    - กรอกคะแนนแต่ละข้อไม่เกิน <strong>3.75</strong><br>
                    - รวมคะแนนทั้งหมดไม่เกิน <strong>15</strong><br>
                    - กรอกได้ทีละ <strong>0.25</strong> เช่น 1.00, 1.25
                </div>
            </div>
            <div class="section-content">
                <table class="evaluation-table">
                    <thead>
                        <tr>
                            <th>องค์ประกอบการพิจารณา</th>
                            <th>หลักฐาน</th>
                            <th>คะแนนที่ได้ (หน่วย : คะแนน)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1.วันมหิดล (วงรอบ 1) </td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td>2.วันคล้ายวันสถาปนาคณะฯ (วงรอบ 2) </td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td>3.วันปฐมนิเทศนิสิตใหม่ (วงรอบ 2) </td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td> 4.วันไหว้ครู (วงรอบ 2) </td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td> 5.พิธีพระราชทานปริญญาบัตร มมส.</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td>6.กิจกรรมวันปีใหม่ (วงรอบ 1)</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td> 7.กิจกรรมวันสงกรานต์ (วงรอบ 2)</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td> 8.กิจกรรมวันบุญเผวด (วงรอบ 1)</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td> 8.กิจกรรมวันคล้ายวันสถาปนามหาวิทยาลัย</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td> 10.เข้าร่วมกิจกรรมอื่น ๆ ตามหนังสือเชิญอื่นๆ</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="score-summary">

                    <div class="score-row">
                        <span>คะแนนรวม:</span>
                        <span id="quality-total">0/1.5</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-section">
            <div class="section-header">
                <div>2.5 ภาระงานด้านการพัฒนาตนเอง (ค่าน้ำหนัก 2 คะแนน) มีองค์ประกอบการพิจารณา ดังนี้ </div>

                <button onclick="toggleTip('tip-box-5')">คำแนะนำการกรอกคะแนน</button>
                <div id="tip-box-5"
                    style="display:none; margin-top:8px; background:#fef3c7; padding:10px; border-radius:6px; color:#92400e;">
                    - กรอกคะแนนแต่ละข้อไม่เกิน <strong>3.75</strong><br>
                    - รวมคะแนนทั้งหมดไม่เกิน <strong>15</strong><br>
                    - กรอกได้ทีละ <strong>0.25</strong> เช่น 1.00, 1.25
                </div>
            </div>
            <div class="section-content">
                <table class="evaluation-table">
                    <thead>
                        <tr>
                            <th>องค์ประกอบการพิจารณา</th>
                            <th>หลักฐาน</th>
                            <th>คะแนนที่ได้ (หน่วย : คะแนน)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1.การพัฒนาองค์กรสู่ความเป็นเลิศ เช่น จัดทำแผน </td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td>2.ด้านการพัฒนาคุณภาพงาน </td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td>3.ด้านการปรับปรุงประสิทธิภาพงาน </td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td> 4.ความเชี่ยวชาญด้านอื่นๆ เช่น ภาษาต่างประเทศ การใช้เครื่องมือทางวิทยาศาสตร์ ฯลฯ (ต่อครั้ง)
                            </td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                        <tr>
                            <td> 5.ด้านการจัดการความรู้ (KM) เพื่อเพิ่มประสิทธิภาพและคุณภาพงานในองค์ก</td>
                            <td>ดึงจาก db</td>
                            <td>
                                <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                    step="0.25">
                                <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="score-summary">

                    <div class="score-row">
                        <span>คะแนนรวม:</span>
                        <span id="quality-total">0/2</span>
                    </div>
                </div>
            </div>
        </div> --}}
        <div class="form-section">
            <div class="section-header">
                <div>2.6 ภาระงานด้านผลงานวิจัย (ค่าน้ำหนัก 2 คะแนน) มีองค์ประกอบการพิจารณา ดังนี้ </div>
                
                <button onclick="toggleTip('tip-box-6')">คำแนะนำการกรอกคะแนน</button>
                <div id="tip-box-6"
                    style="display:none; margin-top:8px; background:#fef3c7; padding:10px; border-radius:6px; color:#92400e;">
                    - กรอกคะแนนแต่ละข้อไม่เกิน <strong>3.75</strong><br>
                    - รวมคะแนนทั้งหมดไม่เกิน <strong>15</strong><br>
                    - กรอกได้ทีละ <strong>0.25</strong> เช่น 1.00, 1.25
                </div>
            </div>
            <div class="section-content">
                <table class="evaluation-table">
                    <thead>
                        <tr>
                            <th>องค์ประกอบการพิจารณา</th>
                            <th>คะแนนที่ได้ (หน่วย : คะแนน)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1.การได้รับทุนสนับสนุนการวิจัย </td>

                            <td>
                                <table border="1" style="width: 100%;">
                                    <tr>
                                    <tr>
                                        <td>หัวหน้า</td>
                                        <td>ผู้ร่วม</td>
                                    </tr>
                                    <td>
                                        <input placeholder="กรอกคะแนน" type="number" class="rating-input quantity-input"
                                            min="0" max="3.75" step="0.25">
                                        <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน
                                            3.75</span>
                                    </td>
                                    <td><input placeholder="กรอกคะแนน" type="number" class="rating-input quantity-input"
                                            min="0" max="3.75" step="0.25">
                                        <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน
                                            3.75</span>
                                    </td>
                        </tr>
                </table>
                </td>

                </tr>
                <tr>
                    <td>2.งานวิจัยแล้วเสร็จ ตามแผนงานวิจัย* (ใช้ได้ถึงกรอบการอนุมัติขยายเวลา) </td>

                    <td>
                        <table border="1" style="width: 100%;">
                            <tr>
                                <td>80-100%</td>
                                <td>60-79%</td>
                                <td>40-59%</td>
                                <td>20-39%</td>
                                <td>น้อย20%</td>
                            </tr>
                            <tr>
                                <td><input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                        step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                                <td><input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                        step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                                <td><input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                        step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                                <td><input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                        step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                                <td><input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                        step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
                <tr>
                    <td>3.งานวิจัยมีการตีพิมพ์เผยแพร่</td>
                    <td>
                        <table border="1" style="width: 100%;">
                            <tr>
                                <th></th>
                                <th>1st author </th>
                                <th>Corresponding author </th>
                                <th>ลำดับอื่น ๆ</th>

                            </tr>
                            <tr>
                                <td>TCI 1 ขึ้นไป</td>
                                <td><input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                        step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                                <td><input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                        step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                                <td><input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                        step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                            </tr>
                            <tr>
                                <td>TCI 2</td>
                                <td><input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                        step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                                <td><input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                        step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                                <td><input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                        step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Proceeding(Full-text)</td>
                                <td><input type="number" class="rating-input quantity-input " min="0" max="3.75"
                                        step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                                <td><input type="number" class="rating-input quantity-input " min="0"
                                        max="3.75" step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                                <td><input type="number" class="rating-input quantity-input " min="0"
                                        max="3.75" step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Proceeding(Abstract)</td>
                                <td><input type="number" class="rating-input quantity-input " min="0"
                                        max="3.75" step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                                <td><input type="number" class="rating-input quantity-input " min="0"
                                        max="3.75" step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                                <td><input type="number" class="rating-input quantity-input " min="0"
                                        max="3.75" step="0.25">
                                    <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                                </td>
                            </tr>

                        </table>
                    </td>

                </tr>
                </tbody>
                </table>
                <div class="score-summary">

                    <div class="score-row">
                        <span>คะแนนรวม:</span>
                        <span id="quality-total">0/2</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-section">
            <div class="section-header">
                <div>2.7 ภาระงานด้านการบริหารองค์กรสู่ความเป็นเลิศ (ค่าน้ำหนัก 8 คะแนน) มีองค์ประกอบการพิจารณา ดังนี้ </div>

                <button onclick="toggleTip('tip-box-7')">คำแนะนำการกรอกคะแนน</button>
                <div id="tip-box-7"
                    style="display:none; margin-top:8px; background:#fef3c7; padding:10px; border-radius:6px; color:#92400e;">
                    - ดึงข้อมูลจากDB <strong>3.75</strong><br>
                </div>
            </div>
            <div class="section-content">
                <table class="evaluation-table">
                    <tr>
                        <th>องค์ประกอบการพิจารณา</th>
                        <th>หลักฐาน</th>
                        <th>คะแนนที่ได้ (หน่วย:คะแนน)</th>

                    </tr>
                    <tr>
                        <td>1. ภาระงานมุ่งสู่ความเป็นเลิศ </td>
                        <td></td>
                        <td></td>

                    </tr>
                    <tr>
                        <td>(1.1) การขับเคลื่อนองค์กรมุ่งสู่ความเป็นเลิศ</td>
                        <td>ดึงข้อมูลจากDB</td>
                        <td>
                            <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                step="0.25">
                            <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                        </td>
                    <tr>
                        <td>(1.2) การพัฒนาคุณภาพงานสู่แนวปฏิบัติที่ดี</td>
                        <td>ดึงข้อมูลจากDB</td>
                        <td>
                            <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                step="0.25">
                            <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                        </td>
                    </tr>
                    <tr>
                        <td>(1.3) การบริการที่เป็นเลิศ</td>
                        <td>ดึงข้อมูลจากDB</td>
                        <td>
                            <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                step="0.25">
                            <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                        </td>

                    </tr>
                    </tr>
                    <tr>
                        <td>2. ภาระงานบริหาร </td>
                        <td></td>
                        <td></td>

                    </tr>

                    <tr>
                        <td>(2.1) กรรมการต่อเนื่องตามคำสั่งคณะ หรือ มหาวิทยาลัย (ต่อ 1 คำสั่ง) </td>
                        <td>ดึงข้อมูลจากDB</td>
                        <td>
                            <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                step="0.25">
                            <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                        </td>
                    </tr>
                    <tr>
                        <td>(2.2) กรรมการตามคำสั่งคณะ หรือ งานที่คณบดี มอบหมาย </td>
                        <td>ดึงข้อมูลจากDB</td>
                        <td>
                            <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                step="0.25">
                            <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                        </td>
                    </tr>
                    <tr>
                        <td>(2.3) การเข้าร่วมประชุมประจำเดือนของคณะ</td>
                        <td>ดึงข้อมูลจากDB</td>
                        <td>
                            <input type="number" class="rating-input quantity-input" min="0" max="3.75"
                                step="0.25">
                            <span class="text-sm text-red-500 hidden warning-max">กรุณากรอกไม่เกิน 3.75</span>
                        </td>
                    </tr>


                </table>

                <div class="score-summary">

                    <div class="score-row">
                        <span>คะแนนรวม:</span>
                        <span id="quality-total">0/8</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- สรุปผลการประเมิน -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-title">สรุปผลการประเมิน</div>
            </div>
            <div class="section-content">
                <div class="score-summary">
                    <div class="score-row">
                        <span>คะแนนรวมด้านปริมาณ:</span>
                        <span id="final-quantity">0/400</span>
                    </div>
                    {{-- <div class="score-row">
                        <span>คะแนนรวมด้านคุณภาพ:</span>
                        <span id="final-quality">0/50</span>
                    </div> --}}
                    <div class="score-row">
                        <span>คะแนนรวมทั้งหมด:</span>
                        <span id="grand-total">0/100</span>
                    </div>
                </div>

                <div class="comment-section">
                    <label class="form-label">ข้อเสนอแนะและความคิดเห็น:</label>
                    <textarea class="comment-textarea" placeholder="กรุณากรอกข้อเสนอแนะและความคิดเห็น"></textarea>
                </div>
            </div>
        </div>
        <!-- ปุ่มดำเนินการ -->
        <div class="button-group">
            <button type="button" class="btn">ยกเลิก</button>
            <button type="button" class="btn">ดูตัวอย่าง</button>
            <button type="button" class="btn btn-primary">บันทึกข้อมูล</button>
        </div>

    </div>
    <script>
        const inputs = document.querySelectorAll('.rating-input');
        const qualityTotal = document.getElementById('quality-total');
        const maxScore = 15;

        const previousValues = new Map();

        inputs.forEach(input => {
            previousValues.set(input, 0);
        });

        function getTotal(excludeInput = null, newValue = null) {
            let total = 0;
            inputs.forEach(input => {
                let val = parseFloat(input.value);
                if (input === excludeInput && newValue !== null) {
                    val = newValue;
                }
                if (!isNaN(val)) {
                    total += val;
                }
            });
            return total;
        }

        function calculateTotal() {
            const total = getTotal();
            if (qualityTotal) {
                qualityTotal.textContent = total + '/' + maxScore;
            }
        }

        inputs.forEach(input => {
            const warning = input.parentElement.querySelector('.warning-max');

            input.addEventListener('input', () => {
                let newVal = parseFloat(input.value);
                if (isNaN(newVal)) {
                    newVal = 0;
                }

                // ตรวจว่ากรอกเกิน 3.75 หรือไม่
                if (newVal > 3.75) {
                    input.value = 3.75;
                    warning.classList.remove('hidden');
                    newVal = 3.75;
                } else {
                    warning.classList.add('hidden');
                }

                // ตรวจว่ารวมแล้วเกิน 15 หรือไม่
                const totalWithNewValue = getTotal(input, newVal);
                if (totalWithNewValue > maxScore) {
                    alert('คะแนนรวมต้องไม่เกิน 15');
                    input.value = previousValues.get(input);
                } else {
                    previousValues.set(input, newVal);
                }

                calculateTotal();
            });
        });

        function toggleTip(id) {
            const tip = document.getElementById(id);
            if (tip.style.display === 'none' || tip.style.display === '') {
                tip.style.display = 'block';
            } else {
                tip.style.display = 'none';
            }
        }

        // ฟังก์ชันคำนวณคะแนนรวม แยกตามแต่ละ section
        function calculateTotal(sectionNumber, maxScore) {
            const inputs = document.querySelectorAll(`.section-${sectionNumber} .rating-input`);
            let total = 0;
            inputs.forEach(input => {
                const val = parseFloat(input.value);
                if (!isNaN(val)) {
                    total += val;
                }
            });
            const totalSpan = document.getElementById(`quality-total-${sectionNumber}`);
            if (totalSpan) {
                totalSpan.textContent = total.toFixed(2) + '/' + maxScore;
            }
        }

        // เรียกใช้ฟังก์ชันนี้ตอนโหลดหน้า และเมื่อมีการกรอกคะแนน
        document.addEventListener('DOMContentLoaded', () => {
            [1, 2].forEach(sectionNum => {
                const container = document.querySelector(`.section-${sectionNum}`);
                if (!container) return;

                const inputs = container.querySelectorAll('.rating-input');
                inputs.forEach(input => {
                    input.addEventListener('input', () => {
                        const maxScore = sectionNum === 1 ? 15 :
                            3; // กำหนดคะแนนเต็มของแต่ละ section
                        calculateTotal(sectionNum, maxScore);
                    });
                });
            });
        });

        // เรียกตอนโหลดหน้า
        calculateTotal();

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.rating-input').forEach(input => {
                input.addEventListener('input', calculateAndUpdate);
            });
            calculateAndUpdate();
        });
        inputs.forEach(input => {
            const warning = document.getElementById('warning-max');
            input.addEventListener('input', () => {
                const max = parseFloat(input.max);
                const val = parseFloat(input.value);

                if (val > max) {
                    warning.classList.remove('hidden');
                    input.value = max;
                } else {
                    warning.classList.add('hidden');
                }
            });
        });
    </script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', Arial, sans-serif;
            background-color: #ffffff;
            color: #333333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            border-bottom: 2px solid #e0e0e0;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .form-section {
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            margin-bottom: 25px;
            overflow: hidden;
        }

        .section-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #374151;
        }

        .section-content {
            padding: 20px;
        }

        .form-row {
            display: flex;
            margin-bottom: 15px;
            align-items: center;
        }

        .form-label {
            flex: 0 0 200px;
            font-weight: 500;
            color: #4b5563;
            margin-right: 15px;
        }

        .form-input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-input:focus {
            outline: none;
            border-color: #6b7280;
            box-shadow: 0 0 0 1px #6b7280;
        }

        .evaluation-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .evaluation-table th,
        .evaluation-table td {
            border: 1px solid #d1d5db;
            padding: 12px 8px;
            text-align: center;
            font-size: 14px;
        }

        .evaluation-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #374151;
        }

        .evaluation-table td:first-child {
            /* text-align: left; */
            font-weight: 500;
        }

        .rating-input {
            width: 60px;
            padding: 4px;
            text-align: center;
            border: 1px solid #d1d5db;
            border-radius: 3px;
        }

        .checkbox-group {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }

        .comment-section {
            margin-top: 20px;
        }

        .comment-textarea {
            width: 100%;
            min-height: 100px;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            resize: vertical;
            font-family: inherit;
        }

        .button-group {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            padding: 10px 25px;
            margin: 0 10px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: #ffffff;
            color: #374151;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }

        .btn:hover {
            background: #f9fafb;
        }

        .btn-primary {
            background: #374151;
            color: #ffffff;
            border-color: #374151;
        }

        .btn-primary:hover {
            background: #4b5563;
        }

        .score-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-top: 15px;
        }

        .score-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .score-row:last-child {
            margin-bottom: 0;
            font-weight: bold;
            padding-top: 8px;
            border-top: 1px solid #d1d5db;
        }
    </style>
@endsection
@push('scripts')
@endpush
