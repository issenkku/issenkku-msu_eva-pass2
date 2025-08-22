<?php

namespace App\Http\Controllers;

use App\Models\QualityScore;
use App\Models\QualitySubCriteria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QualityScoresController extends Controller
{
    public function index(Request $request)
    {
        // ดึงข้อมูลรายงานที่มีโครงสร้างเกณฑ์
        $reportDatas = \App\Models\ReportData::with([
            'criteriaVersion.qualityMainCriterias' => function ($query) {
                $query->orderBy('sequence');
            },
            'criteriaVersion.qualityMainCriterias.qualitySubCriterias' => function ($query) {
                $query->orderBy('sequence');
            },
        ])->get();

        // ดึงข้อมูลคะแนนที่มีอยู่
        $qualityScoresQuery = QualityScore::with(['qualitySubCriteria.qualityMainCriteria', 'user'])
            ->orderBy('created_at', 'desc');

        // กรองตาม criteria ถ้ามี
        if ($request->has('filter_criteria') && $request->filter_criteria) {
            $qualityScoresQuery->where('quality_sub_criteria_id', $request->filter_criteria);
        }

        $qualityScores = $qualityScoresQuery->paginate(10);

        // เก็บ filter criteria สำหรับส่งกลับไป view
        $filterCriteria = $request->filter_criteria;

        return view('quality-scores.index', compact('reportDatas', 'qualityScores', 'filterCriteria'));
    }

    public function create(Request $request)
    {
        // ดึงข้อมูล report_datas ทั้งหมด
        $reportDatas = \App\Models\ReportData::orderBy('report_title')->get();

        // ถ้ามีการเลือก report_id แล้ว ให้ดึงเกณฑ์ของ report นั้น
        $qualitySubCriterias = collect();
        $selectedReport = null;

        if ($request->has('report_id') && $request->report_id) {
            $selectedReport = \App\Models\ReportData::with([
                'criteriaVersion.qualityMainCriterias.qualitySubCriterias' => function ($query) {
                    $query->orderBy('sequence');
                },
            ])->find($request->report_id);

            if ($selectedReport && $selectedReport->criteriaVersion) {
                $qualitySubCriterias = $selectedReport->criteriaVersion->qualityMainCriterias
                    ->flatMap->qualitySubCriterias;
            }
        }

        $users = User::orderBy('name')->get();

        // เก็บ criteria ที่ส่งมาจาก URL parameter สำหรับ pre-select
        $selectedCriteria = $request->get('criteria');

        return view('quality-scores.create', compact('reportDatas', 'qualitySubCriterias', 'users', 'selectedCriteria', 'selectedReport'));
    }

    public function getCriteriaByReport(Request $request)
    {
        $reportId = $request->get('report_id');

        if (! $reportId) {
            return response()->json(['criterias' => []]);
        }

        $reportData = \App\Models\ReportData::with([
            'criteriaVersion.qualityMainCriterias.qualitySubCriterias' => function ($query) {
                $query->orderBy('sequence');
            },
        ])->find($reportId);

        $criterias = [];

        if ($reportData && $reportData->criteriaVersion) {
            foreach ($reportData->criteriaVersion->qualityMainCriterias as $mainCriteria) {
                foreach ($mainCriteria->qualitySubCriterias as $subCriteria) {
                    $criterias[] = [
                        'id' => $subCriteria->id,
                        'name' => $subCriteria->name,
                        'main_criteria_name' => $mainCriteria->name,
                    ];
                }
            }
        }

        return response()->json(['criterias' => $criterias]);
    }

    public function store(Request $request)
    {
        // Log เมื่อเริ่มต้นการบันทึกข้อมูล
        Log::info('QualityScore store method started', [
            'request_data' => $request->all(),
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        $request->validate([
            'report_id' => 'required|exists:report_datas,id',
            'quality_sub_criteria_id' => 'required|exists:quality_sub_criterias,id',
            'users' => 'required|array|min:1',
            'users.*' => 'exists:users,id',
            'scores' => 'required|array',
            'scores.*' => 'required|numeric|min:0|max:100',
        ], [
            'report_id.required' => 'กรุณาเลือกรายงานการประเมิน',
            'report_id.exists' => 'รายงานการประเมินที่เลือกไม่ถูกต้อง',
            'quality_sub_criteria_id.required' => 'กรุณาเลือกเกณฑ์การประเมิน',
            'quality_sub_criteria_id.exists' => 'เกณฑ์การประเมินที่เลือกไม่ถูกต้อง',
            'users.required' => 'กรุณาเลือกผู้ใช้งาน',
            'users.min' => 'กรุณาเลือกผู้ใช้งานอย่างน้อย 1 คน',
            'users.*.exists' => 'ผู้ใช้งานที่เลือกไม่ถูกต้อง',
            'scores.required' => 'กรุณาระบุคะแนน',
            'scores.*.required' => 'กรุณาระบุคะแนนให้ครบทุกคน',
            'scores.*.numeric' => 'คะแนนต้องเป็นตัวเลขเท่านั้น',
            'scores.*.min' => 'คะแนนต้องไม่น้อยกว่า 0',
            'scores.*.max' => 'คะแนนต้องไม่เกิน 100',
        ]);

        // Log หลังจาก validation สำเร็จ
        Log::info('QualityScore validation passed', [
            'validated_data' => [
                'report_id' => $request->report_id,
                'quality_sub_criteria_id' => $request->quality_sub_criteria_id,
                'users_count' => count($request->users),
                'scores_count' => count($request->scores),
            ],
        ]);

        try {
            DB::beginTransaction();

            // Log เริ่มต้น transaction
            Log::info('QualityScore database transaction started');

            // หา report ที่ตรงกับ report_data_id
            $report = \App\Models\Reports::where('report_data_id', $request->report_id)->first();

            if (! $report) {
                // สร้าง report ใหม่ถ้าไม่มี
                Log::info('Creating new report for report_data_id', ['report_data_id' => $request->report_id]);

                $report = \App\Models\Reports::create([
                    'report_data_id' => $request->report_id,
                    'status' => 'active', // หรือ status เริ่มต้นที่เหมาะสม
                    'comment' => null,
                ]);

                Log::info('New report created', ['report_id' => $report->id]);
            }

            $reportId = $report->id;

            $createdScores = [];
            $updatedScores = [];

            foreach ($request->users as $index => $userId) {
                $score = $request->scores[$index] ?? 0;

                Log::info('Processing user score', [
                    'user_id' => $userId,
                    'score' => $score,
                    'index' => $index,
                    'report_id' => $reportId,
                ]);

                // ตรวจสอบว่ามีคะแนนสำหรับ user และ criteria นี้แล้วหรือไม่
                $existingScore = QualityScore::where('user_id', $userId)
                    ->where('quality_sub_criteria_id', $request->quality_sub_criteria_id)
                    ->where('report_id', $reportId)
                    ->first();

                if ($existingScore) {
                    // อัพเดทคะแนนที่มีอยู่แล้ว
                    Log::info('Updating existing score', [
                        'existing_score_id' => $existingScore->id,
                        'old_score' => $existingScore->score,
                        'new_score' => $score,
                    ]);

                    $existingScore->update(['score' => $score]);
                    $updatedScores[] = [
                        'id' => $existingScore->id,
                        'user_id' => $userId,
                        'old_score' => $existingScore->getOriginal('score'),
                        'new_score' => $score,
                    ];
                } else {
                    // สร้างคะแนนใหม่
                    Log::info('Creating new score record', [
                        'user_id' => $userId,
                        'quality_sub_criteria_id' => $request->quality_sub_criteria_id,
                        'report_id' => $reportId,
                        'score' => $score,
                    ]);

                    $newScore = QualityScore::create([
                        'user_id' => $userId,
                        'quality_sub_criteria_id' => $request->quality_sub_criteria_id,
                        'report_id' => $reportId,
                        'score' => $score,
                    ]);

                    $createdScores[] = [
                        'id' => $newScore->id,
                        'user_id' => $userId,
                        'score' => $score,
                    ];

                    Log::info('New score record created successfully', [
                        'new_score_id' => $newScore->id,
                    ]);
                }
            }

            DB::commit();

            // Log สรุปผลการบันทึก
            Log::info('QualityScore store operation completed successfully', [
                'report_data_id' => $request->report_id,
                'actual_report_id' => $reportId,
                'created_scores_count' => count($createdScores),
                'updated_scores_count' => count($updatedScores),
                'created_scores' => $createdScores,
                'updated_scores' => $updatedScores,
                'total_processed' => count($request->users),
            ]);

            return redirect()->route('quality-scores.index')
                ->with('success', 'บันทึกคะแนนสำเร็จ');

        } catch (\Exception $e) {
            DB::rollback();

            // Log error พร้อมรายละเอียดครบถ้วน
            Log::error('QualityScore store operation failed', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: '.$e->getMessage());
        }
    }

    public function show($id)
    {
        $qualityScore = QualityScore::with(['qualitySubCriteria.qualityMainCriteria', 'user'])
            ->findOrFail($id);

        return view('quality-scores.show', compact('qualityScore'));
    }

    public function edit($id)
    {
        $qualityScore = QualityScore::with(['qualitySubCriteria', 'user'])
            ->findOrFail($id);

        $qualitySubCriterias = QualitySubCriteria::with('qualityMainCriteria')
            ->orderBy('name')
            ->get();

        $users = User::orderBy('name')->get();

        return view('quality-scores.edit', compact('qualityScore', 'qualitySubCriterias', 'users'));
    }

    public function update(Request $request, $id)
    {
        $qualityScore = QualityScore::findOrFail($id);

        $request->validate([
            'quality_sub_criteria_id' => 'required|exists:quality_sub_criterias,id',
            'user_id' => 'required|exists:users,id',
            'report_id' => 'required|exists:reports,id',
            'score' => 'required|numeric|min:0|max:100',
        ], [
            'quality_sub_criteria_id.required' => 'กรุณาเลือกเกณฑ์การประเมิน',
            'quality_sub_criteria_id.exists' => 'เกณฑ์การประเมินที่เลือกไม่ถูกต้อง',
            'user_id.required' => 'กรุณาเลือกผู้ใช้งาน',
            'user_id.exists' => 'ผู้ใช้งานที่เลือกไม่ถูกต้อง',
            'report_id.required' => 'กรุณาเลือกรายงาน',
            'report_id.exists' => 'รายงานที่เลือกไม่ถูกต้อง',
            'score.required' => 'กรุณาระบุคะแนน',
            'score.numeric' => 'คะแนนต้องเป็นตัวเลขเท่านั้น',
            'score.min' => 'คะแนนต้องไม่น้อยกว่า 0',
            'score.max' => 'คะแนนต้องไม่เกิน 100',
        ]);

        try {
            $qualityScore->update([
                'quality_sub_criteria_id' => $request->quality_sub_criteria_id,
                'user_id' => $request->user_id,
                'report_id' => $request->report_id,
                'score' => $request->score,
            ]);

            return redirect()->route('quality-scores.index')
                ->with('success', 'อัพเดทคะแนนสำเร็จ');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาดในการอัพเดทข้อมูล: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $qualityScore = QualityScore::findOrFail($id);
            $qualityScore->delete();

            return redirect()->route('quality-scores.index')
                ->with('success', 'ลบคะแนนสำเร็จ');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาดในการลบข้อมูล: '.$e->getMessage());
        }
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'quality_sub_criteria_id' => 'required|exists:quality_sub_criterias,id',
            'report_id' => 'nullable|exists:reports,id',
        ]);

        try {
            $query = QualityScore::where('quality_sub_criteria_id', $request->quality_sub_criteria_id);

            // ถ้ามี report_id ให้กรองตาม report_id ด้วย
            if ($request->report_id) {
                $query->where('report_id', $request->report_id);
            }

            $query->delete();

            return redirect()->route('quality-scores.index')
                ->with('success', 'ลบคะแนนทั้งหมดของเกณฑ์นี้สำเร็จ');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาดในการลบข้อมูล: '.$e->getMessage());
        }
    }
}
