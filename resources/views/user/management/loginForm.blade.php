<!DOCTYPE html>
<html lang="th">
@php
    $facultyName = $siteSetting->faculty ?? 'คณะสาธารณสุขศาสตร์';
    $universityName = $siteSetting->university ?? 'มหาวิทยาลัยมหาสารคาม';
    $siteLogoUrl = $siteSetting?->logo_url ?? asset('favicon-msu.png').'?v=1';
    $siteBackgroundUrl = $siteSetting?->background_url ?? asset('images/workload-background.jpg');
    $siteUseWhiteBackground = $siteSetting?->use_white_background ?? false;
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ระบบประเมินผลการปฏิบัติงาน - {{ $facultyName }} {{ $universityName }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-msu.png') }}?v=1">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-msu.png') }}?v=1">
    <link rel="shortcut icon" href="{{ asset('favicon-msu.png') }}?v=1">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&family=Noto+Serif+Thai:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --purple: #9333ea;
            --purple-dark: #7e22ce;
            --purple-deep: #581c87;
            --blue: #3b82f6;
            --blue-light: #93c5fd;
            --lavender: #f3e8ff;
            --lavender-soft: #faf5ff;
            --pale: #f8fafc;
            --ink: #18181b;
            --muted: #71717a;
            --border: #e4e4f0;
            --white: #ffffff;
            --login-page-padding-y: clamp(0.75rem, 3.5vh, 3rem);
            --login-page-padding-x: clamp(1.25rem, 3vw, 3.5rem);
            --login-section-gap: clamp(0.65rem, 2vh, 2rem);
            --login-logo-size: clamp(56px, 8.5vh, 82px);
            --login-logo-image-size: clamp(38px, 5.6vh, 54px);
            --login-card-padding: clamp(1rem, 2.8vh, 2rem);
            --login-field-gap: clamp(0.65rem, 1.6vh, 1.1rem);
            --login-control-height: clamp(42px, 5.6vh, 46px);
            --login-button-height: clamp(44px, 6vh, 50px);
            --login-feature-height: clamp(82px, 12vh, 120px);
            --login-feature-padding: clamp(0.65rem, 1.8vh, 1rem);
        }

        body {
            min-height: 0;
            height: 100vh;
            height: 100dvh;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            grid-template-rows: minmax(0, 1fr);
            overflow: hidden;
            background-image:
                linear-gradient(180deg, #ffffff 0%, #ffffff 42%, rgba(255, 255, 255, 0.78) 55%, rgba(255, 255, 255, 0.08) 70%, rgba(255, 255, 255, 0) 100%),
                var(--site-background-image);
            background-position: center top, center bottom;
            background-repeat: no-repeat;
            background-size: 100% 100%, 100% auto;
            background-attachment: fixed;
            color: var(--ink);
            font-family: 'Kanit', sans-serif;
        }

        body.is-white-background {
            background: #ffffff;
        }

        .left,
        .right {
            min-height: 0;
            height: 100%;
        }

        .left {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: var(--login-page-padding-y) var(--login-page-padding-x);
            overflow: hidden;
            border-right: 1px solid #e9d5ff;
            background:
                linear-gradient(145deg, rgba(250, 245, 255, 0.96), rgba(243, 232, 255, 0.88)),
                var(--pale);
        }

        .left::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                repeating-linear-gradient(45deg, transparent, transparent 40px, rgba(147, 51, 234, 0.08) 40px, rgba(147, 51, 234, 0.08) 41px),
                repeating-linear-gradient(-45deg, transparent, transparent 40px, rgba(59, 130, 246, 0.05) 40px, rgba(59, 130, 246, 0.05) 41px);
            pointer-events: none;
        }

        .left-glow {
            position: absolute;
            top: -100px;
            right: -100px;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(147, 51, 234, 0.18) 0%, transparent 65%);
            pointer-events: none;
        }

        .left-glow2 {
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.14) 0%, transparent 65%);
            pointer-events: none;
        }

        .top-line {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, #c084fc, var(--purple), #a855f7, transparent);
        }

        .left-header,
        .left-body,
        .left-footer {
            position: relative;
            z-index: 1;
            animation: fadeUp 0.7s ease both;
        }

        .left-body {
            animation-delay: 0.15s;
        }

        .left-footer {
            animation-delay: 0.3s;
        }

        .univ-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: var(--login-section-gap);
            padding: 6px 14px 6px 8px;
            border: 1px solid #d8b4fe;
            border-radius: 999px;
            background: #f3e8ff;
        }

        .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--purple);
        }

        .badge-text {
            color: var(--purple-dark);
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .left-title {
            margin-bottom: 0.6rem;
            color: #111827;
            font-family: 'Noto Serif Thai', serif;
            font-size: clamp(1.65rem, 2.8vw, 2.25rem);
            font-weight: 700;
            line-height: 1.35;
        }

        .left-title span {
            color: var(--purple);
        }

        .left-subtitle {
            margin-bottom: var(--login-section-gap);
            color: #6b7280;
            font-size: 0.82rem;
            font-weight: 400;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .left-desc {
            max-width: 380px;
            padding-left: 1rem;
            border-left: 2px solid #c084fc;
            color: #4b5563;
            font-size: 0.93rem;
            font-weight: 300;
            line-height: 1.85;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: clamp(0.5rem, 1.4vh, 0.75rem);
            margin-bottom: var(--login-section-gap);
        }

        .feature-card {
            min-height: var(--login-feature-height);
            padding: var(--login-feature-padding) clamp(0.75rem, 1.4vw, 1.1rem);
            border: 1px solid #e9d5ff;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.78);
            box-shadow: 0 8px 24px rgba(147, 51, 234, 0.07);
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .feature-card:hover {
            background: #ffffff;
            transform: translateY(-1px);
        }

        .feature-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            margin-bottom: 0.55rem;
            border-radius: 10px;
            background: #f3e8ff;
            color: var(--purple);
        }

        .feature-icon svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
        }

        .feature-title {
            margin-bottom: 2px;
            color: #111827;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .feature-desc {
            color: #6b7280;
            font-size: 0.72rem;
            line-height: 1.55;
        }

        .stats-row {
            display: flex;
            overflow: hidden;
            border: 1px solid #e9d5ff;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.78);
            box-shadow: 0 8px 24px rgba(147, 51, 234, 0.07);
        }

        .stat-item {
            flex: 1;
            padding: clamp(0.55rem, 1.5vh, 1rem);
            border-right: 1px solid #e9d5ff;
            text-align: center;
        }

        .stat-item:last-child {
            border-right: 0;
        }

        .stat-num {
            margin-bottom: 4px;
            color: var(--purple);
            font-family: 'Noto Serif Thai', serif;
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.68rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .footer-logos {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-circle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            overflow: hidden;
            border: 1px solid #d8b4fe;
            border-radius: 999px;
            background: #f3e8ff;
        }

        .logo-circle img {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }

        .footer-text {
            color: #6b7280;
            font-size: 0.75rem;
            line-height: 1.6;
        }

        .footer-text strong {
            color: #374151;
            font-weight: 500;
        }

        .right {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--login-page-padding-y) clamp(1.25rem, 3vw, 3rem);
            overflow: hidden;
            background: rgba(248, 250, 252, 0.72);
            backdrop-filter: blur(10px);
        }

        .right::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(139, 60, 247, 0.06) 1px, transparent 1px);
            background-size: 24px 24px;
            pointer-events: none;
        }

        .form-wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 390px;
        }

        .logo-area {
            margin-bottom: var(--login-section-gap);
            text-align: center;
            animation: fadeUp 0.6s 0.1s ease both;
        }

        .logo-ring {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: var(--login-logo-size);
            height: var(--login-logo-size);
            margin-bottom: clamp(0.45rem, 1.4vh, 1rem);
            border: 2px solid var(--purple);
            border-radius: 999px;
            background: var(--white);
            box-shadow: 0 0 0 6px rgba(139, 60, 247, 0.1);
        }

        .logo-ring::after {
            content: '';
            position: absolute;
            inset: -7px;
            border: 1px dashed rgba(139, 60, 247, 0.28);
            border-radius: 999px;
            animation: spin 20s linear infinite;
        }

        .logo-ring img {
            width: var(--login-logo-image-size);
            height: var(--login-logo-image-size);
            object-fit: contain;
        }

        .logo-faculty {
            margin-bottom: 2px;
            color: var(--purple);
            font-family: 'Noto Serif Thai', serif;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .logo-univ {
            color: var(--muted);
            font-size: 0.76rem;
            font-weight: 400;
        }

        .form-card {
            padding: var(--login-card-padding);
            border: 1px solid var(--border);
            border-radius: 20px;
            background: var(--white);
            box-shadow: 0 4px 40px rgba(139, 60, 247, 0.08), 0 1px 3px rgba(0, 0, 0, 0.04);
            animation: fadeUp 0.6s 0.2s ease both;
        }

        .form-title {
            margin-bottom: 4px;
            color: var(--ink);
            font-family: 'Noto Serif Thai', serif;
            font-size: 1.35rem;
            font-weight: 700;
            text-align: center;
        }

        .form-eyebrow {
            margin-bottom: clamp(0.75rem, 2vh, 1.75rem);
            color: var(--muted);
            font-size: 0.78rem;
            letter-spacing: 0.02em;
            text-align: center;
        }

        .divider-line {
            height: 1px;
            margin-bottom: clamp(0.75rem, 2vh, 1.75rem);
            opacity: 0.5;
            background: linear-gradient(90deg, transparent, var(--purple), var(--blue), transparent);
        }

        .field {
            margin-bottom: var(--login-field-gap);
        }

        .field label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
            color: var(--ink);
            font-size: 0.82rem;
            font-weight: 600;
        }

        .req {
            color: var(--purple);
            font-size: 0.75rem;
        }

        .input-box {
            position: relative;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 13px;
            width: 16px;
            height: 16px;
            transform: translateY(-50%);
            fill: none;
            stroke: var(--muted);
            stroke-width: 1.8;
            pointer-events: none;
        }

        .input-box input {
            width: 100%;
            height: var(--login-control-height);
            padding: 0 2.75rem 0 2.55rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            outline: none;
            background: var(--pale);
            color: var(--ink);
            font-family: 'Kanit', sans-serif;
            font-size: 0.9rem;
            transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }

        .input-box input:focus {
            border-color: var(--purple);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(139, 60, 247, 0.1);
        }

        .input-box input::placeholder {
            color: #a7a3b4;
            font-weight: 300;
        }

        .toggle-pw {
            position: absolute;
            top: 50%;
            right: 12px;
            display: flex;
            padding: 4px;
            border: 0;
            background: none;
            color: var(--muted);
            cursor: pointer;
            transform: translateY(-50%);
            transition: color 0.2s ease;
        }

        .toggle-pw:hover {
            color: var(--purple);
        }

        .toggle-pw svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        .form-meta {
            display: flex;
            justify-content: flex-end;
            margin-top: -4px;
            margin-bottom: clamp(0.75rem, 1.8vh, 1.5rem);
        }

        .forgot {
            color: var(--purple);
            font-size: 0.78rem;
            font-weight: 500;
            text-decoration: none;
            opacity: 0.86;
            transition: opacity 0.2s ease;
        }

        .forgot:hover {
            opacity: 1;
        }

        #error {
            display: none;
            margin-bottom: 1rem;
            padding: 0.75rem 0.9rem;
            border: 1px solid #fecaca;
            border-radius: 10px;
            background: #fef2f2;
            color: #dc2626;
            font-size: 0.82rem;
            line-height: 1.5;
        }

        #error:not(.hidden) {
            display: block;
        }

        .login-notice {
            margin-bottom: 1rem;
            padding: 0.75rem 0.9rem;
            border: 1px solid;
            border-radius: 10px;
            font-size: 0.82rem;
            line-height: 1.5;
        }

        .login-notice--warning {
            border-color: #fbbf24;
            background: #fffbeb;
            color: #92400e;
        }

        .login-notice--success {
            border-color: #86efac;
            background: #f0fdf4;
            color: #166534;
        }

        .btn-submit {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            height: var(--login-button-height);
            overflow: hidden;
            border: 0;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--purple), var(--blue));
            color: var(--white);
            cursor: pointer;
            font-family: 'Kanit', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            transition: opacity 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.12), transparent);
            transition: left 0.4s ease;
        }

        .btn-submit:hover::before {
            left: 100%;
        }

        .btn-submit:hover {
            opacity: 0.94;
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(139, 60, 247, 0.32);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
        }

        .help-text {
            margin-top: 1rem;
            color: var(--muted);
            font-size: 0.75rem;
            text-align: center;
        }

        .help-text a {
            color: var(--purple);
            font-weight: 500;
            text-decoration: none;
        }

        .form-footer-note {
            margin-top: clamp(0.6rem, 1.8vh, 1.5rem);
            color: #9ca3af;
            font-size: 0.7rem;
            line-height: 1.6;
            text-align: center;
            animation: fadeUp 0.6s 0.35s ease both;
        }

        .hidden {
            display: none;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-height: 760px) and (min-width: 881px) {
            :root {
                --login-page-padding-y: clamp(0.5rem, 1.8vh, 0.9rem);
                --login-section-gap: clamp(0.45rem, 1.2vh, 0.75rem);
                --login-card-padding: clamp(0.85rem, 2vh, 1.25rem);
                --login-feature-height: clamp(72px, 10.5vh, 88px);
                --login-feature-padding: clamp(0.45rem, 1.1vh, 0.7rem);
            }

            .left-desc {
                line-height: 1.55;
            }

            .feature-icon {
                width: 30px;
                height: 30px;
                margin-bottom: 0.3rem;
            }

            .feature-desc,
            .footer-text,
            .form-footer-note {
                line-height: 1.4;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        @media (max-width: 880px) {
            body {
                grid-template-columns: 1fr;
                overflow: hidden;
            }

            .left {
                display: none;
            }

            .right {
                min-height: 0;
                height: 100%;
                padding: var(--login-page-padding-y) 1.25rem;
            }
        }
    </style>
</head>
<body class="{{ $siteUseWhiteBackground ? 'is-white-background' : '' }}" style="--site-background-image: url('{{ $siteBackgroundUrl }}');">
    <aside class="left">
        <div class="top-line"></div>
        <div class="left-glow"></div>
        <div class="left-glow2"></div>

        <div class="left-header">
            <div class="univ-badge">
                <span class="badge-dot"></span>
                <span class="badge-text">{{ $universityName }}</span>
            </div>
            <h1 class="left-title">
                <span>{{ $facultyName }}</span><br>
                {{ $universityName }}
            </h1>
            <p class="left-subtitle">Personnel Performance Evaluation System</p>
            <p class="left-desc">
                ระบบประเมินผลการปฏิบัติงานบุคลากร<br>
                สำหรับจัดรอบประเมิน กำหนดเกณฑ์ ติดตามผล และสรุปคะแนนอย่างเป็นระบบ
            </p>
        </div>

        <div class="left-body">
            <div class="feature-grid">
                <div class="feature-card">
                    <span class="feature-icon">
                        <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    </span>
                    <div class="feature-title">จัดการรอบประเมิน</div>
                    <div class="feature-desc">กำหนดช่วงเวลา เกณฑ์ ผู้รับการประเมิน และผู้ประเมินในแต่ละรอบ</div>
                </div>
                <div class="feature-card">
                    <span class="feature-icon">
                        <svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-7"/></svg>
                    </span>
                    <div class="feature-title">ประเมินผลบุคลากร</div>
                    <div class="feature-desc">บันทึกภาระงาน คะแนน และหลักฐานประกอบการประเมิน</div>
                </div>
                <div class="feature-card">
                    <span class="feature-icon">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="M12 3v3M21 12h-3M12 21v-3M3 12h3"/></svg>
                    </span>
                    <div class="feature-title">ติดตามสถานะ</div>
                    <div class="feature-desc">ตรวจสอบความคืบหน้าของการประเมินตามบทบาทที่ได้รับมอบหมาย</div>
                </div>
                <div class="feature-card">
                    <span class="feature-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span>
                    <div class="feature-title">รายงานสรุปผล</div>
                    <div class="feature-desc">สรุปคะแนนและข้อมูลการประเมินเพื่อประกอบการบริหารงานบุคคล</div>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-num">ครบทุกบทบาท</div>
                    <div class="stat-label">ผู้ใช้ตามสิทธิ์</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">จัดการออนไลน์</div>
                    <div class="stat-label">ตั้งค่าและติดตาม</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">ตรวจสอบได้</div>
                    <div class="stat-label">คะแนนและหลักฐาน</div>
                </div>
            </div>
        </div>

        <div class="left-footer">
            <div class="footer-logos">
                <div class="logo-circle">
                    <img src="{{ $siteLogoUrl }}" alt="โลโก้ {{ $universityName }}">
                </div>
                <div class="footer-text">
                    <strong>{{ $facultyName }}<br>{{ $universityName }}</strong><br>
                    ระบบประเมินผลการปฏิบัติงานบุคลากร
                </div>
            </div>
        </div>
    </aside>

    <main class="right">
        <div class="form-wrap">
            <div class="logo-area">
                <div class="logo-ring">
                    <img src="{{ $siteLogoUrl }}" alt="โลโก้ {{ $universityName }}">
                </div>
                <div class="logo-faculty">{{ $facultyName }}</div>
                <div class="logo-univ">{{ $universityName }}</div>
            </div>

            <section class="form-card">
                <h2 class="form-title">เข้าสู่ระบบ</h2>
                <p class="form-eyebrow">ระบบประเมินผลการปฏิบัติงานบุคลากร</p>
                <div class="divider-line"></div>

                <form id="loginForm" method="POST" action="{{ route('login') }}" autocomplete="on">
                    @csrf
                    <div class="field">
                        <label for="employee_id">รหัสพนักงาน <span class="req">*</span></label>
                        <div class="input-box">
                            <svg class="input-icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input
                                type="text"
                                id="employee_id"
                                name="employee_id"
                                placeholder="กรอกรหัสพนักงาน"
                                autocomplete="username"
                                required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">รหัสผ่าน <span class="req">*</span></label>
                        <div class="input-box">
                            <svg class="input-icon" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="กรอกรหัสผ่าน"
                                autocomplete="current-password"
                                required>
                            <button class="toggle-pw" type="button" data-password-toggle aria-label="แสดงหรือซ่อนรหัสผ่าน" aria-pressed="false">
                                <svg id="eyeIcon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    @if (session('session_warning'))
                        <div class="login-notice login-notice--warning" role="alert" aria-live="assertive">
                            {{ session('session_warning') }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="login-notice login-notice--success" role="status" aria-live="polite">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div id="error" class="hidden"></div>

                    <div class="form-meta">
                        <a href="{{ route('password.request') }}" class="forgot">ลืมรหัสผ่าน?</a>
                    </div>

                    <button class="btn-submit" type="submit">
                        เข้าสู่ระบบ
                        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                </form>
            </section>

            <div class="form-footer-note">
                ระบบนี้สงวนสิทธิ์เฉพาะบุคลากร {{ $facultyName }} {{ $universityName }} เท่านั้น<br>
            </div>
        </div>
    </main>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const eye = document.getElementById('eyeIcon');
            const toggle = document.querySelector('[data-password-toggle]');
            const isHidden = password.type === 'password';

            if (isHidden) {
                password.type = 'text';
                eye.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
            } else {
                password.type = 'password';
                eye.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
            }

            if (toggle) {
                toggle.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            }
        }

        document.addEventListener('click', function (event) {
            const toggle = event.target.closest('[data-password-toggle]');
            if (!toggle) {
                return;
            }

            event.preventDefault();
            togglePassword();
        });
    </script>
    @include('user.management.partials.login-form-script')
</body>
</html>
