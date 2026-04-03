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
            'title' => '&#3588;&#3623;&#3634;&#3617;&#3588;&#3636;&#3604;&#3648;&#3627;&#3655;&#3609;&#3592;&#3634;&#3585;&#3612;&#3641;&#3657;&#3611;&#3619;&#3632;&#3648;&#3617;&#3636;&#3609;',
            'titleText' => '&#3588;&#3623;&#3634;&#3617;&#3588;&#3636;&#3604;&#3648;&#3627;&#3655;&#3609;&#3592;&#3634;&#3585;&#3612;&#3641;&#3657;&#3611;&#3619;&#3632;&#3648;&#3617;&#3636;&#3609;',
        ],
        'director' => [
            'field' => 'director_comment',
            'title' => '&#3588;&#3623;&#3634;&#3617;&#3588;&#3636;&#3604;&#3648;&#3627;&#3655;&#3609;&#3592;&#3634;&#3585;&#3585;&#3619;&#3619;&#3617;&#3585;&#3634;&#3619;',
            'titleText' => '&#3588;&#3623;&#3634;&#3617;&#3588;&#3636;&#3604;&#3648;&#3627;&#3655;&#3609;&#3592;&#3634;&#3585;&#3585;&#3619;&#3619;&#3617;&#3585;&#3634;&#3619;',
        ],
        'manager' => [
            'field' => 'manager_comment',
            'title' => '&#3588;&#3623;&#3634;&#3617;&#3588;&#3636;&#3604;&#3648;&#3627;&#3655;&#3609;&#3592;&#3634;&#3585;&#3612;&#3641;&#3657;&#3610;&#3619;&#3636;&#3627;&#3634;&#3619;',
            'titleText' => '&#3588;&#3623;&#3634;&#3617;&#3588;&#3636;&#3604;&#3648;&#3627;&#3655;&#3609;&#3592;&#3634;&#3585;&#3612;&#3641;&#3657;&#3610;&#3619;&#3636;&#3627;&#3634;&#3619;',
        ],
    ];

    $roleComments = collect($roles)->mapWithKeys(function (array $meta, string $roleKey) use ($report) {
        return [$roleKey => data_get($report, $meta['field'])];
    });

    $legacyValue = $roleComments->filter(fn ($value) => filled($value))->isEmpty()
        ? data_get($report, 'comment')
        : null;

    $sections = collect($roles)->map(function (array $meta, string $roleKey) use ($currentRole, $roleComments, $legacyValue) {
        $isCurrentRole = $currentRole === $roleKey;
        $value = $roleComments->get($roleKey);

        if (! filled($value) && $isCurrentRole) {
            $value = $legacyValue;
        }

        return [
            'isCurrentRole' => $isCurrentRole,
            'title' => $meta['title'],
            'titleText' => $meta['titleText'],
            'value' => $value,
        ];
    });
@endphp

<div class="bg-purple-50 border border-blue-200 rounded-lg p-6 mt-8">
    <h3 class="text-lg font-semibold text-purple-900 mb-4">&#3588;&#3623;&#3634;&#3617;&#3588;&#3636;&#3604;&#3648;&#3627;&#3655;&#3609;&#3611;&#3619;&#3632;&#3585;&#3629;&#3610;&#3585;&#3634;&#3619;&#3611;&#3619;&#3632;&#3648;&#3617;&#3636;&#3609;</h3>
    <div class="space-y-5">
        @foreach ($sections as $section)
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-2">{!! $section['title'] !!}</label>

                @if ($section['isCurrentRole'] && ! $readonly)
                    <textarea
                        name="{{ $inputName }}"
                        class="bg-white form-input text-base w-full mt-2 px-3 rounded-lg p-4 border border-gray-400 focus:ring-green-500 focus:border-green-500"
                        placeholder="&#3619;&#3632;&#3610;&#3640;{!! $section['titleText'] !!}"
                    >{{ old($inputName, $section['value'] ?? '') }}</textarea>
                @elseif (filled($section['value']))
                    <div class="bg-white rounded-lg p-4 border border-blue-100 shadow-sm">
                        <div class="prose max-w-none text-gray-700">
                            {!! nl2br(e($section['value'])) !!}
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-lg p-4 border border-blue-100 shadow-sm text-gray-500 italic">
                        &#3652;&#3617;&#3656;&#3617;&#3637;{!! $section['title'] !!}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
