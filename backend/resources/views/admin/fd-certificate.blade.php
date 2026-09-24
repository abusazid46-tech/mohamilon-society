<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fixed Deposit Certificate - {{ $account->certificate_no ?: $account->account_no }} - {{ $societyName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Serif:ital,wght@0,400;0,700;1,400&family=Cinzel:wght@600;700;800;900&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px 16px;
            background: #f1f5f9;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 13px;
            line-height: 1.5;
        }

        .action-bar {
            max-width: 860px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
        }
        .btn-print { background: #0284c7; color: white; }
        .btn-print:hover { background: #0369a1; }
        .btn-download { background: #059669; color: white; }
        .btn-download:hover { background: #047857; }
        .btn-back { background: #e2e8f0; color: #334155; }
        .btn-back:hover { background: #cbd5e1; }

        .cert-paper {
            max-width: 860px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            padding: 28px;
            position: relative;
            background-image: radial-gradient(#0284c7 0.4px, transparent 0.4px);
            background-size: 24px 24px;
            background-color: #ffffff;
        }

        .cert-border {
            border: 3px double #0369a1;
            padding: 26px 30px;
            position: relative;
            background: #ffffff;
            outline: 1px solid #bae6fd;
            outline-offset: -7px;
        }

        .cert-header {
            text-align: center;
            border-bottom: 2px solid #0369a1;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .cert-header h1 {
            font-family: 'Cinzel', 'Noto Serif', serif;
            font-size: 26px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0 0 4px;
            color: #0c4a6e;
        }
        .cert-header .motto {
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #0284c7;
            margin-bottom: 6px;
        }
        .cert-header .badge-cert {
            display: inline-block;
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: white;
            font-weight: 800;
            font-size: 14px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            padding: 6px 24px;
            border-radius: 4px;
            margin-top: 6px;
        }

        .meta-strip {
            display: flex;
            justify-content: space-between;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            padding: 10px 16px;
            border-radius: 6px;
            font-size: 12px;
            margin-bottom: 22px;
        }
        .meta-item b { color: #0369a1; }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }
        .card-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 14px 16px;
        }
        .card-box h3 {
            margin: 0 0 10px;
            font-size: 13px;
            font-weight: 800;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 12.5px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #64748b; font-weight: 600; }
        .info-val { font-weight: 700; color: #0f172a; text-align: right; }

        .financial-highlight {
            background: linear-gradient(135deg, #f0fdf4, #e0f2fe);
            border: 2px solid #38bdf8;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 22px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            text-align: center;
        }
        .fin-cell label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .fin-cell .amount {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }
        .fin-cell .amount.highlight {
            color: #059669;
        }

        .cert-statement {
            font-family: 'Noto Serif', serif;
            font-size: 13px;
            line-height: 1.7;
            text-align: justify;
            margin-bottom: 24px;
            color: #1e293b;
            padding: 0 4px;
        }

        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 36px;
            text-align: center;
        }
        .sig-box {
            padding-top: 40px;
            border-top: 1px dashed #94a3b8;
            font-size: 11.5px;
            color: #475569;
        }
        .sig-box b {
            display: block;
            color: #0f172a;
            font-size: 12.5px;
            margin-bottom: 2px;
        }

        .seal-stamp {
            display: inline-block;
            border: 2px solid #0284c7;
            color: #0284c7;
            font-weight: 800;
            font-size: 11px;
            padding: 6px 14px;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transform: rotate(-5deg);
            margin: 10px auto;
        }

        .terms-footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            font-size: 10.5px;
            color: #64748b;
            line-height: 1.4;
        }

        @media print {
            body { background: white; padding: 0; }
            .action-bar { display: none !important; }
            .cert-paper { box-shadow: none; border: none; padding: 0; }
            @page { margin: 15mm; size: A4 portrait; }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <a href="/admin#fd-rd" class="btn btn-back">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-print">
                <i class="bi bi-printer"></i> Print Certificate
            </button>
            <a href="/admin/accounts/{{ $account->id }}/fd-certificate/download" class="btn btn-download">
                <i class="bi bi-download"></i> Download HTML
            </a>
        </div>
    </div>

    <div class="cert-paper">
        <div class="cert-border">
            <div class="cert-header">
                <div class="motto">Cooperative Credit & Thrift Society</div>
                <h1>{{ $societyName }}</h1>
                <div style="font-size: 11.5px; color: #475569; margin-bottom: 6px;">Registered under Cooperative Societies Act &bull; Serving Financial Prosperity</div>
                <div class="badge-cert">Fixed Deposit Certificate</div>
            </div>

            <div class="meta-strip">
                <div class="meta-item">Certificate No: <b>{{ $account->certificate_no ?: ('FD-' . $account->account_no) }}</b></div>
                <div class="meta-item">Account No: <b>{{ $account->account_no }}</b></div>
                <div class="meta-item">Issued Date: <b>{{ $account->opened_on ?: date('Y-m-d') }}</b></div>
            </div>

            <div class="cert-statement">
                This is to certify that <b>{{ $account->member_name }}</b> (Member ID: <b>{{ $account->member_no }}</b>) has deposited with <b>{{ $societyName }}</b> the principal sum of <b>&#8377;{{ number_format($account->principal, 2) }}</b> under the <b>{{ $account->scheme_name }}</b> scheme. This term deposit earns interest at the contracted rate of <b>{{ $account->custom_interest_rate ?: $account->default_rate }}% per annum</b> with quarterly compounding, maturing on <b>{{ $account->matures_on ?: 'N/A' }}</b> with a total maturity value of <b>&#8377;{{ number_format($account->maturity_amount ?: $account->balance, 2) }}</b>, payable subject to the terms and rules of the society.
            </div>

            <div class="financial-highlight">
                <div class="fin-cell">
                    <label>Principal Amount</label>
                    <div class="amount">&#8377;{{ number_format($account->principal, 2) }}</div>
                </div>
                <div class="fin-cell">
                    <label>Annual Interest Rate</label>
                    <div class="amount">{{ $account->custom_interest_rate ?: $account->default_rate }}% p.a.</div>
                </div>
                <div class="fin-cell">
                    <label>Maturity Date</label>
                    <div class="amount" style="font-size: 15px; padding-top: 3px;">{{ $account->matures_on ?: 'On Demand' }}</div>
                </div>
                <div class="fin-cell">
                    <label>Maturity Value</label>
                    <div class="amount highlight">&#8377;{{ number_format($account->maturity_amount ?: $account->balance, 2) }}</div>
                </div>
            </div>

            <div class="details-grid">
                <div class="card-box">
                    <h3><i class="bi bi-person-badge"></i> Member / Depositor Info</h3>
                    <div class="info-row">
                        <span class="info-label">Depositor Name:</span>
                        <span class="info-val">{{ $account->member_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Member No:</span>
                        <span class="info-val">{{ $account->member_no }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Contact Phone:</span>
                        <span class="info-val">{{ $account->phone }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Registered Address:</span>
                        <span class="info-val" style="max-width: 200px; font-size: 11.5px;">{{ $account->address ?: 'On Record' }}</span>
                    </div>
                </div>

                <div class="card-box">
                    <h3><i class="bi bi-shield-check"></i> Scheme & Nomination</h3>
                    <div class="info-row">
                        <span class="info-label">Scheme Name:</span>
                        <span class="info-val">{{ $account->scheme_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Compounding Cycle:</span>
                        <span class="info-val">Quarterly Compounded</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Registered Nominee:</span>
                        <span class="info-val">{{ $account->nominee_name ?: 'None Registered' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-val" style="color: #059669; text-transform: uppercase;">{{ $account->status }}</span>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin: 10px 0;">
                <div class="seal-stamp"><i class="bi bi-patch-check-fill"></i> Official Term Deposit Seal</div>
            </div>

            <div class="signatures">
                <div class="sig-box">
                    <b>Account Holder / Depositor</b>
                    Signature on Acceptance
                </div>
                <div class="sig-box">
                    <b>Cashier / Accountant</b>
                    Verified & Recorded
                </div>
                <div class="sig-box">
                    <b>Authorized Signatory / Secretary</b>
                    For {{ $societyName }}
                </div>
            </div>

            <div class="terms-footer">
                <b>Terms & Conditions:</b> 1. Interest is compounded quarterly as per society bylaws. 2. Premature withdrawal is permitted subject to 1.0% interest rate penalty. 3. This certificate must be surrendered upon maturity or closure for final payment. 4. In case of depositor demise, settlement shall be made to the registered nominee.
            </div>
        </div>
    </div>

</body>
</html>
