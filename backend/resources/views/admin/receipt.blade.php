<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt #{{ $receipt->receipt_no }} - Mahamilon Society</title>
    <style>
        :root{--navy:#092f6d;--navy2:#061f49;--red:#d71920;--bg:#f3f6fb;--line:#dfe6f0;--muted:#64748b;--green:#087443;--gold:#e2a829}
        *{box-sizing:border-box}
        body{margin:0;font:14px/1.45 Inter,system-ui,sans-serif;background:var(--bg);color:#15213a;padding:24px 16px}
        .container{max-width:680px;margin:0 auto;background:white;border:1px solid var(--line);border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden}
        .header{background:linear-gradient(135deg,var(--navy2),var(--navy));color:white;padding:20px 24px;display:flex;justify-content:space-between;align-items:center}
        .header-brand{display:flex;align-items:center;gap:14px}
        .header h1{margin:0;font-size:20px;font-weight:800;letter-spacing:0.3px}
        .header p{margin:3px 0 0;font-size:12px;color:#bcd0f0}
        .badge{display:inline-block;padding:5px 12px;border-radius:20px;background:#e8f7ee;color:var(--green);font-weight:800;font-size:12px;letter-spacing:0.5px}
        .content{padding:28px}
        .receipt-no{font-size:16px;font-weight:750;color:var(--navy);margin-bottom:16px}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px}
        .field{background:#f8fafc;border:1px solid var(--line);border-radius:8px;padding:12px}
        .field label{display:block;font-size:11px;text-transform:uppercase;color:var(--muted);font-weight:700;margin-bottom:4px}
        .field strong{font-size:15px;color:#15213a}
        .amount-box{background:#f0f5ff;border:2px dashed #93b4f0;border-radius:8px;padding:16px;text-align:center;margin-bottom:24px}
        .amount-box label{font-size:12px;color:var(--navy);font-weight:700;text-transform:uppercase}
        .amount-box strong{display:block;font-size:28px;color:var(--navy);margin-top:4px}
        .notes{background:#fffbeb;border:1px solid #fef08a;border-radius:8px;padding:12px;font-size:13px;color:#854d0e;margin-bottom:24px}
        .footer{border-top:1px solid var(--line);padding-top:20px;display:flex;justify-content:space-between;align-items:flex-end}
        .sign{text-align:center;width:180px}
        .sign-line{border-top:1px solid #15213a;margin-top:40px;padding-top:4px;font-size:11px;font-weight:700}
        .actions{margin-top:24px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
        .btn{border:0;padding:10px 18px;border-radius:8px;background:var(--navy);color:white;font-weight:750;text-decoration:none;cursor:pointer;font-size:13px;display:inline-flex;align-items:center;gap:6px}
        .btn-green{background:#25D366}
        .btn-secondary{background:#64748b}
        @media print{
            @page{size:A4 portrait;margin:12mm}
            body{background:white;padding:0}
            .container{border:1px solid #ddd;box-shadow:none;max-width:100%}
            .actions{display:none !important}
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-brand">
            <!-- Official Society Emblem Logo -->
            <svg width="48" height="48" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0">
                <circle cx="100" cy="100" r="95" fill="#082352" stroke="#f8d675" stroke-width="4"/>
                <circle cx="100" cy="100" r="88" fill="none" stroke="#ffffff" stroke-width="1" stroke-dasharray="3,3" opacity="0.6"/>
                <g transform="translate(100, 100)">
                    <path d="M 0 -38 C 24 -38 32 -30 32 -14 C 32 16 0 34 0 38 C 0 34 -32 16 -32 -14 C -32 -30 -24 -38 0 -38 Z" fill="#092f6d" stroke="#f8d675" stroke-width="2.5"/>
                    <path d="M 0 -24 L 0 16 M -18 -14 L 18 -14" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
                    <path d="M -18 -14 L -23 -4 L -13 -4 Z" fill="none" stroke="#f8d675" stroke-width="1.5"/>
                    <path d="M -26 -4 C -26 0 -10 0 -10 -4 Z" fill="#f8d675"/>
                    <path d="M 18 -14 L 13 -4 L 23 -4 Z" fill="none" stroke="#f8d675" stroke-width="1.5"/>
                    <path d="M 10 -4 C 10 0 26 0 26 -4 Z" fill="#f8d675"/>
                    <text x="0" y="24" fill="#ffffff" font-size="10" font-family="Inter, system-ui, sans-serif" font-weight="800" text-anchor="middle" letter-spacing="1.2">MMS</text>
                </g>
            </svg>
            <div>
                <h1>Mahamilon Society</h1>
                <p>Official Society Payment Receipt</p>
            </div>
        </div>
        <div>
            <span class="badge">PAID</span>
        </div>
    </div>
    <div class="content">
        <div class="receipt-no">Receipt: {{ $receipt->receipt_no }}</div>
        <div class="amount-box">
            <label>Amount Received</label>
            <strong>₹{{ number_format((float) $receipt->amount, 2) }}</strong>
        </div>
        <div class="grid">
            <div class="field">
                <label>Member Name</label>
                <strong>{{ $receipt->member_name }}</strong>
            </div>
            <div class="field">
                <label>Member ID</label>
                <strong>{{ $receipt->member_no }}</strong>
            </div>
            <div class="field">
                <label>Payment Category</label>
                <strong>{{ ucwords(str_replace('_', ' ', $receipt->category)) }}</strong>
            </div>
            <div class="field">
                <label>Payment Mode</label>
                <strong>{{ strtoupper($receipt->payment_mode) }}</strong>
            </div>
            <div class="field">
                <label>Date & Time</label>
                <strong>{{ \Carbon\Carbon::parse($receipt->created_at)->format('d M Y, h:i A') }}</strong>
            </div>
            <div class="field">
                <label>Transaction Ref</label>
                <strong>{{ $receipt->transaction_reference ?: 'Direct Entry' }}</strong>
            </div>
        </div>
        @if($receipt->notes)
            <div class="notes">
                <strong>Notes:</strong> {{ $receipt->notes }}
            </div>
        @endif
        <div class="footer">
            <div style="font-size:11px;color:var(--muted)">
                Generated on {{ now()->format('d M Y') }}<br>
                Issued by: {{ $receipt->issued_by_name ?: 'System Administrator' }}
            </div>
            <div class="sign">
                <div class="sign-line">Authorized Signatory</div>
            </div>
        </div>
    </div>
</div>

<div class="actions">
    <button class="btn" onclick="window.print()">🖨️ Print Receipt</button>
    <button class="btn" onclick="window.print()">📄 Save as PDF</button>
    <a class="btn btn-secondary" href="/admin/receipts/{{ $receipt->id }}/download">📥 Download Receipt</a>
    @php
        $cleanPhone = preg_replace('/[^0-9]/', '', $receipt->member_phone ?? '');
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }
        $waMsg = urlencode("Mahamilon Society Official Receipt\nReceipt No: {$receipt->receipt_no}\nMember: {$receipt->member_name} ({$receipt->member_no})\nCategory: " . ucwords(str_replace('_', ' ', $receipt->category)) . "\nAmount: Rs. " . number_format((float) $receipt->amount, 2) . "\nPayment Mode: " . strtoupper($receipt->payment_mode) . "\nDate: " . \Carbon\Carbon::parse($receipt->created_at)->format('d M Y, h:i A') . "\nTransaction Ref: " . ($receipt->transaction_reference ?: 'Direct Entry') . "\nStatus: PAID");
        $waUrl = !empty($cleanPhone) ? "https://wa.me/{$cleanPhone}?text={$waMsg}" : "https://wa.me/?text={$waMsg}";
    @endphp
    <a class="btn btn-green" href="{{ $waUrl }}" target="_blank">💬 Send via WhatsApp</a>
    <a class="btn btn-secondary" href="/admin#receipts">Back to Dashboard</a>
</div>
</body>
</html>
