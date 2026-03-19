<?php

namespace App\Http\Controllers\Evaluatee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workload\StoreWorkloadEntryRequest;
use App\Http\Requests\Workload\UpdateWorkloadEntryRequest;
use App\Models\EvidenceAnswer;
use App\Models\QuantitySubCriteria;
use App\Models\Reports;
use App\Models\Subject;
use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;
use App\Models\WorkloadFormItem;
use App\Services\WorkloadFormulaEvaluator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;

class EvaluateeWorkloadEntryController extends Controller
{
    private array $editableStatuses = ['Draft', 'Assigned'];

    public function store(StoreWorkloadEntryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->ensureReportEditable((int) $validated['report_id']);

        if (empty($validated['workload_form_id']) && $request->filled('workload_form_item_id')) {
            $item = WorkloadFormItem::findOrFail($request->input('workload_form_item_id'));
            $validated['workload_form_id'] = $item->workload_form_id;
        }

        $form = WorkloadForm::with(['fields', 'items'])->findOrFail($validated['workload_form_id']);
        $fieldValues = (array) ($validated['field_values'] ?? []);
        $evidenceLinks = $request->input('evidence_links', []);
        $evaluationListId = $this->resolveEvaluationListId($form);

        if ($request->filled('workload_form_item_id')) {
            $item = WorkloadFormItem::findOrFail($request->input('workload_form_item_id'));
            $variableName = $this->resolveItemVariableName($form, (int) $item->sequence);
            if ($variableName !== '') {
                $fieldValues[$variableName] = $item->score;
            }
            if (! array_key_exists('item_*', $fieldValues) && ! array_key_exists('item_star', $fieldValues)) {
                $fieldValues['item_*'] = $item->score;
            }
        }

        $fieldValues = $this->mergeSubjectCreditFieldValues(
            $fieldValues,
            isset($validated['subject_id']) ? (int) $validated['subject_id'] : null
        );
        $fieldValues = $this->resolveItemFieldValues($form, $fieldValues);
        $calculatedScore = app(WorkloadFormulaEvaluator::class)
            ->evaluate($form->formula_logic, $form->fields, $fieldValues);

        $validated['calculated_score'] = $calculatedScore;
        $validated['field_values'] = $fieldValues;
        $entry = WorkloadEntry::create($validated);

        if ($evaluationListId) {
            foreach ((array) $evidenceLinks as $link) {
                $link = trim((string) $link);
                if ($link === '') {
                    continue;
                }
                EvidenceAnswer::create([
                    'evaluation_list_id' => $evaluationListId,
                    'report_id' => $validated['report_id'],
                    'workload_entry_id' => $entry->id,
                    'link' => $link,
                ]);
            }
        }

        return redirect()
            ->back()
            ->with('success', 'บันทึกข้อมูลภาระงานเรียบร้อยแล้ว');
    }

