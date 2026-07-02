<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: 'Noto Sans Devanagari', 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #A00000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: #A00000; margin: 5px 0; font-size: 18px; }
        .header h2 { margin: 0; font-size: 14px; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #A00000; color: white; padding: 8px; text-align: left; }
        td { padding: 6px 8px; border: 1px solid #ccc; }
        .title { font-size: 16px; font-weight: bold; text-align: center; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $college['college_name'] ?? 'Manmohan Memorial Polytechnic' }}</h1>
        <h2>Hatimuda, Morang, Nepal</h2>
    </div>

    <div class="title">Report - {{ ucfirst($type) }} Wise</div>

    <table>
        <thead>
            <tr><th>Label</th><th>Count</th></tr>
        </thead>
        <tbody>
            <tr><td>Sample Data</td><td>0</td></tr>
        </tbody>
    </table>
</body>
</html>
