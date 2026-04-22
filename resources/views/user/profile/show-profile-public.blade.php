<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์บุคลากร</title>
    @include('user.profile.partials.show-profile-public-style')
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
                            <th>มหาวิทยาลัย</th>
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
                <div class="empty-note">ยังไม่มีข้อมูลประวัติการศึกษา</div>
            @endif
        </section>

        <section class="section">
            <h2>ผลงาน</h2>
            @if (filled($user->portfolio))
                <div class="text-block">{{ $user->portfolio }}</div>
            @else
                <div class="empty-note">ยังไม่มีข้อมูลผลงาน</div>
            @endif
        </section>
    </main>
</body>
</html>
