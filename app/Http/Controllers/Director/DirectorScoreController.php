<?php

namespace App\Http\Controllers\Director;


use App\Http\Controllers\Controller;
use App\Models\EvidenceAnswer;
use App\Models\QualityScore;
use App\Models\QuantityScore;
use App\Models\Reports;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ReportDataService;
use App\Support\QuantityScoreHistoryRecorder;

class DirectorScoreController extends Controller
{
    protected $allowedEditStatuses = ['Director_assigned', 'Director_draft'];

    protected $reportDataService;

    /**
     * เมธอด: __construct
     * จุดประสงค์: ประมวลผลคำขอ
     * อินพุต: โมเดล ReportDataService
     * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
     * @param ReportDataService $reportDataService ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function __construct(ReportDataService $reportDataService)
    {
        $this->reportDataService = $reportDataService;
    }

    protected function checkReportEditableStatus(Reports $report, $action)
    {
        if (! in_array($report->status, $this->allowedEditStatuses)) {
            return response()->json([
                'message' => "Cannot {$action}. Report must be in Director_assigned or Director_draft status.",
            ], 403);
        }

        return null; // ถ้าผ่านการตรวจสอบ
    }

    /**
     * เมธอด: director
     * จุดประสงค์: แสดงหน้า director_dashboard.director และเปลี่ยนเส้นทางไปที่ route director.show
     * อินพุต: ข้อมูลจากคำขอ, ตัวระบุ ($id)
     * เอาต์พุต: หน้า director_dashboard.director
     * @param Request $request ค่าที่รับเข้ามา
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function director(Request $request, $id)
    {
        $user = $request->user()->load('position', 'department');

        $data = $this->reportDataService->getReportData($id);
        $report = $data['report'];

        if (in_array($report->status, ['Assigned', 'Draft', 'Pending', 'Evaluator_draft'])) {
            abort(403, 'ไม่สามารถเข้าถึงหน้าประเมินนี้ได้ เนื่องจากสถานะไม่อนุญาต');
        }

        $canEdit = in_array($report->status, ['Director_assigned', 'Director_draft']);
        $readonly = ! $canEdit; // true if status is something else

        if ($readonly && $request->query('readonly') != 1) {
            return redirect()->route('director.show', ['id' => $id, 'readonly' => 1]);
        }

        return view('director_dashboard.director', array_merge($data, [
            'id' => $id,
            'user' => $user,
            'readonly' => $readonly,
        ]));
    }

    /**
     * เมธอด: storeDirectorScores
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ บันทึกข้อมูล QuantityScore, QualityScore ลบข้อมูล ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ, ตัวระบุ ($reportId)
     * เอาต์พุต: ข้อมูล JSON
     * @param Request $request ค่าที่รับเข้ามา
     * @param mixed $reportId ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function storeDirectorScores(Request $request, $reportId)
    {
        try {
            $reportId = is_array($reportId) ? $reportId[0] : (int) $reportId;
            $report = Reports::findOrFail($reportId);

            $statusCheck = $this->checkReportEditableStatus($report, 'process evaluation scores');
            if ($statusCheck) {
                return $statusCheck;
            }

            $validated = $request->validate([
                'quantity_list' => 'nullable|array',
                'quantity_list.*.quantity_sub_criteria_id' => 'nullable|integer|exists:quantity_sub_criterias,id',
                'quantity_list.*.score_C' => 'nullable|numeric',
                'quantity_list.*.description' => 'nullable|string',

                'quality_list' => 'nullable|array',
                'quality_list.*.quality_sub_criteria_id' => 'nullable|integer|exists:quality_sub_criterias,id',
                'quality_list.*.score' => 'nullable|numeric',

                'status' => 'required|string|in:Director_assigned,Manager_assign,Director_draft,Submitted',
                'comment' => 'nullable|string',
            ]);

            DB::beginTransaction();
            $modifierRole = $request->user()?->getRoleNames()->first() ?: 'กรรมการ';

            $oldQuantityScores = QuantityScore::where('report_id', $reportId)->get();
            $oldQualityScores  = QualityScore::where('report_id', $reportId)->get();

            // Delete existing records for this report
            QuantityScore::where('report_id', $reportId)->delete();
            QualityScore::where('report_id', $reportId)->delete();

            $newQuantityScores = [];
            if (isset($validated['quantity_list'])) {
                foreach ($validated['quantity_list'] as $item) {
                    $subCriteriaId = is_array($item['quantity_sub_criteria_id'])
                        ? $item['quantity_sub_criteria_id'][0]
                        : (int) $item['quantity_sub_criteria_id'];

                    $subCriteria = \App\Models\QuantitySubCriteria::find($subCriteriaId);
                    $scoreC = $item['score_C'] ?? null;
                    $description = isset($item['description']) ? trim((string) $item['description']) : null;

                    if ($scoreC === null) {
                        continue;
                    }

                    $scoreD = null;
                    if ($subCriteria && $subCriteria->score_b != 0) {
                        $scoreD = ($subCriteria->score_a * $scoreC) / $subCriteria->score_b;
                    }

                    QuantityScore::create([
                        'quantity_sub_criteria_id' => $subCriteriaId,
                        'report_id' => $reportId,
                        'score_C' => $scoreC,
                        'score_D' => $scoreD,
                        'description' => $description !== '' ? $description : null,
                        'modifier_user_id' => $request->user()?->id,
                        'modifier_role' => $modifierRole,
                    ]);
                    $newQuantityScores[] = compact('subCriteriaId', 'scoreC', 'scoreD', 'description');
                }
            }

            QuantityScoreHistoryRecorder::record(
                $reportId,
                $oldQuantityScores,
                $newQuantityScores,
                $request->user()?->id,
                $modifierRole
            );

            // ✅ Quality loop with check
            $newQualityScores = [];
            if (isset($validated['quality_list'])) {
                foreach ($validated['quality_list'] as $item) {
                    $score = $item['score'] ?? null;
                    if ($score === null) {
                        continue;
                    }

                    $subCriteriaId = is_array($item['quality_sub_criteria_id'])
                        ? $item['quality_sub_criteria_id'][0]
                        : (int) $item['quality_sub_criteria_id'];

                    QualityScore::create([
                        'quality_sub_criteria_id' => $subCriteriaId,
                        'report_id' => $reportId,
                        'score' => $score,
                    ]);
                    $newQualityScores[] = compact('subCriteriaId', 'score');
                }
            }

            $statusMessages = [
                'Director_draft'   => 'กรรมการกรอกคะแนน',
                'Manager_assign' => 'กรรมการอนุมัติ',
                'Assigned'=> 'ระบบมอบหมาย',
                'Submitted' => 'รายงานถูกส่งเรียบร้อยแล้ว',
            ];

            $oldStatus = $report->status;
            $oldComment = $report->director_comment;

            $status = $validated['status'];
            $report->status = $status;

            if (isset($validated['comment'])) {
                $report->director_comment = $validated['comment'];
            }
            $report->syncCombinedComment();
            $newComment = $report->director_comment;

            $report->save();
            // if ($report->save() && $status === 'Pending') {
            //     $this->sendEvaluationCompletedMail($reportId);
            // }

            activity()
                ->causedBy($request->user()) // who did it
                ->useLog('การประเมิน')
                ->performedOn($report)     // which model
                ->withProperties([
                    'สถานะรายงานก่อนหน้า' => $oldStatus,
                    'อัพเดตสถานะรายงาน' => $status,
                    'ความคิดเห็นก่อนหน้า' => $oldComment,
                    'อัพเดตความคิดเห็น'   => $newComment,
                    'คะแนนเชิงปริมาณก่อนหน้า' => $oldQuantityScores,
                    'อัพเดตคะแนนเชิงปริมาณ' => $newQuantityScores,
                    'คะแนนเชิงคุณภาพก่อนหน้า' => $oldQualityScores,
                    'อัพเดตคะแนนเชิงคุณภาพ' => $newQualityScores,
                ])
                ->log($statusMessages[$status] ?? "เปลี่ยนสถานะเป็น {$status}");

            DB::commit();

            $message = $status === 'Director_draft' ? 'บันทึกข้อมูลเรียบร้อยแล้ว' : 'ส่งรายงานเรียบร้อยแล้ว';

            return redirect('/director-dashboard')->with('success', $message);

        } catch (Exception $e) {
            DB::rollback();

            return response()->json(['message' => 'Error processing evaluation scores', 'error' => $e->getMessage()], 500);
        }
    }
}
