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
                    <div class="col-lg-4 col-md-6">
                        <label for="search" class="form-label fw-semibold">ค้นหาครอบคลุม</label>
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
                                placeholder="ชื่อ, อีเมล, รหัสพนักงาน, กิจกรรม, IP..."
                            >
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-6">
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

                    <div class="col-lg-2 col-md-6">
                        <label for="event" class="form-label fw-semibold">เหตุการณ์</label>
                        <select class="form-select" id="event" name="event">
                            <option value="all" {{ request('event', 'all') == 'all' ? 'selected' : '' }}>
                                ทั้งหมด
                            </option>
                            @foreach ($eventNames as $eventName)
                                <option value="{{ $eventName }}" {{ request('event') == $eventName ? 'selected' : '' }}>
                                    {{ ucfirst($eventName) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="actor" class="form-label fw-semibold">ผู้ทำรายการ</label>
                        <select class="form-select" id="actor" name="actor">
                            <option value="all" {{ request('actor', 'all') == 'all' ? 'selected' : '' }}>ทั้งหมด</option>
                            <option value="user" {{ request('actor') == 'user' ? 'selected' : '' }}>ผู้ใช้</option>
                            <option value="system" {{ request('actor') == 'system' ? 'selected' : '' }}>ระบบ</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="period" class="form-label fw-semibold">ช่วงเวลาเร็ว</label>
                        <select class="form-select" id="period" name="period">
                            <option value="all" {{ request('period', 'all') == 'all' ? 'selected' : '' }}>
                                ทั้งหมด
                            </option>
                            <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>วันนี้</option>
                            <option value="yesterday" {{ request('period') == 'yesterday' ? 'selected' : '' }}>เมื่อวาน</option>
                            <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>สัปดาห์นี้</option>
                            <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>เดือนนี้</option>
                            <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>ปีนี้</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="date_from" class="form-label fw-semibold">ตั้งแต่วันที่</label>
                        <input
                            type="date"
                            class="form-control"
                            id="date_from"
                            name="date_from"
                            value="{{ request('date_from') }}"
                        >
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="date_to" class="form-label fw-semibold">ถึงวันที่</label>
                        <input
                            type="date"
                            class="form-control"
                            id="date_to"
                            name="date_to"
                            value="{{ request('date_to') }}"
                        >
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="ip" class="form-label fw-semibold">IP Address</label>
                        <input
                            type="text"
                            class="form-control"
                            id="ip"
                            name="ip"
                            value="{{ request('ip') }}"
                            placeholder="เช่น 127.0.0.1"
                        >
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="sort" class="form-label fw-semibold">เรียงลำดับ</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>ล่าสุดก่อน</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>เก่าสุดก่อน</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="per_page" class="form-label fw-semibold">จำนวนต่อหน้า</label>
                        <select class="form-select" id="per_page" name="per_page">
                            @foreach ([10, 20, 50, 100] as $size)
                                <option value="{{ $size }}" {{ (int) request('per_page', 20) === $size ? 'selected' : '' }}>
                                    {{ $size }} รายการ
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 d-flex align-items-end">
                        <div class="w-100">
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-search me-1"></i>ค้นหา
                            </button>
                            <a href="{{ route('user.management.log') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo me-1"></i>รีเซ็ต
                            </a>
                        </div>
                    </div>

                    @if (!empty($activeFilterLabels))
                        <div class="col-12">
                            <div class="active-filter-box">
                                <span class="active-filter-title">
                                    <i class="fas fa-sliders-h me-1"></i>กำลังกรอง:
                                </span>
                                @foreach ($activeFilterLabels as $label)
                                    <span class="active-filter-chip">{{ $label }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