    public function update(UpdateWorkloadEntryRequest $request, $id): RedirectResponse
    {
        try {
            $entry = WorkloadEntry::findOrFail($id);
            $this->ensureReportEditable((int) $entry->report_id);

            $validated = $request->validated();
            $workloadFormId = $validated['workload_form_id'] ?? $entry->workload_form_id;
            $fieldValues = $validated['field_values'] ?? $entry->field_values ?? [];
            $evidenceLinks = $request->input('evidence_links', []);

            if ($request->filled('workload_form_item_id')) {
                $item = WorkloadFormItem::findOrFail($request->input('workload_form_item_id'));
                $workloadFormId = $item->workload_form_id;
                $variableName = $this->resolveItemVariableName(
                    WorkloadForm::with(['fields'])->findOrFail($workloadFormId),
                    (int) $item->sequence
                );
                if ($variableName !== '') {
                    $fieldValues[$variableName] = $item->score;
                }
                if (! array_key_exists('item_*', (array) $fieldValues) && ! array_key_exists('item_star', (array) $fieldValues)) {
                    $fieldValues['item_*'] = $item->score;
                }
            }

            $form = WorkloadForm::with(['fields', 'items'])->findOrFail($workloadFormId);
            $fieldValues = $this->mergeSubjectCreditFieldValues(
                (array) $fieldValues,
                array_key_exists('subject_id', $validated)
                    ? ($validated['subject_id'] !== null ? (int) $validated['subject_id'] : null)
                    : ($entry->subject_id !== null ? (int) $entry->subject_id : null)
            );
            $fieldValues = $this->resolveItemFieldValues($form, (array) $fieldValues);
            $calculatedScore = app(WorkloadFormulaEvaluator::class)
                ->evaluate($form->formula_logic, $form->fields, $fieldValues);

            $validated['workload_form_id'] = $workloadFormId;
            $validated['calculated_score'] = $calculatedScore;
            $validated['field_values'] = $fieldValues;
            $entry->update($validated);

            $evaluationListId = $this->resolveEvaluationListId($form);
            EvidenceAnswer::where('workload_entry_id', $entry->id)->delete();
            if ($evaluationListId) {
                foreach ((array) $evidenceLinks as $link) {
                    $link = trim((string) $link);
                    if ($link === '') {
                        continue;
                    }
                    EvidenceAnswer::create([
                        'evaluation_list_id' => $evaluationListId,
                        'report_id' => $entry->report_id,
                        'workload_entry_id' => $entry->id,
                        'link' => $link,
                    ]);
                }
            }

            return redirect()
                ->back()
                ->with('success', 'แก้ไขข้อมูลภาระงานเรียบร้อยแล้ว');
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->back()
                ->with('error', 'ไม่พบข้อมูลภาระงานที่ต้องการแก้ไข');
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $entry = WorkloadEntry::findOrFail($id);
            $this->ensureReportEditable((int) $entry->report_id);

            EvidenceAnswer::where('workload_entry_id', $entry->id)->delete();
            $entry->delete();

            return redirect()
                ->back()
                ->with('success', 'ลบข้อมูลภาระงานเรียบร้อยแล้ว');
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->back()
                ->with('error', 'ไม่พบข้อมูลภาระงานที่ต้องการลบ');
        }
    }

    private function resolveItemVariableName(WorkloadForm $form, int $sequence): string
    {
        foreach ($form->fields as $field) {
            $type = strtolower((string) ($field->field_type ?? 'number'));
            if ($type !== 'item') {
                continue;
            }
            if (preg_match('/^item_(\d+)$/i', (string) $field->variable_name, $matches)) {
                if ((int) $matches[1] === $sequence) {
                    return $field->variable_name;
                }
            }
        }

        return $sequence > 0 ? 'item_' . $sequence : '';
    }

    private function mergeSubjectCreditFieldValues(array $fieldValues, ?int $subjectId): array
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

        $normalized['credits'] = (float) ($subject->credits ?? 0);
        $normalized['lecture_credits'] = (float) ($subject->lecture_credits ?? 0);
        $normalized['lab_credits'] = (float) ($subject->lab_credits ?? 0);
        $normalized['self_study_credits'] = (float) ($subject->self_study_credits ?? 0);

        return $normalized;
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

        if (! array_key_exists('item_*', $normalized) && ! array_key_exists('item_star', $normalized)) {
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

    private function resolveEvaluationListId(WorkloadForm $form): ?int
    {
        $subCriteriaId = $form->quantity_sub_criteria_id;
        if (! $subCriteriaId) {
            return null;
        }

        $subCriteria = QuantitySubCriteria::find($subCriteriaId);
        if (! $subCriteria || ! $subCriteria->evaluation_list_id) {
            return null;
        }

        return (int) $subCriteria->evaluation_list_id;
    }

    private function ensureReportEditable(int $reportId): void
    {
        $report = Reports::findOrFail($reportId);

        if (! in_array($report->status, $this->editableStatuses, true)) {
            abort(403, 'รายงานนี้อยู่ในโหมดอ่านอย่างเดียว');
        }
    }
}
