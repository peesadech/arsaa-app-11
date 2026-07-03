<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Sarabun', 'Tahoma', sans-serif; font-size: 13px; color: #1f2937; padding: 24px; background: #f9fafb; }
        .sheet { max-width: 900px; margin: 0 auto; background: white; padding: 32px; border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        h1 { font-size: 20px; margin-bottom: 2px; }
        h2 { font-size: 14px; margin: 18px 0 8px; padding-bottom: 4px; border-bottom: 2px solid #e5e7eb; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; }
        .sub { color: #6b7280; font-size: 12px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 4px 16px; }
        .field { padding: 3px 0; }
        .field b { color: #6b7280; font-weight: 600; font-size: 11px; display: block; }
        .right { text-align: right; }
        .center { text-align: center; }
        .badge { display: inline-block; padding: 1px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .badge.green { background: #d1fae5; color: #047857; }
        .badge.red { background: #ffe4e6; color: #be123c; }
        .print-bar { max-width: 900px; margin: 0 auto 16px; display: flex; justify-content: flex-end; gap: 8px; }
        .print-btn { background: #111827; color: white; border: none; padding: 8px 20px; border-radius: 999px; font-size: 12px; font-weight: 600; cursor: pointer; }
        .photo { width: 96px; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb; }
        @media print {
            body { background: white; padding: 0; }
            .sheet { box-shadow: none; padding: 0; max-width: none; border-radius: 0; }
            .print-bar { display: none; }
        }
    </style>
</head>
<body>
    <div class="print-bar">
        <button class="print-btn" onclick="window.print()">🖨 {{ __('Print') }} / PDF</button>
    </div>
    <div class="sheet">
        @yield('body')
    </div>
</body>
</html>
