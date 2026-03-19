@extends('layouts.app')

@section('content')
@php
    $educationHistory = $user->education_history_entries;
    $formattedPhone = $user->phone ? preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', preg_replace('/\D/', '', $user->phone)) : '-';
    $roleNames = $user->roles->pluck('name');
@endphp

<div class="max-w-6xl mx-auto space-y-6">
    <x-header
        title="โปรไฟล์ผู้ใช้"
        text="รายละเอียดข้อมูลผู้ใช้ทั้งหมดในระบบ"
        icon="fas fa-user" />

    <div class="bg-white rounded-3xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-slate-50 via-white to-blue-50 px-6 py-8 border-b border-gray-200">
            <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                <div class="flex-shrink-0">
                    <img src="{{ $user->profile_photo_url }}"
                        alt="Profile Photo"
                        class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg ring-4 ring-slate-100">
                </div>

                <div class="flex-grow">
                    <h2 class="text-3xl font-bold text-gray-900">{{ $user->display_name }}</h2>
                    <p class="text-lg text-gray-600 mt-1">{{ $user->email ?? '-' }}</p>
                    <p class="text-sm text-gray-500 mt-2">รหัสพนักงาน: {{ $user->employee_id ?? '-' }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse ($roleNames as $role)
                            <span class="rounded-full bg-purple-100 text-purple-800 px-3 py-1 text-xs font-medium">{{ $role }}</span>
                        @empty
                            <span class="text-sm text-gray-500">ไม่มีบทบาท</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <section class="bg-gray-50 border border-gray-200 rounded-2xl p-5">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">ข้อมูลงาน</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">ตำแหน่งงาน</p>
                        <p class="text-gray-900 font-medium mt-1">{{ optional($user->position)->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">ระดับตำแหน่งงาน</p>
                        <p class="text-gray-900 font-medium mt-1">{{ optional($user->jobLevel)->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">หน่วยงาน/สาขาวิชา</p>
                        <p class="text-gray-900 font-medium mt-1">{{ optional($user->department)->department_name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">ประเภทบุคลากร</p>
                        <p class="text-gray-900 font-medium mt-1">{{ $user->personnel_type ?? '-' }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-gray-50 border border-gray-200 rounded-2xl p-5">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">ข้อมูลติดต่อ</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">อีเมล</p>
                        <p class="text-gray-900 font-medium mt-1 break-all">{{ $user->email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">เบอร์โทร</p>
                        <p class="text-gray-900 font-medium mt-1">{{ $formattedPhone }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-gray-50 border border-gray-200 rounded-2xl p-5">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">ประวัติการศึกษา</h3>
                @if (!empty($educationHistory))
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-700">
                            <thead class="text-gray-600 border-b border-gray-200">
                                <tr>
                                    <th class="text-left py-3 pr-4">ปีที่จบ</th>
                                    <th class="text-left py-3 pr-4">วุฒิการศึกษา</th>
                                    <th class="text-left py-3">มหาวิทยาลัย</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($educationHistory as $entry)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-3 pr-4">{{ $entry['graduation_year'] ?? '-' }}</td>
                                        <td class="py-3 pr-4">{{ $entry['degree'] ?? '-' }}</td>
                                        <td class="py-3">{{ $entry['university'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-600">-</p>
                @endif
            </section>

            <section class="bg-gray-50 border border-gray-200 rounded-2xl p-5">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">ผลงาน</h3>
                @if (filled($user->portfolio))
                    <div class="text-gray-700 whitespace-pre-line leading-7">{{ $user->portfolio }}</div>
                @else
                    <p class="text-gray-600">-</p>
                @endif
            </section>

            <section class="bg-gray-50 border border-gray-200 rounded-2xl p-5">
                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">ผลงานการประเมิน</h3>
                        <p class="text-sm text-gray-600">สรุปงานและรอบประเมินที่บันทึกอยู่ในระบบประเมินผล</p>
                    </div>
                </div>

                @if (($evaluatedWorks ?? collect())->isNotEmpty())
                    <div class="space-y-4">
                        @foreach ($evaluatedWorks as $work)
                            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                <div>
                                    <h4 class="text-lg font-semibold text-slate-900">{{ $work['report_title'] }}</h4>
                                    @if (!empty($work['report_description']))
                                        <p class="mt-1 text-sm text-slate-600">{{ $work['report_description'] }}</p>
                                    @endif
                                </div>

                                <div class="mt-4 grid grid-cols-1 gap-3 text-sm text-slate-700 md:grid-cols-3">
                                    <div class="rounded-xl bg-slate-50 p-3">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">ช่วงประเมิน</p>
                                        <p class="mt-2 font-medium text-slate-900">
                                            {{ $work['period_start'] && $work['period_end'] ? $work['period_start'].' - '.$work['period_end'] : '-' }}
                                        </p>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 p-3">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">ผู้ประเมิน</p>
                                        <p class="mt-2 font-medium text-slate-900">{{ $work['evaluator_name'] ?: '-' }}</p>
                                        @if (!empty($work['evaluator_position']))
                                            <p class="mt-1 text-xs text-slate-500">{{ $work['evaluator_position'] }}</p>
                                        @endif
                                    </div>
                                    <div class="rounded-xl bg-slate-50 p-3">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">คะแนนรวม</p>
                                        <p class="mt-2 font-medium text-slate-900">QNT {{ $work['quantity_score'] }} | QLT {{ $work['quality_score'] }}</p>
                                    </div>
                                </div>

                                @if (!empty($work['subjects']) && count($work['subjects']) > 0)
                                    <div class="mt-4">
                                        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">รายวิชาหรือหัวข้องานที่เกี่ยวข้อง</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($work['subjects'] as $subject)
                                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $subject }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($work['updated_at']))
                                    <p class="mt-4 text-xs text-slate-400">อัปเดตล่าสุด {{ $work['updated_at'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-6 text-sm text-slate-500">
                        ยังไม่มีผลงานที่ถูกบันทึกไว้ในระบบประเมิน
                    </div>
                @endif
            </section>

            <div class="pt-2 flex flex-wrap justify-end gap-3">
                @if ($user->is_public_profile_enabled)
                    <x-button
                        type="primary"
                        text="ดู Public View"
                        icon="fas fa-up-right-from-square"
                        href="{{ $user->public_profile_url }}" />
                @endif

                <x-button
                    type="warning"
                    text="แก้ไขข้อมูล"
                    icon="fas fa-edit"
                    href="{{ route('profile.edit') }}" />
            </div>
        </div>
    </div>
</div>
@endsection
