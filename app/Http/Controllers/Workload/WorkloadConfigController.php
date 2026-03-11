<?php

namespace App\Http\Controllers\Workload;


use App\Http\Controllers\Controller;
use App\Models\WorkloadForm;
use App\Models\WorkloadFormField;
use App\Models\WorkloadFormItem;
use App\Models\QuantitySubCriteria;
use App\Models\QuantitySubCriteriaGroup;
use App\Models\QuantitySubCriteriaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkloadConfigController extends Controller
{
    /**
     * เมธอด: index
     * จุดประสงค์: แสดงหน้า workload.app
     * อินพุต: ไม่มี
     * เอาต์พุต: หน้า workload.app
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function index()
    {
        return view('workload.app');
    }

    /**
     * เมธอด: quantitySubCriteriaNav
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ข้อมูล JSON
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function quantitySubCriteriaNav(Request $request)
    {
        $request->validate([
            'quant_sub_criteria_id' => ['required', 'integer', 'exists:quantity_sub_criterias,id'],
        ]);

        $active = QuantitySubCriteria::with('mainCriteria')->findOrFail($request->input('quant_sub_criteria_id'));

        $items = QuantitySubCriteria::where('evaluation_list_id', $active->evaluation_list_id)
            ->orderBy('sequence')
            ->get(['id', 'name', 'sequence', 'quantity_main_criteria_id', 'evaluation_list_id']);

        return response()->json([
            'active' => [
                'id' => $active->id,
                'name' => $active->name,
                'sequence' => $active->sequence,
                'evaluation_list_id' => $active->evaluation_list_id,
                'quantity_main_criteria_id' => $active->quantity_main_criteria_id,
                'criteria_version_id' => $active->criteria_version_id,
                'main_criteria_name' => optional($active->mainCriteria)->name,
            ],
            'items' => $items,
        ]);
    }

    /**
     * เมธอด: subBlocks
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ข้อมูล JSON
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function subBlocks(Request $request)
    {
        $request->validate([
            'quant_sub_criteria_id' => ['required', 'integer', 'exists:quantity_sub_criterias,id'],
        ]);

        $active = QuantitySubCriteria::findOrFail($request->input('quant_sub_criteria_id'));

        $groups = QuantitySubCriteriaGroup::where('quantity_sub_criteria_id', $active->id)
            ->orderBy('sequence')
            ->get();

        if ($groups->isEmpty()) {
            $defaultGroupName = optional($active->mainCriteria)->name ?: $active->name;

            return response()->json([
                'groups' => [
                    [
                        'group' => [
                            'id' => null,
                            'name' => $defaultGroupName,
                            'sequence' => 1,
                        ],
                        'items' => [
                            [
                                'item' => [
                                    'id' => null,
                                    'name' => $active->name,
                                    'sequence' => 1,
                                ],
                                'form' => null,
                            ],
                        ],
                    ],
                ],
            ]);
        }

        $blocks = $groups->map(function ($group) {
            $items = QuantitySubCriteriaItem::where('quantity_sub_criteria_group_id', $group->id)
                ->orderBy('sequence')
                ->get();

            $itemBlocks = $items->map(function ($item) {
                $form = WorkloadForm::with(['fields', 'items'])
                    ->where('quantity_sub_criteria_item_id', $item->id)
                    ->first();

                return [
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'sequence' => $item->sequence,
                    ],
                    'form' => $form ? [
                        'id' => $form->id,
                        'formula_logic' => $form->formula_logic,
                        'fields' => $form->fields,
                        'items' => $form->items,
                    ] : null,
                ];
            });

            return [
                'group' => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'sequence' => $group->sequence,
                ],
                'items' => $itemBlocks,
            ];
        });

        return response()->json([
            'groups' => $blocks,
        ]);
    }

    /**
     * เมธอด: save
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ บันทึกข้อมูล QuantitySubCriteriaGroup, QuantitySubCriteriaItem, WorkloadFormField, WorkloadFormItem อัปเดตข้อมูล ลบข้อมูล ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ข้อมูล JSON
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'quant_sub_criteria_id' => ['required', 'integer', 'exists:quantity_sub_criterias,id'],
            'groups' => ['required', 'array', 'min:1'],
            'groups.*.id' => ['nullable', 'integer'],
            'groups.*.group_name' => ['required', 'string', 'max:255'],
            'groups.*.sequence' => ['required', 'integer', 'min:1'],
            'groups.*.items' => ['required', 'array', 'min:1'],
            'groups.*.items.*.id' => ['nullable', 'integer'],
            'groups.*.items.*.item_name' => ['required', 'string', 'max:255'],
            'groups.*.items.*.sequence' => ['required', 'integer', 'min:1'],
            'groups.*.items.*.formula_logic' => ['nullable', 'string'],
            'groups.*.items.*.fields' => ['nullable', 'array'],
            'groups.*.items.*.fields.*.label' => ['required', 'string', 'max:255'],
            'groups.*.items.*.fields.*.variable_name' => ['required', 'string', 'max:255'],
            'groups.*.items.*.fields.*.field_type' => ['required', 'string', 'max:255'],
            'groups.*.items.*.form_items' => ['nullable', 'array'],
            'groups.*.items.*.form_items.*.label' => ['required', 'string', 'max:255'],
            'groups.*.items.*.form_items.*.score' => ['nullable', 'integer', 'min:0'],
            'groups.*.items.*.form_items.*.sequence' => ['required', 'integer', 'min:1'],
        ]);

        $subCriteria = QuantitySubCriteria::with('mainCriteria')->findOrFail($validated['quant_sub_criteria_id']);

        DB::transaction(function () use ($validated, $subCriteria) {
            $payloadGroupIds = [];
            foreach ($validated['groups'] as $group) {
                if (!empty($group['id'])) {
                    $currentGroup = QuantitySubCriteriaGroup::findOrFail($group['id']);
                } else {
                    $currentGroup = QuantitySubCriteriaGroup::create([
                        'name' => $group['group_name'],
                        'sequence' => $group['sequence'],
                        'quantity_sub_criteria_id' => $subCriteria->id,
                        'criteria_version_id' => $subCriteria->criteria_version_id,
                        'evaluation_list_id' => $subCriteria->evaluation_list_id,
                    ]);
                }

                $currentGroup->update([
                    'name' => $group['group_name'],
                    'sequence' => $group['sequence'],
                ]);

                $payloadGroupIds[] = $currentGroup->id;
                $payloadItemIds = [];
                foreach ($group['items'] as $itemBlock) {
                    $fields = $itemBlock['fields'] ?? [];
                    $formulaLogic = trim((string) ($itemBlock['formula_logic'] ?? ''));
                    if ($formulaLogic !== '') {
                        $this->validateFormulaLogic($formulaLogic, $fields);
                    }

                    if (!empty($itemBlock['id'])) {
                        $currentItem = QuantitySubCriteriaItem::findOrFail($itemBlock['id']);
                    } else {
                        $currentItem = QuantitySubCriteriaItem::create([
                            'name' => $itemBlock['item_name'],
                            'sequence' => $itemBlock['sequence'],
                            'quantity_sub_criteria_group_id' => $currentGroup->id,
                            'criteria_version_id' => $subCriteria->criteria_version_id,
                            'evaluation_list_id' => $subCriteria->evaluation_list_id,
                            'score_a' => 0,
                            'score_b' => 0,
                        ]);
                    }

                    $currentItem->update([
                        'name' => $itemBlock['item_name'],
                        'sequence' => $itemBlock['sequence'],
                        'quantity_sub_criteria_group_id' => $currentGroup->id,
                    ]);

                    $payloadItemIds[] = $currentItem->id;
                    $form = WorkloadForm::firstOrCreate(
                        ['quantity_sub_criteria_item_id' => $currentItem->id],
                        [
                            'formula_logic' => $formulaLogic,
                            'quantity_sub_criteria_id' => $subCriteria->id,
                        ]
                    );

                    $form->update([
                        'formula_logic' => $formulaLogic,
                        'quantity_sub_criteria_id' => $subCriteria->id,
                    ]);

                    WorkloadFormField::where('workload_form_id', $form->id)->delete();
                    WorkloadFormItem::where('workload_form_id', $form->id)->delete();

                    foreach ($itemBlock['fields'] ?? [] as $field) {
                        WorkloadFormField::create([
                            'label' => $field['label'],
                            'variable_name' => $field['variable_name'],
                            'field_type' => $field['field_type'],
                            'workload_form_id' => $form->id,
                        ]);
                    }

                    foreach ($itemBlock['form_items'] ?? [] as $entry) {
                        WorkloadFormItem::create([
                            'label' => $entry['label'],
                            'score' => array_key_exists('score', $entry) && $entry['score'] !== null
                                ? (int) $entry['score']
                                : null,
                            'sequence' => $entry['sequence'],
                            'workload_form_id' => $form->id,
                        ]);
                    }
                }

                $existingItemIds = QuantitySubCriteriaItem::where('quantity_sub_criteria_group_id', $currentGroup->id)
                    ->pluck('id')
                    ->all();
                $deleteItemIds = array_diff($existingItemIds, $payloadItemIds);
                if (!empty($deleteItemIds)) {
                    QuantitySubCriteriaItem::whereIn('id', $deleteItemIds)->delete();
                }
            }

            $existingGroupIds = QuantitySubCriteriaGroup::where('quantity_sub_criteria_id', $subCriteria->id)
                ->pluck('id')
                ->all();
            $deleteGroupIds = array_diff($existingGroupIds, $payloadGroupIds);
            if (!empty($deleteGroupIds)) {
                QuantitySubCriteriaGroup::whereIn('id', $deleteGroupIds)->delete();
            }
        });

        return response()->json(['success' => true]);
    }

    protected function validateFormulaLogic(string $formula, array $fields): void
    {
        $allowedFunctions = [
            'if', 'and', 'or', 'not', 'xor', 'xnor', 'nand', 'nor', 'true', 'false',
        ];

        $formula = preg_replace('/(?<![A-Za-z0-9_])item_\\*(?![A-Za-z0-9_])/i', 'item_star', $formula);

        $variables = array_map(
            static fn ($field) => strtolower($field['variable_name'] ?? ''),
            $fields
        );
        $textVariables = array_map(
            static fn ($field) => strtolower($field['variable_name'] ?? ''),
            array_filter($fields, static fn ($field) => strtolower($field['field_type'] ?? '') === 'text')
        );

        preg_match_all('/[A-Za-z_][A-Za-z0-9_]*/', $formula, $matches);
        $tokens = array_unique(array_map('strtolower', $matches[0] ?? []));

        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }
            if (in_array($token, $allowedFunctions, true)) {
                continue;
            }
            if ($token === 'item_star') {
                continue;
            }
            if (!in_array($token, $variables, true)) {
                throw ValidationException::withMessages([
                    'formula_logic' => ["สูตรมีตัวแปรที่ไม่รู้จัก: {$token}"],
                ]);
            }
            if (in_array($token, $textVariables, true)) {
                throw ValidationException::withMessages([
                    'formula_logic' => ["สูตรห้ามใช้ตัวแปรชนิดข้อความ: {$token}"],
                ]);
            }
        }

        $balance = 0;
        foreach (str_split($formula) as $char) {
            if ($char === '(') {
                $balance++;
            } elseif ($char === ')') {
                $balance--;
            }
            if ($balance < 0) {
                throw ValidationException::withMessages([
                    'formula_logic' => ['สูตรมีวงเล็บไม่ถูกต้อง'],
                ]);
            }
        }
        if ($balance !== 0) {
            throw ValidationException::withMessages([
                'formula_logic' => ['สูตรมีวงเล็บไม่ครบ'],
            ]);
        }
    }
}
