{{-- ไฟล์มุมมอง: resources/views/components/header.blade.php --}}
@props([
    'title',
    'text',
    'icon' => null,
])

<div class="page-header">
    <h2><i class="{{ $icon }} me-2"></i>{{ $title }}</h2>
    <p>{{ $text }}</p>
</div>

@include('components.header-styles')
