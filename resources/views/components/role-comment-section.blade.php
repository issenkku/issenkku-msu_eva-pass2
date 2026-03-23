@props([
    'report',
    'currentRole' => null,
    'readonly' => true,
    'inputName' => 'comment',
])

@php
    $roles = [
        'evaluator' => [
            'label' => 'ผู้ประเมิน',
            'field' => 'evaluator_comment',
            'title' => 'ความคิดเห็นจากผู้ประเมิน',
        ],
        'director' => [
            'label' => 'กรรมการ',
            'field' => 'director_comment',
            'title' => 'ความคิดเห็นจากกรรมการ',
        ],
        'manager' => [
            'label' => 'ผู้บริหาร',
            'field' => 'manager_comment',
            'title' => 'ความคิดเห็นจากผู้บริหาร',
        ],
    ];
@endphp

<div class="bg-purple-50 border border-blue-200 rounded-lg p-6 mt-8">
    <h3 class="text-lg font-semibold text-purple-900 mb-4">ความคิดเห็นประกอบการประเมิน</h3>
    <div class="space-y-5">
        @foreach ($roles as $roleKey => $meta)
            @php
                $isCurrentRole = $currentRole === $roleKey;
                $roleValues = collect($roles)->map(fn ($roleMeta) => data_get($report, $roleMeta['field']))->filter(fn ($value) => filled($value));
                $legacyValue = $roleValues->isEmpty() ? data_get($report, 'comment') : null;
                $value = data_get($report, $meta['field']) ?: ($isCurrentRole ? $legacyValue : null);
            @endphp
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-2">{{ $meta['title'] }}</label>

                @if ($isCurrentRole && ! $readonly)
                    <textarea
                        name="{{ $inputName }}"
                        class="bg-white form-input text-base w-full mt-2 px-3 rounded-lg p-4 border border-gray-400 focus:ring-green-500 focus:border-green-500"
                        placeholder="ระบุ{{ $meta['title'] }}"
                    >{{ old($inputName, $value ?? '') }}</textarea>
                @elseif (filled($value))
                    <div class="bg-white rounded-lg p-4 border border-blue-100 shadow-sm">
                        <div class="prose max-w-none text-gray-700">
                            {!! nl2br(e($value)) !!}
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-lg p-4 border border-blue-100 shadow-sm text-gray-500 italic">
                        ไม่มี{{ $meta['title'] }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
