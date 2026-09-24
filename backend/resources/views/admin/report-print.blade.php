<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $reportTitle }} - Mahamilon Society</title>
    <style>
        :root{--navy:#092f6d;--navy2:#061f49;--red:#d71920;--bg:#f3f6fb;--line:#dfe6f0;--muted:#64748b;--green:#087443;--gold:#e2a829}
        *{box-sizing:border-box}
        body{margin:0;font:12px/1.4 Inter,system-ui,sans-serif;background:var(--bg);color:#15213a;padding:24px 16px}
        .container{max-width:1050px;margin:0 auto;background:white;border:1px solid var(--line);border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden}
        .header{background:linear-gradient(135deg,var(--navy2),var(--navy));color:white;padding:20px 28px;display:flex;justify-content:space-between;align-items:center}
        .header-brand{display:flex;align-items:center;gap:14px}
        .header h1{margin:0;font-size:19px;font-weight:800;letter-spacing:0.3px}
        .header p{margin:3px 0 0;font-size:11.5px;color:#bcd0f0}
        .content{padding:24px 28px}
        
        /* Filter Tags Summary */
        .filters-summary{background:#f8fafc;border:1px solid var(--line);border-radius:8px;padding:12px 16px;margin-bottom:18px;display:flex;flex-wrap:wrap;gap:8px;align-items:center}
        .filter-tag{background:#e2e8f0;color:#334155;padding:4px 9px;border-radius:4px;font-size:11px;font-weight:600}
        .filter-tag b{color:var(--navy)}
        
        /* KPI Metrics */
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px}
        .stat-card{background:#f0f5ff;border:1px solid #c7d9f9;border-radius:8px;padding:12px 14px;text-align:center}
        .stat-card label{display:block;font-size:10px;text-transform:uppercase;color:var(--navy);font-weight:750;margin-bottom:3px}
        .stat-card strong{display:block;font-size:18px;color:var(--navy);font-weight:800}

        table{width:100%;border-collapse:collapse;font-size:11.5px;margin-bottom:24px}
        th{background:#f1f5f9;color:#334155;font-weight:750;padding:9px 7px;text-align:left;border-bottom:2px solid var(--line);font-size:10.5px;text-transform:uppercase}
        td{padding:8px 7px;border-bottom:1px solid #e2e8f0;color:#1e293b}
        tr:nth-child(even){background:#fafcff}
        
        .footer{border-top:1px solid var(--line);padding-top:20px;display:flex;justify-content:space-between;align-items:flex-end}
        .sign{text-align:center;width:180px}
        .sign-line{border-top:1px solid #15213a;margin-top:40px;padding-top:4px;font-size:11px;font-weight:700}
        
        .actions{margin-top:24px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
        .btn{border:0;padding:9px 16px;border-radius:8px;background:var(--navy);color:white;font-weight:750;text-decoration:none;cursor:pointer;font-size:12.5px;display:inline-flex;align-items:center;gap:6px}
        .btn-secondary{background:#64748b}

        @media print{
            @page{size:A4 landscape;margin:10mm}
            body{background:white;padding:0}
            .container{border:none;box-shadow:none;max-width:100%}
            .actions{display:none !important}
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-brand">
            <svg width="40" height="40" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0">
                <circle cx="100" cy="100" r="95" fill="#082352" stroke="#f8d675" stroke-width="4"/>
                <g transform="translate(100, 100)">
                    <path d="M 0 -38 C 24 -38 32 -30 32 -14 C 32 16 0 34 0 38 C 0 34 -32 16 -32 -14 C -32 -30 -24 -38 0 -38 Z" fill="#092f6d" stroke="#f8d675" stroke-width="2.5"/>
                    <path d="M 0 -24 L 0 16 M -18 -14 L 18 -14" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
                    <text x="0" y="24" fill="#ffffff" font-size="10" font-family="Inter, system-ui, sans-serif" font-weight="800" text-anchor="middle">MMS</text>
                </g>
            </svg>
            <div>
                <h1>Mahamilon Society</h1>
                <p>{{ $reportTitle }} · Official Operations Report (Section 20)</p>
            </div>
        </div>
        <div style="font-size:11.5px;color:#bcd0f0;text-align:right">
            Generated: {{ now()->format('d M Y, h:i A') }}<br>
            User: {{ auth()->user()->name }}
        </div>
    </div>

    <div class="content">
        <!-- Active Filter Summary -->
        <div class="filters-summary">
            <span style="font-weight:750;color:var(--navy);font-size:11px;text-transform:uppercase">Applied Filters:</span>
            <span class="filter-tag">Date: <b>{{ $filters['from'] ?? 'All Time' }} to {{ $filters['to'] ?? 'Today' }}</b></span>
            @if(!empty($filters['member_id']))
                <span class="filter-tag">Member: <b>{{ $activeMemberName ?? 'ID #'.$filters['member_id'] }}</b></span>
            @endif
            @if(!empty($filters['staff_id']))
                <span class="filter-tag">Staff: <b>{{ $activeStaffName ?? 'ID #'.$filters['staff_id'] }}</b></span>
            @endif
            @if(!empty($filters['payment_mode']) && $filters['payment_mode'] !== 'all')
                <span class="filter-tag">Mode: <b>{{ strtoupper($filters['payment_mode']) }}</b></span>
            @endif
            @if(!empty($filters['status']) && $filters['status'] !== 'all')
                <span class="filter-tag">Status: <b>{{ strtoupper($filters['status']) }}</b></span>
            @endif
            @if(!empty($filters['amount_min']))
                <span class="filter-tag">Min Amt: <b>₹{{ number_format((float)$filters['amount_min'], 2) }}</b></span>
            @endif
            @if(!empty($filters['amount_max']))
                <span class="filter-tag">Max Amt: <b>₹{{ number_format((float)$filters['amount_max'], 2) }}</b></span>
            @endif
        </div>

        <!-- Summary KPI Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <label>Total Records</label>
                <strong>{{ number_format($totalRecords) }}</strong>
            </div>
            <div class="stat-card">
                <label>Total Volume / Turnover</label>
                <strong>₹{{ number_format($totalAmount, 2) }}</strong>
            </div>
            <div class="stat-card">
                <label>Average Transaction</label>
                <strong>₹{{ number_format($avgAmount, 2) }}</strong>
            </div>
        </div>

        <!-- Data Grid Table -->
        <table>
            <thead>
                <tr>
                    @foreach($headers as $h)
                        <th>{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    @foreach((array) $row as $val)
                        <td>{{ $val }}</td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($headers) }}" style="text-align:center;padding:24px;color:var(--muted)">
                        No records found matching the specified filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Signatures & Authority -->
        <div class="footer">
            <div style="font-size:11px;color:var(--muted)">
                Official Society Audit & Compliance Document.<br>
                Certified by Mahamilon Society System Administration.
            </div>
            <div class="sign">
                <div class="sign-line">Authorized Signatory</div>
            </div>
        </div>
    </div>
</div>

<div class="actions">
    <button class="btn" onclick="window.print()">🖨️ Print Report</button>
    <button class="btn" onclick="window.print()">📄 Save as PDF</button>
    <a class="btn btn-secondary" href="/admin/reports/{{ $type }}.csv?{{ http_build_query($filters) }}">📊 Export to Excel (CSV)</a>
    <a class="btn btn-secondary" href="/admin#reports">⬅️ Back to Dashboard</a>
</div>
</body>
</html>
