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
        .footer { margin-top: 40px; }
        .signature-row { display: flex; justify-content: space-between; margin-top: 30px; }
        .signature-box { text-align: center; width: 30%; }
        .signature-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; }
        .detail-box { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .detail-box h3 { margin: 0 0 10px; color: #A00000; font-size: 14px; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 60px; color: rgba(0,0,0,0.03); z-index: -1; }
    </style>
</head>
<body>
    <div class="watermark">MANMOHAN MEMORIAL POLYTECHNIC</div>

    <div class="header">
        <h1>{{ $college['college_name'] ?? 'Manmohan Memorial Polytechnic' }}</h1>
        <h2>Hatimuda, Morang, Nepal</h2>
        <div class="form-title">मर्मत आदेश (Repair Order Form)</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">मर्मत नम्बर (Repair No.):</td>
            <td>{{ $order->repair_number }}</td>
            <td class="label">मिति (Date BS):</td>
            <td>{{ $order->date_bs }}</td>
        </tr>
        <tr>
            <td class="label">निवेदक (Applicant):</td>
            <td>{{ $order->user?->name }}</td>
            <td class="label">विभाग (Department):</td>
            <td>{{ $order->department?->name }}</td>
        </tr>
    </table>

    <div class="detail-box">
        <h3>उपकरणको नाम (Equipment Name)</h3>
        <p>{{ $order->equipment_name }}</p>
    </div>

    <div class="detail-box">
        <h3>समस्या विवरण (Problem Description)</h3>
        <p>{{ $order->problem_description }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">अनुमानित लागत (Estimated Cost):</td>
            <td>Rs. {{ number_format($order->estimated_cost ?? 0, 2) }}</td>
            <td class="label">स्वीकृत लागत (Approved Cost):</td>
            <td>Rs. {{ number_format($order->approved_cost ?? 0, 2) }}</td>
        </tr>
    </table>

    @if($order->remarks)
    <p><strong>टिप्पणी (Remarks):</strong> {{ $order->remarks }}</p>
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
