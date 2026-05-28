@props([
    'startTimeFormatted' => '-',
    'endTimeFormatted' => '-',
    'reportName' => '-',
    'report' => null,
    'user' => null,
    'assignment' => null,
    'assessmentType' => null,
])

@php
    $assignmentData = $assignment?->assignmentData;
    $stageLabels = [
        'evaluator' => 'หัวหน้างาน',
        'director' => 'กรรมการ',
        'manager' => 'ผู้บริหาร',
    ];

    $participants = collect(\App\Support\AssignmentFlow::stagesFor($assignmentData))
        ->filter(fn (string $stage) => in_array($stage, ['evaluator', 'director', 'manager'], true))
        ->map(function (string $stage) use ($assignmentData, $assignment, $stageLabels) {
            $user = match ($stage) {
                'evaluator' => $assignmentData?->evaluatorUser,
                'director' => $assignmentData?->directorUser,
                'manager' => $assignmentData?->managerUser,
                default => null,
            };

            if (! $user) {
                return null;
            }

            $position = match ($stage) {
                'evaluator' => $assignment->evaluatorPosition ?? null,
                'director' => $assignment->directorPosition ?? null,
                'manager' => $assignment->managerPosition ?? null,
                default => null,
            };

            return [
                'label' => $stageLabels[$stage] ?? 'ผู้เกี่ยวข้อง',
                'name' => trim(collect([$user->prefix ?? null, $user->name ?? null])->filter()->implode(' ')) ?: '-',
                'position' => $position ?: ($user->position->name ?? '-'),
            ];
        })
        ->filter()
        ->values();
@endphp

<div class="rounded-2xl bg-gradient-to-br from-purple-100 to-pink-100 p-6 shadow-md">
    <h3 class="mb-6 border-b border-purple-300 pb-2 text-xl font-bold text-purple-900">ข้อมูลผู้เกี่ยวข้องในการประเมิน</h3>

    @if ($participants->isNotEmpty())
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach ($participants as $participant)
                <div class="rounded-xl border border-purple-200 bg-white/70 px-4 py-4">
                    <div class="text-sm font-semibold text-purple-700">{{ $participant['label'] }}</div>
                    <div class="mt-3 space-y-2">
                        <div class="flex">
                            <span class="w-32 flex-shrink-0 font-bold text-gray-800">ชื่อ-สกุล:</span>
                            <span class="text-gray-700">{{ $participant['name'] }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 flex-shrink-0 font-bold text-gray-800">ตำแหน่ง:</span>
                            <span class="text-gray-700">{{ $participant['position'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-xl border border-dashed border-purple-200 bg-white/60 px-4 py-6 text-sm text-gray-500">
            ไม่พบข้อมูลผู้เกี่ยวข้องในการประเมินในรอบนี้
        </div>
    @endif
</div>
