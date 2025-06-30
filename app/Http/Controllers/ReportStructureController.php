<?php

namespace App\Http\Controllers;

use App\Models\CriteriaVersion;
use Illuminate\Http\Request;
use App\Http\Resources\CriteriaVersionResource;
use App\Models\ReportData;
use App\Models\Category;
use App\Models\EvaluationList;
use App\Models\QuantityMainCriteria;
use App\Models\QuantitySubCriteria;
use App\Models\QualityMainCriteria;
use App\Models\QualitySubCriteria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportStructureController extends Controller
{
    // Get all criteria versions
    public function index()
    {
        $criteriaVersions = CriteriaVersion::all();
        return CriteriaVersionResource::collection($criteriaVersions);
    }

    public function show($id)
    {
        try {
            // ตรวจสอบว่ามีเวอร์ชัน
            $versionExists = CriteriaVersion::where('id', $id)->exists();
            if (!$versionExists) {
                return response()->json([
                    'message' => 'CriteriaVersion not found'
                ], 404);
            }

            $version = CriteriaVersion::select('id', 'version_name', 'created_by')
                ->with([
                    'reportDatas:id,criteria_version_id,report_title,report_description,assessment_type,comment',
                    'categories' => function ($query) {
                        $query->select('id', 'criteria_version_id', 'main_categories', 'sub_categories', 'sequence')
                            ->orderBy('sequence');
                    },
                    'categories.evaluationLists' => function ($query) {
                        $query->select('id', 'categorie_id', 'criteria_version_id', 'name', 'sum_score', 'sequence', 'annotation')
                            ->orderBy('sequence');
                    },
                    'categories.evaluationLists.quantitySubCriterias' => function ($query) {
                        $query->select(
                            'quantity_sub_criterias.id',
                            'quantity_sub_criterias.name',
                            'quantity_sub_criterias.sequence',
                            'score_a',
                            'score_b',
                            'quantity_main_criteria_id',
                            'evaluation_list_id'
                        )
                            ->orderBy('sequence');
                    },
                    'categories.evaluationLists.quantitySubCriterias.mainCriteria' => function ($query) {
                        $query->select('id', 'name', 'tooltips');
                    },
                    'categories.evaluationLists.qualitySubCriterias' => function ($query) {
                        $query->select(
                            'quality_sub_criterias.id',
                            'quality_sub_criterias.name',
                            'quality_sub_criterias.sequence',
                            'num_score',
                            'quality_main_criteria_id',
                            'evaluation_list_id'
                        )
                            ->orderBy('sequence');
                    },
                    'categories.evaluationLists.qualitySubCriterias.mainCriteria' => function ($query) {
                        $query->select('id', 'name', 'ratio', 'tooltips', 'sequence');
                    }
                ])
                ->where('id', $id)
                ->first();

            if (!$version) {
                return response()->json([
                    'message' => 'Error retrieving CriteriaVersion data'
                ], 500);
            }

            // สร้าง response ในรูปแบบที่ต้องการ
            $formattedResponse = [
                'version_name' => $version->version_name,
                'created_by' => $version->created_by,
                'report_datas' => $version->reportDatas->map(function ($reportData) {
                    return [
                        'report_title' => $reportData->report_title,
                        'report_description' => $reportData->report_description,
                        'assessment_type' => $reportData->assessment_type,
                        'comment' => $reportData->comment,
                    ];
                }),
                'categories' => $version->categories->map(function ($category) {
                    // กลุ่ม evaluation lists ตาม category
                    return [
                        'main_categories' => $category->main_categories,
                        'sub_categories' => $category->sub_categories,
                        'sequence' => $category->sequence,
                        'evaluation_lists' => $category->evaluationLists->map(function ($evalList) {
                            // สร้าง Map ของ quantity main criterias
                            $quantityMainMap = collect();
                            foreach ($evalList->quantitySubCriterias as $qSub) {
                                $mainId = $qSub->quantity_main_criteria_id;
                                $main = $qSub->mainCriteria;

                                if (!$quantityMainMap->has($mainId) && $main) {
                                    $quantityMainMap->put($mainId, [
                                        'name' => $main->name,
                                        'tooltips' => $main->tooltips,
                                        'quantity_sub_criterias' => []
                                    ]);
                                }

                                if ($main) {
                                    $quantityMainMap->get($mainId)['quantity_sub_criterias'][] = [
                                        'name' => $qSub->name,
                                        'sequence' => $qSub->sequence,
                                        'score_a' => (float)$qSub->score_a,
                                        'score_b' => (float)$qSub->score_b,
                                    ];
                                }
                            }

                            // สร้าง Map ของ quality main criterias
                            $qualityMainMap = collect();
                            foreach ($evalList->qualitySubCriterias as $qSub) {
                                $mainId = $qSub->quality_main_criteria_id;
                                $main = $qSub->mainCriteria;

                                if (!$qualityMainMap->has($mainId) && $main) {
                                    $qualityMainMap->put($mainId, [
                                        'name' => $main->name,
                                        'ratio' => $main->ratio,
                                        'tooltips' => $main->tooltips,
                                        'sequence' => $main->sequence,
                                        'quality_sub_criterias' => []
                                    ]);
                                }

                                if ($main) {
                                    $qualityMainMap->get($mainId)['quality_sub_criterias'][] = [
                                        'name' => $qSub->name,
                                        'sequence' => $qSub->sequence,
                                        'num_score' => (float)$qSub->num_score,
                                    ];
                                }
                            }

                            return [
                                'name' => $evalList->name,
                                'sum_score' => (float)$evalList->sum_score,
                                'sequence' => $evalList->sequence,
                                'annotation' => $evalList->annotation,
                                'quantity_main_criterias' => $quantityMainMap->values()->all(),
                                'quality_main_criterias' => $qualityMainMap->values()->all(),
                            ];
                        })->values()->all(),
                    ];
                })->values()->all(),
            ];

            return response()->json([
                'data' => $formattedResponse
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching criteria version: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve criteria version',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Create new (POST)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'version_name' => 'required|string|max:255|unique:criteria_versions,version_name',
            'created_by' => 'required|integer|exists:users,id',

            'report_datas' => 'required|array',
            'report_datas.*.report_title' => 'required|string',
            'report_datas.*.report_description' => 'required|string',
            'report_datas.*.assessment_type' => 'required|string', //ถ้าหากมี 2 อย่างนี้ |in:quantity,quality
            'report_datas.*.comment' => 'nullable|string',

            'categories' => 'required|array',
            'categories.*.main_categories' => 'required|string',
            'categories.*.sub_categories' => 'required|string',
            'categories.*.sequence' => 'required|integer|min:1',

            'categories.*.evaluation_lists' => 'nullable|array',
            'categories.*.evaluation_lists.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.sum_score' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.annotation' => 'nullable|string',

            'categories.*.evaluation_lists.*.quantity_main_criterias' => 'nullable|array',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.tooltips' => 'required|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias' => 'nullable|array',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.score_a' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.score_b' => 'required|numeric|min:0',

            'categories.*.evaluation_lists.*.quality_main_criterias' => 'nullable|array',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.ratio' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.tooltips' => 'required|string',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias' => 'nullable|array',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.num_score' => 'required|numeric|min:0',
        ]);

        try {
            $version = DB::transaction(function () use ($validated) {

                // 1. Create Criteria Version
                $version = CriteriaVersion::create([
                    'version_name' => $validated['version_name'],
                    'created_by'   => $validated['created_by'],
                ]);

                // 2. Create Report Datas
                foreach ($validated['report_datas'] as $reportDatum) {
                    ReportData::create([
                        'criteria_version_id' => $version->id,
                        'report_title'        => $reportDatum['report_title'],
                        'report_description'  => $reportDatum['report_description'],
                        'assessment_type'     => $reportDatum['assessment_type'],
                        'comment'             => $reportDatum['comment'] ?? null,
                    ]);
                }

                // 3. Create Categories and children
                foreach ($validated['categories'] as $categoryData) {
                    $category = Category::create([
                        'criteria_version_id' => $version->id,
                        'main_categories'     => $categoryData['main_categories'],
                        'sub_categories'      => $categoryData['sub_categories'],
                        'sequence'            => $categoryData['sequence'],
                    ]);

                    // Evaluation Lists
                    if (!empty($categoryData['evaluation_lists'])) {
                        foreach ($categoryData['evaluation_lists'] as $evalListData) {
                            $evaluationList = EvaluationList::create([
                                'categorie_id' => $category->id,
                                'criteria_version_id' => $version->id,
                                'name'        => $evalListData['name'],
                                'sum_score'   => $evalListData['sum_score'],
                                'sequence'    => $evalListData['sequence'],
                                'annotation'  => $evalListData['annotation'] ?? null,
                            ]);

                            // Quantity Main Criterias
                            if (!empty($evalListData['quantity_main_criterias'])) {
                                foreach ($evalListData['quantity_main_criterias'] as $qMain) {
                                    $quantityMainCriteria = QuantityMainCriteria::create([
                                        'criteria_version_id' => $version->id,
                                        'name' => $qMain['name'],
                                        'tooltips' => $qMain['tooltips'],
                                    ]);
                                    if (!empty($qMain['quantity_sub_criterias'])) {
                                        foreach ($qMain['quantity_sub_criterias'] as $qSub) {
                                            QuantitySubCriteria::create([
                                                'criteria_version_id' => $version->id,
                                                'quantity_main_criteria_id' => $quantityMainCriteria->id,
                                                'evaluation_list_id' => $evaluationList->id,
                                                'name'      => $qSub['name'],
                                                'sequence'  => $qSub['sequence'],
                                                'score_a'   => $qSub['score_a'],
                                                'score_b'   => $qSub['score_b'],
                                            ]);
                                        }
                                    }
                                }
                            }

                            // Quality Main Criterias
                            if (!empty($evalListData['quality_main_criterias'])) {
                                foreach ($evalListData['quality_main_criterias'] as $qlMain) {
                                    $qualityMainCriteria = QualityMainCriteria::create([
                                        'criteria_version_id' => $version->id,
                                        'name'               => $qlMain['name'],
                                        'ratio'              => $qlMain['ratio'],
                                        'tooltips'           => $qlMain['tooltips'],
                                        'sequence'           => $qlMain['sequence'],
                                    ]);
                                    if (!empty($qlMain['quality_sub_criterias'])) {
                                        foreach ($qlMain['quality_sub_criterias'] as $qlSub) {
                                            QualitySubCriteria::create([
                                                'quality_main_criteria_id' => $qualityMainCriteria->id,
                                                'criteria_version_id' => $version->id,
                                                'evaluation_list_id' => $evaluationList->id,
                                                'name'      => $qlSub['name'],
                                                'sequence'  => $qlSub['sequence'],
                                                'num_score' => $qlSub['num_score'],
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                return $version;
            });

            // Optionally, eager load relationships for response
            $version->load([
                'quantityMainCriterias.quantitySubCriterias',
                'qualityMainCriterias.qualitySubCriterias',
                'reportDatas',
                // Now load evaluationLists' sub-criterias, and have each sub-criteria load its main criteria
                'categories.evaluationLists.quantitySubCriterias.mainCriteria',
                'categories.evaluationLists.qualitySubCriterias.mainCriteria',
            ]);

            return response()->json([
                'message' => 'Criteria version and related records created successfully',
                'data' => $version,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create criteria version',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Update (PUT/PATCH)

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'version_name' => 'sometimes|required|string|max:255',
            // 'created_by' => 'nullable|exists:users,user_id',
        ]);

        // $authUser = Auth::guard('api')->user();
        $version = CriteriaVersion::where('version_id', $id)->first();

        // $validated['created_by'] = $authUser->user_id;

        $version->update($validated);

        return response()->json([
            'message' => 'Criteria version updated successfully',
            'data' => new CriteriaVersionResource($version)
        ]);
    }

    // Delete (DELETE)
    public function destroy($id)
    {
        $criteriaVersion = CriteriaVersion::findOrFail($id);
        $criteriaVersion->delete();
        return response()->json(null, 204);
    }
}
