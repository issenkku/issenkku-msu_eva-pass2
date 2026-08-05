@php
    $canReorder = request('sort', 'manual') === 'manual' && !request()->filled('search');
@endphp

<style>
    .reorder-row.is-dragging { opacity: .55; }
    .reorder-row.is-drag-over { box-shadow: inset 0 -3px 0 #0d6efd; }
    .reorder-handle { cursor: grab; user-select: none; color: #6c757d; font-weight: 700; letter-spacing: 1px; }
    .reorder-handle:active { cursor: grabbing; }
    .reorder-disabled .reorder-handle { cursor: not-allowed; opacity: .45; }
</style>

<div class="table-container" data-async-table-region data-reorder-table data-reorder-url="{{ route('job-level.reorder') }}" data-can-reorder="{{ $canReorder ? 1 : 0 }}" data-start-order="{{ $jobLevels->firstItem() ?? 1 }}">
    <div class="table-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <h4><i class="fas fa-table me-2"></i>ข้อมูลระดับตำแหน่งงาน</h4>
        <small class="text-muted" data-reorder-status></small>
    </div>

    <div class="table-responsive">
        @if (isset($jobLevels) && $jobLevels->count() > 0)
            <table class="table table-custom {{ $canReorder ? '' : 'reorder-disabled' }}">
                <thead>
                    <tr>
                        <th style="width: 6%" class="text-center">
                            <input type="checkbox" class="form-check-input" aria-label="เลือกระดับตำแหน่งงานทั้งหมดในหน้านี้" data-bulk-select-all>
                        </th>
                        <th style="width: 8%"></th>
                        <th style="width: 12%">ลำดับ</th>
                        <th style="width: 54%">ชื่อระดับตำแหน่งงาน</th>
                        <th style="width: 20%">การจัดการ</th>
                    </tr>
                </thead>
                <tbody data-reorder-body data-resource-rows>
                    @foreach ($jobLevels as $index => $jobLevel)
                        <tr class="reorder-row" data-id="{{ $jobLevel->id }}" data-resource-row data-resource-id="{{ $jobLevel->id }}">
                            <td class="text-center align-middle">
                                <input type="checkbox" class="form-check-input" value="{{ $jobLevel->id }}" aria-label="เลือก {{ $jobLevel->name }}" data-bulk-checkbox>
                            </td>
                            <td class="text-center align-middle"><span class="reorder-handle" data-drag-handle title="ลากเพื่อจัดอันดับ">⋮⋮</span></td>
                            <td class="align-middle" data-sequence>{{ $jobLevels->firstItem() + $index }}</td>
                            <td class="align-middle"><strong>{{ $jobLevel->name }}</strong></td>
                            <td class="align-middle">
                                <div class="d-flex gap-2 align-items-center">
                                    <x-button
                                        type="warning"
                                        text="แก้ไข"
                                        class="text-sm"
                                        icon="fas fa-edit"
                                        data-job-level-edit
                                        data-job-level-id="{{ $jobLevel->id }}"
                                        data-job-level-name="{{ $jobLevel->name }}" />
                                    <x-button type="danger" text="ลบ" class="text-sm" icon="fas fa-trash-alt" data-delete-trigger data-delete-id="{{ $jobLevel->id }}" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-3">
                {{ $jobLevels->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-user-tie"></i>
                <h5>ยังไม่มีข้อมูล</h5>
                <p>คลิกปุ่ม "เพิ่มระดับตำแหน่งงาน" เพื่อเริ่มต้นเพิ่มข้อมูลระดับตำแหน่งงาน</p>
            </div>
        @endif
    </div>
</div>
