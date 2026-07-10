{{-- ความคิดเห็นจากผู้ประเมินในหน้าผู้ถูกประเมิน แยกจากหน้าแม่เพื่อลด block HTML ซ้ำ --}}
@php
    $roleComments = collect([
        [
            'title' => 'ความคิดเห็นจากผู้ประเมิน',
            'value' => data_get($report, 'evaluator_comment'),
        ],
        [
            'title' => 'ความคิดเห็นจากกรรมการ',
            'value' => data_get($report, 'director_comment'),
        ],
        [
            'title' => 'ความคิดเห็นจากผู้บริหาร',
            'value' => data_get($report, 'manager_comment'),
        ],
    ])->filter(fn (array $comment) => filled($comment['value']))->values();

    if ($roleComments->isEmpty() && filled(data_get($report, 'comment'))) {
        $roleComments = collect([
            [
                'title' => 'ความคิดเห็นจากผู้ประเมิน',
                'value' => data_get($report, 'comment'),
            ],
        ]);
    }
@endphp

<div class="mt-8 rounded-lg border border-blue-200 bg-purple-50 p-6">
    <h3 class="mb-4 flex items-center text-lg font-semibold text-purple-900">
        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
            </path>
        </svg>
        ความคิดเห็นจากผู้ประเมิน
    </h3>

    @if ($roleComments->isNotEmpty())
        <div class="space-y-4">
            @foreach ($roleComments as $comment)
                <div class="rounded-lg border border-blue-100 bg-white p-4 shadow-sm">
                    <div class="mb-2 text-sm font-semibold text-gray-800">{{ $comment['title'] }}</div>
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($comment['value'])) !!}
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-lg border border-blue-100 bg-white p-4 shadow-sm">
            <div class="italic text-gray-500">
                ไม่มีความคิดเห็นเพิ่มเติม
            </div>
        </div>
    @endif
</div>
