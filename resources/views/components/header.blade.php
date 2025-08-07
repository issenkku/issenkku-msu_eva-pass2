@props([
    'title',
    'text',
    'icon' => null,
])

<div class="page-header">
    <h2><i class="{{ $icon }} me-2"></i>{{ $title}}</h2>
    <p>{{ $text }}</p>
</div>

<style>
    .page-header {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        padding: 24px 32px;
        border-radius: 4px;
        margin-bottom: 24px;
        text-align: center;
    }

    .page-header h2 {
        color: #2c2c2c;
        margin-bottom: 6px;
        font-weight: 500;
        font-size: 1.75rem;
    }

    .page-header p {
        color: #666666;
        margin: 0;
        font-size: 0.95rem;
    }
</style>

