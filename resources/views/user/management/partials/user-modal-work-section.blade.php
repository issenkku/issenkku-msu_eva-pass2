{{-- ข้อมูลงาน ใช้กำหนดหน่วยงาน ตำแหน่ง และประเภทบุคลากรที่มีผลต่อการใช้งานระบบ --}}
<div>
    <h3 class="mb-2 font-semibold text-purple-600">ข้อมูลงาน</h3>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div>
            <label for="department_id" class="block">หน่วยงาน <span class="text-red-600">*</span></label>
            <select name="department_id" id="department_id" class="w-full rounded border px-3 py-2" required>
                <option value="" disabled selected hidden>--เลือกหน่วยงาน--</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                @endforeach
            </select>
            <div class="mt-1 hidden text-sm text-red-500" id="department_idError">จำเป็นต้องกรอกข้อมูล</div>
        </div>
        <div>
            <label for="position_id" class="block">ตำแหน่งงาน <span class="text-red-600">*</span></label>
            <select name="position_id" id="position_id" class="w-full rounded border px-3 py-2" required>
                <option value="" disabled selected hidden>--เลือกตำแหน่งงาน--</option>
                @foreach ($positions as $position)
                    <option value="{{ $position->id }}">{{ $position->name }}</option>
                @endforeach
            </select>
            <div class="mt-1 hidden text-sm text-red-500" id="position_idError">จำเป็นต้องกรอกข้อมูล</div>
        </div>
        <div>
            <label for="job_level_id" class="block">ระดับตำแหน่งงาน</label>
            <select name="job_level_id" id="job_level_id" class="w-full rounded border px-3 py-2">
                <option value="">--เลือกระดับตำแหน่งงาน--</option>
                @foreach ($jobLevels as $jobLevel)
                    <option value="{{ $jobLevel->id }}">{{ $jobLevel->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="personnel_type" class="block">ประเภทบุคลากร <span class="text-red-600">*</span></label>
            <select name="personnel_type" id="personnel_type" class="w-full rounded border px-3 py-2" required>
                <option value="" disabled selected hidden>--เลือกประเภทบุคลากร--</option>
                <option value="สนับสนุน">สนับสนุน</option>
                <option value="วิชาการ">วิชาการ</option>
                <option value="บริหาร">บริหาร</option>
            </select>
            <div class="mt-1 hidden text-sm text-red-500" id="personnel_typeError">จำเป็นต้องกรอกข้อมูล</div>
        </div>
    </div>
</div>
