<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Member Statement - {{ $member->name }} ({{ $member->member_no }}) - Mahamilon Society</title>
    <style>
        :root{--navy:#092f6d;--navy2:#061f49;--red:#d71920;--bg:#f3f6fb;--line:#dfe6f0;--muted:#64748b;--green:#087443;--gold:#e2a829}
        *{box-sizing:border-box}
        body{margin:0;font:13px/1.45 Inter,system-ui,sans-serif;background:var(--bg);color:#15213a;padding:24px 16px}
        .container{max-width:900px;margin:0 auto;background:white;border:1px solid var(--line);border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden}
        .header{background:linear-gradient(135deg,var(--navy2),var(--navy));color:white;padding:20px 28px;display:flex;justify-content:space-between;align-items:center}
        .header-brand{display:flex;align-items:center;gap:14px}
        .header h1{margin:0;font-size:20px;font-weight:800;letter-spacing:0.3px}
        .header p{margin:3px 0 0;font-size:12px;color:#bcd0f0}
        .period-tag{background:rgba(255,255,255,0.15);padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700}
        .content{padding:24px 28px}
        .meta-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;background:#f8fafc;border:1px solid var(--line);border-radius:8px;padding:14px;margin-bottom:20px}
        .meta-item label{display:block;font-size:10.5px;text-transform:uppercase;color:var(--muted);font-weight:750;margin-bottom:2px}
        .meta-item strong{font-size:13.5px;color:#15213a}
        
        /* 8-Metric Scorecard */
        .scorecard-title{font-size:14px;font-weight:750;color:var(--navy);margin:0 0 10px;text-transform:uppercase;letter-spacing:0.5px}
        .scorecard{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:24px}
        .metric-card{background:white;border:1px solid var(--line);border-radius:8px;padding:12px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.03)}
        .metric-card.highlight{background:#f0f5ff;border-color:#93b4f0}
        .metric-card.closing{background:#eefaf2;border-color:#86efac}
        .metric-card label{display:block;font-size:10px;text-transform:uppercase;color:var(--muted);font-weight:750;margin-bottom:4px}
        .metric-card strong{display:block;font-size:16px;color:#15213a;font-weight:800}
        .metric-card.highlight strong{color:var(--navy)}
        .metric-card.closing strong{color:var(--green)}
        
        /* Table */
        table{width:100%;border-collapse:collapse;font-size:12px;margin-bottom:24px}
        th{background:#f1f5f9;color:#334155;font-weight:750;padding:10px 8px;text-align:left;border-bottom:2px solid var(--line);font-size:11px;text-transform:uppercase}
        td{padding:9px 8px;border-bottom:1px solid #e2e8f0;color:#1e293b}
        tr:nth-child(even){background:#fafcff}
        .dr{color:var(--red);font-weight:700}
        .cr{color:var(--green);font-weight:700}
        .bal{font-weight:800;color:var(--navy)}
        
        .footer{border-top:1px solid var(--line);padding-top:20px;display:flex;justify-content:space-between;align-items:flex-end}
        .sign{text-align:center;width:180px}
        .sign-line{border-top:1px solid #15213a;margin-top:40px;padding-top:4px;font-size:11px;font-weight:700}
        .actions{margin-top:24px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
        .btn{border:0;padding:9px 16px;border-radius:8px;background:var(--navy);color:white;font-weight:750;text-decoration:none;cursor:pointer;font-size:12.5px;display:inline-flex;align-items:center;gap:6px}
        .btn-green{background:#25D366}
        .btn-secondary{background:#64748b}

        @media print{
            @page{size:A4 landscape;margin:10mm}
            body{background:white;padding:0}
            .container{border:none;box-shadow:none;max-width:100%}
            .actions{display:none !important}
            .scorecard{gap:6px}
            .metric-card strong{font-size:14px}
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-brand">
            <svg width="44" height="44" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0">
                <circle cx="100" cy="100" r="95" fill="#082352" stroke="#f8d675" stroke-width="4"/>
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
                <p>Official Member Account Statement (Section 19)</p>
            </div>
        </div>
        <div class="period-tag">
            Period: {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}
        </div>
    </div>

    <div class="content">
        <!-- Member & Account Metadata -->
        <div class="meta-grid">
            <div class="meta-item">
                <label>Member Name</label>
                <strong>{{ $member->name }}</strong>
            </div>
            <div class="meta-item">
                <label>Member ID</label>
                <strong>{{ $member->member_no }}</strong>
            </div>
            <div class="meta-item">
                <label>Contact Phone</label>
                <strong>{{ $member->phone ?: 'N/A' }}</strong>
            </div>
            <div class="meta-item">
                <label>Selected Account</label>
                <strong>{{ $accountLabel }}</strong>
            </div>
        </div>

        <!-- 8-Metric Financial Scorecard (Section 19 Specification) -->
        <div class="scorecard-title">Statement Financial Summary</div>
        <div class="scorecard">
            <div class="metric-card highlight">
                <label>1. Opening Balance</label>
                <strong>₹{{ number_format($statement['opening_balance'], 2) }}</strong>
            </div>
            <div class="metric-card">
                <label>2. Deposit</label>
                <strong style="color:var(--green)">+₹{{ number_format($statement['deposits'], 2) }}</strong>
            </div>
            <div class="metric-card">
                <label>3. Withdrawal</label>
                <strong style="color:var(--red)">-₹{{ number_format($statement['withdrawals'], 2) }}</strong>
            </div>
            <div class="metric-card">
                <label>4. Loan (Disbursed)</label>
                <strong>₹{{ number_format($statement['loans'], 2) }}</strong>
            </div>
            <div class="metric-card">
                <label>5. EMI (Repayments)</label>
                <strong>₹{{ number_format($statement['emis'], 2) }}</strong>
            </div>
            <div class="metric-card">
                <label>6. Interest (Credited)</label>
                <strong style="color:var(--green)">+₹{{ number_format($statement['interest'], 2) }}</strong>
            </div>
            <div class="metric-card">
                <label>7. Fine / Penalty</label>
                <strong style="color:#b45309">₹{{ number_format($statement['fines'], 2) }}</strong>
            </div>
            <div class="metric-card closing">
                <label>8. Closing Balance</label>
                <strong>₹{{ number_format($statement['closing_balance'], 2) }}</strong>
            </div>
        </div>

        <!-- Itemized Transaction History -->
        <div class="scorecard-title">Itemized Transaction Ledger</div>
        <table>
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th style="width:90px">Date</th>
                    <th style="width:130px">Reference / Receipt</th>
                    <th style="width:110px">Account / Loan</th>
                    <th>Particulars / Description</th>
                    <th style="width:75px">Mode</th>
                    <th style="width:90px;text-align:right">Debit (Dr)</th>
                    <th style="width:90px;text-align:right">Credit (Cr)</th>
                    <th style="width:100px;text-align:right">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background:#f8fafc;font-weight:700">
                    <td>-</td>
                    <td>{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}</td>
                    <td colspan="4"><i>Opening Balance B/F</i></td>
                    <td>-</td>
                    <td>-</td>
                    <td style="text-align:right" class="bal">₹{{ number_format($statement['opening_balance'], 2) }}</td>
                </tr>
                @forelse($statement['entries'] as $idx => $row)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                    <td>
                        <b>{{ $row['reference'] }}</b>
                        @if(!empty($row['receipt_no']))
                            <br><small style="color:var(--muted)">RCP: {{ $row['receipt_no'] }}</small>
                        @endif
                    </td>
                    <td>{{ $row['account_no'] }}</td>
                    <td>{{ $row['description'] }}</td>
                    <td><span style="font-size:10px;text-transform:uppercase;background:#e2e8f0;padding:2px 5px;border-radius:4px">{{ $row['payment_mode'] }}</span></td>
                    <td style="text-align:right" class="dr">
                        {{ $row['debit'] > 0 ? '₹' . number_format($row['debit'], 2) : '-' }}
                    </td>
                    <td style="text-align:right" class="cr">
                        {{ $row['credit'] > 0 ? '₹' . number_format($row['credit'], 2) : '-' }}
                    </td>
                    <td style="text-align:right" class="bal">₹{{ number_format($row['running_balance'], 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:20px;color:var(--muted)">
                        No transactions recorded for the selected period.
                    </td>
                </tr>
                @endforelse
                <tr style="background:#eefaf2;font-weight:800">
                    <td colspan="6" style="text-align:right;text-transform:uppercase">Closing Balance as of {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}:</td>
                    <td style="text-align:right" class="dr">{{ $statement['withdrawals'] > 0 ? '₹' . number_format($statement['withdrawals'], 2) : '-' }}</td>
                    <td style="text-align:right" class="cr">{{ ($statement['deposits'] + $statement['interest']) > 0 ? '₹' . number_format($statement['deposits'] + $statement['interest'], 2) : '-' }}</td>
                    <td style="text-align:right;font-size:14px" class="bal">₹{{ number_format($statement['closing_balance'], 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Signatures & Authority -->
        <div class="footer">
            <div style="font-size:11px;color:var(--muted);line-height:1.6">
                Computer-generated official statement from Mahamilon Society Core Banking System.<br>
                Generated on: {{ now()->format('d M Y, h:i A') }} · Authorized by: {{ auth()->user()->name }} ({{ ucwords(str_replace('_',' ',auth()->user()->role)) }})
            </div>
            <div class="sign">
                <div class="sign-line">Authorized Signatory</div>
            </div>
        </div>
    </div>
</div>

<div class="actions">
    <button class="btn" onclick="window.print()">🖨️ Print Statement</button>
    <button class="btn" onclick="window.print()">📄 Save as PDF</button>
    <a class="btn btn-secondary" href="/admin/statements/{{ $member->id }}/export?from_date={{ $fromDate }}&to_date={{ $toDate }}&account_id={{ $accountId }}">📊 Export to Excel (CSV)</a>
    @php
        $cleanPhone = preg_replace('/[^0-9]/', '', $member->phone ?? '');
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }
        $waSummary = urlencode("Mahamilon Society - Member Statement\nMember: {$member->name} ({$member->member_no})\nPeriod: " . \Carbon\Carbon::parse($fromDate)->format('d-m-Y') . " to " . \Carbon\Carbon::parse($toDate)->format('d-m-Y') . "\nOpening Balance: Rs. " . number_format($statement['opening_balance'], 2) . "\nDeposits: Rs. " . number_format($statement['deposits'], 2) . "\nWithdrawals: Rs. " . number_format($statement['withdrawals'], 2) . "\nInterest Credited: Rs. " . number_format($statement['interest'], 2) . "\nClosing Balance: Rs. " . number_format($statement['closing_balance'], 2));
        $waUrl = !empty($cleanPhone) ? "https://wa.me/{$cleanPhone}?text={$waSummary}" : "https://wa.me/?text={$waSummary}";
    @endphp
    <a class="btn btn-green" href="{{ $waUrl }}" target="_blank">💬 Send via WhatsApp</a>
    <a class="btn btn-secondary" href="/admin#statements">⬅️ Back to Statements</a>
    <a class="btn btn-secondary" href="/admin/members/{{ $member->id }}">Member Profile</a>
</div>
</body>
</html>
