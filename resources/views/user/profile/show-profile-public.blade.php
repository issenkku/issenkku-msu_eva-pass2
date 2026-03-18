<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์บุคลากร</title>
    <link rel="icon" href="{{ asset('favicon-msu.png') }}?v=1" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        .document-card {
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }

        @media print {
            @page {
                size: A4;
                margin: 12mm;
            }

            html, body {
                background: #ffffff !important;
            }

            body {
                font-size: 12pt;
                line-height: 1.45;
                color: #000;
            }

            .no-print {
                display: none !important;
            }

            .document-card {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
            }

            .print-avoid-break {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
@php($educationHistory = $user->education_history_entries)

<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="no-print mb-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">โปรไฟล์บุคลากร</h1>
            <p class="text-sm text-slate-600">หน้าแสดงประวัติและผลงานสำหรับใช้งานสาธารณะและสั่งพิมพ์</p>
        </div>
        <button type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">
            <i class="fas fa-print"></i>
            พิมพ์โปรไฟล์
        </button>
    </div>

    <article class="document-card overflow-hidden rounded-[28px] border border-slate-200 bg-white">
        <section class="border-b border-slate-200 bg-white px-8 py-10">
            <div class="flex flex-col gap-6 md:flex-row md:items-center">
                <div class="flex-shrink-0">
                    <img src="{{ $user->profile_photo_url }}"
                         alt="Profile Photo"
                         class="h-32 w-32 rounded-3xl border border-slate-200 bg-white object-cover shadow-sm">
                </div>
                <div class="flex-grow">
                    <p class="text-sm font-medium uppercase tracking-[0.18em] text-slate-500">Public Profile</p>
                    <h2 class="mt-2 text-3xl font-bold text-slate-900 sm:text-4xl">{{ $user->prefix }} {{ $user->name }}</h2>
                    <p class="mt-3 text-lg font-medium text-slate-700">{{ optional($user->position)->name ?? '-' }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ optional($user->department)->department_name ?? '-' }}</p>

                    <div class="mt-5 flex flex-wrap gap-2 text-sm">
                        <span class="rounded-full bg-slate-200 px-3 py-1 text-slate-700">{{ $user->personnel_type ?? 'ไม่ระบุประเภท' }}</span>
                        @foreach ($user->roles->pluck('name') as $role)
                            <span class="rounded-full bg-white px-3 py-1 text-slate-600 ring-1 ring-slate-200">{{ $role }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="print-avoid-break px-8 py-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">รหัสพนักงาน</p>
                    <p class="mt-2 text-base font-medium text-slate-900">{{ $user->employee_id ?? '-' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">อีเมล</p>
                    <p class="mt-2 break-all text-base font-medium text-slate-900">{{ $user->email ?? '-' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">เบอร์โทร</p>
                    <p class="mt-2 text-base font-medium text-slate-900">{{ $user->phone ?: '-' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">หน่วยงาน</p>
                    <p class="mt-2 text-base font-medium text-slate-900">{{ optional($user->department)->department_name ?? '-' }}</p>
                </div>
            </div>
        </section>

        <section class="print-avoid-break border-t border-slate-200 px-8 py-8">
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-900">ประวัติการศึกษา</h3>
                    <p class="text-sm text-slate-600">แสดงวุฒิการศึกษาและสถาบันที่สำเร็จการศึกษา</p>
                </div>
            </div>

            @if (!empty($educationHistory))
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-100">
                            <tr class="text-left text-sm font-semibold text-slate-700">
                                <th class="px-4 py-3">ปีที่จบ</th>
                                <th class="px-4 py-3">วุฒิการศึกษา</th>
                                <th class="px-4 py-3">มหาวิทยาลัยที่จบ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white text-sm text-slate-700">
                            @foreach ($educationHistory as $entry)
                                <tr>
                                    <td class="px-4 py-3 align-top">{{ $entry['graduation_year'] ?? '-' }}</td>
                                    <td class="px-4 py-3 align-top">{{ $entry['degree'] ?? '-' }}</td>
                                    <td class="px-4 py-3 align-top">{{ $entry['university'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-6 text-sm text-slate-500">
                    ไม่ระบุข้อมูลประวัติการศึกษา
                </div>
            @endif
        </section>

        <section class="print-avoid-break border-t border-slate-200 px-8 py-8">
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                    <i class="fas fa-award"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-900">ผลงาน</h3>
                    <p class="text-sm text-slate-600">สรุปผลงานหรือข้อมูลที่เจ้าของโปรไฟล์ต้องการเผยแพร่</p>
                </div>
            </div>

            @if (filled($user->portfolio))
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="whitespace-pre-line text-sm leading-7 text-slate-700">
                        {{ $user->portfolio }}
                    </div>
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-6 text-sm text-slate-500">
                    ไม่ระบุข้อมูลผลงาน
                </div>
            @endif
        </section>

        <section class="border-t border-slate-200 px-8 py-8">
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-900">ผลงานที่ใช้ในการประเมิน</h3>
                    <p class="text-sm text-slate-600">สรุปงานและรอบประเมินที่บันทึกอยู่ในระบบประเมินผล</p>
                </div>
            </div>

            @if (($evaluatedWorks ?? collect())->isNotEmpty())
                <div class="space-y-4">
                    @foreach ($evaluatedWorks as $work)
                        <div class="print-avoid-break rounded-2xl border border-slate-200 bg-white p-5">
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
    </article>
</div>
</body>
</html>
