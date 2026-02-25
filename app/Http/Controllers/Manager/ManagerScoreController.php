<?php
/**
 * ไฟล์คอนโทรลเลอร์: app/Http/Controllers\Manager\ManagerScoreController.php
 */

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\EvidenceAnswer;
use Illuminate\Support\Facades\Mail;
use App\Models\QualityScore;
use App\Models\QuantityScore;
use App\Models\Reports;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ReportDataService;

class ManagerScoreController extends Controller
{
    protected $allowedEditStatuses = ['Manager_assign', 'Manager_draft'];

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
                'message' => "Cannot {$action}. Report must be in Manager_assign or Manager_draft status.",
            ], 403);
        }

        return null; // ถ้าผ่านการตรวจสอบ
    }

    /**
     * เมธอด: manager
     * จุดประสงค์: แสดงหน้า manager_dashboard.manager และเปลี่ยนเส้นทางไปที่ route manager.show
     * อินพุต: ข้อมูลจากคำขอ, ตัวระบุ ($id)
     * เอาต์พุต: หน้า manager_dashboard.manager
     * @param Request $request ค่าที่รับเข้ามา
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function manager(Request $request, $id)
    {
        $user = $request->user()->load('position', 'department');

        $data = $this->reportDataService->getReportData($id);
        $report = $data['report'];

        if (in_array($report->status, ['Assigned', 'Draft',
            'Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft'])) {
            abort(403, 'ไม่สามารถเข้าถึงหน้าประเมินนี้ได้ เนื่องจากสถานะไม่อนุญาต');
        }

        $canEdit = in_array($report->status, ['Manager_assign', 'Manager_draft']);
        $readonly = ! $canEdit; // true if status is something else

        if ($readonly && $request->query('readonly') != 1) {
            return redirect()->route('manager.show', ['id' => $id, 'readonly' => 1]);
        }

        return view('manager_dashboard.manager', array_merge($data, [
            'id' => $id,
            'user' => $user,
            'readonly' => $readonly,
        ]));
    }

    /**
     * เมธอด: storeManagerScores
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ บันทึกข้อมูล QuantityScore, QualityScore ลบข้อมูล ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ, ตัวระบุ ($reportId)
     * เอาต์พุต: ข้อมูล JSON
     * @param Request $request ค่าที่รับเข้ามา
     * @param mixed $reportId ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function storeManagerScores(Request $request, $reportId)
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

                'quality_list' => 'nullable|array',
                'quality_list.*.quality_sub_criteria_id' => 'nullable|integer|exists:quality_sub_criterias,id',
                'quality_list.*.score' => 'nullable|numeric',

                'status' => 'required|string|in:Completed,Manager_assign,Manager_draft,Submitted',
                'comment' => 'nullable|string',
            ]);

            DB::beginTransaction();

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
                    ]);
                    $newQuantityScores[] = compact('subCriteriaId', 'scoreC', 'scoreD');
                }
            }

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
                'Manager_draft'   => 'คณบดีกรอกคะแนน',
                'Completed' => 'คณบดีอนุมัติ',
                'Assigned'=> 'ระบบมอบหมาย',
                'Submitted' => 'รายงานถูกส่งเรียบร้อยแล้ว',
            ];

            $oldStatus = $report->status;
            $oldComment = $report->comment;

            $status = $validated['status'];
            $report->status = $status;

            if (isset($validated['comment'])) {
                $report->comment = $validated['comment'];
            }
            $newComment = $report->comment;

            $report->save();
            if ($status === 'Completed') {
                $this->sendEvaluationCompletedMail($reportId);
            }

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

            $message = $status === 'Manager_draft' ? 'บันทึกข้อมูลเรียบร้อยแล้ว' : 'ส่งรายงานเรียบร้อยแล้ว';

            return redirect('/manager-dashboard')->with('success', $message);

        } catch (Exception $e) {
            DB::rollback();

            return response()->json(['message' => 'Error processing evaluation scores', 'error' => $e->getMessage()], 500);
        }
    }

    private function sendEvaluationCompletedMail($reportId)
    {
        $report = Reports::with(['reportData', 'reportData.criteriaVersion'])->find($reportId);
        if (! $report) {
            return;
        }

        // สมมติว่าต้องการแจ้งเตือน evaluatee (ผู้ถูกประเมิน)
        $assignment = \App\Models\Assignments::where('report_id', $reportId)->first();
        if (! $assignment) {
            return;
        }
        $user = \App\Models\User::find($assignment->evaluatee_id);
        if (! $user || ! $user->email) {
            return;
        }

        $mailData = [
            'name' => $user->name,
            'report_title' => optional($report->reportData)->report_title,
            'version_name' => optional(optional($report->reportData)->criteriaVersion)->version_name,
            'status' => $report->status,
        ];

        Mail::send('emails.evaluation_completed', $mailData, function ($message) use ($user) {
            $message->to($user->email, $user->name)
                ->subject('แจ้งเตือน: ผลการประเมินของคุณเสร็จสมบูรณ์');
        });
    }
}
