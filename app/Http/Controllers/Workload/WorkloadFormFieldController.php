<?php

namespace App\Http\Controllers\Workload;


use App\Http\Controllers\Controller;
use App\Http\Requests\Workload\StoreWorkloadFormFieldRequest;
use App\Http\Requests\Workload\UpdateWorkloadFormFieldRequest;
use App\Models\WorkloadForm;
use App\Models\WorkloadFormField;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class WorkloadFormFieldController extends Controller
{
    /**
     * เมธอด: index
     * จุดประสงค์: ส่งข้อมูลแบบ JSON
     * อินพุต: ตัวระบุ ($workloadFormId)
     * เอาต์พุต: ข้อมูล JSON
     * @param mixed $workloadFormId ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function index($workloadFormId)
    {
        try {
            $form = WorkloadForm::findOrFail($workloadFormId);

            return response()->json($form->fields);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload form not found'], 404);
        }
    }

    /**
     * เมธอด: store
     * จุดประสงค์: บันทึกข้อมูล WorkloadFormField ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ, ตัวระบุ ($workloadFormId)
     * เอาต์พุต: ข้อมูล JSON
     * @param StoreWorkloadFormFieldRequest $request ค่าที่รับเข้ามา
     * @param mixed $workloadFormId ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function store(StoreWorkloadFormFieldRequest $request, $workloadFormId)
    {
        try {
            WorkloadForm::findOrFail($workloadFormId);

            $field = WorkloadFormField::create([
                'label' => $request->validated()['label'],
                'note' => $request->validated()['note'] ?? null,
                'variable_name' => $request->validated()['variable_name'],
                'field_type' => $request->validated()['field_type'],
                'workload_form_id' => $workloadFormId,
            ]);

            return response()->json($field, 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload form not found'], 404);
        }
    }

    /**
     * เมธอด: update
     * จุดประสงค์: อัปเดตข้อมูล ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ, ตัวระบุ ($id)
     * เอาต์พุต: ข้อมูล JSON
     * @param UpdateWorkloadFormFieldRequest $request ค่าที่รับเข้ามา
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function update(UpdateWorkloadFormFieldRequest $request, $id)
    {
        try {
            $field = WorkloadFormField::findOrFail($id);
            $field->update($request->validated());

            return response()->json($field);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload form field not found'], 404);
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
            $field = WorkloadFormField::findOrFail($id);
            $field->delete();

            return response()->json(['message' => 'Workload form field deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload form field not found'], 404);
        }
    }
}
