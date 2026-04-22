{{-- ส่วนตัวกรองข้อมูลกิจกรรม --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header bg-gradient-primary text-white py-3" style="border-radius: 15px 15px 0 0;">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i>ตัวกรองข้อมูล
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="GET" action="{{ route('user.management.log') }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label fw-semibold">ค้นหาชื่อผู้ใช้</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input
                                type="text"
                                class="form-control border-start-0"
                                id="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="กรอกชื่อผู้ใช้..."
                            >
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="log_name" class="form-label fw-semibold">ประเภทกิจกรรม</label>
                        <select class="form-select" id="log_name" name="log_name">
                            <option value="all" {{ request('log_name', 'all') == 'all' ? 'selected' : '' }}>
                                ทั้งหมด
                            </option>
                            @foreach ($logNames as $logName)
                                <option value="{{ $logName }}" {{ request('log_name') == $logName ? 'selected' : '' }}>
                                    {{ ucfirst($logName) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="period" class="form-label fw-semibold">ช่วงเวลา</label>
                        <select class="form-select" id="period" name="period">
                            <option value="all" {{ request('period', 'all') == 'all' ? 'selected' : '' }}>
                                ทั้งหมด
                            </option>
                            <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>วันนี้</option>
                            <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>สัปดาห์นี้</option>
                            <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>เดือนนี้</option>
                            <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>ปีนี้</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <div class="w-100">
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-search me-1"></i>ค้นหา
                            </button>
                            <a href="{{ route('user.management.log') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo me-1"></i>รีเซ็ต
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
