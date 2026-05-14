@props([
    'report',
    'currentRole' => null,
    'readonly' => true,
    'inputName' => 'comment',
])

@php
    $roles = [
        'evaluator' => [
            'field' => 'evaluator_comment',
            'title' => 'ความคิดเห็นจากผู้ประเมิน',
        ],
        'director' => [
            'field' => 'director_comment',
            'title' => 'ความคิดเห็นจากกรรมการ',
        ],
        'manager' => [
            'field' => 'manager_comment',
            'title' => 'ความคิดเห็นจากผู้บริหาร',
        ],
    ];

    $roleComments = collect($roles)->mapWithKeys(function (array $meta, string $roleKey) use ($report) {
        return [$roleKey => data_get($report, $meta['field'])];
    });

    $legacyValue = $roleComments->filter(fn ($value) => filled($value))->isEmpty()
        ? data_get($report, 'comment')
        : null;

    $visibleRoleKeys = match ($currentRole) {
        'evaluator' => ['evaluator'],
        'director' => ['evaluator', 'director'],
        'manager' => ['evaluator', 'director', 'manager'],
        default => array_keys($roles),
    };

    $sections = collect($roles)
        ->filter(fn (array $meta, string $roleKey) => in_array($roleKey, $visibleRoleKeys, true))
        ->map(function (array $meta, string $roleKey) use ($currentRole, $roleComments, $legacyValue) {
        $isCurrentRole = $currentRole === $roleKey;
        $value = $roleComments->get($roleKey);

        if (! filled($value) && $isCurrentRole) {
            $value = $legacyValue;
        }

        return [
            'isCurrentRole' => $isCurrentRole,
            'title' => $meta['title'],
            'value' => $value,
        ];
    });
@endphp

<div class="mt-8 rounded-lg border border-blue-200 bg-purple-50 p-6">
    <h3 class="mb-4 text-lg font-semibold text-purple-900">ความคิดเห็นประกอบการประเมิน</h3>
    <div class="space-y-5">
        @foreach ($sections as $section)
            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-800">{{ $section['title'] }}</label>

                @if ($section['isCurrentRole'] && ! $readonly)
                    <textarea
                        name="{{ $inputName }}"
                        class="mt-2 w-full rounded-lg border border-gray-400 bg-white p-4 text-base form-input focus:border-green-500 focus:ring-green-500"
                        placeholder="ระบุ{{ $section['title'] }}"
                    >{{ old($inputName, $section['value'] ?? '') }}</textarea>
                @elseif (filled($section['value']))
                    <div class="rounded-lg border border-blue-100 bg-white p-4 shadow-sm">
                        <div class="prose max-w-none text-gray-700">
                            {!! nl2br(e($section['value'])) !!}
                        </div>
                    </div>
                @else
                    <div class="rounded-lg border border-blue-100 bg-white p-4 text-gray-500 italic shadow-sm">
                        ไม่มี{{ $section['title'] }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
