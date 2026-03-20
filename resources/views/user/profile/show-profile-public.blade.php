<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์บุคลากร</title>
    <style>
        body {
            margin: 0;
            font-family: Tahoma, "Sarabun", sans-serif;
            font-size: 14px;
            line-height: 1.45;
            color: #111827;
            background: #eef1f5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .page {
            max-width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 12mm;
            background: #ffffff;
            box-sizing: border-box;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
        }

        .toolbar {
            margin: 18px auto 14px;
            max-width: 210mm;
            text-align: right;
        }

        .toolbar button {
            padding: 9px 18px;
            border: 1px solid #1f3a5f;
            border-radius: 999px;
            background: #1f3a5f;
            color: #ffffff;
            font-size: 13px;
            cursor: pointer;
        }

        h1,
        h2,
        p {
            margin: 0;
        }

        .header {
            margin-bottom: 16px;
            padding: 0 0 12px;
            border-bottom: 2px solid #1f3a5f;
        }

        .header h1 {
            font-size: 26px;
            margin-bottom: 6px;
            color: #0f172a;
        }

        .header p {
            font-size: 13px;
            color: #475569;
        }

        .section {
            margin-top: 14px;
            padding: 12px 14px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #fcfdff;
        }

        .section h2 {
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #dbe4ee;
            font-size: 17px;
            color: #1e3a5f;
        }

        .info-table,
        .education-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
        }

        .info-table th,
        .info-table td,
        .education-table th,
        .education-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            text-align: left;
        }

        .info-table th,
        .education-table th {
            width: 18%;
            background: #f2f6fb;
            font-weight: 600;
            color: #1e293b;
        }

        .text-block {
            min-height: 48px;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            white-space: pre-line;
        }

        .empty-note {
            padding: 10px 12px;
            border: 1px dashed #94a3b8;
            border-radius: 8px;
            background: #f8fafc;
            color: #475569;
        }

        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }

            body {
                background: #ffffff;
                font-size: 12px;
            }

            .toolbar {
                display: none;
            }

            .page {
                max-width: none;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .section {
                page-break-inside: avoid;
                margin-top: 10px;
                padding: 10px 0 0;
                border: none;
                border-radius: 0;
                background: transparent;
            }

            .header h1 {
                font-size: 18px;
            }

            .section h2 {
                font-size: 14px;
                margin-bottom: 6px;
                padding-bottom: 4px;
            }

            .info-table th,
            .info-table td,
            .education-table th,
            .education-table td {
                padding: 4px 6px;
                border: 1px solid #94a3b8;
            }

            .text-block,
            .empty-note {
                padding: 6px;
                border-radius: 0;
                background: transparent;
            }

            .info-table,
            .education-table {
                border-collapse: separate;
                border-spacing: 0;
                border: 1px solid #94a3b8;
            }

            .info-table th,
            .info-table td,
            .education-table th,
            .education-table td {
                border-top: 0;
                border-left: 0;
                border-right: 1px solid #94a3b8;
                border-bottom: 1px solid #94a3b8;
            }

            .info-table tr > *:last-child,
            .education-table tr > *:last-child {
                border-right: 0;
            }

            .info-table tbody tr:last-child > *,
            .education-table tbody tr:last-child > * {
                border-bottom: 0;
            }
        }
    </style>
</head>
<body>
@php($educationHistory = $user->education_history_entries)

    <div class="toolbar">
        <button type="button" onclick="window.print()">พิมพ์เอกสาร</button>
    </div>

    <main class="page">
        <header class="header">
            <h1>ข้อมูลประวัติบุคลากร</h1>
            <p>เอกสารสรุปข้อมูลสำหรับเผยแพร่และใช้งานในการพิมพ์</p>
        </header>

        <section class="section">
            <h2>ข้อมูลทั่วไป</h2>
            <table class="info-table">
                <tbody>
                    <tr>
                        <th>ชื่อ - สกุล</th>
                        <td>{{ $user->display_name ?? '-' }}</td>
                        <th>รหัสพนักงาน</th>
                        <td>{{ $user->employee_id ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>ตำแหน่ง</th>
                        <td>{{ optional($user->position)->name ?? '-' }}</td>
                        <th>ระดับตำแหน่ง</th>
                        <td>{{ optional($user->jobLevel)->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>ประเภทบุคลากร</th>
                        <td>{{ $user->personnel_type ?? '-' }}</td>
                        <th>หน่วยงาน</th>
                        <td>{{ optional($user->department)->department_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>อีเมล</th>
                        <td>{{ $user->email ?? '-' }}</td>
                        <th>โทรศัพท์</th>
                        <td>{{ $user->phone ?: '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2>ประวัติการศึกษา</h2>
            @if (!empty($educationHistory))
                <table class="education-table">
                    <thead>
                        <tr>
                            <th>ปีที่จบ</th>
                            <th>วุฒิการศึกษา</th>
                            <th>สถาบันการศึกษา</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($educationHistory as $entry)
                            <tr>
                                <td>{{ $entry['graduation_year'] ?? '-' }}</td>
                                <td>{{ $entry['degree'] ?? '-' }}</td>
                                <td>{{ $entry['university'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-note">ไม่ระบุข้อมูลประวัติการศึกษา</div>
            @endif
        </section>

        <section class="section">
            <h2>ผลงาน</h2>
            @if (filled($user->portfolio))
                <div class="text-block">{{ $user->portfolio }}</div>
            @else
                <div class="empty-note">ไม่ระบุข้อมูลผลงาน</div>
            @endif
        </section>
    </main>
</body>
</html>
