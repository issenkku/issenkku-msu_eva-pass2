@props([
    'action' => url()->current(),
    'searchName' => 'search',
    'searchValue' => request('search'),
    'searchPlaceholder' => 'ค้นหาข้อมูล...',
    'sortName' => 'sort',
    'sortValue' => request('sort'),
    'sortOptions' => [],
    'filters' => [],
    'resetLabel' => 'ล้างตัวกรอง',
])

@php
    $searchValue = (string) $searchValue;
    $hasSearchValue = trim($searchValue) !== '';

    $handledKeys = collect($filters)
        ->pluck('name')
        ->filter()
        ->push($searchName, $sortName, 'page')
        ->unique()
        ->values()
        ->all();

    $preservedInputs = request()->except($handledKeys);
@endphp

<form action="{{ $action }}" method="GET" class="row g-2 align-items-end mb-3" data-auto-search-form>
    <div class="col-12 col-lg-3">
        <label for="{{ $searchName }}" class="form-label mb-1 small fw-semibold">ค้นหา</label>
        <div class="position-relative">
            <input
                type="text"
                id="{{ $searchName }}"
                name="{{ $searchName }}"
                value="{{ $searchValue }}"
                placeholder="{{ $searchPlaceholder }}"
                class="form-control form-control-sm ps-5 {{ $hasSearchValue ? 'pe-5' : '' }}"
                data-auto-search-input
                autocomplete="off">
            <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                <i class="fas fa-search"></i>
            </span>
            @if ($hasSearchValue)
                <button
                    type="button"
                    class="btn btn-link position-absolute top-50 end-0 translate-middle-y pe-3 text-muted text-decoration-none"
                    aria-label="ล้างคำค้นหา"
                    title="ล้างคำค้นหา"
                    data-auto-search-clear>
                    <i class="fas fa-times"></i>
                </button>
            @endif
        </div>
    </div>

    @foreach ($filters as $filter)
        <div class="col-12 col-md-6 col-lg-2">
            <label for="{{ $filter['name'] }}" class="form-label mb-1 small fw-semibold">{{ $filter['label'] }}</label>
            <select id="{{ $filter['name'] }}" name="{{ $filter['name'] }}" class="form-control form-control-sm" data-auto-submit-select>
                <option value="">{{ $filter['placeholder'] ?? 'ทั้งหมด' }}</option>
                @foreach (($filter['options'] ?? []) as $value => $label)
                    <option value="{{ $value }}" @selected(request($filter['name'], $filter['value'] ?? null) == $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    @endforeach

    @if (!empty($sortOptions))
        <div class="col-12 col-md-6 col-lg-4">
            <label for="{{ $sortName }}" class="form-label mb-1 small fw-semibold">เรียงลำดับ</label>
            <div class="d-flex gap-2 align-items-center">
                <select id="{{ $sortName }}" name="{{ $sortName }}" class="form-control form-control-sm" data-auto-submit-select>
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected($sortValue == $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <x-button type="secondary" :href="$action" :text="$resetLabel" icon="fas fa-rotate-left" class="px-3 py-2 text-sm flex-shrink-0" />
            </div>
        </div>
    @endif

    @foreach ($preservedInputs as $key => $value)
        @if (is_array($value))
            @foreach ($value as $item)
                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
</form>

@include('components.list-toolbar-script')
@include('components.auto-submit-script')
