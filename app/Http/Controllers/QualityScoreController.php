<?php

namespace App\Http\Controllers;


use App\Models\QualityScore;
use App\Models\QualitySubCriteria;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QualityScoreController extends Controller
{
    /**
     * เมธอด: index
     * จุดประสงค์: แสดงหน้า Admin/QualityScores/Index
     * อินพุต: ไม่มี
     * เอาต์พุต: หน้า Admin/QualityScores/Index
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function index()
    {
        $qualityScores = QualityScore::with([
            'qualitySubCriteria',
            'report.reportData.user',
        ])->paginate(15);

        return Inertia::render('Admin/QualityScores/Index', [
            'qualityScores' => $qualityScores,
        ]);
    }

    /**
     * เมธอด: create
     * จุดประสงค์: แสดงหน้า Admin/QualityScores/Create
     * อินพุต: ไม่มี
     * เอาต์พุต: หน้า Admin/QualityScores/Create
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function create()
    {
        $qualitySubCriterias = QualitySubCriteria::with(['mainCriteria', 'evaluationList'])
            ->orderBy('sequence')
            ->get();

        $users = User::select('id', 'employee_id', 'prefix', 'name', 'email')
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/QualityScores/Create', [
            'qualitySubCriterias' => $qualitySubCriterias,
            'users' => $users,
        ]);
    }

    /**
     * เมธอด: store
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ และเปลี่ยนเส้นทางไปที่ route quality-scores.index
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: Redirect ไปที่ route quality-scores.index
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function store(Request $request)
    {
        $request->validate([
            'quality_sub_criteria_id' => 'required|exists:quality_sub_criterias,id',
            'users' => 'required|array|min:1',
            'users.*.user_id' => 'required|exists:users,id',
            'users.*.score' => 'required|numeric|min:0|max:100',
        ], [
            'quality_sub_criteria_id.required' => 'กรุณาเลือกหัวข้อย่อย',
            'quality_sub_criteria_id.exists' => 'หัวข้อย่อยที่เลือกไม่ถูกต้อง',
            'users.required' => 'กรุณาเลือกผู้ใช้อย่างน้อย 1 คน',
            'users.*.user_id.required' => 'กรุณาเลือกผู้ใช้',
            'users.*.user_id.exists' => 'ผู้ใช้ที่เลือกไม่ถูกต้อง',
            'users.*.score.required' => 'กรุณากรอกคะแนน',
            'users.*.score.numeric' => 'คะแนนต้องเป็นตัวเลข',
            'users.*.score.min' => 'คะแนนต้องไม่น้อยกว่า 0',
            'users.*.score.max' => 'คะแนนต้องไม่เกิน 100',
        ]);

        $qualitySubCriteriaId = $request->quality_sub_criteria_id;
        $users = $request->users;

        foreach ($users as $userData) {
            $userId = $userData['user_id'];
            $score = $userData['score'];

            // Find or create report data for this user
            $reportData = ReportData::firstOrCreate([
                'user_id' => $userId,
            ]);

            // Find or create report for this report data
            $report = Reports::firstOrCreate([
                'report_data_id' => $reportData->id,
            ], [
                'status' => 'pending',
                'comment' => null,
            ]);

            // Create or update quality score
            QualityScore::updateOrCreate([
                'quality_sub_criteria_id' => $qualitySubCriteriaId,
                'report_id' => $report->id,
            ], [
                'score' => $score,
            ]);
        }

        return redirect()->route('quality-scores.index')
            ->with('success', 'เพิ่มคะแนนคุณภาพสำเร็จ');
    }

    /**
     * เมธอด: show
     * จุดประสงค์: แสดงหน้า Admin/QualityScores/Show
     * อินพุต: โมเดล QualityScore
     * เอาต์พุต: หน้า Admin/QualityScores/Show
     * @param QualityScore $qualityScore ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function show(QualityScore $qualityScore)
    {
        $qualityScore->load([
            'qualitySubCriteria.mainCriteria',
            'report.reportData.user',
        ]);

        return Inertia::render('Admin/QualityScores/Show', [
            'qualityScore' => $qualityScore,
        ]);
    }

    /**
     * เมธอด: edit
     * จุดประสงค์: แสดงหน้า Admin/QualityScores/Edit
     * อินพุต: โมเดล QualityScore
     * เอาต์พุต: หน้า Admin/QualityScores/Edit
     * @param QualityScore $qualityScore ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function edit(QualityScore $qualityScore)
    {
        $qualityScore->load([
            'qualitySubCriteria.mainCriteria',
            'report.reportData.user',
        ]);

        $qualitySubCriterias = QualitySubCriteria::with(['mainCriteria', 'evaluationList'])
            ->orderBy('sequence')
            ->get();

        $users = User::select('id', 'employee_id', 'prefix', 'name', 'email')
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/QualityScores/Edit', [
            'qualityScore' => $qualityScore,
            'qualitySubCriterias' => $qualitySubCriterias,
            'users' => $users,
        ]);
    }

    /**
     * เมธอด: update
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ อัปเดตข้อมูล และเปลี่ยนเส้นทางไปที่ route quality-scores.index
     * อินพุต: ข้อมูลจากคำขอ, โมเดล QualityScore
     * เอาต์พุต: Redirect ไปที่ route quality-scores.index
     * @param Request $request ค่าที่รับเข้ามา
     * @param QualityScore $qualityScore ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function update(Request $request, QualityScore $qualityScore)
    {
        $request->validate([
            'score' => 'required|numeric|min:0|max:100',
        ], [
            'score.required' => 'กรุณากรอกคะแนน',
            'score.numeric' => 'คะแนนต้องเป็นตัวเลข',
            'score.min' => 'คะแนนต้องไม่น้อยกว่า 0',
            'score.max' => 'คะแนนต้องไม่เกิน 100',
        ]);

        $qualityScore->update([
            'score' => $request->score,
        ]);

        return redirect()->route('quality-scores.index')
            ->with('success', 'แก้ไขคะแนนคุณภาพสำเร็จ');
    }

    /**
     * เมธอด: destroy
     * จุดประสงค์: ลบข้อมูล และเปลี่ยนเส้นทางไปที่ route quality-scores.index
     * อินพุต: โมเดล QualityScore
     * เอาต์พุต: Redirect ไปที่ route quality-scores.index
     * @param QualityScore $qualityScore ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function destroy(QualityScore $qualityScore)
    {
        $qualityScore->delete();

        return redirect()->route('quality-scores.index')
            ->with('success', 'ลบคะแนนคุณภาพสำเร็จ');
    }

    /**
     * เมธอด: getQualitySubCriterias
     * จุดประสงค์: ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ข้อมูล JSON
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function getQualitySubCriterias(Request $request)
    {
        $evaluationListId = $request->get('evaluation_list_id');

        $qualitySubCriterias = QualitySubCriteria::with(['mainCriteria'])
            ->when($evaluationListId, function ($query, $evaluationListId) {
                return $query->where('evaluation_list_id', $evaluationListId);
            })
            ->orderBy('sequence')
            ->get();

        return response()->json($qualitySubCriterias);
    }
}
