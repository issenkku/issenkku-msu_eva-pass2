<?php

namespace App\Http\Controllers\Workload;


use App\Http\Controllers\Controller;
use App\Http\Requests\Workload\StoreWorkloadEntryRequest;
use App\Http\Requests\Workload\UpdateWorkloadEntryRequest;
use App\Models\Subject;
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
        $fieldValues = $this->mergeSubjectCreditFieldValues(
            $form,
            (array) ($validated['field_values'] ?? []),
            isset($validated['subject_id']) ? (int) $validated['subject_id'] : null
        );
        $fieldValues = $this->resolveItemFieldValues($form, $fieldValues);
        $calculatedScore = app(WorkloadFormulaEvaluator::class)
            ->evaluate($form->formula_logic, $form->fields, $fieldValues);
        $calculatedScore = max(0, $calculatedScore);

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
            $fieldValues = $this->mergeSubjectCreditFieldValues(
                $form,
                (array) $fieldValues,
                array_key_exists('subject_id', $validated)
                    ? ($validated['subject_id'] !== null ? (int) $validated['subject_id'] : null)
                    : ($entry->subject_id !== null ? (int) $entry->subject_id : null)
            );
            $fieldValues = $this->resolveItemFieldValues($form, (array) $fieldValues);
            $calculatedScore = app(WorkloadFormulaEvaluator::class)
                ->evaluate($form->formula_logic, $form->fields, $fieldValues);
            $calculatedScore = max(0, $calculatedScore);

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

    private function mergeSubjectCreditFieldValues(WorkloadForm $form, array $fieldValues, ?int $subjectId): array
    {
        $normalized = [];
        foreach ($fieldValues as $key => $value) {
            $normalized[strtolower((string) $key)] = $value;
        }

        if (! $subjectId) {
            return $normalized;
        }

        $subject = Subject::find($subjectId);
        if (! $subject) {
            return $normalized;
        }

        $creditType = $this->resolvePreferredCreditType($form);
        $lectureCredits = (float) ($subject->lecture_credits ?? 0);
        $labCredits = (float) ($subject->lab_credits ?? 0);
        $selfStudyCredits = (float) ($subject->self_study_credits ?? 0);

        $normalized['credits'] = match ($creditType) {
            'lecture_credits' => $lectureCredits,
            'lab_credits' => $labCredits,
            default => (float) ($subject->credits ?? 0),
        };
        $normalized['lecture_credits'] = $lectureCredits;
        $normalized['lab_credits'] = $labCredits;
        $normalized['self_study_credits'] = $selfStudyCredits;

        return $normalized;
    }

    private function resolvePreferredCreditType(WorkloadForm $form): string
    {
        $form->loadMissing(['fields', 'subCriteriaItem.group', 'quantitySubCriteria']);

        $context = collect([
            optional($form->subCriteriaItem)->name,
            optional(optional($form->subCriteriaItem)->group)->name,
            optional($form->quantitySubCriteria)->name,
        ])->filter()->implode(' ');

        foreach ($form->fields as $field) {
            $name = strtolower(trim((string) $field->variable_name));
            if ($name === 'lecture_credits') {
                return 'lecture_credits';
            }
            if ($name === 'lab_credits') {
                return 'lab_credits';
            }

            $context .= ' ' . trim((string) ($field->label ?? ''));
            $context .= ' ' . trim((string) ($field->note ?? ''));
        }

        $normalizedContext = mb_strtolower($context);
        if (str_contains($normalizedContext, 'ปฏิบัติ') || str_contains($normalizedContext, 'lab')) {
            return 'lab_credits';
        }
        if (str_contains($normalizedContext, 'บรรยาย') || str_contains($normalizedContext, 'lecture')) {
            return 'lecture_credits';
        }

        return 'credits';
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
