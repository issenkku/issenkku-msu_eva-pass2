<?php
/**
 * ไฟล์คอนโทรลเลอร์: app/Http/Controllers\Workload\WorkloadEntryController.php
 */

namespace App\Http\Controllers\Workload;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workload\StoreWorkloadEntryRequest;
use App\Http\Requests\Workload\UpdateWorkloadEntryRequest;
use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;
use App\Services\WorkloadFormulaEvaluator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class WorkloadEntryController extends Controller
{
    /**
     * เมธอด: index
     * จุดประสงค์: ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ข้อมูล JSON
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function index(Request $request)
    {
        $query = WorkloadEntry::query();

        if ($request->filled('report_id')) {
            $query->where('report_id', $request->input('report_id'));
        }

        if ($request->filled('workload_form_id')) {
            $query->where('workload_form_id', $request->input('workload_form_id'));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        return response()->json($query->get());
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
            return response()->json(WorkloadEntry::findOrFail($id));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload entry not found'], 404);
        }
    }

    /**
     * เมธอด: store
     * จุดประสงค์: บันทึกข้อมูล WorkloadEntry ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ข้อมูล JSON
     * @param StoreWorkloadEntryRequest $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function store(StoreWorkloadEntryRequest $request)
    {
        $validated = $request->validated();
        $form = WorkloadForm::with(['fields', 'items'])->findOrFail($validated['workload_form_id']);
        $fieldValues = $this->resolveItemFieldValues($form, (array) ($validated['field_values'] ?? []));
        $calculatedScore = app(WorkloadFormulaEvaluator::class)
            ->evaluate($form->formula_logic, $form->fields, $fieldValues);

        $validated['calculated_score'] = $calculatedScore;
        $validated['field_values'] = $fieldValues;
        $entry = WorkloadEntry::create($validated);

        return response()->json($entry, 201);
    }

    /**
     * เมธอด: update
     * จุดประสงค์: อัปเดตข้อมูล ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ, ตัวระบุ ($id)
     * เอาต์พุต: ข้อมูล JSON
     * @param UpdateWorkloadEntryRequest $request ค่าที่รับเข้ามา
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function update(UpdateWorkloadEntryRequest $request, $id)
    {
        try {
            $entry = WorkloadEntry::findOrFail($id);
            $validated = $request->validated();
            $workloadFormId = $validated['workload_form_id'] ?? $entry->workload_form_id;
            $fieldValues = $validated['field_values'] ?? $entry->field_values ?? [];

            $form = WorkloadForm::with(['fields', 'items'])->findOrFail($workloadFormId);
            $fieldValues = $this->resolveItemFieldValues($form, (array) $fieldValues);
            $calculatedScore = app(WorkloadFormulaEvaluator::class)
                ->evaluate($form->formula_logic, $form->fields, $fieldValues);

            $validated['calculated_score'] = $calculatedScore;
            $validated['field_values'] = $fieldValues;
            $entry->update($validated);

            return response()->json($entry);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload entry not found'], 404);
        }
    }

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
        try {
            $entry = WorkloadEntry::findOrFail($id);
            $entry->delete();

            return response()->json(['message' => 'Workload entry deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload entry not found'], 404);
        }
    }

    private function resolveItemFieldValues(WorkloadForm $form, array $fieldValues): array
    {
        $normalized = [];
        foreach ($fieldValues as $key => $value) {
            $normalized[strtolower((string) $key)] = $value;
        }

        $itemScores = [];
        foreach ($form->items ?? [] as $item) {
            $sequence = (int) ($item->sequence ?? 0);
            if ($sequence > 0) {
                $itemScores[$sequence] = $item->score;
            }
        }

        foreach ($form->fields as $field) {
            $type = strtolower((string) ($field->field_type ?? 'number'));
            if ($type !== 'item') {
                continue;
            }
            $name = strtolower((string) $field->variable_name);
            if ($name === '' || array_key_exists($name, $normalized)) {
                continue;
            }
            if (preg_match('/^item_(\d+)$/i', $name, $matches)) {
                $sequence = (int) $matches[1];
                if (array_key_exists($sequence, $itemScores)) {
                    $normalized[$name] = $itemScores[$sequence];
                }
            }
        }

        if (!array_key_exists('item_*', $normalized) && !array_key_exists('item_star', $normalized)) {
            foreach ($normalized as $key => $value) {
                if (preg_match('/^item_\d+$/', $key)) {
                    $normalized['item_*'] = $value;
                    $normalized['item_star'] = $value;
                    break;
                }
            }
        }

        return $normalized;
    }
}
