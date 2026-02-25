<?php
/**
 * ไฟล์คอนโทรลเลอร์: app/Http/Controllers\Workload\WorkloadFormController.php
 */

namespace App\Http\Controllers\Workload;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workload\StoreWorkloadFormRequest;
use App\Http\Requests\Workload\UpdateWorkloadFormRequest;
use App\Models\WorkloadForm;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class WorkloadFormController extends Controller
{
    /**
     * เมธอด: index
     * จุดประสงค์: ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ข้อมูล JSON
     * @param \Illuminate\Http\Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $query = WorkloadForm::with(['fields', 'items']);

        $quantitySubCriteriaId = $request->input('quantity_sub_criteria_id', $request->input('quant_sub_criteria_id'));
        $quantitySubCriteriaItemId = $request->input('quantity_sub_criteria_item_id');

        if (!is_null($quantitySubCriteriaId) && $quantitySubCriteriaId !== '') {
            $query->where('quantity_sub_criteria_id', $quantitySubCriteriaId);
        }
        if (!is_null($quantitySubCriteriaItemId) && $quantitySubCriteriaItemId !== '') {
            $query->where('quantity_sub_criteria_item_id', $quantitySubCriteriaItemId);
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
            return response()->json(WorkloadForm::with(['fields', 'items'])->findOrFail($id));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload form not found'], 404);
        }
    }

    /**
     * เมธอด: store
     * จุดประสงค์: บันทึกข้อมูล WorkloadForm ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: ข้อมูล JSON
     * @param StoreWorkloadFormRequest $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function store(StoreWorkloadFormRequest $request)
    {
        $form = WorkloadForm::create($request->validated());

        return response()->json($form, 201);
    }

    /**
     * เมธอด: update
     * จุดประสงค์: อัปเดตข้อมูล ส่งข้อมูลแบบ JSON
     * อินพุต: ข้อมูลจากคำขอ, ตัวระบุ ($id)
     * เอาต์พุต: ข้อมูล JSON
     * @param UpdateWorkloadFormRequest $request ค่าที่รับเข้ามา
     * @param mixed $id ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function update(UpdateWorkloadFormRequest $request, $id)
    {
        try {
            $form = WorkloadForm::findOrFail($id);
            $form->update($request->validated());

            return response()->json($form);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload form not found'], 404);
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
            $form = WorkloadForm::findOrFail($id);
            $form->delete();

            return response()->json(['message' => 'Workload form deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload form not found'], 404);
        }
    }
}
