<?php

namespace App\Http\Controllers;


use App\Models\Reports;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    /**
     * เมธอด: index
     * จุดประสงค์: ประมวลผลคำขอ
     * อินพุต: ไม่มี
     * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function index()
    {
        //
    }

    /**
     * เมธอด: create
     * จุดประสงค์: ประมวลผลคำขอ
     * อินพุต: ไม่มี
     * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function create()
    {
        //
    }

    /**
     * เมธอด: store
     * จุดประสงค์: ประมวลผลคำขอ
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * เมธอด: show
     * จุดประสงค์: ประมวลผลคำขอ
     * อินพุต: โมเดล Reports
     * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
     * @param Reports $reports ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function show(Reports $reports)
    {
        //
    }

    /**
     * เมธอด: edit
     * จุดประสงค์: ประมวลผลคำขอ
     * อินพุต: โมเดล Reports
     * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
     * @param Reports $reports ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function edit(Reports $reports)
    {
        //
    }

    /**
     * เมธอด: update
     * จุดประสงค์: ประมวลผลคำขอ
     * อินพุต: ข้อมูลจากคำขอ, โมเดล Reports
     * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
     * @param Request $request ค่าที่รับเข้ามา
     * @param Reports $reports ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function update(Request $request, Reports $reports)
    {
        //
    }

    /**
     * เมธอด: destroy
     * จุดประสงค์: ประมวลผลคำขอ
     * อินพุต: โมเดล Reports
     * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
     * @param Reports $reports ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function destroy(Reports $reports)
    {
        //
    }
}
