<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Member Card - {{ $member->name }} - Mahamilon Society</title>
    <style>
        :root{--navy:#092f6d;--navy2:#061f49;--gold:#d4af37;--bg:#f3f6fb;--line:#dfe6f0;--muted:#64748b}
        *{box-sizing:border-box}
        body{margin:0;font:13px/1.4 Inter,system-ui,sans-serif;background:var(--bg);padding:30px 16px;display:flex;flex-direction:column;align-items:center}
        .card{width:360px;height:220px;background:linear-gradient(135deg,#061f49,#0b3a82);border-radius:14px;color:white;padding:16px 18px;position:relative;box-shadow:0 8px 24px rgba(0,0,0,0.15);overflow:hidden;display:flex;flex-direction:column;justify-content:space-between}
        .card::before{content:"";position:absolute;right:-40px;top:-40px;width:160px;height:160px;background:rgba(255,255,255,0.06);border-radius:50%}
        .header{display:flex;justify-content:space-between;align-items:flex-start}
        .title h2{margin:0;font-size:16px;font-weight:800;letter-spacing:0.5px}
        .title p{margin:2px 0 0;font-size:10px;color:#bcd0f0;text-transform:uppercase}
        .badge{background:rgba(255,255,255,0.15);padding:3px 8px;border-radius:12px;font-size:10px;font-weight:700}
        .body{display:flex;gap:14px;align-items:center}
        .avatar{width:56px;height:56px;border-radius:50%;background:#ffffff20;border:2px solid #ffffff60;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;overflow:hidden}
        .avatar img{width:100%;height:100%;object-fit:cover}
        .info h3{margin:0;font-size:15px;font-weight:750}
        .info p{margin:2px 0;font-size:11px;color:#cbd5e1}
        .info .member-no{color:var(--gold);font-weight:800;font-size:12px;letter-spacing:1px}
        .footer{display:flex;justify-content:space-between;align-items:flex-end;border-top:1px solid rgba(255,255,255,0.15);padding-top:8px}
        .dates{font-size:9px;color:#94a3b8}
        .qr-box{width:46px;height:46px;background:white;border-radius:4px;padding:3px}
        .qr-box img{width:100%;height:100%}
        .actions{margin-top:24px;display:flex;gap:10px}
        .btn{border:0;padding:10px 18px;border-radius:8px;background:var(--navy);color:white;font-weight:750;text-decoration:none;cursor:pointer;font-size:13px}
        .btn-secondary{background:#64748b}
        @media print{
            body{background:white;padding:0}
            .card{box-shadow:none}
            .actions{display:none}
        }
    </style>
</head>
<body>

<div class="card">
    <div class="header">
        <div class="title">
            <h2>Mahamilon Society</h2>
            <p>Member Identity Card</p>
        </div>
        <span class="badge">ACTIVE</span>
    </div>
    <div class="body">
        <div class="avatar">
            @if(isset($photo) && $photo->path)
                <img src="/admin/kyc/{{ $photo->id }}/download" alt="{{ $member->name }}">
            @else
                {{ strtoupper(substr($member->name, 0, 1)) }}
            @endif
        </div>
        <div class="info">
            <h3>{{ $member->name }}</h3>
            <div class="member-no">{{ $member->member_no }}</div>
            <p>{{ $member->phone }}</p>
        </div>
    </div>
    <div class="footer">
        <div class="dates">
            Member Since: {{ \Carbon\Carbon::parse($member->created_at)->format('M Y') }}<br>
            Nominee: {{ $member->nominee_name }}
        </div>
        <div class="qr-box">
            @php
                $qrData = urlencode("MEMBER:{$member->member_no}|NAME:{$member->name}|STATUS:ACTIVE");
            @endphp
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ $qrData }}" alt="QR Code">
        </div>
    </div>
</div>

<div class="actions">
    <button class="btn" onclick="window.print()">Print Card</button>
    <a class="btn btn-secondary" href="/admin#members">Back to Dashboard</a>
</div>

</body>
</html>
