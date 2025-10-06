<?php

namespace App\Http\Controllers;

use App\Http\Resources\CriteriaVersionResource;
use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QualityMainCriteria;
use App\Models\QualitySubCriteria;
use App\Models\QuantityMainCriteria;
use App\Models\QuantitySubCriteria;
use App\Models\ReportData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReportStructureController extends Controller
{
    // Get all criteria versions
    public function index()
    {
        $criteriaVersions = CriteriaVersion::with(['createdByUser', 'reportDatas'])->get();
        // Map to include user name and report title
        $result = $criteriaVersions->map(function ($item) {
            $arr = $item->toArray();
            $arr['created_by'] = $item->createdByUser ? [
                'id' => $item->createdByUser->id,
                'name' => $item->createdByUser->name,
            ] : null;
            $arr['created_by_name'] = $item->createdByUser ? $item->createdByUser->name : null;

            // Get the first report_title from reportDatas if available
            $arr['report_title'] = $item->reportDatas->isNotEmpty() ? $item->reportDatas->first()->report_title : null;

            return $arr;
        });

        return response()->json(['data' => $result]);
    }

    public function show($id)
    {
        try {
            // ตรวจสอบว่ามีเวอร์ชัน
            $versionExists = CriteriaVersion::where('id', $id)->exists();
            if (! $versionExists) {
                return response()->json([
                    'message' => 'CriteriaVersion not found',
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
                        $query->select('id', 'name', 'tooltips')->with('formulas:id,condition,quantity_main_criteria_id');
                    },
                    'categories.evaluationLists.qualitySubCriterias' => function ($query) {
                        $query->select(
                            'quality_sub_criterias.id',
                            'quality_sub_criterias.name',
                            'quality_sub_criterias.sequence',
                            'num_score',
                            'description',
                            'quality_main_criteria_id',
                            'evaluation_list_id'
                        )
                            ->orderBy('sequence');
                    },
                    'categories.evaluationLists.qualitySubCriterias.mainCriteria' => function ($query) {
                        $query->select('id', 'name', 'ratio', 'tooltips', 'sequence')->orderBy('sequence');
                    },
                ])
                ->where('id', $id)
                ->first();

            if (! $version) {
                return response()->json([
                    'message' => 'Error retrieving CriteriaVersion data',
                ], 500);
            }

            // สร้าง response ในรูปแบบที่ต้องการ
            $formattedResponse = [
                'version_name' => $version->version_name,
                'created_by' => $version->created_by,
                'report_datas' => $version->reportDatas->map(function ($reportData) {
                    return [
                        'report_data_id' => $reportData->id,
                        'report_title' => $reportData->report_title,
                        'report_description' => $reportData->report_description,
                        'assessment_type' => $reportData->assessment_type,
                        'comment' => $reportData->comment,
                    ];
                }),
                'categories' => $version->categories->map(function ($category) {
                    // กลุ่ม evaluation lists ตาม category
                    return [
                        'categorie_id' => $category->id,
                        'main_categories' => $category->main_categories,
                        'sub_categories' => $category->sub_categories,
                        'sequence' => $category->sequence,
                        'evaluation_lists' => $category->evaluationLists->map(function ($evalList) {
                            // สร้าง Map ของ quantity main criterias
                            $quantityMainMap = [];

                            foreach ($evalList->quantitySubCriterias as $qSub) {
                                $mainId = $qSub->quantity_main_criteria_id;
                                $main = $qSub->mainCriteria;

                                if (! $main) {
                                    continue; // ข้ามถ้าไม่มี main criteria
                                }

                                if (! isset($quantityMainMap[$mainId])) {
                                    $quantityMainMap[$mainId] = [
                                        'quantity_main_criteria_id' => $main->id,
                                        'name' => $main->name,
                                        'tooltips' => $main->tooltips,
                                        'formulas' => $main->formulas->map(function ($formula) {
                                            return [
                                                'id' => $formula->id,
                                                'condition' => $formula->condition,
                                            ];
                                        }),
                                        'quantity_sub_criterias' => [],
                                    ];
                                }

                                $quantityMainMap[$mainId]['quantity_sub_criterias'][] = [
                                    'quantity_sub_criteria_id' => $qSub->id,
                                    'name' => $qSub->name,
                                    'sequence' => $qSub->sequence,
                                    'score_a' => (float) $qSub->score_a,
                                    'score_b' => (float) $qSub->score_b,
                                ];
                            }

                            // สร้าง Map ของ quality main criterias
                            $qualityMainMap = [];

                            foreach ($evalList->qualitySubCriterias as $qSub) {
                                $mainId = $qSub->quality_main_criteria_id;
                                $main = $qSub->mainCriteria;

                                if (! $main) {
                                    continue; // ข้ามถ้าไม่มี main criteria
                                }

                                if (! isset($qualityMainMap[$mainId])) {
                                    $qualityMainMap[$mainId] = [
                                        'quality_main_criteria_id' => $main->id, // ใช้ $main->id ไม่ใช่ $main->name
                                        'name' => $main->name,
                                        'ratio' => $main->ratio,
                                        'tooltips' => $main->tooltips,
                                        'sequence' => $main->sequence,
                                        'quality_sub_criterias' => [],
                                    ];
                                }

                                $qualityMainMap[$mainId]['quality_sub_criterias'][] = [
                                    'quality_sub_criteria_id' => $qSub->id,
                                    'name' => $qSub->name,
                                    'sequence' => $qSub->sequence,
                                    'num_score' => (float) $qSub->num_score,
                                    'description' => $qSub->description,
                                ];
                            }

                            return [
                                'evaluation_id' => $evalList->id,
                                'name' => $evalList->name,
                                'sum_score' => (float) $evalList->sum_score,
                                'sequence' => $evalList->sequence,
                                'annotation' => $evalList->annotation,
                                'quantity_main_criterias' => array_values($quantityMainMap),
                                'quality_main_criterias' => collect($qualityMainMap)
                                    ->sortBy('sequence')
                                    ->values()
                                    ->all(),
                            ];
                        })->values()->all(),
                    ];
                })->values()->all(),
            ];

            return response()->json([
                'data' => $formattedResponse,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching criteria version: '.$e->getMessage());

            return response()->json([
                'message' => 'Failed to retrieve criteria version',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Create new (POST)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'version_name' => 'sometimes|string|max:255', // เปลี่ยนจาก required เป็น sometimes
            'created_by' => 'required|integer|exists:users,id',

            'report_datas' => 'required|array',
            'report_datas.*.report_title' => 'required|string',
            'report_datas.*.report_description' => 'nullable|string',
            'report_datas.*.assessment_type' => 'required|string', // ถ้าหากมี 2 อย่างนี้ |in:quantity,quality
            'report_datas.*.comment' => 'nullable|string',

            'categories' => 'required|array|min:1',
            'categories.*.main_categories' => 'required|string',
            'categories.*.sub_categories' => 'required|string',
            'categories.*.sequence' => 'required|integer|min:1',

            'categories.*.evaluation_lists' => 'sometimes|array|min:1',
            'categories.*.evaluation_lists.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.sum_score' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.annotation' => 'nullable|string',

            'categories.*.evaluation_lists.*.quantity_main_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.tooltips' => 'nullable|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.formula' => 'nullable|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.score_a' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.score_b' => 'required|numeric|min:0',

            'categories.*.evaluation_lists.*.quality_main_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.ratio' => 'required|numeric|min:1',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.tooltips' => 'nullable|string',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.num_score' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.description' => 'nullable|string',
        ]);

        try {
            $version = DB::transaction(function () use ($validated) {
                // Generate version_name automatically if not provided or contains AUTO
                if (empty($validated['version_name']) || strpos($validated['version_name'], 'AUTO') !== false) {
                    $currentYear = now()->year + 543; // Convert to Buddhist Era
                    $yearPrefix = 'เกณฑ์ประเมินปี '.$currentYear.' ครั้งที่ ';

                    // Find the latest number for this year
                    $latestVersion = CriteriaVersion::where('version_name', 'LIKE', 'เกณฑ์ประเมินปี '.$currentYear.' ครั้งที่ %')
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($latestVersion) {
                        // Extract number from version_name (format: เกณฑ์ประเมินปี YYYY ครั้งที่ X)
                        preg_match('/ครั้งที่ (\d+)$/', $latestVersion->version_name, $matches);
                        $lastNumber = isset($matches[1]) ? (int) $matches[1] : 0;
                        $nextNumber = $lastNumber + 1;
                    } else {
                        // First version for this year
                        $nextNumber = 1;
                    }

                    $validated['version_name'] = $yearPrefix.$nextNumber;
                }

                // 1. Create Criteria Version
                $version = CriteriaVersion::create([
                    'version_name' => $validated['version_name'],
                    'created_by' => $validated['created_by'],
                ]);

                // 2. Create Report Datas
                foreach ($validated['report_datas'] as $reportDatum) {
                    ReportData::create([
                        'criteria_version_id' => $version->id,
                        'report_title' => $reportDatum['report_title'],
                        'report_description' => $reportDatum['report_description'],
                        'assessment_type' => $reportDatum['assessment_type'],
                        'comment' => $reportDatum['comment'] ?? null,
                    ]);
                }

                // 3. Create Categories and children
                foreach ($validated['categories'] as $categoryData) {
                    $category = Category::create([
                        'criteria_version_id' => $version->id,
                        'main_categories' => $categoryData['main_categories'],
                        'sub_categories' => $categoryData['sub_categories'],
                        'sequence' => $categoryData['sequence'],
                    ]);

                    // Evaluation Lists
                    if (! empty($categoryData['evaluation_lists'])) {
                        foreach ($categoryData['evaluation_lists'] as $evalListData) {
                            $evaluationList = EvaluationList::create([
                                'categorie_id' => $category->id,
                                'criteria_version_id' => $version->id,
                                'name' => $evalListData['name'],
                                'sum_score' => $evalListData['sum_score'],
                                'sequence' => $evalListData['sequence'],
                                'annotation' => $evalListData['annotation'] ?? null,
                            ]);

                            // Quantity Main Criterias
                            if (! empty($evalListData['quantity_main_criterias'])) {
                                foreach ($evalListData['quantity_main_criterias'] as $qMain) {
                                    $quantityMainCriteria = QuantityMainCriteria::create([
                                        'criteria_version_id' => $version->id,
                                        'name' => $qMain['name'],
                                        'tooltips' => $qMain['tooltips'],
                                        'description' => $qMain['description'] ?? null,
                                    ]);

                                    // บันทึกสูตรถ้ามี
                                    if (! empty($qMain['formula'])) {
                                        \App\Models\Formula::create([
                                            'condition' => $qMain['formula'],
                                            'quantity_main_criteria_id' => $quantityMainCriteria->id,
                                        ]);
                                    }

                                    if (! empty($qMain['quantity_sub_criterias'])) {
                                        foreach ($qMain['quantity_sub_criterias'] as $qSub) {
                                            QuantitySubCriteria::create([
                                                'criteria_version_id' => $version->id,
                                                'quantity_main_criteria_id' => $quantityMainCriteria->id,
                                                'evaluation_list_id' => $evaluationList->id,
                                                'name' => $qSub['name'],
                                                'sequence' => $qSub['sequence'],
                                                'score_a' => $qSub['score_a'],
                                                'score_b' => $qSub['score_b'],
                                            ]);
                                        }
                                    }
                                }
                            }

                            // Quality Main Criterias
                            if (! empty($evalListData['quality_main_criterias'])) {
                                foreach ($evalListData['quality_main_criterias'] as $qlMain) {
                                    $qualityMainCriteria = QualityMainCriteria::create([
                                        'criteria_version_id' => $version->id,
                                        'name' => $qlMain['name'],
                                        'ratio' => $qlMain['ratio'],
                                        'tooltips' => $qlMain['tooltips'],
                                        'sequence' => $qlMain['sequence'],
                                    ]);
                                    if (! empty($qlMain['quality_sub_criterias'])) {
                                        foreach ($qlMain['quality_sub_criterias'] as $qlSub) {
                                            QualitySubCriteria::create([
                                                'quality_main_criteria_id' => $qualityMainCriteria->id,
                                                'criteria_version_id' => $version->id,
                                                'evaluation_list_id' => $evaluationList->id,
                                                'name' => $qlSub['name'],
                                                'sequence' => $qlSub['sequence'],
                                                'num_score' => $qlSub['num_score'],
                                                'description' => $qlSub['description'] ?? null,
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

            return response()->json([
                'success' => true,
                'message' => 'Criteria version and related records created successfully',
                'data' => $version->load([
                    'quantityMainCriterias.quantitySubCriterias',
                    'quantityMainCriterias.formulas',
                    'qualityMainCriterias.qualitySubCriterias',
                    'reportDatas',
                    // Now load evaluationLists' sub-criterias, and have each sub-criteria load its main criteria
                    'categories.evaluationLists.quantitySubCriterias.mainCriteria.formulas',
                    'categories.evaluationLists.qualitySubCriterias.mainCriteria',
                ]),
            ], 201);
        } catch (ValidationException $e) {
            Log::error('Validation error in store: '.json_encode($e->errors()));

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Server error in store: '.$e->getMessage(), ['exception' => $e]);
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    // Update (PUT/PATCH)

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'version_name' => 'required|string|max:255',
            'created_by' => 'required|integer|exists:users,id',

            'report_datas' => 'required|array',
            'report_datas.*.report_title' => 'required|string',
            'report_datas.*.report_description' => 'nullable|string',
            'report_datas.*.assessment_type' => 'required|string',
            'report_datas.*.comment' => 'nullable|string',

            'categories' => 'required|array|min:1',
            'categories.*.main_categories' => 'required|string',
            'categories.*.sub_categories' => 'required|string',
            'categories.*.sequence' => 'required|integer|min:1',

            'categories.*.evaluation_lists' => 'sometimes|array|min:1',
            'categories.*.evaluation_lists.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.sum_score' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.annotation' => 'nullable|string',

            'categories.*.evaluation_lists.*.quantity_main_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.tooltips' => 'nullable|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.formula' => 'nullable|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.score_a' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.score_b' => 'required|numeric|min:0',

            'categories.*.evaluation_lists.*.quality_main_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.ratio' => 'required|numeric|min:1',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.tooltips' => 'nullable|string',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.num_score' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.description' => 'nullable|string',
        ]);

        $version = CriteriaVersion::findOrFail($id);

        try {
            DB::transaction(function () use ($version, $validated) {
                // 1. Delete all related data in correct order (children first)

                // Delete formulas first (if they reference quantity main criterias)
                DB::table('formulas')
                    ->whereIn('quantity_main_criteria_id', function ($query) use ($version) {
                        $query->select('id')
                            ->from('quantity_main_criterias')
                            ->where('criteria_version_id', $version->id);
                    })
                    ->delete();

                // Delete sub criterias
                DB::table('quantity_sub_criterias')
                    ->whereIn('evaluation_list_id', function ($query) use ($version) {
                        $query->select('id')
                            ->from('evaluation_lists')
                            ->where('criteria_version_id', $version->id);
                    })
                    ->delete();

                DB::table('quality_sub_criterias')
                    ->whereIn('evaluation_list_id', function ($query) use ($version) {
                        $query->select('id')
                            ->from('evaluation_lists')
                            ->where('criteria_version_id', $version->id);
                    })
                    ->delete();

                // Delete main criterias
                $version->quantityMainCriterias()->delete();
                $version->qualityMainCriterias()->delete();

                // Delete evaluation lists
                $version->evaluationLists()->delete();

                // Delete categories and report datas
                $version->categories()->delete();
                $version->reportDatas()->delete();

                // 2. Update Criteria Version
                $version->update([
                    'version_name' => $validated['version_name'],
                    'created_by' => $validated['created_by'],
                ]);

                // 3. Re-create Report Datas
                foreach ($validated['report_datas'] as $reportDatum) {
                    ReportData::create([
                        'criteria_version_id' => $version->id,
                        'report_title' => $reportDatum['report_title'],
                        'report_description' => $reportDatum['report_description'],
                        'assessment_type' => $reportDatum['assessment_type'],
                        'comment' => $reportDatum['comment'] ?? null,
                    ]);
                }

                // 4. Re-create Categories and children
                foreach ($validated['categories'] as $categoryData) {
                    $category = Category::create([
                        'criteria_version_id' => $version->id,
                        'main_categories' => $categoryData['main_categories'],
                        'sub_categories' => $categoryData['sub_categories'],
                        'sequence' => $categoryData['sequence'],
                    ]);

                    if (! empty($categoryData['evaluation_lists'])) {
                        foreach ($categoryData['evaluation_lists'] as $evalListData) {
                            $evaluationList = EvaluationList::create([
                                'categorie_id' => $category->id,
                                'criteria_version_id' => $version->id,
                                'name' => $evalListData['name'],
                                'sum_score' => $evalListData['sum_score'],
                                'sequence' => $evalListData['sequence'],
                                'annotation' => $evalListData['annotation'] ?? null,
                            ]);

                            if (! empty($evalListData['quantity_main_criterias'])) {
                                foreach ($evalListData['quantity_main_criterias'] as $qMain) {
                                    $quantityMainCriteria = QuantityMainCriteria::create([
                                        'criteria_version_id' => $version->id,
                                        'name' => $qMain['name'],
                                        'tooltips' => $qMain['tooltips'],
                                    ]);

                                    if (! empty($qMain['formula'])) {
                                        \App\Models\Formula::create([
                                            'condition' => $qMain['formula'],
                                            'quantity_main_criteria_id' => $quantityMainCriteria->id,
                                        ]);
                                    }

                                    if (! empty($qMain['quantity_sub_criterias'])) {
                                        foreach ($qMain['quantity_sub_criterias'] as $qSub) {
                                            QuantitySubCriteria::create([
                                                'criteria_version_id' => $version->id,
                                                'quantity_main_criteria_id' => $quantityMainCriteria->id,
                                                'evaluation_list_id' => $evaluationList->id,
                                                'name' => $qSub['name'],
                                                'sequence' => $qSub['sequence'],
                                                'score_a' => $qSub['score_a'],
                                                'score_b' => $qSub['score_b'],
                                            ]);
                                        }
                                    }
                                }
                            }

                            if (! empty($evalListData['quality_main_criterias'])) {
                                foreach ($evalListData['quality_main_criterias'] as $qlMain) {
                                    $qualityMainCriteria = QualityMainCriteria::create([
                                        'criteria_version_id' => $version->id,
                                        'name' => $qlMain['name'],
                                        'ratio' => $qlMain['ratio'],
                                        'tooltips' => $qlMain['tooltips'],
                                        'sequence' => $qlMain['sequence'],
                                    ]);
                                    if (! empty($qlMain['quality_sub_criterias'])) {
                                        foreach ($qlMain['quality_sub_criterias'] as $qlSub) {
                                            QualitySubCriteria::create([
                                                'quality_main_criteria_id' => $qualityMainCriteria->id,
                                                'criteria_version_id' => $version->id,
                                                'evaluation_list_id' => $evaluationList->id,
                                                'name' => $qlSub['name'],
                                                'sequence' => $qlSub['sequence'],
                                                'num_score' => $qlSub['num_score'],
                                                'description' => $qlSub['description'] ?? null,
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Criteria version updated successfully',
                'data' => new CriteriaVersionResource($version->fresh()),
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation error in update: '.json_encode($e->errors()));

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Server error in update: '.$e->getMessage(), ['exception' => $e]);
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    // Delete (DELETE)
    public function destroy($id)
    {
        $criteriaVersion = CriteriaVersion::findOrFail($id);

        $relatedReports = DB::table('reports')
            ->where('report_data_id', $id)->get();

        if ($relatedReports->count() > 0) {
            // ถ้ามี report ไหนที่ status ไม่ใช่ Completed ห้ามลบ
            $notCompleted = $relatedReports->where('status', '!=', 'Completed');
            if ($notCompleted->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถลบได้ เนื่องจากมีการประเมินที่ใช้โครงสร้างเกณฑ์นี้อยู่ ต้องให้การประเมินครบถ้วนก่อน',
                    'not_completed_count' => $notCompleted->count(),
                ], 409);
            }
        }

        $criteriaVersion->delete();

        return response()->json(null, 204);
    }
}
