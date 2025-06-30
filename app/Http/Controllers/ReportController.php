<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\QuantityScore;
use App\Models\QualityScore;
use App\Models\EvidenceAnswer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Http\Resources\ReportResource;
use App\Http\Resources\ReportSummaryResource;
use App\Http\Resources\QuantityScoreResource;
use App\Http\Resources\QualityScoreResource;
use App\Http\Resources\EvidenceAnswerResource;



class ReportController extends Controller
{
    // GET /reports
    public function index()
    {
        $reports = Report::all();
        return ReportSummaryResource::collection($reports);
    }

    public function show($id)
    {
        $report = Report::with(['quantityScores', 'qualityScores', 'evidenceAnswers'])->find($id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }
        return new ReportResource($report);
    }

    // POST /reports
    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_data_id' => 'required|integer|exists:report_datas,id',
            'report_code'    => 'required|string|unique:reports,report_code|max:255',
            'status'         => 'required|string|max:255',
        ]);

        $report = Report::create($validated);

        return new ReportResource($report);
    }

    // PUT /reports/{id}
    public function update(Request $request, $id)
    {
        $report = Report::find($id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $validated = $request->validate([
            'report_data_id' => 'sometimes|required|integer|exists:report_datas,id',
            'report_code'    => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('reports')->ignore($report->id),
            ],
            'status'         => 'sometimes|required|string|max:255',
        ]);

        $report->update($validated);

        return new ReportResource($report);
    }

    // DELETE /reports/{id}
    public function destroy($id)
    {
        $report = Report::find($id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }
        $report->delete();

        return response()->json(['message' => 'Report deleted successfully']);
    }

    // POST /reports/{report}/quantity-scores
    public function addQuantityScore(Request $request, $report)
    {
        $reportObj = Report::findOrFail($report);
        if (!$reportObj) {
            return response()->json(['message' => 'Report not found'], 404);
        }
        $validated = $request->validate([
            'quantity_list'=> 'required|array',
            'quantity_list.*.quantity_sub_criteria_id' => 'required|integer|exists:quantity_sub_criterias,id',
            'quantity_list.*.score_C' => 'nullable|numeric',
            'quantity_list.*.score_D' => 'nullable|numeric',
        ]);

        $created = [];
        foreach ($validated['quantity_list'] as $item) {
            $quantity_score = QuantityScore::create([
                'quantity_sub_criteria_id' => $item['quantity_sub_criteria_id'],
                'report_id' => $report,
                'score_C' => $item['score_C'] ?? null,
                'score_D' => $item['score_D'] ?? null,
            ]);
            $created[] = $quantity_score;
        }
        return QuantityScoreResource::collection(collect($created));
        // return new QuantityScoreResource($quantity_score);
    }

    // POST /reports/{report}/quality-scores
    public function addQualityScore(Request $request, $report)
    {
        $reportObj = Report::findOrFail($report);
        if (!$reportObj) {
            return response()->json(['message' => 'Report not found'], 404);
        }
        $validated = $request->validate([
            'quality_list' => 'required|array',
            'quality_list.*.quality_sub_criteria_id' => 'required|integer|exists:quality_sub_criterias,id',
            'quality_list.*.score' => 'nullable|numeric',
        ]);

        $created = [];
        foreach ($validated['quality_list'] as $item) {
            $quality_score = QualityScore::create([
                'quality_sub_criteria_id' => $item['quality_sub_criteria_id'],
                'report_id' => $report,
                'score' => $item['score'] ?? null,
            ]);
            $created[] = $quality_score;
        }
        return QualityScoreResource::collection(collect($created));
        // return new QualityScoreResource($quality_score);
    }

    // POST /reports/{report}/evidence-answers
    public function addEvidence(Request $request, $report)
    {
        $reportObj = Report::findOrFail($report);
        if (!$reportObj) {
            return response()->json(['message' => 'Report not found'], 404);
        }
        $validated = $request->validate([
            'evidence_list' => 'required|array',
            'evidence_list.*.evaluation_list_id' => 'required|integer|exists:evaluation_lists,id',
            'evidence_list.*.link' => 'nullable|string|url',
        ]);

        $created = [];
        foreach ($validated['evidence_list'] as $item) {
            $evidence_ans = EvidenceAnswer::create([
                'evaluation_list_id' => $item['evaluation_list_id'],
                'report_id' => $report,
                'link' => $item['link'] ?? null,
            ]);
            $created[] = $evidence_ans;
        }

        return EvidenceAnswerResource::collection(collect($created));
    }
}
