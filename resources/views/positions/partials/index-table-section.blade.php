@php
    $canReorder = request('sort', 'manual') === 'manual' && !request()->filled('search') && !request()->filled('usage');
@endphp

<style>
    .reorder-row.is-dragging { opacity: .55; }
    .reorder-row.is-drag-over { box-shadow: inset 0 -3px 0 #0d6efd; }
    .reorder-handle { cursor: grab; user-select: none; color: #6c757d; font-weight: 700; letter-spacing: 1px; }
    .reorder-handle:active { cursor: grabbing; }
    .reorder-disabled .reorder-handle { cursor: not-allowed; opacity: .45; }
</style>

<div class="table-container" data-reorder-table data-reorder-url="{{ route('positions.reorder') }}" data-can-reorder="{{ $canReorder ? 1 : 0 }}" data-start-order="{{ $positions->firstItem() ?? 1 }}">
    <div class="table-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <h4><i class="fas fa-table me-2"></i>ข้อมูลตำแหน่งงาน</h4>
        <small class="text-muted" data-reorder-status></small>
    </div>

    <div class="table-responsive">
        @if (isset($positions) && $positions->count() > 0)
            <table class="table table-custom {{ $canReorder ? '' : 'reorder-disabled' }}">
                <thead>
                    <tr>
                        <th style="width: 8%"></th>
                        <th style="width: 12%">ลำดับ</th>
                        <th style="width: 60%">ชื่อตำแหน่ง</th>
                        <th style="width: 20%">การจัดการ</th>
                    </tr>
                </thead>
                <tbody data-reorder-body>
                    @foreach ($positions as $index => $position)
                        <tr class="reorder-row" data-id="{{ $position->id }}">
                            <td class="text-center align-middle"><span class="reorder-handle" data-drag-handle title="ลากเพื่อจัดอันดับ">⋮⋮</span></td>
                            <td class="align-middle" data-sequence>{{ $positions->firstItem() + $index }}</td>
                            <td class="align-middle"><strong>{{ $position->name }}</strong></td>
                            <td class="align-middle">
                                <div class="d-flex gap-2 align-items-center">
                                    <x-button
                                        type="warning"
                                        text="แก้ไข"
                                        class="text-sm"
                                        icon="fas fa-edit"
                                        data-position-edit
                                        data-position-id="{{ $position->id }}"
                                        data-position-name="{{ $position->name }}" />
                                    <x-button type="danger" text="ลบ" class="text-sm" icon="fas fa-trash-alt" onclick="confirmDelete({{ $position->id }})" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-3">
                {{ $positions->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-user-tie"></i>
                <h5>ยังไม่มีข้อมูล</h5>
                <p>คลิกปุ่ม "เพิ่มตำแหน่ง" เพื่อเริ่มต้นเพิ่มข้อมูลตำแหน่ง</p>
            </div>
        @endif
    </div>
</div>
