{{-- ตารางแสดงรายการกิจกรรม --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header bg-gradient-primary text-white py-3 d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>รายการกิจกรรม
                </h5>
                <span class="badge bg-light text-dark px-3 py-2">
                    ทั้งหมด {{ $activities->total() }} รายการ
                </span>
            </div>
            <div class="card-body p-0">
                @if ($activities->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3 border-0">#</th>
                                    <th class="px-4 py-3 border-0">ผู้ใช้</th>
                                    <th class="px-4 py-3 border-0">ประเภท</th>
                                    <th class="px-4 py-3 border-0">เหตุการณ์</th>
                                    <th class="px-4 py-3 border-0">กิจกรรม</th>
                                    <th class="px-4 py-3 border-0">รายละเอียด</th>
                                    <th class="px-4 py-3 border-0">วันที่</th>
                                    <th class="px-4 py-3 border-0 text-center">การดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($activities as $index => $activity)
                                    @php
                                        $logNameClasses = [
                                            'จัดการผู้ใช้' => 'bg-green-100 text-green-800',
                                            'การเข้าใช้งาน' => 'bg-blue-100 text-blue-800',
                                            'การประเมิน' => 'bg-yellow-100 text-yellow-800',
                                            'จัดการรอบการประเมิน' => 'bg-orange-100 text-orange-800',
                                            'จัดการตำแหน่งงาน' => 'bg-purple-100 text-purple-800',
                                            'จัดการหน่วยงาน' => 'bg-pink-100 text-pink-800',
                                        ];

                                        $eventClasses = [
                                            'created' => 'bg-green-100 text-green-800',
                                            'updated' => 'bg-blue-100 text-blue-800',
                                            'deleted' => 'bg-red-100 text-red-800',
                                        ];

                                        $logClass = $logNameClasses[$activity->log_name] ?? 'bg-gray-100 text-gray-800';
                                        $eventClass = $eventClasses[$activity->event] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <tr class="border-bottom">
                                        <td class="px-4 py-3">{{ $activities->firstItem() + $index }}</td>
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-gradient-info rounded-circle d-flex align-items-center justify-content-center me-3">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $activity->causer->name ?? 'ระบบ' }}</div>
                                                    @if ($activity->causer)
                                                        <small class="text-muted">{{ $activity->causer->email }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge {{ $logClass }} px-3 py-2">
                                                {{ ucfirst($activity->log_name) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge {{ $eventClass }} px-3 py-2">
                                                {{ $activity->event_label }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="fw-semibold">{{ $activity->description }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($activity->subject)
                                                <small class="text-muted">
                                                    {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                                                </small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                            @if ($activity->ip_address && $activity->ip_address !== '-')
                                                <div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-network-wired me-1"></i>{{ $activity->ip_address }}
                                                    </small>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-nowrap">
                                                <div class="fw-semibold">{{ $activity->thai_created_at }}</div>
                                                <small class="text-muted">{{ $activity->thai_time }}</small>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button
                                                type="button"
                                                class="btn btn-outline-info btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#activityModal{{ $activity->id }}"
                                            >
                                                <i class="fas fa-eye me-1"></i>ดูรายละเอียด
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-4">
                        <div class="text-muted">
                            แสดง {{ $activities->firstItem() }} ถึง {{ $activities->lastItem() }}
                            จากทั้งหมด {{ $activities->total() }} รายการ
                        </div>
                        {{ $activities->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-inbox fa-3x text-muted"></i>
                        </div>
                        <h5 class="text-muted">ไม่พบข้อมูลกิจกรรม</h5>
                        <p class="text-muted">ลองเปลี่ยนตัวกรองหรือค้นหาด้วยคำอื่น</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
