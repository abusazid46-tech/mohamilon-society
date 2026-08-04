<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Mahamilon Operations</title>
    <style>
        :root{--navy:#092f6d;--navy2:#061f49;--red:#d71920;--bg:#f3f6fb;--line:#dfe6f0;--muted:#64748b;--green:#087443}
        *{box-sizing:border-box}body{margin:0;font:14px/1.45 Inter,system-ui,sans-serif;background:var(--bg);color:#15213a}
        .shell{display:grid;grid-template-columns:260px 1fr;min-height:100vh}.side{position:sticky;top:0;height:100vh;background:linear-gradient(160deg,var(--navy2),var(--navy));color:white;padding:24px 16px;overflow:auto}
        .brand{font-size:20px;font-weight:900;padding:0 10px 22px}.brand small{display:block;color:#bcd0f0;font-size:12px;margin-top:5px}.nav a{display:flex;color:#dbe7fa;text-decoration:none;padding:11px 12px;border-radius:8px;margin:3px 0;font-weight:750;border-left:3px solid transparent}.nav a:hover{background:#ffffff18;color:white}.nav a.active{background:#ffffff20;color:white;border-left-color:#fff}
        .logout{margin-top:24px}.logout button{width:100%;background:#ffffff18}.main{min-width:0}.top{background:white;border-bottom:1px solid var(--line);padding:18px 3%;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:4}.content{padding:24px 3% 60px}
        h1{font-size:24px;margin:0}h2{font-size:20px;margin:0 0 16px}h3{font-size:16px;margin:18px 0 10px}.muted{color:var(--muted)}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px}.metric,.card{background:white;border:1px solid var(--line);border-radius:12px;box-shadow:0 4px 18px #17335a0c}.metric{padding:17px}.metric strong{display:block;font-size:25px;color:var(--navy)}.card{padding:18px;margin-top:0;overflow:auto}.section{display:none;scroll-margin-top:90px}.section.is-active{display:block}
        table{width:100%;border-collapse:collapse;min-width:780px}th,td{text-align:left;padding:10px;border-bottom:1px solid #e8edf4;vertical-align:top}th{font-size:12px;text-transform:uppercase;color:var(--muted);letter-spacing:.04em}
        input,select,textarea{padding:9px 10px;border:1px solid #cbd5e1;border-radius:7px;background:white;max-width:100%}textarea{min-height:64px}.row{display:flex;flex-wrap:wrap;gap:8px;align-items:end}.field{display:grid;gap:5px;min-width:150px}.field label{font-size:12px;color:var(--muted);font-weight:750}
        button,.button{border:0;padding:9px 12px;border-radius:7px;background:var(--navy);color:white;text-decoration:none;display:inline-block;font-weight:750;cursor:pointer}.danger{background:var(--red)}.success{background:var(--green)}.secondary{background:#64748b}
        .flash{background:#dff7e9;color:#075f38;padding:11px 14px;border-radius:8px;margin-bottom:16px}.error{background:#ffe4e5;color:#8e1015;padding:11px 14px;border-radius:8px;margin-bottom:16px}
        .badge{display:inline-block;padding:4px 8px;border-radius:20px;background:#e8eef7;color:#29405f;font-weight:800;font-size:11px;text-transform:capitalize}.badge.active,.badge.accepted,.badge.approved{background:#dff7e9;color:#087443}.badge.rejected{background:#ffe4e5;color:#a3171c}.perm-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:6px}.check{display:flex;gap:7px;align-items:flex-start;padding:6px}.check input{margin-top:3px}.split{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        @media(max-width:900px){.shell{grid-template-columns:1fr}.side{height:auto;position:static}.nav{display:flex;overflow:auto}.nav a{white-space:nowrap}.main .top{position:static}.split{grid-template-columns:1fr}}
    </style>
</head>
<body>
@php
    $can=fn($p)=>auth()->user()->role==='super_admin'||in_array($p,$permissions,true);
    $role=str_replace('_',' ',auth()->user()->role);
@endphp
<div class="shell">
<aside class="side">
    <div class="brand">Mahamilon Society<small>Operations & finance console</small></div>
    <nav class="nav">
        <a href="#overview">Overview</a><a href="#members">Members & KYC</a><a href="#accounts">Accounts</a>
        <a href="#payments">Payments</a><a href="#transactions">Transactions</a><a href="#loans">Loans & MCS</a>
        @if($can('cash.manage'))<a href="#cash">Cash management</a>@endif
        @if($can('profit_loss.manage'))<a href="#profit-loss">Profit & loss</a>@endif
        @if($can('account.close'))<a href="#closures">Account closure</a>@endif
        @if($can('reports.view'))<a href="#reports">Reports</a>@endif
        @if(auth()->user()->role==='super_admin')<a href="#staff">Staff & permissions</a><a href="#settings">Settings</a>@endif
        <a href="#security">Security & audit</a>
    </nav>
    <form class="logout" method="post" action="/admin/logout">@csrf<button>Sign out</button></form>
</aside>
<div class="main">
<header class="top"><div><h1 id="section-title">Overview</h1><span class="muted">Admin dashboard · Live society operations</span></div><div><b>{{auth()->user()->name}}</b><br><span class="muted">{{ucwords($role)}}</span></div></header>
<main class="content">
    @if(session('ok'))<div class="flash">{{session('ok')}}</div>@endif
    @if($errors->any())<div class="error">{{$errors->first()}}</div>@endif

    <section id="overview" class="section is-active">
        <div class="grid">
            <div class="metric"><strong>{{$metrics['members']}}</strong><span>Total members</span></div>
            <div class="metric"><strong>{{$metrics['pending_members']}}</strong><span>Pending members</span></div>
            <div class="metric"><strong>₹{{number_format($metrics['deposits'],2)}}</strong><span>Active deposits</span></div>
            <div class="metric"><strong>{{$metrics['pending_payments']}}</strong><span>Payments to verify</span></div>
            <div class="metric"><strong>{{$metrics['pending_loans']}}</strong><span>Open loans</span></div>
            <div class="metric"><strong>{{$metrics['pending_repayments']}}</strong><span>Repayments to verify</span></div>
            <div class="metric"><strong>{{$metrics['overdue_installments']}}</strong><span>Overdue installments</span></div>
            <div class="metric"><strong>{{$metrics['transactions']}}</strong><span>Posted transactions</span></div>
            <div class="metric"><strong>₹{{number_format($metrics['total_loans'],2)}}</strong><span>Total sanctioned loans</span></div>
            <div class="metric"><strong>₹{{number_format($metrics['outstanding_loans'],2)}}</strong><span>Outstanding loans</span></div>
            <div class="metric"><strong>₹{{number_format($metrics['bank_cash'],2)}}</strong><span>Amount in bank</span></div>
            <div class="metric"><strong>₹{{number_format($metrics['treasury_cash'],2)}}</strong><span>Cash in treasury</span></div>
        </div>
    </section>

    <section id="members" class="card section">
        <h2>Members and KYC</h2>
        @if($can('kyc.approve'))
        <h3>Upload KYC from admin</h3>
        <form method="post" action="/admin/kyc" enctype="multipart/form-data" class="row">@csrf
            <div class="field"><label>Member</label><select name="member_id" required>@foreach($members as $m)<option value="{{$m->id}}">{{$m->member_no}} — {{$m->name}}</option>@endforeach</select></div>
            <div class="field"><label>Document type</label><select name="type"><option value="aadhaar">Aadhaar</option><option value="pan">PAN</option><option value="photo">Photo</option><option value="address_proof">Address proof</option></select></div>
            <div class="field"><label>File (JPG, PNG, PDF; 5 MB)</label><input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required></div><button>Upload KYC</button>
        </form>
        @endif
        <h3>Member directory</h3>
        <table><thead><tr><th>Member</th><th>Contact</th><th>Member status</th><th>KYC</th><th>MCS</th><th>Action</th></tr></thead><tbody>
        @foreach($members as $m)<tr><td><b>{{$m->name}}</b><br>{{$m->member_no}}</td><td>{{$m->phone}}<br>{{$m->email}}</td><td><span class="badge {{$m->user_status}}">{{$m->user_status}}</span></td><td><span class="badge {{$m->kyc_status}}">{{str_replace('_',' ',$m->kyc_status)}}</span></td><td><b>{{$m->mcs_score}}</b>/900</td><td>
            @if($can('member.approve'))
            <form method="post" action="/admin/members/{{$m->id}}/decision" style="display:inline">@csrf<input type="hidden" name="decision" value="approve"><button class="success">Approve</button></form>
            <form method="post" action="/admin/members/{{$m->id}}/decision" style="display:inline">@csrf<input type="hidden" name="decision" value="reject"><button class="danger">Reject</button></form>
            @else Read only @endif
        </td></tr>@endforeach</tbody></table>
        <h3>KYC folders by member</h3>
        <p class="muted">Each member appears once. Downloading creates one ZIP folder containing every uploaded KYC document and a verification manifest.</p>
        @php $kycFolders=$documents->groupBy('member_id'); @endphp
        <table><thead><tr><th>Member</th><th>Folder contents</th><th>Overall status</th><th>Download and review</th></tr></thead><tbody>
        @forelse($kycFolders as $memberId=>$memberDocuments)
        @php
            $d=$memberDocuments->first();
            $folderStatus=$memberDocuments->contains('status','pending')?'pending':($memberDocuments->contains('status','rejected')?'rejected':'approved');
            $documentTypes=$memberDocuments->pluck('type')->unique()->map(fn($type)=>str_replace('_',' ',$type))->implode(', ');
        @endphp
        <tr><td><b>{{$d->name}}</b><br>{{$d->member_no}}</td><td>{{$memberDocuments->count()}} file(s)<br><span class="muted">{{$documentTypes}}</span></td><td><span class="badge {{$folderStatus}}">{{$folderStatus}}</span></td><td>
            <a class="button secondary" href="/admin/members/{{$memberId}}/kyc-folder">Download KYC folder</a>
            @if($can('kyc.approve') && $memberDocuments->contains('status','pending'))
            <form method="post" action="/admin/members/{{$memberId}}/kyc-decision" style="display:inline">@csrf<input type="hidden" name="decision" value="approved"><button class="success">Approve pending</button></form>
            <form method="post" action="/admin/members/{{$memberId}}/kyc-decision" style="display:inline">@csrf<input type="hidden" name="decision" value="rejected"><button class="danger">Reject pending</button></form>
            @else Reviewed/read only @endif
        </td></tr>@empty<tr><td colspan="4">No KYC folders.</td></tr>@endforelse
        </tbody></table>
    </section>

    <section id="accounts" class="card section">
        <h2>Accounts</h2>
        @if($can('account.create'))
        <h3>Create account from admin</h3>
        <p class="muted">Open an account for an approved member, or register a completely new applicant and create their account request in one operation.</p>
        @php $eligibleAccountMembers=$members->where('user_status','active')->where('kyc_status','approved'); @endphp
        @php $defaultMemberSource=old('member_source',$eligibleAccountMembers->isNotEmpty()?'existing':'new'); @endphp
        <form method="post" action="/admin/accounts" id="admin-account-form">@csrf
            <div class="row">
                <div class="field"><label>Applicant type</label><select name="member_source" id="account-member-source" required><option value="existing" @selected($defaultMemberSource==='existing') @disabled($eligibleAccountMembers->isEmpty())>Existing approved member</option><option value="new" @selected($defaultMemberSource==='new')>New person (not a member yet)</option></select></div>
            </div>
            <div id="existing-member-fields" class="row" style="margin-top:12px" @if($defaultMemberSource!=='existing') hidden @endif>
                <div class="field"><label>Approved member</label><select name="member_id" data-required="true" @disabled($eligibleAccountMembers->isEmpty())>@foreach($eligibleAccountMembers as $m)<option value="{{$m->id}}" @selected((string)old('member_id')===(string)$m->id)>{{$m->member_no}} — {{$m->name}}</option>@endforeach</select></div>
                @if($eligibleAccountMembers->isEmpty())<div class="error">No approved member is currently eligible. Choose “New person” to start onboarding.</div>@endif
            </div>
            <div id="new-applicant-fields" style="border:1px solid var(--line);border-radius:10px;padding:14px;margin-top:12px" @if($defaultMemberSource!=='new') hidden @endif>
                <b>New applicant and mobile-login details</b>
                <p class="muted">The person is created as a pending member. Upload and approve KYC, approve membership, then approve the account to generate the QR payment request.</p>
                <div class="row">
                    <div class="field"><label>Full name</label><input name="applicant_name" value="{{old('applicant_name')}}" maxlength="120" data-required="true"></div>
                    <div class="field"><label>Mobile number</label><input name="applicant_phone" value="{{old('applicant_phone')}}" inputmode="numeric" pattern="[6-9][0-9]{9}" maxlength="10" data-required="true"></div>
                    <div class="field"><label>Email (optional)</label><input name="applicant_email" value="{{old('applicant_email')}}" type="email" maxlength="255"></div>
                    <div class="field"><label>6-digit mobile MPIN</label><input name="applicant_mpin" type="password" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" data-required="true" autocomplete="new-password"></div>
                    <div class="field"><label>Date of birth</label><input name="applicant_date_of_birth" value="{{old('applicant_date_of_birth')}}" type="date"></div>
                    <div class="field"><label>Occupation</label><input name="applicant_occupation" value="{{old('applicant_occupation')}}" maxlength="120"></div>
                    <div class="field"><label>Nominee name</label><input name="applicant_nominee" value="{{old('applicant_nominee')}}" maxlength="120" data-required="true"></div>
                    <div class="field" style="min-width:280px"><label>Address</label><textarea name="applicant_address" maxlength="1000" data-required="true">{{old('applicant_address')}}</textarea></div>
                    <div class="field"><label>Aadhaar number</label><input name="applicant_aadhaar" inputmode="numeric" pattern="[0-9]{12}" minlength="12" maxlength="12" data-required="true"></div>
                    <div class="field"><label>PAN (optional)</label><input name="applicant_pan" value="{{old('applicant_pan')}}" pattern="[A-Z]{5}[0-9]{4}[A-Z]" maxlength="10" style="text-transform:uppercase"></div>
                </div>
            </div>
            <h3>Account details</h3>
            <div class="row">
                <div class="field"><label>Account plan</label><select name="scheme_id" required>@foreach($schemes as $s)<option value="{{$s->id}}" @selected((string)old('scheme_id')===(string)$s->id)>{{$s->name}} ({{$s->annual_rate}}%)</option>@endforeach</select></div>
                <div class="field"><label>Opening amount</label><input name="opening_deposit" value="{{old('opening_deposit')}}" type="number" min="1" step=".01" required></div>
                <div class="field"><label>Shares (₹500/month each)</label><input name="share_count" type="number" value="{{old('share_count',1)}}" min="1" max="100"></div>
                <div class="field"><label>Custom interest % (optional)</label><input name="custom_interest_rate" value="{{old('custom_interest_rate')}}" type="number" step=".001" min="0" max="30" placeholder="Blank = plan rate"></div>
                <button id="admin-account-submit" data-existing-label="{{$can('account.approve')?'Create account & request payment':'Submit for approval'}}" data-new-label="Create applicant & account request">{{$defaultMemberSource==='new'?'Create applicant & account request':($can('account.approve')?'Create account & request payment':'Submit for approval')}}</button>
            </div>
        </form>
        @endif
        <h3>Account requests and active accounts</h3>
        <table><thead><tr><th>Member / account</th><th>Plan</th><th>Amount / shares</th><th>Interest</th><th>Workflow</th><th>Actions</th></tr></thead><tbody>
        @foreach($accounts as $a)<tr><td><b>{{$a->name}}</b><br>{{$a->member_no}} · {{$a->account_no}}</td><td>{{$a->scheme_name}}</td><td>₹{{number_format($a->principal,2)}}<br>{{$a->share_count}} share(s)</td><td>
            @if($can('interest.manage'))<form method="post" action="/admin/accounts/{{$a->id}}/interest">@csrf<input name="custom_interest_rate" value="{{$a->custom_interest_rate}}" type="number" step=".001" min="0" max="30" style="width:85px" placeholder="{{$a->annual_rate}}"><button>Save</button></form>
            @else {{$a->effective_interest_rate}}% @endif
        </td><td><span class="badge {{$a->status}}">{{$a->status}}</span><br>{{str_replace('_',' ',$a->onboarding_status)}}</td><td>
            @if($a->onboarding_status==='awaiting_review')
                @if($can('account.approve'))<form method="post" action="/admin/accounts/{{$a->id}}/decision" style="display:inline">@csrf<input type="hidden" name="decision" value="approve"><button class="success">Approve & request payment</button></form>@endif
                @if($can('account.reject'))<form method="post" action="/admin/accounts/{{$a->id}}/decision" style="display:inline">@csrf<input type="hidden" name="decision" value="reject"><button class="danger">Reject</button></form>@endif
            @else Processed @endif
        </td></tr>@endforeach</tbody></table>
    </section>

    <section id="payments" class="card section">
        <h2>Payment verification</h2>
        <p class="muted">An account becomes active only after its opening payment is submitted by the member and accepted here.</p>
        <table><thead><tr><th>Member</th><th>Reference</th><th>Amount</th><th>UTR</th><th>Status</th><th>Decision</th></tr></thead><tbody>
        @forelse($payments as $p)<tr><td>{{$p->name}}<br>{{$p->account_no}}</td><td>{{$p->reference}}</td><td>₹{{number_format($p->amount,2)}}</td><td>{{$p->utr ?: 'Not submitted'}}</td><td><span class="badge {{$p->status}}">{{$p->status}}</span></td><td>
            @if($can('payment.accept') && $p->status==='submitted')
            <form method="post" action="/admin/payments/{{$p->id}}/decision" style="display:inline">@csrf<input type="hidden" name="decision" value="accepted"><button class="success">Accept & activate</button></form>
            <form method="post" action="/admin/payments/{{$p->id}}/decision" style="display:inline">@csrf<input type="hidden" name="decision" value="rejected"><button class="danger">Reject</button></form>
            @else Awaiting/processed @endif
        </td></tr>@empty<tr><td colspan="6">No payment requests.</td></tr>@endforelse</tbody></table>
    </section>

    <section id="transactions" class="card section">
        <h2>Accounts and transactions</h2>
        @if($can('transaction.create'))
        <h3>Post account transaction</h3>
        <form method="post" action="/admin/transactions" class="row">@csrf
            <div class="field"><label>Active account</label><select name="account_id">@foreach($accounts->where('status','active') as $a)<option value="{{$a->id}}">{{$a->account_no}} — {{$a->name}} (₹{{$a->balance}})</option>@endforeach</select></div>
            <div class="field"><label>Direction</label><select name="direction"><option value="credit">Credit / deposit</option><option value="debit">Debit / withdrawal</option></select></div>
            <div class="field"><label>Amount</label><input name="amount" type="number" min="1" step=".01" required></div>
            <div class="field"><label>Description</label><input name="description" maxlength="255" required></div><button>Post transaction</button>
        </form>
        @endif
        <h3>Transaction explorer</h3>
        <form method="get" action="/admin#transactions" class="row" style="background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:14px">
            <div class="field" style="min-width:240px"><label>Search</label><input name="tx_search" value="{{$transactionFilters['tx_search']??''}}" maxlength="100" placeholder="Member, account, reference or description"></div>
            <div class="field" style="min-width:240px"><label>Account</label><select name="tx_account_id"><option value="">All accounts</option>@foreach($transactionAccountOptions as $option)<option value="{{$option->id}}" @selected((string)($transactionFilters['tx_account_id']??'')===(string)$option->id)>{{$option->account_no}} — {{$option->name}} ({{$option->status}})</option>@endforeach</select></div>
            <div class="field"><label>Direction</label><select name="tx_direction"><option value="">Credit and debit</option><option value="credit" @selected(($transactionFilters['tx_direction']??'')==='credit')>Credit</option><option value="debit" @selected(($transactionFilters['tx_direction']??'')==='debit')>Debit</option></select></div>
            <div class="field"><label>Status</label><select name="tx_status"><option value="">All statuses</option>@foreach(['posted','pending','reversed'] as $status)<option value="{{$status}}" @selected(($transactionFilters['tx_status']??'')===$status)>{{ucfirst($status)}}</option>@endforeach</select></div>
            <div class="field"><label>From date</label><input name="tx_from" value="{{$transactionFilters['tx_from']??''}}" type="date"></div>
            <div class="field"><label>To date</label><input name="tx_to" value="{{$transactionFilters['tx_to']??''}}" type="date"></div>
            <div class="field"><label>Minimum amount</label><input name="tx_min_amount" value="{{$transactionFilters['tx_min_amount']??''}}" type="number" min="0" step=".01"></div>
            <div class="field"><label>Maximum amount</label><input name="tx_max_amount" value="{{$transactionFilters['tx_max_amount']??''}}" type="number" min="0" step=".01"></div>
            <div class="field"><label>Sort</label><select name="tx_sort"><option value="newest" @selected(($transactionFilters['tx_sort']??'newest')==='newest')>Newest first</option><option value="oldest" @selected(($transactionFilters['tx_sort']??'')==='oldest')>Oldest first</option><option value="amount_high" @selected(($transactionFilters['tx_sort']??'')==='amount_high')>Amount: high to low</option><option value="amount_low" @selected(($transactionFilters['tx_sort']??'')==='amount_low')>Amount: low to high</option></select></div>
            <button>Apply filters</button><a class="button secondary" href="/admin#transactions">Clear filters</a>
        </form>
        <div class="grid" style="margin-top:14px">
            <div class="metric"><strong>{{$transactions->total()}}</strong><span>Matching transactions</span></div>
            <div class="metric"><strong style="color:var(--green)">₹{{number_format($transactionSummary['credits'],2)}}</strong><span>Filtered credits</span></div>
            <div class="metric"><strong style="color:var(--red)">₹{{number_format($transactionSummary['debits'],2)}}</strong><span>Filtered debits</span></div>
            <div class="metric"><strong>₹{{number_format($transactionSummary['net'],2)}}</strong><span>Filtered net movement</span></div>
        </div>
        <div class="row" style="justify-content:space-between;margin:16px 0 6px"><b>Results</b><span class="muted">Showing {{$transactions->firstItem()??0}}–{{$transactions->lastItem()??0}} of {{$transactions->total()}}</span></div>
        <table><thead><tr><th>Date</th><th>Member / account</th><th>Description</th><th>Direction</th><th>Amount</th><th>Status</th></tr></thead><tbody>
        @forelse($transactions as $t)<tr><td>{{$t->posted_at}}</td><td><b>{{$t->name}}</b><br>{{$t->member_no}} · {{$t->account_no}}</td><td>{{$t->description}}<br><small>{{$t->reference}}</small></td><td><span class="badge {{$t->direction==='credit'?'active':'rejected'}}">{{$t->direction}}</span></td><td>₹{{number_format($t->amount,2)}}</td><td><span class="badge {{$t->status}}">{{$t->status}}</span></td></tr>@empty<tr><td colspan="6">No transactions match the selected filters.</td></tr>@endforelse
        </tbody></table>
        @if($transactions->hasPages())<div class="row" style="justify-content:flex-end;margin-top:14px">@if($transactions->onFirstPage())<span class="button secondary" style="opacity:.5">Previous</span>@else<a class="button secondary" href="{{$transactions->previousPageUrl()}}#transactions">Previous</a>@endif <span class="muted" style="align-self:center">Page {{$transactions->currentPage()}} of {{$transactions->lastPage()}}</span> @if($transactions->hasMorePages())<a class="button" href="{{$transactions->nextPageUrl()}}#transactions">Next</a>@else<span class="button secondary" style="opacity:.5">Next</span>@endif</div>@endif
    </section>

    @if($can('cash.manage'))
    <section id="cash" class="card section">
        <h2>Cash management</h2>
        <p class="muted">Bank and treasury totals come from an auditable cash ledger. Transfers create matching debit and credit entries.</p>
        <div class="grid">
            <div class="metric"><strong>₹{{number_format($metrics['bank_cash'],2)}}</strong><span>Total amount in bank</span></div>
            <div class="metric"><strong>₹{{number_format($metrics['treasury_cash'],2)}}</strong><span>Total cash in treasury</span></div>
            <div class="metric"><strong>₹{{number_format($metrics['total_loans'],2)}}</strong><span>Total sanctioned loan amount</span></div>
            <div class="metric"><strong>₹{{number_format($metrics['outstanding_loans'],2)}}</strong><span>Loan principal outstanding</span></div>
        </div>
        @if($can('cash.manage'))
        <h3>Post cash movement</h3>
        <form method="post" action="/admin/finance/cash" class="row">@csrf
            <div class="field"><label>Action</label><select name="action"><option value="credit">Money received</option><option value="debit">Money paid</option><option value="transfer">Transfer</option></select></div>
            <div class="field"><label>Location / source</label><select name="location"><option value="bank">Bank</option><option value="treasury">Treasury</option></select></div>
            <div class="field"><label>Transfer destination</label><select name="destination_location"><option value="">Not a transfer</option><option value="bank">Bank</option><option value="treasury">Treasury</option></select></div>
            <div class="field"><label>Category</label><input name="category" value="manual_adjustment" maxlength="60"></div>
            <div class="field"><label>Amount</label><input name="amount" type="number" min=".01" step=".01" required></div>
            <div class="field"><label>P&L treatment</label><select name="profit_loss_class"><option value="none">Balance sheet only</option><option value="income">Income</option><option value="expense">Expense</option></select></div>
            <div class="field"><label>Date</label><input name="occurred_on" type="date" value="{{today()->toDateString()}}" required></div>
            <div class="field"><label>Note</label><input name="note" maxlength="1000" required></div><button>Post cash entry</button>
        </form>
        @endif
        <h3>Recent cash ledger</h3>
        <table><thead><tr><th>Date</th><th>Location</th><th>Direction</th><th>Category</th><th>Amount</th><th>Note</th><th>Staff</th></tr></thead><tbody>
        @forelse($cashEntries as $entry)<tr><td>{{$entry->occurred_on}}</td><td>{{ucfirst($entry->location)}}</td><td><span class="badge">{{$entry->direction}}</span></td><td>{{str_replace('_',' ',$entry->category)}}</td><td>₹{{number_format($entry->amount,2)}}</td><td>{{$entry->note}}<br><small>{{$entry->reference}}</small></td><td>{{$entry->actor??'System'}}</td></tr>
        @empty<tr><td colspan="7">No cash entries.</td></tr>@endforelse
        </tbody></table>
    </section>
    @endif

    @if($can('profit_loss.manage'))
    <section id="profit-loss" class="card section">
        <h2>Profit and loss</h2>
        <p class="muted">Loan interest and fines are recognized as income; interest credited to members is recognized as expense.</p>
        <div class="grid">
            @foreach($profitLoss as $period=>$summary)
            <div class="metric"><strong style="color:{{$summary['net']<0?'var(--red)':'var(--green)'}}">₹{{number_format($summary['net'],2)}}</strong><span>{{ucfirst($period)}} profit / loss</span><br><small class="muted">Income ₹{{number_format($summary['income'],2)}} · Expense ₹{{number_format($summary['expense'],2)}}</small></div>
            @endforeach
        </div>
        @if($can('profit_loss.manage'))
        <h3>Manual income or expense adjustment</h3>
        <form method="post" action="/admin/finance/profit-loss" class="row">@csrf
            <div class="field"><label>Type</label><select name="entry_type"><option value="income">Income</option><option value="expense">Expense</option></select></div>
            <div class="field"><label>Category</label><input name="category" maxlength="60" required></div>
            <div class="field"><label>Amount</label><input name="amount" type="number" min=".01" step=".01" required></div>
            <div class="field"><label>Date</label><input name="occurred_on" type="date" value="{{today()->toDateString()}}" required></div>
            <div class="field"><label>Note</label><input name="note" maxlength="1000" required></div><button>Post P&L entry</button>
        </form>
        @endif
        <h3>Recent P&L ledger</h3>
        <table><thead><tr><th>Date</th><th>Type</th><th>Category</th><th>Amount</th><th>Note</th><th>Staff</th></tr></thead><tbody>
        @forelse($profitLossEntries as $entry)<tr><td>{{$entry->occurred_on}}</td><td><span class="badge {{$entry->entry_type==='income'?'active':'rejected'}}">{{$entry->entry_type}}</span></td><td>{{str_replace('_',' ',$entry->category)}}</td><td>₹{{number_format($entry->amount,2)}}</td><td>{{$entry->note}}</td><td>{{$entry->actor??'System'}}</td></tr>
        @empty<tr><td colspan="6">No profit/loss entries.</td></tr>@endforelse
        </tbody></table>
    </section>
    @endif

    @if($can('account.close'))
    <section id="closures" class="card section">
        <h2>Account closure and maturity</h2>
        <p class="muted">After maturity, a closure request starts the configured notice period. Withdrawal becomes available after {{(int)($settings['closure_notice_days']??30)}} days.</p>
        @php $latestClosureByAccount=$closures->groupBy('account_id')->map->first(); @endphp
        <table><thead><tr><th>Member / account</th><th>Balance</th><th>Opened</th><th>Matures</th><th>Closure status</th><th>Actions</th></tr></thead><tbody>
        @foreach($accounts->where('status','active') as $account)
        @php $closure=$latestClosureByAccount->get($account->id); @endphp
        <tr><td>{{$account->name}}<br>{{$account->account_no}}</td><td>₹{{number_format($account->balance,2)}}</td><td>{{$account->opened_on}}</td><td>
            @if($can('account.close'))<form method="post" action="/admin/accounts/{{$account->id}}/maturity" class="row">@csrf<input name="matures_on" type="date" value="{{$account->matures_on}}" required><button>Save</button></form>
            @else {{$account->matures_on}} @endif
        </td><td>@if($closure)<span class="badge {{$closure->status==='completed'?'active':''}}">{{str_replace('_',' ',$closure->status)}}</span><br>Withdrawal: {{$closure->withdrawal_available_on}}@else No closure request @endif</td><td>
            @if($can('account.close'))
                @if(!$closure || in_array($closure->status,['completed','rejected']))
                    @if($account->matures_on && !\Carbon\Carbon::parse($account->matures_on)->isFuture())
                    <form method="post" action="/admin/accounts/{{$account->id}}/closure">@csrf<input type="hidden" name="action" value="request"><input name="note" placeholder="Closure note"><button>Start notice</button></form>
                    @else Not yet matured @endif
                @elseif(in_array($closure->status,['notice_period','withdrawal_available']))
                    @if(!\Carbon\Carbon::parse($closure->withdrawal_available_on)->isFuture())
                    <form method="post" action="/admin/accounts/{{$account->id}}/closure" class="row">@csrf<input type="hidden" name="action" value="complete"><select name="payout_location"><option value="bank">Pay from bank</option><option value="treasury">Pay from treasury</option></select><button class="success">Complete withdrawal</button></form>
                    @else Notice active @endif
                    <form method="post" action="/admin/accounts/{{$account->id}}/closure" style="margin-top:5px">@csrf<input type="hidden" name="action" value="reject"><button class="danger">Reject closure</button></form>
                @endif
            @else Read only @endif
        </td></tr>@endforeach
        </tbody></table>
        <h3>Closure history</h3>
        <table><thead><tr><th>Member / account</th><th>Requested</th><th>Maturity</th><th>Withdrawal available</th><th>Amount</th><th>Status</th></tr></thead><tbody>
        @forelse($closures as $closure)<tr><td>{{$closure->name}}<br>{{$closure->account_no}}</td><td>{{$closure->requested_on}}</td><td>{{$closure->maturity_on}}</td><td>{{$closure->withdrawal_available_on}}</td><td>₹{{number_format($closure->closure_amount??$closure->balance,2)}}</td><td><span class="badge">{{str_replace('_',' ',$closure->status)}}</span></td></tr>
        @empty<tr><td colspan="6">No account closure requests.</td></tr>@endforelse
        </tbody></table>
    </section>
    @endif

    @if($can('reports.view'))
    <section id="reports" class="card section">
        <h2>Reports</h2>
        @if($can('reports.view'))
        <div class="row">
            <a class="button" href="/admin/reports/members.csv">Export all members</a>
            <a class="button" href="/admin/reports/accounts.csv">Export all accounts</a>
            <a class="button" href="/admin/reports/loans.csv">Export all loans</a>
        </div>
        <h3>Date-filtered financial exports</h3>
        @foreach(['transactions'=>'Account transactions','cash'=>'Cash ledger','profit-loss'=>'Profit and loss'] as $type=>$label)
        <form method="get" action="/admin/reports/{{$type}}.csv" class="row" style="margin:8px 0">
            <b style="min-width:150px">{{$label}}</b><div class="field"><label>From</label><input name="from" type="date"></div><div class="field"><label>To</label><input name="to" type="date"></div><button>Export CSV</button>
        </form>
        @endforeach
        @endif
        <h3>All-member operational report</h3>
        <table><thead><tr><th>Member</th><th>Contact</th><th>Status / KYC</th><th>MCS</th><th>Accounts</th><th>Balance</th><th>Loans</th><th>Loan outstanding</th></tr></thead><tbody>
        @foreach($memberReports as $report)<tr><td>{{$report->name}}<br>{{$report->member_no}}</td><td>{{$report->phone}}</td><td>{{$report->status}} / {{$report->kyc_status}}</td><td>{{$report->mcs_score}}/900</td><td>{{$report->account_count}}</td><td>₹{{number_format($report->account_balance,2)}}</td><td>{{$report->loan_count}}</td><td>₹{{number_format($report->loan_outstanding,2)}}</td></tr>@endforeach
        </tbody></table>
    </section>
    @endif

    <section id="loans" class="card section">
        <h2>Loans and Mahamilon Credit Score (MCS)</h2>
        <p class="muted">Loan sanction is capped at 85% of the member's active savings. Emergency applications are flagged for a decision within three days.</p>
        @if($can('interest.manage'))<form method="post" action="/admin/finance/run" style="float:right">@csrf<button>Run financial automation now</button></form>@endif
        @if($can('loan.offer'))
        <h3>Create individual loan offer</h3>
        @if($errors->loanOffer->any())
        <div class="error" role="alert"><b>Offer could not be created.</b><ul style="margin:6px 0 0;padding-left:20px">@foreach($errors->loanOffer->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>
        @endif
        <form method="post" action="/admin/loans/offers" class="row">@csrf
            <div class="field"><label>Member</label><select id="loan-offer-member" name="member_id" required><option value="">Select an eligible member</option>@foreach($members as $m)@php($maximumOffer=round(((float)$m->active_savings)*.85,2))<option value="{{$m->id}}" data-savings="{{number_format((float)$m->active_savings,2,'.','')}}" data-max-offer="{{number_format($maximumOffer,2,'.','')}}" @selected((string)old('member_id')===(string)$m->id) @disabled($maximumOffer<1000)>{{$m->member_no}} — {{$m->name}} · Savings ₹{{number_format($m->active_savings,2)}} · Max offer ₹{{number_format($maximumOffer,2)}}@if($maximumOffer<1000) (not eligible)@endif</option>@endforeach</select></div>
            <div class="field"><label>Loan type</label><select name="loan_type"><option value="personal" @selected(old('loan_type')==='personal')>Personal</option><option value="business" @selected(old('loan_type')==='business')>Business</option><option value="emergency" @selected(old('loan_type')==='emergency')>Emergency health</option></select></div>
            <div class="field"><label>EMI frequency</label><select name="emi_frequency"><option value="monthly" @selected(old('emi_frequency','monthly')==='monthly')>Monthly</option><option value="weekly" @selected(old('emi_frequency')==='weekly')>Weekly</option><option value="daily" @selected(old('emi_frequency')==='daily')>Daily</option></select></div>
            <div class="field"><label>Offer amount</label><input id="loan-offer-amount" name="amount" type="number" min="1000" step=".01" value="{{old('amount')}}" required><small id="loan-offer-limit" class="muted">Select a member to see the 85% eligibility limit.</small></div>
            <div class="field"><label>Tenure months</label><input name="tenure_months" type="number" min="1" max="120" value="{{old('tenure_months')}}" required></div>
            <div class="field"><label>Valid days</label><input name="offer_days" value="{{old('offer_days',14)}}" type="number" min="1" max="90" required></div>
            <div class="field"><label>Purpose</label><input name="purpose" value="{{old('purpose')}}" required></div><button>Create offer</button>
        </form>
        @endif
        <table><thead><tr><th>Member</th><th>Type / EMI</th><th>Requested</th><th>MCS</th><th>Status / SLA</th><th>Decision</th></tr></thead><tbody>
        @foreach($loans as $l)<tr><td>{{$l->name}}<br>{{$l->member_no}}</td><td>{{ucfirst($l->loan_type)}}<br>{{ucfirst($l->emi_frequency)}}</td><td>₹{{number_format($l->amount,2)}}<br>{{$l->tenure_months}} months</td><td><b>{{$l->mcs_snapshot ?? $l->mcs_score}}</b>/900</td><td><span class="badge {{$l->status}}">{{$l->status}}</span>@if($l->loan_type==='emergency' && in_array($l->status,['submitted','under_review']))<br><b style="color:var(--red)">Due {{\Carbon\Carbon::parse($l->created_at)->addDays(3)->format('d M Y')}}</b>@endif @if($l->is_offer)<br>Individual offer @endif</td><td>
            @if(in_array($l->status,['submitted','under_review']))
                @if($can('loan.approve'))<form method="post" action="/admin/loans/{{$l->id}}/decision" style="display:inline">@csrf<input type="hidden" name="decision" value="approved"><input name="sanctioned_amount" type="number" value="{{$l->amount}}" style="width:110px" title="Sanctioned amount"><input name="annual_interest_rate" type="number" step=".001" value="{{\App\Services\LoanSchedule::defaultRate($l->loan_type)}}" style="width:78px" title="Annual interest rate"><button class="success">Approve</button></form>@endif
                @if($can('loan.reject'))<form method="post" action="/admin/loans/{{$l->id}}/decision" style="display:inline">@csrf<input type="hidden" name="decision" value="rejected"><button class="danger">Reject</button></form>@endif
            @else Processed @endif
        </td></tr>@endforeach</tbody></table>
        <h3>Loan repayment verification</h3>
        <table><thead><tr><th>Member / loan</th><th>Reference</th><th>Amount</th><th>UTR</th><th>Status</th><th>Decision</th></tr></thead><tbody>
        @forelse($repayments as $repayment)<tr><td>{{$repayment->name}}<br>{{$repayment->application_no}}</td><td>{{$repayment->reference}}</td><td>₹{{number_format($repayment->amount,2)}}</td><td>{{$repayment->utr}}</td><td><span class="badge {{$repayment->status}}">{{$repayment->status}}</span></td><td>
            @if($can('loan.repayment')&&$repayment->status==='submitted')
            <form method="post" action="/admin/loan-repayments/{{$repayment->id}}/decision" style="display:inline">@csrf<input type="hidden" name="decision" value="accepted"><button class="success">Accept repayment</button></form>
            <form method="post" action="/admin/loan-repayments/{{$repayment->id}}/decision" style="display:inline">@csrf<input type="hidden" name="decision" value="rejected"><button class="danger">Reject</button></form>
            @else Processed/read only @endif
        </td></tr>@empty<tr><td colspan="6">No loan repayments submitted.</td></tr>@endforelse</tbody></table>
        <h3>Due and overdue installments</h3>
        <table><thead><tr><th>Member / loan</th><th>Installment</th><th>Due date</th><th>Principal</th><th>Interest</th><th>Fine</th><th>Status</th><th>Custom fine</th></tr></thead><tbody>
        @forelse($installments as $installment)<tr><td>{{$installment->name}}<br>{{$installment->application_no}}</td><td>#{{$installment->installment_no}}</td><td>{{$installment->due_on}}</td><td>₹{{number_format($installment->principal_due,2)}}</td><td>₹{{number_format($installment->interest_due,2)}}</td><td>₹{{number_format($installment->penalty_due,2)}}<br><small>{{$installment->penalty_note}}</small></td><td><span class="badge {{$installment->status}}">{{$installment->status}}</span></td><td>
            @if($can('loan.fine') && \Carbon\Carbon::parse($installment->due_on)->isBefore(today()))
            <form method="post" action="/admin/loan-installments/{{$installment->id}}/fine" class="row">@csrf
                <input name="penalty_amount" type="number" min="0" step=".01" value="{{$installment->penalty_due ?: ($settings['default_overdue_fine_amount']??0)}}" style="width:95px" required>
                <input name="note" value="{{$installment->penalty_note}}" placeholder="Fine reason" style="width:150px"><button>Apply / waive</button>
            </form>
            @else Read only @endif
        </td></tr>@empty<tr><td colspan="8">No due or overdue installments.</td></tr>@endforelse</tbody></table>
    </section>

    @if(auth()->user()->role==='super_admin')
    <section id="staff" class="card section">
        <h2>Staff, roles and action permissions</h2>
        <p class="muted">Only super admins can assign or change these permissions. Role names are organizational labels; the checked actions are the actual authorization.</p>
        <h3>Create staff</h3>
        <form method="post" action="/admin/staff">@csrf
            <div class="row"><div class="field"><label>Name</label><input name="name" required></div><div class="field"><label>Email</label><input name="email" type="email" required></div><div class="field"><label>Temporary password</label><input name="password" type="password" minlength="12" required></div><div class="field"><label>Role</label><select name="role"><option value="manager">Manager</option><option value="agent">Agent</option><option value="secretary">Secretary</option><option value="president">President</option><option value="super_admin">Super admin</option></select></div><button>Create staff</button></div>
            <div class="perm-grid">@foreach($permissionDefinitions as $key=>$label)<label class="check"><input type="checkbox" name="permissions[]" value="{{$key}}"><span><b>{{$key}}</b><br><small class="muted">{{$label}}</small></span></label>@endforeach</div>
        </form>
        <h3>Existing staff</h3>
        @foreach($staffUsers as $staff)
        <form method="post" action="/admin/staff/{{$staff->id}}" style="border-top:1px solid var(--line);padding:16px 0">@csrf
            <div class="row"><div style="min-width:230px"><b>{{$staff->name}}</b><br><span class="muted">{{$staff->email}}</span></div><select name="role">@foreach(['super_admin','president','manager','secretary','agent'] as $r)<option value="{{$r}}" @selected($staff->role===$r)>{{ucwords(str_replace('_',' ',$r))}}</option>@endforeach</select><select name="status"><option value="active" @selected($staff->status==='active')>Active</option><option value="suspended" @selected($staff->status==='suspended')>Suspended</option></select><button>Save assignment</button></div>
            <div class="perm-grid">@foreach($permissionDefinitions as $key=>$label)<label class="check"><input type="checkbox" name="permissions[]" value="{{$key}}" @checked(in_array($key,\App\Services\StaffPermissions::for($staff),true)) @disabled($staff->role==='super_admin')><span><b>{{$key}}</b><br><small class="muted">{{$label}}</small></span></label>@endforeach</div>
        </form>
        @endforeach
    </section>

    <section id="settings" class="card section">
        <h2>Payment QR settings</h2>
        <form method="post" action="/admin/settings" class="row">@csrf<div class="field"><label>Society UPI ID</label><input name="upi_id" value="{{$settings['upi_id']??''}}" required></div><div class="field"><label>Payee name</label><input name="payee_name" value="{{$settings['payee_name']??'Mahamilon Society'}}" required></div><button>Save payment settings</button></form>
        <h2 style="margin-top:24px">Finance, maturity and fine defaults</h2>
        <p class="muted">These values provide defaults. Authorized staff can still set an individual maturity date or fully custom EMI fine.</p>
        <form method="post" action="/admin/finance/settings" class="row">@csrf
            <div class="field"><label>Default account term (months)</label><input name="default_account_term_months" type="number" min="1" max="120" value="{{$settings['default_account_term_months']??12}}" required></div>
            <div class="field"><label>Closure notice (days)</label><input name="closure_notice_days" type="number" min="0" max="365" value="{{$settings['closure_notice_days']??30}}" required></div>
            <div class="field"><label>Default overdue fine (₹)</label><input name="default_overdue_fine_amount" type="number" min="0" step=".01" value="{{$settings['default_overdue_fine_amount']??0}}" required></div>
            <button>Save finance settings</button>
        </form>
    </section>
    @endif

    <section id="security" class="card section">
        <h2>Security and recent audit</h2>
        <form method="post" action="/admin/password" class="row">@csrf<div class="field"><label>Current password</label><input name="current_password" type="password" required></div><div class="field"><label>New password (12+)</label><input name="password" type="password" minlength="12" required></div><div class="field"><label>Confirm password</label><input name="password_confirmation" type="password" minlength="12" required></div><button>Change password</button></form>
        <h3>Recent actions</h3><table><thead><tr><th>Date</th><th>Staff</th><th>Action</th><th>Entity</th></tr></thead><tbody>@foreach($auditLogs as $log)<tr><td>{{$log->created_at}}</td><td>{{$log->actor??'System'}}</td><td>{{$log->action}}</td><td>{{$log->entity_type}} #{{$log->entity_id}}</td></tr>@endforeach</tbody></table>
    </section>
</main></div></div>
<script>
(() => {
    const sections = [...document.querySelectorAll('.section[id]')];
    const links = [...document.querySelectorAll('.nav a[href^="#"]')];
    const sectionIds = new Set(sections.map((section) => section.id));
    const title = document.getElementById('section-title');
    const storageKey = 'mahamilon.admin.active-section';

    const labelFor = (id) => {
        const link = links.find((item) => item.hash === `#${id}`);
        return link ? link.textContent.trim() : 'Admin dashboard';
    };

    const activate = (requestedId, updateHistory = false) => {
        const id = sectionIds.has(requestedId) ? requestedId : (sectionIds.has('overview') ? 'overview' : sections[0]?.id);
        if (!id) return;

        sections.forEach((section) => {
            const active = section.id === id;
            section.classList.toggle('is-active', active);
            section.hidden = !active;
        });
        links.forEach((link) => {
            const active = link.hash === `#${id}`;
            link.classList.toggle('active', active);
            if (active) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });

        const label = labelFor(id);
        if (title) title.textContent = label;
        document.title = `${label} · Mahamilon Operations`;
        sessionStorage.setItem(storageKey, id);

        if (updateHistory && window.location.hash !== `#${id}`) {
            history.pushState(null, '', `#${id}`);
        }
        window.scrollTo({top: 0, behavior: 'auto'});
    };

    const activateFromLocation = () => {
        const hashId = decodeURIComponent(window.location.hash.slice(1));
        const storedId = sessionStorage.getItem(storageKey);
        const id = sectionIds.has(hashId) ? hashId : (sectionIds.has(storedId) ? storedId : 'overview');
        activate(id);
        if (!sectionIds.has(hashId)) history.replaceState(null, '', `#${id}`);
    };

    const applicantSource = document.getElementById('account-member-source');
    const existingMemberFields = document.getElementById('existing-member-fields');
    const newApplicantFields = document.getElementById('new-applicant-fields');
    const accountSubmit = document.getElementById('admin-account-submit');
    const setFieldsEnabled = (container, enabled) => {
        if (!container) return;
        container.hidden = !enabled;
        container.querySelectorAll('input,select,textarea').forEach((control) => {
            const hasOptions = control.tagName !== 'SELECT' || control.options.length > 0;
            control.disabled = !enabled || !hasOptions;
            control.required = enabled && hasOptions && control.dataset.required === 'true';
        });
    };
    const syncApplicantSource = () => {
        if (!applicantSource) return;
        const isNew = applicantSource.value === 'new';
        setFieldsEnabled(existingMemberFields, !isNew);
        setFieldsEnabled(newApplicantFields, isNew);
        if (accountSubmit) accountSubmit.textContent = isNew ? accountSubmit.dataset.newLabel : accountSubmit.dataset.existingLabel;
    };
    applicantSource?.addEventListener('change', syncApplicantSource);
    syncApplicantSource();

    const loanOfferMember = document.getElementById('loan-offer-member');
    const loanOfferAmount = document.getElementById('loan-offer-amount');
    const loanOfferLimit = document.getElementById('loan-offer-limit');
    const syncLoanOfferLimit = () => {
        if (!loanOfferMember || !loanOfferAmount || !loanOfferLimit) return;
        const option = loanOfferMember.selectedOptions[0];
        const maximum = Number(option?.dataset.maxOffer || 0);
        const savings = Number(option?.dataset.savings || 0);
        if (!option?.value) {
            loanOfferAmount.removeAttribute('max');
            loanOfferLimit.textContent = 'Select a member to see the 85% eligibility limit.';
            return;
        }
        loanOfferAmount.max = maximum.toFixed(2);
        loanOfferLimit.textContent = `Maximum ₹${maximum.toLocaleString('en-IN', {minimumFractionDigits: 2})} (85% of ₹${savings.toLocaleString('en-IN', {minimumFractionDigits: 2})} active savings).`;
    };
    loanOfferMember?.addEventListener('change', syncLoanOfferLimit);
    syncLoanOfferLimit();

    links.forEach((link) => link.addEventListener('click', (event) => {
        event.preventDefault();
        activate(decodeURIComponent(link.hash.slice(1)), true);
    }));
    document.querySelectorAll('form').forEach((form) => form.addEventListener('submit', () => {
        const active = sections.find((section) => section.classList.contains('is-active'));
        if (active) sessionStorage.setItem(storageKey, active.id);
    }));
    window.addEventListener('hashchange', activateFromLocation);
    window.addEventListener('popstate', activateFromLocation);
    activateFromLocation();
})();
</script>
</body></html>
