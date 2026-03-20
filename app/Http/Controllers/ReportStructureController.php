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
use App\Models\QuantitySubCriteriaGroup;
use App\Models\QuantitySubCriteriaItem;
use App\Models\ReportData;
use App\Models\WorkloadForm;
use App\Models\WorkloadFormField;
use App\Models\WorkloadFormItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ReportStructureController extends Controller
{
    private function hasQuantityRequireEvidenceColumn(): bool
    {
        static $cached = null;
        if ($cached === null) {
            $cached = Schema::hasColumn('quantity_sub_criterias', 'require_evidence');
        }

        return $cached;
    }

    private function hasQuantityRequireSubjectColumn(): bool
    {
        static $cached = null;
        if ($cached === null) {
            $cached = Schema::hasColumn('quantity_sub_criterias', 'require_subject');
        }

        return $cached;
    }

    private function hasQualityRequireEvidenceColumn(): bool
    {
        static $cached = null;
        if ($cached === null) {
            $cached = Schema::hasColumn('quality_main_criterias', 'require_evidence');
        }

        return $cached;
    }

    private function jsonNoStore(array $payload, int $status = 200)
    {
        return response()->json($payload, $status, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    // Get all criteria versions
    /**
     * เมธอด: index
     * จุดประสงค์: ส่งข้อมูลแบบ JSON
     * อินพุต: ไม่มี
     * เอาต์พุต: ข้อมูล JSON
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
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

        return $this->jsonNoStore(['data' => $result]);
    }

    /**
     * เมธอด: show
     * จุดประสงค์: ส่งข้อมูลแบบ JSON
     * อินพุต: ตัวระบุ ($id)
     * เอาต์พุต: ข้อมูล JSON
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function show($id)
    {
        try {
            $hasQuantityRequireEvidence = $this->hasQuantityRequireEvidenceColumn();
            $hasQuantityRequireSubject = $this->hasQuantityRequireSubjectColumn();
            $hasQualityRequireEvidence = $this->hasQualityRequireEvidenceColumn();

            // ตรวจสอบว่ามีเวอร์ชัน
            $versionExists = CriteriaVersion::where('id', $id)->exists();
            if (! $versionExists) {
                return $this->jsonNoStore([
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
                    'categories.evaluationLists.quantitySubCriterias' => function ($query) use ($hasQuantityRequireEvidence, $hasQuantityRequireSubject) {
                        $columns = [
                            'quantity_sub_criterias.id',
                            'quantity_sub_criterias.name',
                            'quantity_sub_criterias.sequence',
                            'score_a',
                            'score_b',
                            'quantity_main_criteria_id',
                            'evaluation_list_id',
                        ];
                        if ($hasQuantityRequireEvidence) {
                            $columns[] = 'require_evidence';
                        }
                        if ($hasQuantityRequireSubject) {
                            $columns[] = 'require_subject';
                        }

                        $query->select($columns)
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
                    'categories.evaluationLists.qualitySubCriterias.mainCriteria' => function ($query) use ($hasQualityRequireEvidence) {
                        $columns = ['id', 'name', 'ratio', 'tooltips', 'sequence'];
                        if ($hasQualityRequireEvidence) {
                            $columns[] = 'require_evidence';
                        }

                        $query->select($columns)->orderBy('sequence');
                    },
                ])
                ->where('id', $id)
                ->first();

            if (! $version) {
                return $this->jsonNoStore([
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
                'categories' => $version->categories->map(function ($category) use ($hasQuantityRequireEvidence, $hasQuantityRequireSubject, $hasQualityRequireEvidence) {
                    // กลุ่ม evaluation lists ตาม category
                    return [
                        'categorie_id' => $category->id,
                        'main_categories' => $category->main_categories,
                        'sub_categories' => $category->sub_categories,
                        'sequence' => $category->sequence,
                        'evaluation_lists' => $category->evaluationLists->map(function ($evalList) use ($hasQuantityRequireEvidence, $hasQuantityRequireSubject, $hasQualityRequireEvidence) {
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
                                    'require_evidence' => $hasQuantityRequireEvidence ? (bool) $qSub->require_evidence : false,
                                    'require_subject' => $hasQuantityRequireSubject ? (bool) $qSub->require_subject : false,
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
                                        'require_evidence' => $hasQualityRequireEvidence ? (bool) $main->require_evidence : false,
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

            return $this->jsonNoStore([
                'data' => $formattedResponse,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching criteria version: '.$e->getMessage());

            return $this->jsonNoStore([
                'message' => 'Failed to retrieve criteria version',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Create new (POST)
    /**
     * เมธอด: store
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ บันทึกข้อมูล CriteriaVersion, ReportData, Category, EvaluationList, QuantityMainCriteria, Formula, QuantitySubCriteria, QualityMainCriteria, QualitySubCriteria ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ข้อมูล JSON
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function store(Request $request)
    {
        $hasQuantityRequireEvidence = $this->hasQuantityRequireEvidenceColumn();
        $hasQuantityRequireSubject = $this->hasQuantityRequireSubjectColumn();
        $hasQualityRequireEvidence = $this->hasQualityRequireEvidenceColumn();

        $validated = $request->validate([
            'version_name' => 'sometimes|string|max:255', // เปลี่ยนจาก required เป็น sometimes
            'source_version_id' => 'nullable|integer|exists:criteria_versions,id',
            'created_by' => 'required|integer|exists:users,id',

            'report_datas' => 'required|array',
            'report_datas.*.report_data_id' => 'sometimes|nullable|integer|exists:report_datas,id',
            'report_datas.*.report_title' => 'required|string',
            'report_datas.*.report_description' => 'nullable|string',
            'report_datas.*.assessment_type' => 'required|string', // ถ้าหากมี 2 อย่างนี้ |in:quantity,quality
            'report_datas.*.comment' => 'nullable|string',

            'categories' => 'required|array|min:1',
            'categories.*.categorie_id' => 'sometimes|nullable|integer|exists:categories,id',
            'categories.*.main_categories' => 'required|string',
            'categories.*.sub_categories' => 'required|string',
            'categories.*.sequence' => 'required|integer|min:1',

            'categories.*.evaluation_lists' => 'sometimes|array|min:1',
            'categories.*.evaluation_lists.*.evaluation_id' => 'sometimes|nullable|integer|exists:evaluation_lists,id',
            'categories.*.evaluation_lists.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.sum_score' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.annotation' => 'nullable|string',

            'categories.*.evaluation_lists.*.quantity_main_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_main_criteria_id' => 'sometimes|nullable|integer|exists:quantity_main_criterias,id',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.tooltips' => 'nullable|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.formula' => 'nullable|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.quantity_sub_criteria_id' => 'sometimes|nullable|integer|exists:quantity_sub_criterias,id',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.score_a' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.score_b' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.require_evidence' => 'nullable|boolean',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.require_subject' => 'nullable|boolean',

            'categories.*.evaluation_lists.*.quality_main_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_main_criteria_id' => 'sometimes|nullable|integer|exists:quality_main_criterias,id',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.ratio' => 'required|numeric|min:1',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.tooltips' => 'nullable|string',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.require_evidence' => 'nullable|boolean',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.quality_sub_criteria_id' => 'sometimes|nullable|integer|exists:quality_sub_criterias,id',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.num_score' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.description' => 'nullable|string',
        ]);

        try {
            $version = DB::transaction(function () use ($validated, $hasQuantityRequireEvidence, $hasQuantityRequireSubject, $hasQualityRequireEvidence) {
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
                                                ...($hasQuantityRequireEvidence ? ['require_evidence' => (bool) ($qSub['require_evidence'] ?? false)] : []),
                                                ...($hasQuantityRequireSubject ? ['require_subject' => (bool) ($qSub['require_subject'] ?? false)] : []),
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
                                        ...($hasQualityRequireEvidence ? ['require_evidence' => (bool) ($qlMain['require_evidence'] ?? false)] : []),
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

                
                if (! empty($validated['source_version_id'])) {
                    $this->copyWorkloadFromSource(
                        (int) $validated['source_version_id'],
                        (int) $version->id
                    );
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

    /**
     * เมธอด: update
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ บันทึกข้อมูล ReportData, Category, EvaluationList, QuantityMainCriteria, Formula, QuantitySubCriteria, QualityMainCriteria, QualitySubCriteria, CriteriaVersionResource อัปเดตข้อมูล ลบข้อมูล ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ, ตัวระบุ ($id)
     * เอาต์พุต: ข้อมูล JSON
     * @param Request $request ค่าที่รับเข้ามา
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function update(Request $request, $id)
    {
        $hasQuantityRequireEvidence = $this->hasQuantityRequireEvidenceColumn();
        $hasQuantityRequireSubject = $this->hasQuantityRequireSubjectColumn();
        $hasQualityRequireEvidence = $this->hasQualityRequireEvidenceColumn();

        $validated = $request->validate([
            'version_name' => 'required|string|max:255',
            'created_by' => 'sometimes|nullable|integer|exists:users,id',

            'report_datas' => 'required|array',
            'report_datas.*.report_data_id' => 'sometimes|nullable|integer|exists:report_datas,id',
            'report_datas.*.report_title' => 'required|string',
            'report_datas.*.report_description' => 'nullable|string',
            'report_datas.*.assessment_type' => 'required|string',
            'report_datas.*.comment' => 'nullable|string',

            'categories' => 'required|array|min:1',
            'categories.*.categorie_id' => 'sometimes|nullable|integer|exists:categories,id',
            'categories.*.main_categories' => 'required|string',
            'categories.*.sub_categories' => 'required|string',
            'categories.*.sequence' => 'required|integer|min:1',

            'categories.*.evaluation_lists' => 'sometimes|array|min:1',
            'categories.*.evaluation_lists.*.evaluation_id' => 'sometimes|nullable|integer|exists:evaluation_lists,id',
            'categories.*.evaluation_lists.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.sum_score' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.annotation' => 'nullable|string',

            'categories.*.evaluation_lists.*.quantity_main_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_main_criteria_id' => 'sometimes|nullable|integer|exists:quantity_main_criterias,id',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.tooltips' => 'nullable|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.formula' => 'nullable|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.quantity_sub_criteria_id' => 'sometimes|nullable|integer|exists:quantity_sub_criterias,id',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.score_a' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.score_b' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.require_evidence' => 'nullable|boolean',
            'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.require_subject' => 'nullable|boolean',

            'categories.*.evaluation_lists.*.quality_main_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_main_criteria_id' => 'sometimes|nullable|integer|exists:quality_main_criterias,id',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.ratio' => 'required|numeric|min:1',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.tooltips' => 'nullable|string',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.require_evidence' => 'nullable|boolean',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias' => 'sometimes|array',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.quality_sub_criteria_id' => 'sometimes|nullable|integer|exists:quality_sub_criterias,id',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.name' => 'required|string',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.sequence' => 'required|integer|min:1',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.num_score' => 'required|numeric|min:0',
            'categories.*.evaluation_lists.*.quality_main_criterias.*.quality_sub_criterias.*.description' => 'nullable|string',
        ]);

        $version = CriteriaVersion::findOrFail($id);
        $hasExistingCategories = $version->categories()->exists();
        $submittedCategoryIds = collect($validated['categories'] ?? [])
            ->pluck('categorie_id')
            ->filter()
            ->values();

        if ($hasExistingCategories && $submittedCategoryIds->isEmpty()) {
            throw ValidationException::withMessages([
                'categories' => ['ไม่พบรหัสหมวดหมู่เดิมในข้อมูลที่ส่งมา กรุณาโหลดหน้าใหม่ก่อนบันทึกเพื่อป้องกันข้อมูลซ้ำ'],
            ]);
        }

        try {
            DB::transaction(function () use ($version, $validated, $hasQuantityRequireEvidence, $hasQuantityRequireSubject, $hasQualityRequireEvidence) {
                // 1. Update Criteria Version
                $versionUpdateData = [
                    'version_name' => $validated['version_name'],
                ];

                if (array_key_exists('created_by', $validated)) {
                    $versionUpdateData['created_by'] = $validated['created_by'];
                }

                $version->update($versionUpdateData);

                $keptReportDataIds = [];
                $keptCategoryIds = [];
                $keptEvaluationIds = [];
                $keptQuantMainIds = [];
                $keptQuantSubIds = [];
                $keptQualMainIds = [];
                $keptQualSubIds = [];
                $processedQualMainIds = [];

                // 2. Update/Create Report Datas
                foreach ($validated['report_datas'] as $reportDatum) {
                    $reportDataId = $reportDatum['report_data_id'] ?? null;
                    $reportData = null;

                    if (! empty($reportDataId)) {
                        $reportData = $version->reportDatas()->where('id', $reportDataId)->first();
                    } elseif ($version->reportDatas()->count() > 0) {
                        $reportData = $version->reportDatas()->first();
                    }

                    if ($reportData) {
                        $reportData->update([
                            'report_title' => $reportDatum['report_title'],
                            'report_description' => $reportDatum['report_description'],
                            'assessment_type' => $reportDatum['assessment_type'],
                            'comment' => $reportDatum['comment'] ?? null,
                        ]);
                    } else {
                        $reportData = ReportData::create([
                            'criteria_version_id' => $version->id,
                            'report_title' => $reportDatum['report_title'],
                            'report_description' => $reportDatum['report_description'],
                            'assessment_type' => $reportDatum['assessment_type'],
                            'comment' => $reportDatum['comment'] ?? null,
                        ]);
                    }

                    $keptReportDataIds[] = $reportData->id;
                }

                // 3. Update/Create Categories and children
                foreach ($validated['categories'] as $categoryData) {
                    $categoryId = $categoryData['categorie_id'] ?? null;
                    $category = null;

                    if (! empty($categoryId)) {
                        $category = $version->categories()->where('id', $categoryId)->first();
                    }

                    if ($category) {
                        $category->update([
                            'main_categories' => $categoryData['main_categories'],
                            'sub_categories' => $categoryData['sub_categories'],
                            'sequence' => $categoryData['sequence'],
                        ]);
                    } else {
                        $category = Category::create([
                            'criteria_version_id' => $version->id,
                            'main_categories' => $categoryData['main_categories'],
                            'sub_categories' => $categoryData['sub_categories'],
                            'sequence' => $categoryData['sequence'],
                        ]);
                    }

                    $keptCategoryIds[] = $category->id;

                    if (! empty($categoryData['evaluation_lists'])) {
                        foreach ($categoryData['evaluation_lists'] as $evalListData) {
                            $evaluationId = $evalListData['evaluation_id'] ?? null;
                            $evaluationList = null;

                            if (! empty($evaluationId)) {
                                $evaluationList = $version->evaluationLists()->where('id', $evaluationId)->first();
                            }

                            if ($evaluationList) {
                                $evaluationList->update([
                                    'categorie_id' => $category->id,
                                    'name' => $evalListData['name'],
                                    'sum_score' => $evalListData['sum_score'],
                                    'sequence' => $evalListData['sequence'],
                                    'annotation' => $evalListData['annotation'] ?? null,
                                ]);
                            } else {
                                $evaluationList = EvaluationList::create([
                                    'categorie_id' => $category->id,
                                    'criteria_version_id' => $version->id,
                                    'name' => $evalListData['name'],
                                    'sum_score' => $evalListData['sum_score'],
                                    'sequence' => $evalListData['sequence'],
                                    'annotation' => $evalListData['annotation'] ?? null,
                                ]);
                            }

                            $keptEvaluationIds[] = $evaluationList->id;

                            if (! empty($evalListData['quantity_main_criterias'])) {
                                foreach ($evalListData['quantity_main_criterias'] as $qMain) {
                                    $qMainId = $qMain['quantity_main_criteria_id'] ?? null;
                                    $quantityMainCriteria = null;

                                    if (! empty($qMainId)) {
                                        $quantityMainCriteria = $version->quantityMainCriterias()->where('id', $qMainId)->first();
                                    }

                                    if ($quantityMainCriteria) {
                                        $quantityMainCriteria->update([
                                            'name' => $qMain['name'],
                                            'tooltips' => $qMain['tooltips'],
                                        ]);
                                    } else {
                                        $quantityMainCriteria = QuantityMainCriteria::create([
                                            'criteria_version_id' => $version->id,
                                            'name' => $qMain['name'],
                                            'tooltips' => $qMain['tooltips'],
                                        ]);
                                    }

                                    $keptQuantMainIds[] = $quantityMainCriteria->id;

                                    if (! empty($qMain['formula'])) {
                                        DB::table('formulas')
                                            ->where('quantity_main_criteria_id', $quantityMainCriteria->id)
                                            ->delete();

                                        \App\Models\Formula::create([
                                            'condition' => $qMain['formula'],
                                            'quantity_main_criteria_id' => $quantityMainCriteria->id,
                                        ]);
                                    } else {
                                        DB::table('formulas')
                                            ->where('quantity_main_criteria_id', $quantityMainCriteria->id)
                                            ->delete();
                                    }

                                    if (! empty($qMain['quantity_sub_criterias'])) {
                                        foreach ($qMain['quantity_sub_criterias'] as $qSub) {
                                            $qSubId = $qSub['quantity_sub_criteria_id'] ?? null;
                                            $quantitySubCriteria = null;

                                        if (! empty($qSubId)) {
                                            $quantitySubCriteria = QuantitySubCriteria::where('id', $qSubId)
                                                ->where('criteria_version_id', $version->id)
                                                ->first();
                                        }

                                        if ($quantitySubCriteria) {
                                            $quantitySubCriteria->update([
                                                'quantity_main_criteria_id' => $quantityMainCriteria->id,
                                                'evaluation_list_id' => $evaluationList->id,
                                                'name' => $qSub['name'],
                                                    'sequence' => $qSub['sequence'],
                                                    'score_a' => $qSub['score_a'],
                                                    'score_b' => $qSub['score_b'],
                                                    ...($hasQuantityRequireEvidence ? ['require_evidence' => (bool) ($qSub['require_evidence'] ?? false)] : []),
                                                    ...($hasQuantityRequireSubject ? ['require_subject' => (bool) ($qSub['require_subject'] ?? false)] : []),
                                                ]);
                                            } else {
                                                $quantitySubCriteria = QuantitySubCriteria::create([
                                                    'criteria_version_id' => $version->id,
                                                    'quantity_main_criteria_id' => $quantityMainCriteria->id,
                                                    'evaluation_list_id' => $evaluationList->id,
                                                    'name' => $qSub['name'],
                                                    'sequence' => $qSub['sequence'],
                                                    'score_a' => $qSub['score_a'],
                                                    'score_b' => $qSub['score_b'],
                                                    ...($hasQuantityRequireEvidence ? ['require_evidence' => (bool) ($qSub['require_evidence'] ?? false)] : []),
                                                    ...($hasQuantityRequireSubject ? ['require_subject' => (bool) ($qSub['require_subject'] ?? false)] : []),
                                                ]);
                                            }

                                            $keptQuantSubIds[] = $quantitySubCriteria->id;
                                        }
                                    }
                                }
                            }

                            if (! empty($evalListData['quality_main_criterias'])) {
                                foreach ($evalListData['quality_main_criterias'] as $qlMain) {
                                    $qlMainId = $qlMain['quality_main_criteria_id'] ?? null;
                                    $qualityMainCriteria = null;

                                    if (! empty($qlMainId)) {
                                        $qualityMainCriteria = $version->qualityMainCriterias()->where('id', $qlMainId)->first();
                                    }

                                    if ($qualityMainCriteria) {
                                        if (! in_array($qualityMainCriteria->id, $processedQualMainIds, true)) {
                                            $qualityMainCriteria->update([
                                                'name' => $qlMain['name'],
                                                'ratio' => $qlMain['ratio'],
                                                'tooltips' => $qlMain['tooltips'],
                                                'sequence' => $qlMain['sequence'],
                                                ...($hasQualityRequireEvidence ? ['require_evidence' => (bool) ($qlMain['require_evidence'] ?? false)] : []),
                                            ]);
                                            $processedQualMainIds[] = $qualityMainCriteria->id;
                                        }
                                    } else {
                                        $qualityMainCriteria = QualityMainCriteria::create([
                                            'criteria_version_id' => $version->id,
                                            'name' => $qlMain['name'],
                                            'ratio' => $qlMain['ratio'],
                                            'tooltips' => $qlMain['tooltips'],
                                            'sequence' => $qlMain['sequence'],
                                            ...($hasQualityRequireEvidence ? ['require_evidence' => (bool) ($qlMain['require_evidence'] ?? false)] : []),
                                        ]);
                                        $processedQualMainIds[] = $qualityMainCriteria->id;
                                    }

                                    $keptQualMainIds[] = $qualityMainCriteria->id;

                                    if (! empty($qlMain['quality_sub_criterias'])) {
                                        foreach ($qlMain['quality_sub_criterias'] as $qlSub) {
                                            $qlSubId = $qlSub['quality_sub_criteria_id'] ?? null;
                                            $qualitySubCriteria = null;

                                        if (! empty($qlSubId)) {
                                            $qualitySubCriteria = QualitySubCriteria::where('id', $qlSubId)
                                                ->where('criteria_version_id', $version->id)
                                                ->first();
                                        }

                                        if ($qualitySubCriteria) {
                                            $qualitySubCriteria->update([
                                                'quality_main_criteria_id' => $qualityMainCriteria->id,
                                                'evaluation_list_id' => $evaluationList->id,
                                                'name' => $qlSub['name'],
                                                    'sequence' => $qlSub['sequence'],
                                                    'num_score' => $qlSub['num_score'],
                                                    'description' => $qlSub['description'] ?? null,
                                                ]);
                                            } else {
                                                $qualitySubCriteria = QualitySubCriteria::create([
                                                    'quality_main_criteria_id' => $qualityMainCriteria->id,
                                                    'criteria_version_id' => $version->id,
                                                    'evaluation_list_id' => $evaluationList->id,
                                                    'name' => $qlSub['name'],
                                                    'sequence' => $qlSub['sequence'],
                                                    'num_score' => $qlSub['num_score'],
                                                    'description' => $qlSub['description'] ?? null,
                                                ]);
                                            }

                                            $keptQualSubIds[] = $qualitySubCriteria->id;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // 4. Protect quantity sub criteria that are referenced by workload_forms
                $protectedQuantSubIds = DB::table('workload_forms')
                    ->whereNotNull('quantity_sub_criteria_id')
                    ->pluck('quantity_sub_criteria_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $protectedQuantMainIds = [];
                $protectedEvaluationIds = [];
                $protectedCategoryIds = [];

                if (! empty($protectedQuantSubIds)) {
                    $protectedQuantMainIds = QuantitySubCriteria::whereIn('id', $protectedQuantSubIds)
                        ->pluck('quantity_main_criteria_id')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    $protectedEvaluationIds = QuantitySubCriteria::whereIn('id', $protectedQuantSubIds)
                        ->pluck('evaluation_list_id')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    $protectedCategoryIds = EvaluationList::whereIn('id', $protectedEvaluationIds)
                        ->pluck('categorie_id')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                }

                // 5. Delete removed children (never delete protected quantity sub criterias)
                QuantitySubCriteria::where('criteria_version_id', $version->id)
                    ->when(! empty($keptQuantSubIds), function ($query) use ($keptQuantSubIds) {
                        $query->whereNotIn('id', $keptQuantSubIds);
                    })
                    ->when(! empty($protectedQuantSubIds), function ($query) use ($protectedQuantSubIds) {
                        $query->whereNotIn('id', $protectedQuantSubIds);
                    })
                    ->when(empty($keptQuantSubIds), function ($query) {
                        $query->whereNotNull('id');
                    })
                    ->delete();

                QualitySubCriteria::where('criteria_version_id', $version->id)
                    ->when(! empty($keptQualSubIds), function ($query) use ($keptQualSubIds) {
                        $query->whereNotIn('id', $keptQualSubIds);
                    })
                    ->when(empty($keptQualSubIds), function ($query) {
                        $query->whereNotNull('id');
                    })
                    ->delete();

                // 6. Delete removed main criterias (skip those that have protected subs)
                $version->quantityMainCriterias()
                    ->when(! empty($keptQuantMainIds), function ($query) use ($keptQuantMainIds) {
                        $query->whereNotIn('id', $keptQuantMainIds);
                    })
                    ->when(! empty($protectedQuantMainIds), function ($query) use ($protectedQuantMainIds) {
                        $query->whereNotIn('id', $protectedQuantMainIds);
                    })
                    ->when(empty($keptQuantMainIds), function ($query) {
                        $query->whereNotNull('id');
                    })
                    ->delete();

                $version->qualityMainCriterias()
                    ->when(! empty($keptQualMainIds), function ($query) use ($keptQualMainIds) {
                        $query->whereNotIn('id', $keptQualMainIds);
                    })
                    ->when(empty($keptQualMainIds), function ($query) {
                        $query->whereNotNull('id');
                    })
                    ->delete();

                // 7. Delete removed evaluation lists, categories, report datas
                $version->evaluationLists()
                    ->when(! empty($keptEvaluationIds), function ($query) use ($keptEvaluationIds) {
                        $query->whereNotIn('id', $keptEvaluationIds);
                    })
                    ->when(! empty($protectedEvaluationIds), function ($query) use ($protectedEvaluationIds) {
                        $query->whereNotIn('id', $protectedEvaluationIds);
                    })
                    ->when(empty($keptEvaluationIds), function ($query) {
                        $query->whereNotNull('id');
                    })
                    ->delete();

                $version->categories()
                    ->when(! empty($keptCategoryIds), function ($query) use ($keptCategoryIds) {
                        $query->whereNotIn('id', $keptCategoryIds);
                    })
                    ->when(! empty($protectedCategoryIds), function ($query) use ($protectedCategoryIds) {
                        $query->whereNotIn('id', $protectedCategoryIds);
                    })
                    ->when(empty($keptCategoryIds), function ($query) {
                        $query->whereNotNull('id');
                    })
                    ->delete();

                $version->reportDatas()
                    ->when(! empty($keptReportDataIds), function ($query) use ($keptReportDataIds) {
                        $query->whereNotIn('id', $keptReportDataIds);
                    })
                    ->when(empty($keptReportDataIds), function ($query) {
                        $query->whereNotNull('id');
                    })
                    ->delete();
            });

            $freshVersion = CriteriaVersion::with(['reportDatas', 'categories.evaluationLists'])->find($version->id);

            Log::info('Report structure updated successfully', [
                'criteria_version_id' => $version->id,
                'version_name' => $freshVersion?->version_name,
                'report_title' => optional($freshVersion?->reportDatas?->first())->report_title,
                'report_description' => optional($freshVersion?->reportDatas?->first())->report_description,
                'assessment_type' => optional($freshVersion?->reportDatas?->first())->assessment_type,
                'categories_count' => $freshVersion?->categories?->count(),
            ]);

            return $this->jsonNoStore([
                'success' => true,
                'message' => 'Criteria version updated successfully',
                'data' => new CriteriaVersionResource($version->fresh()),
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation error in update: '.json_encode($e->errors()));

            return $this->jsonNoStore([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Server error in update: '.$e->getMessage(), ['exception' => $e]);
            DB::rollBack();

            return $this->jsonNoStore([
                'success' => false,
                'message' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    // Delete (DELETE)
    /**
     * เมธอด: destroy
     * จุดประสงค์: ลบข้อมูล ส่งข้อมูลแบบ JSON
     * อินพุต: ตัวระบุ ($id)
     * เอาต์พุต: ข้อมูล JSON
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
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
    private function copyWorkloadFromSource(int $sourceVersionId, int $newVersionId): void
    {
        if ($sourceVersionId === $newVersionId) {
            return;
        }

        $sourceRows = DB::table('quantity_sub_criterias as qs')
            ->join('evaluation_lists as el', 'qs.evaluation_list_id', '=', 'el.id')
            ->join('categories as c', 'el.categorie_id', '=', 'c.id')
            ->join('quantity_main_criterias as qm', 'qs.quantity_main_criteria_id', '=', 'qm.id')
            ->where('qs.criteria_version_id', $sourceVersionId)
            ->select([
                'qs.id as sub_id',
                'qs.name as sub_name',
                'qs.sequence as sub_sequence',
                'el.sequence as eval_sequence',
                'c.sequence as category_sequence',
                'qm.name as main_name',
            ])
            ->get();

        if ($sourceRows->isEmpty()) {
            return;
        }

        $newRows = DB::table('quantity_sub_criterias as qs')
            ->join('evaluation_lists as el', 'qs.evaluation_list_id', '=', 'el.id')
            ->join('categories as c', 'el.categorie_id', '=', 'c.id')
            ->join('quantity_main_criterias as qm', 'qs.quantity_main_criteria_id', '=', 'qm.id')
            ->where('qs.criteria_version_id', $newVersionId)
            ->select([
                'qs.id as sub_id',
                'qs.name as sub_name',
                'qs.sequence as sub_sequence',
                'el.sequence as eval_sequence',
                'c.sequence as category_sequence',
                'qm.name as main_name',
            ])
            ->get();

        $makeKey = function ($row) {
            return trim((string) $row->category_sequence).'|'.
                trim((string) $row->eval_sequence).'|'.
                trim((string) $row->main_name).'|'.
                trim((string) $row->sub_sequence).'|'.
                trim((string) $row->sub_name);
        };

        $newMap = [];
        foreach ($newRows as $row) {
            $newMap[$makeKey($row)] = (int) $row->sub_id;
        }

        foreach ($sourceRows as $row) {
            $key = $makeKey($row);
            if (! isset($newMap[$key])) {
                continue;
            }

            $oldSubId = (int) $row->sub_id;
            $newSubId = (int) $newMap[$key];
            $newSub = QuantitySubCriteria::find($newSubId);
            if (! $newSub) {
                continue;
            }

            $groups = QuantitySubCriteriaGroup::where('quantity_sub_criteria_id', $oldSubId)
                ->orderBy('sequence')
                ->get();

            foreach ($groups as $group) {
                $newGroup = QuantitySubCriteriaGroup::create([
                    'name' => $group->name,
                    'sequence' => $group->sequence,
                    'quantity_sub_criteria_id' => $newSubId,
                    'criteria_version_id' => $newVersionId,
                    'evaluation_list_id' => $newSub->evaluation_list_id,
                ]);

                $items = QuantitySubCriteriaItem::where('quantity_sub_criteria_group_id', $group->id)
                    ->orderBy('sequence')
                    ->get();

                foreach ($items as $item) {
                    $newItem = QuantitySubCriteriaItem::create([
                        'name' => $item->name,
                        'sequence' => $item->sequence,
                        'score_a' => $item->score_a,
                        'score_b' => $item->score_b,
                        'description' => $item->description,
                        'quantity_sub_criteria_group_id' => $newGroup->id,
                        'criteria_version_id' => $newVersionId,
                        'evaluation_list_id' => $newSub->evaluation_list_id,
                    ]);

                    $oldForm = WorkloadForm::with(['fields', 'items'])
                        ->where('quantity_sub_criteria_item_id', $item->id)
                        ->first();

                    if (! $oldForm) {
                        continue;
                    }

                    $newForm = WorkloadForm::create([
                        'formula_logic' => $oldForm->formula_logic,
                        'quantity_sub_criteria_id' => $newSubId,
                        'quantity_sub_criteria_item_id' => $newItem->id,
                    ]);

                    foreach ($oldForm->fields as $field) {
                        WorkloadFormField::create([
                            'label' => $field->label,
                            'note' => $field->note,
                            'default_value' => $field->default_value,
                            'variable_name' => $field->variable_name,
                            'field_type' => $field->field_type,
                            'workload_form_id' => $newForm->id,
                        ]);
                    }

                    foreach ($oldForm->items as $formItem) {
                        WorkloadFormItem::create([
                            'label' => $formItem->label,
                            'score' => $formItem->score,
                            'sequence' => $formItem->sequence,
                            'workload_form_id' => $newForm->id,
                        ]);
                    }
                }
            }
        }
    }
}
