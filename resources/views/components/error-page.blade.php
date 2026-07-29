@props([
    'title',
    'message',
    'primaryLabel',
    'primaryUrl',
    'secondaryLabel' => null,
    'secondaryUrl' => null,
    'tone' => 'warning',
])

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title }} · {{ config('app.name', 'ระบบประเมิน') }}</title>
    <link rel="icon" href="{{ asset('favicon-msu.png') }}">
    <style>
        :root {
            color-scheme: light;
            font-family: "Noto Sans Thai", "Leelawadee UI", Tahoma, sans-serif;
            color: #172033;
            background: #f5f7fb;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            min-height: 100dvh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(circle at top, rgba(111, 45, 189, 0.11), transparent 36rem),
                #f5f7fb;
        }

        .error-card {
            width: min(100%, 560px);
            padding: clamp(28px, 6vw, 48px);
            border: 1px solid #e3e7ef;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 18px 48px rgba(23, 32, 51, 0.10);
            text-align: center;
        }

        .error-mark {
            width: 72px;
            height: 72px;
            margin: 0 auto 22px;
            display: grid;
            place-items: center;
            border-radius: 22px;
            background: #f3e8ff;
            color: #7e22ce;
            font-size: 2rem;
            font-weight: 800;
        }

        .error-mark[data-tone="danger"] {
            background: #fef2f2;
            color: #b91c1c;
        }

        h1 {
            margin: 0;
            font-size: clamp(1.55rem, 4vw, 2rem);
            line-height: 1.3;
        }

        p {
            margin: 14px auto 0;
            max-width: 44ch;
            color: #596579;
            line-height: 1.75;
        }

        .actions {
            margin-top: 30px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
        }

        .button {
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 20px;
            border: 1px solid #7e22ce;
            border-radius: 12px;
            color: #fff;
            background: #7e22ce;
            font-weight: 700;
            text-decoration: none;
        }

        .button--secondary {
            border-color: #d7dce5;
            color: #354056;
            background: #fff;
        }

        .button:hover {
            filter: brightness(0.96);
        }

        .button:focus-visible {
            outline: 3px solid #fbbf24;
            outline-offset: 3px;
        }

        @media (max-width: 480px) {
            .actions,
            .button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="error-card" aria-labelledby="error-title">
        <div class="error-mark" data-tone="{{ $tone }}" aria-hidden="true">!</div>
        <h1 id="error-title">{{ $title }}</h1>
        <p>{{ $message }}</p>
        <nav class="actions" aria-label="ตัวเลือกดำเนินการ">
            <a class="button" href="{{ $primaryUrl }}">{{ $primaryLabel }}</a>
            @if ($secondaryLabel && $secondaryUrl)
                <a class="button button--secondary" href="{{ $secondaryUrl }}">
                    {{ $secondaryLabel }}
                </a>
            @endif
        </nav>
    </main>
</body>
</html>
