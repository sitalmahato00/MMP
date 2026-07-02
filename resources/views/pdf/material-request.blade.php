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
        .header .form-title { font-size: 16px; font-weight: bold; margin-top: 10px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 4px 8px; border: 1px solid #ccc; }
        .info-table .label { font-weight: bold; width: 150px; background: #f5f5f5; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #A00000; color: white; padding: 8px; text-align: left; font-size: 11px; }
        .items-table td { padding: 6px 8px; border: 1px solid #ccc; }
        .items-table tr:nth-child(even) { background: #f9f9f9; }
        .footer { margin-top: 40px; }
        .signature-row { display: flex; justify-content: space-between; margin-top: 30px; }
        .signature-box { text-align: center; width: 30%; }
        .signature-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 60px; color: rgba(0,0,0,0.03); z-index: -1; }
    </style>
</head>
<body>
    <div class="watermark">MANMOHAN MEMORIAL POLYTECHNIC</div>

    <div class="header">
        <h1>{{ $college['college_name'] ?? 'Manmohan Memorial Polytechnic' }}</h1>
        <h2>Hatimuda, Morang, Nepal</h2>
        <div class="form-title">माग फाराम (Material Request Form)</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">फाराम नम्बर (Form No.):</td>
            <td>{{ $request->request_number }}</td>
            <td class="label">मिति (Date BS):</td>
            <td>{{ $request->date_bs }}</td>
        </tr>
        <tr>
            <td class="label">निवेदक (Applicant):</td>
            <td>{{ $request->user?->name }}</td>
            <td class="label">विभाग (Department):</td>
            <td>{{ $request->department?->name }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>क्र.सं. (SN)</th>
                <th>सामानको नाम (Item Name)</th>
                <th>विशिष्टीकरण (Specification)</th>
                <th>एकाइ (Unit)</th>
                <th>परिमाण (Qty)</th>
                <th>टिप्पणी (Remarks)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($request->items as $idx => $item)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->specification ?? '-' }}</td>
                <td>{{ $item->unit }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->remarks ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($request->remarks)
    <p><strong>टिप्पणी (Remarks):</strong> {{ $request->remarks }}</p>
    @endif

    <div class="footer">
        <div class="signature-row">
            <div class="signature-box">
                <div class="signature-line">निवेदक (Applicant)</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">विभाग प्रमुख (Department Head)</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">प्रिन्सिपल (Principal)</div>
            </div>
        </div>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text(270, 770, 'Page {PAGE_NUM} of {PAGE_COUNT}', null, 8, array(0,0,0));
        }
    </script>
</body>
</html>
