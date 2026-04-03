{{-- header มาตรฐานของหน้าแบบประเมิน --}}
@props([
    'title' => 'แบบประเมินผลงาน',
    'versionName' => null,
    'showVersion' => true,
])

<div class="page-header">
    <h1>{{ $title }}</h1>

    @if ($showVersion && filled($versionName))
        <p class="version">เวอร์ชัน: {{ $versionName }}</p>
    @endif
</div>
