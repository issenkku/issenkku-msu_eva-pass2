{{-- ชุดโมดัลสำหรับดูรายละเอียดกิจกรรม --}}
@foreach ($activities as $activity)
    <div class="modal fade" id="activityModal{{ $activity->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>รายละเอียดกิจกรรม
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong>ผู้ใช้:</strong> {{ $activity->causer->name ?? 'ระบบ' }}</p>
                    <p class="mb-2"><strong>ประเภท:</strong> {{ ucfirst($activity->log_name) }}</p>
                    <p class="mb-2"><strong>เหตุการณ์:</strong> {{ $activity->event_label }}</p>
                    <p class="mb-2"><strong>กิจกรรม:</strong> {{ $activity->description }}</p>
                    <p class="mb-2"><strong>IP Address:</strong> {{ $activity->ip_address ?? '-' }}</p>
                    <p class="mb-2"><strong>วันที่และเวลา:</strong> {{ $activity->thai_created_at }} เวลา {{ $activity->thai_time }}</p>
                    @if ($activity->properties->count() > 0)
                        <pre class="bg-light p-3 rounded small">{{ json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
