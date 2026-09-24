<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $member->name }} ({{ $member->member_no }}) — Member 360° Profile | Mahamilon</title>
    <style>
        :root{--navy:#092f6d;--navy2:#061f49;--red:#d71920;--bg:#f3f6fb;--line:#dfe6f0;--muted:#64748b;--green:#087443;--yellow:#b45309}
        *{box-sizing:border-box}body{margin:0;font:14px/1.45 Inter,system-ui,sans-serif;background:var(--bg);color:#15213a}
        .container{max-width:1400px;margin:0 auto;padding:24px 20px 80px}
        header.profile-top{background:white;border:1px solid var(--line);border-radius:14px;padding:22px 26px;margin-bottom:20px;box-shadow:0 4px 20px #092f6d0a}
        .top-row{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px}
        .user-summary{display:flex;align-items:center;gap:16px}
        .avatar{width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--navy),#1e40af);color:white;display:grid;place-items:center;font-size:20px;font-weight:900}
        .user-meta h1{margin:0 0 4px;font-size:22px;color:var(--navy)}
        .user-meta .sub{color:var(--muted);font-size:13px;display:flex;gap:10px;align-items:center;flex-wrap:wrap}
        .badge{display:inline-block;padding:3px 9px;border-radius:14px;font-weight:800;font-size:11px;text-transform:capitalize}
        .badge.active,.badge.approved,.badge.paid,.badge.posted{background:#dff7e9;color:var(--green)}
        .badge.pending,.badge.scheduled,.badge.due,.badge.notice_period{background:#fef3c7;color:var(--yellow)}
        .badge.rejected,.badge.overdue,.badge.closed{background:#fee2e2;color:var(--red)}
        .badge.partial{background:#e0e7ff;color:#3730a3}
        .actions-bar{display:flex;gap:8px;flex-wrap:wrap}
        .button,button{border:0;padding:8px 14px;border-radius:7px;background:var(--navy);color:white;text-decoration:none;font-weight:750;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px}
        .button.secondary{background:#f1f5f9;color:#334155;border:1px solid #cbd5e1}
        .button.secondary:hover{background:#e2e8f0}
        .button.danger{background:var(--red)}
        .button.success{background:var(--green)}
        .kpi-strip{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:24px}
        .kpi-card{background:white;border:1px solid var(--line);border-radius:11px;padding:16px;box-shadow:0 2px 10px #092f6d06}
        .kpi-card span{display:block;color:var(--muted);font-size:12px;margin-bottom:4px;font-weight:600}
        .kpi-card strong{font-size:22px;color:var(--navy);font-weight:800}
        .kpi-card small{color:var(--muted);font-size:11px}
        .tab-nav{display:flex;gap:6px;overflow-x:auto;border-bottom:1px solid var(--line);padding-bottom:8px;margin-bottom:20px}
        .tab-btn{padding:8px 15px;border-radius:7px;background:transparent;border:0;color:var(--muted);font-weight:750;font-size:13px;cursor:pointer;white-space:nowrap}
        .tab-btn.active{background:var(--navy);color:white}
        .panel-card{background:white;border:1px solid var(--line);border-radius:12px;padding:22px;margin-bottom:20px;box-shadow:0 3px 12px #092f6d06}
        .panel-card h2{margin:0 0 16px;font-size:17px;color:var(--navy);display:flex;align-items:center;justify-content:space-between}
        .panel-card h2 small{font-size:12px;color:var(--muted);font-weight:normal}
        .field-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}
        .field-item label{display:block;font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.03em;margin-bottom:3px}
        .field-item div{font-size:14px;font-weight:600;color:#1e293b}
        table{width:100%;border-collapse:collapse;margin-top:10px}
        th,td{text-align:left;padding:10px 12px;border-bottom:1px solid #eef2f6;font-size:13px}
        th{font-size:11px;text-transform:uppercase;color:var(--muted);letter-spacing:.04em;background:#f8fafc}
        tr:hover td{background:#f8fafc}
        .amount-pos{color:var(--green);font-weight:700}
        .amount-neg{color:var(--red);font-weight:700}
        .empty-state{text-align:center;padding:28px 10px;color:var(--muted);font-style:italic}
        @media(max-width:768px){.top-row{flex-direction:column;align-items:flex-start}.container{padding:14px 10px}}
    </style>
</head>
<body>
<div class="container">
    <!-- Breadcrumb & Back -->
    <div style="margin-bottom:14px;display:flex;justify-content:space-between;align-items:center">
        <a href="/admin#members" class="button secondary" style="font-size:12px;padding:6px 12px">← Back to Dashboard Directory</a>
        <span style="color:var(--muted);font-size:12px">Member 360° View · Section 2.2</span>
    </div>

    <!-- Header Summary Card -->
    <header class="profile-top">
        <div class="top-row">
            <div class="user-summary">
                <div class="avatar">
                    {{ strtoupper(substr($member->name, 0, 1)) }}{{ strtoupper(substr(strrchr($member->name, ' ') ?: $member->name, 1, 1)) }}
                </div>
                <div class="user-meta">
                    <h1>{{ $member->name }}</h1>
                    <div class="sub">
                        <span><b>Member No:</b> {{ $member->member_no }}</span>
                        <span>•</span>
                        <span><b>Phone:</b> {{ $member->phone }}</span>
                        <span>•</span>
                        <span><b>Status:</b> <span class="badge {{ $member->user_status }}">{{ $member->user_status }}</span></span>
                        <span>•</span>
                        <span><b>KYC:</b> <span class="badge {{ $member->kyc_status }}">{{ str_replace('_', ' ', $member->kyc_status) }}</span></span>
                        <span>•</span>
                        <span><b>MCS Score:</b> <strong style="color:var(--navy)">{{ $kpi['mcsScore'] }}</strong>/900</span>
                    </div>
                </div>
            </div>
            <div class="actions-bar">
                <a href="/admin/statements/{{ $member->id }}/print" target="_blank" class="button">📄 Official Statement</a>
                <a href="/admin/members/{{ $member->id }}/card" target="_blank" class="button secondary">🪪 Print ID Card</a>
                <a href="/admin/members/{{ $member->id }}/kyc-folder" class="button secondary">📁 Download KYC Folder</a>
                @if(in_array('member.approve', $permissions, true) || auth()->user()->role === 'super_admin')
                    @if($member->user_status === 'pending')
                    <form method="post" action="/admin/members/{{ $member->id }}/decision" style="display:inline">@csrf
                        <input type="hidden" name="decision" value="approve"><button class="button success">Approve Member</button>
                    </form>
                    <form method="post" action="/admin/members/{{ $member->id }}/decision" style="display:inline">@csrf
                        <input type="hidden" name="decision" value="reject"><button class="button danger">Reject Member</button>
                    </form>
                    @endif
                @endif
            </div>
        </div>
    </header>

    <!-- Top KPI Strip -->
    <div class="kpi-strip">
        <div class="kpi-card" style="border-left:4px solid var(--green)">
            <span>Savings Deposits</span>
            <strong>₹{{ number_format($kpi['totalSavings'], 2) }}</strong>
            <small>{{ $savingsAccounts->count() }} active account(s)</small>
        </div>
        <div class="kpi-card" style="border-left:4px solid var(--navy)">
            <span>Fixed Deposits (FD)</span>
            <strong>₹{{ number_format($kpi['totalFd'], 2) }}</strong>
            <small>{{ $fdAccounts->count() }} certificate(s)</small>
        </div>
        <div class="kpi-card" style="border-left:4px solid #4f46e5">
            <span>Recurring Deposits (RD)</span>
            <strong>₹{{ number_format($kpi['totalRd'], 2) }}</strong>
            <small>{{ $rdAccounts->count() }} account(s)</small>
        </div>
        <div class="kpi-card" style="border-left:4px solid var(--red)">
            <span>Active Loan Principal</span>
            <strong>₹{{ number_format($kpi['totalLoanOutstanding'], 2) }}</strong>
            <small>{{ $loans->whereIn('status', ['approved','disbursed'])->count() }} active loan(s)</small>
        </div>
        <div class="kpi-card" style="border-left:4px solid var(--yellow)">
            <span>Profit / Interest Earned</span>
            <strong>₹{{ number_format($kpi['totalProfitEarned'], 2) }}</strong>
            <small>Total interest credited</small>
        </div>
        <div class="kpi-card" style="border-left:4px solid {{ $kpi['totalPenaltiesDue'] > 0 ? 'var(--red)' : 'var(--line)' }}">
            <span>Penalties Due</span>
            <strong style="color:{{ $kpi['totalPenaltiesDue'] > 0 ? 'var(--red)' : 'var(--navy)' }}">₹{{ number_format($kpi['totalPenaltiesDue'], 2) }}</strong>
            <small>Fine/overdue charges</small>
        </div>
    </div>

    <!-- Section Navigation Tabs -->
    <nav class="tab-nav">
        <button class="tab-btn active" onclick="showTab('tab-personal')">1. Personal & Nominee</button>
        <button class="tab-btn" onclick="showTab('tab-kyc')">2. KYC & Documents</button>
        <button class="tab-btn" onclick="showTab('tab-savings')">3. Savings Deposits ({{ $savingsAccounts->count() }})</button>
        <button class="tab-btn" onclick="showTab('tab-fd')">4. Fixed Deposits ({{ $fdAccounts->count() }})</button>
        <button class="tab-btn" onclick="showTab('tab-rd')">5. Recurring Deposits ({{ $rdAccounts->count() }})</button>
        <button class="tab-btn" onclick="showTab('tab-loans')">6. Loans & EMIs ({{ $loans->count() }})</button>
        <button class="tab-btn" onclick="showTab('tab-payments')">7. Payments & Receipts ({{ $payments->count() + $receipts->count() }})</button>
        <button class="tab-btn" onclick="showTab('tab-withdrawals')">8. Withdrawals ({{ $withdrawals->count() }})</button>
        <button class="tab-btn" onclick="showTab('tab-profit')">9. Profit / Interest</button>
        <button class="tab-btn" onclick="showTab('tab-penalties')">10. Fines & Penalties</button>
        <button class="tab-btn" onclick="showTab('tab-ledger')">11. Transaction History ({{ $transactions->count() }})</button>
    </nav>

    <!-- TAB 1: Personal & Nominee Details -->
    <section id="tab-personal" class="tab-content">
        <div class="panel-card">
            <h2>Personal Details & Membership Metadata <small>Registered: {{ \Carbon\Carbon::parse($member->registered_at)->format('d M Y, h:i A') }}</small></h2>
            <div class="field-grid">
                <div class="field-item"><label>Full Name</label><div>{{ $member->name }}</div></div>
                <div class="field-item"><label>Membership ID</label><div>{{ $member->member_no }}</div></div>
                <div class="field-item"><label>Mobile Phone</label><div>{{ $member->phone }}</div></div>
                <div class="field-item"><label>Email Address</label><div>{{ $member->email ?: 'Not provided' }}</div></div>
                <div class="field-item"><label>Date of Birth</label><div>{{ $member->date_of_birth ? \Carbon\Carbon::parse($member->date_of_birth)->format('d M Y') . ' (' . $age . ' years)' : 'Not provided' }}</div></div>
                <div class="field-item"><label>Occupation</label><div>{{ $member->occupation ?: 'Not provided' }}</div></div>
                <div class="field-item"><label>Residential Address</label><div>{{ $member->address ?: 'Not provided' }}</div></div>
                <div class="field-item"><label>Membership Approval</label><div>{{ $member->approved_at ? \Carbon\Carbon::parse($member->approved_at)->format('d M Y') . ' by ' . ($member->approver_name ?: 'System') : 'Pending approval' }}</div></div>
            </div>
        </div>

        <div class="panel-card">
            <h2>Nominee Information</h2>
            <div class="field-grid">
                <div class="field-item"><label>Nominee Name</label><div>{{ $member->nominee_name ?: 'Not registered' }}</div></div>
                <div class="field-item"><label>Member Relationship</label><div>Registered nominee for all accounts and claims</div></div>
                <div class="field-item"><label>Nominee Rights</label><div>Entitled to account balance & settlement in event of unforeseen casualty</div></div>
            </div>
        </div>
    </section>

    <!-- TAB 2: KYC & Documents -->
    <section id="tab-kyc" class="tab-content" style="display:none">
        <div class="panel-card">
            <h2>KYC Status & Identification Details <small>Overall Status: <span class="badge {{ $member->kyc_status }}">{{ str_replace('_', ' ', $member->kyc_status) }}</span></small></h2>
            <div class="field-grid" style="margin-bottom:20px">
                <div class="field-item"><label>Aadhaar Last 4</label><div>{{ $member->aadhaar_last4 ? 'XXXX-XXXX-' . $member->aadhaar_last4 : 'Not submitted' }}</div></div>
                <div class="field-item"><label>PAN Last 4</label><div>{{ $member->pan_last4 ? 'XXXXXX' . $member->pan_last4 : 'Not submitted' }}</div></div>
                <div class="field-item"><label>Approval Status</label><div><span class="badge {{ $member->kyc_status }}">{{ strtoupper(str_replace('_', ' ', $member->kyc_status)) }}</span></div></div>
            </div>

            <h3 style="margin:20px 0 10px;font-size:15px;color:var(--navy)">Uploaded KYC Documents</h3>
            @if($documents->isEmpty())
                <div class="empty-state">No KYC documents uploaded yet.</div>
            @else
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr><th>Document Type</th><th>Status</th><th>Uploaded On</th><th>Review Note</th><th>Reviewed By</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                            <tr>
                                <td><b>{{ ucwords(str_replace('_', ' ', $doc->type)) }}</b><br><small class="text-muted">{{ $doc->mime }}</small></td>
                                <td><span class="badge {{ $doc->status }}">{{ $doc->status }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($doc->created_at)->format('d M Y, h:i A') }}</td>
                                <td>{{ $doc->review_note ?: '—' }}</td>
                                <td>{{ $doc->reviewer_name ?: 'Pending review' }}</td>
                                <td>
                                    <a href="/admin/kyc/{{ $doc->id }}/download" class="button secondary" style="font-size:12px;padding:4px 8px">Download / View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <!-- TAB 3: Savings Deposit Accounts -->
    <section id="tab-savings" class="tab-content" style="display:none">
        <div class="panel-card">
            <h2>Savings Accounts (Deposit) <small>Total Balance: ₹{{ number_format($kpi['totalSavings'], 2) }}</small></h2>
            @if($savingsAccounts->isEmpty())
                <div class="empty-state">No active savings accounts found for this member.</div>
            @else
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr><th>Account No</th><th>Scheme</th><th>Current Balance</th><th>Annual Interest</th><th>Status</th><th>Opened Date</th><th>Last Interest Posted</th></tr>
                        </thead>
                        <tbody>
                            @foreach($savingsAccounts as $acc)
                            <tr>
                                <td><b>{{ $acc->account_no }}</b></td>
                                <td>{{ $acc->scheme_name }}</td>
                                <td class="amount-pos">₹{{ number_format($acc->balance, 2) }}</td>
                                <td>{{ $acc->effective_rate }}%</td>
                                <td><span class="badge {{ $acc->status }}">{{ $acc->status }}</span></td>
                                <td>{{ $acc->opened_on ? \Carbon\Carbon::parse($acc->opened_on)->format('d M Y') : '—' }}</td>
                                <td>{{ $acc->last_interest_posted_on ? \Carbon\Carbon::parse($acc->last_interest_posted_on)->format('d M Y') : 'Pending cycle' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <!-- TAB 4: Fixed Deposits (FD) -->
    <section id="tab-fd" class="tab-content" style="display:none">
        <div class="panel-card">
            <h2>Fixed Deposits (FD) <small>Total Active: ₹{{ number_format($kpi['totalFd'], 2) }}</small></h2>
            @if($fdAccounts->isEmpty())
                <div class="empty-state">No fixed deposits on record for this member.</div>
            @else
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr><th>Certificate No</th><th>Account No</th><th>Principal Deposited</th><th>Annual Rate</th><th>Tenure</th><th>Maturity Amount</th><th>Maturity Date</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($fdAccounts as $fd)
                            <tr>
                                <td><b>{{ $fd->certificate_no ?: 'FD-'.str_pad($fd->id, 6, '0', STR_PAD_LEFT) }}</b></td>
                                <td>{{ $fd->account_no }}</td>
                                <td><b>₹{{ number_format($fd->principal ?: $fd->balance, 2) }}</b></td>
                                <td>{{ $fd->effective_rate }}%</td>
                                <td>{{ $fd->term_months }} Months</td>
                                <td class="amount-pos">₹{{ number_format($fd->maturity_amount ?: $fd->balance, 2) }}</td>
                                <td>{{ $fd->matures_on ? \Carbon\Carbon::parse($fd->matures_on)->format('d M Y') : '—' }}</td>
                                <td><span class="badge {{ $fd->status }}">{{ $fd->status }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <!-- TAB 5: Recurring Deposits (RD) -->
    <section id="tab-rd" class="tab-content" style="display:none">
        <div class="panel-card">
            <h2>Recurring Deposits (RD) <small>Total Accumulated: ₹{{ number_format($kpi['totalRd'], 2) }}</small></h2>
            @if($rdAccounts->isEmpty())
                <div class="empty-state">No recurring deposits found for this member.</div>
            @else
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr><th>Certificate / Acc No</th><th>Scheme</th><th>Monthly Installment</th><th>Rate</th><th>Tenure</th><th>Maturity Target</th><th>Current Balance</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($rdAccounts as $rd)
                            <tr>
                                <td><b>{{ $rd->certificate_no ?: $rd->account_no }}</b><br><small class="text-muted">{{ $rd->account_no }}</small></td>
                                <td>{{ $rd->scheme_name }}</td>
                                <td><b>₹{{ number_format($rd->monthly_commitment, 2) }}</b>/mo</td>
                                <td>{{ $rd->effective_rate }}%</td>
                                <td>{{ $rd->term_months }} Months</td>
                                <td class="amount-pos">₹{{ number_format($rd->maturity_amount, 2) }}</td>
                                <td>₹{{ number_format($rd->balance, 2) }}</td>
                                <td><span class="badge {{ $rd->status }}">{{ $rd->status }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h3 style="margin:24px 0 12px;font-size:15px;color:var(--navy)">RD Installments Schedule & Status</h3>
                @if($rdInstallments->isEmpty())
                    <div class="empty-state">No RD installment schedule generated yet.</div>
                @else
                    <div style="overflow-x:auto;max-height:400px">
                        <table>
                            <thead>
                                <tr><th>Inst #</th><th>Account No</th><th>Due Date</th><th>Amount Due</th><th>Penalty Due</th><th>Paid Amount</th><th>Status</th><th>Paid At</th></tr>
                            </thead>
                            <tbody>
                                @foreach($rdInstallments as $inst)
                                <tr>
                                    <td>#{{ $inst->installment_no }}</td>
                                    <td>{{ $inst->account_no }}</td>
                                    <td>{{ \Carbon\Carbon::parse($inst->due_on)->format('d M Y') }}</td>
                                    <td>₹{{ number_format($inst->amount_due, 2) }}</td>
                                    <td class="{{ $inst->penalty_due > 0 ? 'amount-neg' : '' }}">₹{{ number_format($inst->penalty_due, 2) }}</td>
                                    <td class="amount-pos">₹{{ number_format($inst->paid_amount, 2) }}</td>
                                    <td><span class="badge {{ $inst->status }}">{{ $inst->status }}</span></td>
                                    <td>{{ $inst->paid_at ? \Carbon\Carbon::parse($inst->paid_at)->format('d M Y') : '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>
    </section>

    <!-- TAB 6: Loans & EMI Schedule -->
    <section id="tab-loans" class="tab-content" style="display:none">
        <div class="panel-card">
            <h2>Loan Applications & Active Borrowings <small>Outstanding Principal: ₹{{ number_format($kpi['totalLoanOutstanding'], 2) }}</small></h2>
            @if($loans->isEmpty())
                <div class="empty-state">No loans recorded for this member.</div>
            @else
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr><th>Application No</th><th>Loan Type</th><th>Sanctioned Amount</th><th>Interest Rate</th><th>Tenure</th><th>Outstanding</th><th>Repayment Starts</th><th>Next Due</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($loans as $loan)
                            <tr>
                                <td><b>{{ $loan->application_no }}</b></td>
                                <td>{{ ucwords($loan->loan_type) }}</td>
                                <td>₹{{ number_format($loan->sanctioned_amount ?: $loan->amount, 2) }}</td>
                                <td>{{ $loan->annual_interest_rate ?: '12.00' }}%</td>
                                <td>{{ $loan->tenure_months }} Mos</td>
                                <td class="amount-neg">₹{{ number_format($loan->outstanding_principal ?: 0, 2) }}</td>
                                <td>{{ $loan->repayment_started_on ? \Carbon\Carbon::parse($loan->repayment_started_on)->format('d M Y') : '—' }}</td>
                                <td>{{ $loan->next_due_on ? \Carbon\Carbon::parse($loan->next_due_on)->format('d M Y') : '—' }}</td>
                                <td><span class="badge {{ $loan->status }}">{{ $loan->status }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h3 style="margin:24px 0 12px;font-size:15px;color:var(--navy)">Complete EMI Schedule (Amortization & Counter Collection)</h3>
                @if($loanInstallments->isEmpty())
                    <div class="empty-state">No EMI installments scheduled for this member's loans.</div>
                @else
                    <div style="overflow-x:auto;max-height:420px">
                        <table>
                            <thead>
                                <tr><th>Loan No</th><th>Inst #</th><th>Due Date</th><th>Principal Due</th><th>Interest Due</th><th>Penalty Due</th><th>Total Due</th><th>Paid Amount</th><th>Status</th><th>Paid Date</th></tr>
                            </thead>
                            <tbody>
                                @foreach($loanInstallments as $emi)
                                <tr>
                                    <td><b>{{ $emi->application_no }}</b></td>
                                    <td>#{{ $emi->installment_no }}</td>
                                    <td>{{ \Carbon\Carbon::parse($emi->due_on)->format('d M Y') }}</td>
                                    <td>₹{{ number_format($emi->principal_due, 2) }}</td>
                                    <td>₹{{ number_format($emi->interest_due, 2) }}</td>
                                    <td class="{{ $emi->penalty_due > 0 ? 'amount-neg' : '' }}">₹{{ number_format($emi->penalty_due, 2) }}</td>
                                    <td><b>₹{{ number_format($emi->principal_due + $emi->interest_due + $emi->penalty_due, 2) }}</b></td>
                                    <td class="amount-pos">₹{{ number_format($emi->paid_amount, 2) }}</td>
                                    <td><span class="badge {{ $emi->status }}">{{ $emi->status }}</span></td>
                                    <td>{{ $emi->paid_at ? \Carbon\Carbon::parse($emi->paid_at)->format('d M Y') : '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>
    </section>

    <!-- TAB 7: Payments & Receipts -->
    <section id="tab-payments" class="tab-content" style="display:none">
        <div class="panel-card">
            <h2>Official Payment Receipts (Section 18) <small>Total Issued: {{ $receipts->count() }}</small></h2>
            @if($receipts->isEmpty())
                <div class="empty-state">No official payment receipts issued yet.</div>
            @else
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr><th>Receipt No</th><th>Category</th><th>Amount</th><th>Mode</th><th>Payer Name</th><th>Notes</th><th>Date</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @foreach($receipts as $rcp)
                            <tr>
                                <td><b>{{ $rcp->receipt_no }}</b></td>
                                <td><span class="badge" style="background:#e0f2fe;color:#0369a1">{{ ucwords(str_replace('_', ' ', $rcp->category)) }}</span></td>
                                <td class="amount-pos">₹{{ number_format($rcp->amount, 2) }}</td>
                                <td>{{ strtoupper($rcp->payment_mode) }}</td>
                                <td>{{ $rcp->payer_name ?: $member->name }}</td>
                                <td>{{ $rcp->notes ?: '—' }}</td>
                                <td>{{ \Carbon\Carbon::parse($rcp->created_at)->format('d M Y, h:i A') }}</td>
                                <td>
                                    <a href="/admin/receipts/{{ $rcp->id }}/view" target="_blank" class="button secondary" style="font-size:12px;padding:4px 8px">View / Print Voucher</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="panel-card">
            <h2>Counter & Online Submissions (Payments Table)</h2>
            @if($payments->isEmpty())
                <div class="empty-state">No manual payment submissions found.</div>
            @else
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr><th>Reference</th><th>Account No</th><th>Purpose</th><th>Amount</th><th>UTR</th><th>Status</th><th>Submitted At</th><th>Reviewer</th></tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $p)
                            <tr>
                                <td><b>{{ $p->reference }}</b></td>
                                <td>{{ $p->account_no }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $p->purpose)) }}</td>
                                <td class="amount-pos">₹{{ number_format($p->amount, 2) }}</td>
                                <td>{{ $p->utr ?: '—' }}</td>
                                <td><span class="badge {{ $p->status }}">{{ $p->status }}</span></td>
                                <td>{{ $p->submitted_at ? \Carbon\Carbon::parse($p->submitted_at)->format('d M Y, h:i A') : '—' }}</td>
                                <td>{{ $p->reviewer_name ?: '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <!-- TAB 8: Withdrawals -->
    <section id="tab-withdrawals" class="tab-content" style="display:none">
        <div class="panel-card">
            <h2>Withdrawals History <small>Debit entries across accounts</small></h2>
            @if($withdrawals->isEmpty())
                <div class="empty-state">No withdrawal transactions found for this member.</div>
            @else
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr><th>Date</th><th>Reference</th><th>Account No</th><th>Amount</th><th>Description</th><th>Processed By</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($withdrawals as $w)
                            <tr>
                                <td>{{ $w->posted_at ? \Carbon\Carbon::parse($w->posted_at)->format('d M Y, h:i A') : \Carbon\Carbon::parse($w->created_at)->format('d M Y') }}</td>
                                <td><b>{{ substr($w->reference, 0, 14) }}...</b></td>
                                <td>{{ $w->account_no }}</td>
                                <td class="amount-neg">-₹{{ number_format($w->amount, 2) }}</td>
                                <td>{{ $w->description }}</td>
                                <td>{{ $w->creator_name ?: 'Counter Staff' }}</td>
                                <td><span class="badge {{ $w->status }}">{{ $w->status }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <!-- TAB 9: Profit & Interest -->
    <section id="tab-profit" class="tab-content" style="display:none">
        <div class="panel-card">
            <h2>Profit & Interest Postings <small>Total Credited: ₹{{ number_format($kpi['totalProfitEarned'], 2) }}</small></h2>
            @if($interestPostings->isEmpty())
                <div class="empty-state">No automated interest or dividend postings recorded yet.</div>
            @else
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr><th>Period Start</th><th>Period End</th><th>Account No</th><th>Opening Balance</th><th>Annual Rate</th><th>Interest / Profit Credited</th><th>Posted On</th></tr>
                        </thead>
                        <tbody>
                            @foreach($interestPostings as $ip)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($ip->period_start)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($ip->period_end)->format('d M Y') }}</td>
                                <td>{{ $ip->account_no }}</td>
                                <td>₹{{ number_format($ip->opening_balance, 2) }}</td>
                                <td>{{ $ip->annual_rate }}%</td>
                                <td class="amount-pos">+₹{{ number_format($ip->interest_amount, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($ip->created_at)->format('d M Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <!-- TAB 10: Fines & Penalties -->
    <section id="tab-penalties" class="tab-content" style="display:none">
        <div class="panel-card">
            <h2>Fines & Overdue Penalties <small>Total Due: ₹{{ number_format($kpi['totalPenaltiesDue'], 2) }} | Paid: ₹{{ number_format($kpi['totalPenaltiesPaid'], 2) }}</small></h2>
            @if($loanPenalties->isEmpty() && $rdPenalties->isEmpty())
                <div class="empty-state">No fines or penalties applied to this member. Clean record!</div>
            @else
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr><th>Source / Ref</th><th>Inst #</th><th>Penalty Due</th><th>Penalty Reason / Note</th><th>Due Date</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($loanPenalties as $lp)
                            <tr>
                                <td><b>Loan: {{ $lp->application_no }}</b></td>
                                <td>#{{ $lp->installment_no }}</td>
                                <td class="amount-neg">₹{{ number_format($lp->penalty_due, 2) }}</td>
                                <td>{{ $lp->penalty_note ?: 'EMI overdue charge' }}</td>
                                <td>{{ \Carbon\Carbon::parse($lp->due_on)->format('d M Y') }}</td>
                                <td><span class="badge {{ $lp->status }}">{{ $lp->status }}</span></td>
                            </tr>
                            @endforeach
                            @foreach($rdPenalties as $rp)
                            <tr>
                                <td><b>RD: {{ $rp->account_no }}</b></td>
                                <td>#{{ $rp->installment_no }}</td>
                                <td class="amount-neg">₹{{ number_format($rp->penalty_due, 2) }}</td>
                                <td>RD installment delayed penalty</td>
                                <td>{{ \Carbon\Carbon::parse($rp->due_on)->format('d M Y') }}</td>
                                <td><span class="badge {{ $rp->status }}">{{ $rp->status }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <!-- TAB 11: Complete Transaction History -->
    <section id="tab-ledger" class="tab-content" style="display:none">
        <div class="panel-card">
            <h2>Complete Passbook & Transaction Ledger <small>Total Records: {{ $transactions->count() }}</small></h2>
            @if($transactions->isEmpty())
                <div class="empty-state">No transactions recorded for this member.</div>
            @else
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr><th>Posted Date</th><th>Reference</th><th>Account No</th><th>Scheme</th><th>Type</th><th>Amount</th><th>Description</th><th>Processed By</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $t)
                            <tr>
                                <td>{{ $t->posted_at ? \Carbon\Carbon::parse($t->posted_at)->format('d M Y, h:i A') : \Carbon\Carbon::parse($t->created_at)->format('d M Y') }}</td>
                                <td><b>{{ substr($t->reference, 0, 16) }}...</b></td>
                                <td>{{ $t->account_no }}</td>
                                <td>{{ $t->scheme_name }}</td>
                                <td>
                                    @if($t->direction === 'credit')
                                        <span class="badge" style="background:#dff7e9;color:var(--green)">CREDIT</span>
                                    @else
                                        <span class="badge" style="background:#fee2e2;color:var(--red)">DEBIT</span>
                                    @endif
                                </td>
                                <td class="{{ $t->direction === 'credit' ? 'amount-pos' : 'amount-neg' }}">
                                    {{ $t->direction === 'credit' ? '+' : '-' }}₹{{ number_format($t->amount, 2) }}
                                </td>
                                <td>{{ $t->description }}</td>
                                <td>{{ $t->creator_name ?: 'System' }}</td>
                                <td><span class="badge {{ $t->status }}">{{ $t->status }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
</div>

<script>
    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        const activeSection = document.getElementById(tabId);
        if (activeSection) activeSection.style.display = 'block';
        event.currentTarget.classList.add('active');
    }
</script>
</body>
</html>
