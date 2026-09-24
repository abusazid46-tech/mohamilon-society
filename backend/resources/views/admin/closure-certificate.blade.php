<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Closure & Settlement Certificate - {{ $closure->account_no }} - Mahamilan Society</title>
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

        /* Top Action Bar (Screen Only) */
        .action-bar {
            max-width: 840px;
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

        /* Certificate Container */
        .cert-paper {
            max-width: 840px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            padding: 30px;
            position: relative;
            background-image: radial-gradient(#0284c7 0.5px, transparent 0.5px);
            background-size: 24px 24px;
            background-color: #ffffff;
        }

        /* Ornate Double Border */
        .cert-border {
            border: 3px solid #0f172a;
            padding: 24px 28px;
            position: relative;
            background: #ffffff;
        }
        .cert-border::before {
            content: '';
            position: absolute;
            inset: 4px;
            border: 1px solid #cbd5e1;
            pointer-events: none;
        }

        /* Certificate Header */
        .cert-header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .cert-header h1 {
            font-family: 'Cinzel', 'Noto Serif', serif;
            font-size: 26px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0 0 4px;
            color: #0f172a;
        }
        .cert-header .motto {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #0284c7;
            margin: 0 0 4px;
        }
        .cert-header .regd {
            font-size: 11px;
            color: #64748b;
            margin: 0;
        }

        /* Title Badge */
        .cert-title-badge {
            text-align: center;
            margin: 20px 0 16px;
        }
        .cert-title-badge h2 {
            display: inline-block;
            font-family: 'Cinzel', serif;
            font-size: 17px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #0f172a;
            border: 2px solid #0284c7;
            padding: 6px 24px;
            border-radius: 4px;
            background: #f0f9ff;
            margin: 0 0 6px;
        }
        .cert-meta-bar {
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
            color: #475569;
            margin-bottom: 18px;
            padding: 0 4px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 8px;
        }

        /* Formal Narrative */
        .cert-narrative {
            font-size: 13px;
            line-height: 1.8;
            color: #1e293b;
            text-align: justify;
            margin-bottom: 20px;
        }

        /* Grid Information Box */
        .cert-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 24px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 18px;
            margin-bottom: 22px;
        }
        .cert-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dotted #cbd5e1;
            padding-bottom: 4px;
            font-size: 12.5px;
        }
        .cert-row .label {
            color: #64748b;
            font-weight: 600;
        }
        .cert-row .val {
            color: #0f172a;
            font-weight: 700;
        }

        /* Settlement Amount Highlight */
        .settlement-box {
            background: #ecfdf5;
            border: 1.5px solid #10b981;
            border-radius: 6px;
            padding: 12px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .settlement-box .txt {
            font-size: 12px;
            font-weight: 700;
            color: #065f46;
        }
        .settlement-box .amt {
            font-size: 20px;
            font-weight: 800;
            color: #047857;
            font-family: 'Noto Serif', serif;
        }

        /* Declaration & Undertaking */
        .cert-undertaking {
            font-size: 11.5px;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 36px;
            padding: 10px 14px;
            border-left: 3px solid #0284c7;
            background: #f8fafc;
        }

        /* Signatures Section */
        .cert-signatures {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 30px;
            padding-top: 20px;
        }
        .sig-block {
            text-align: center;
            width: 200px;
        }
        .sig-line {
            border-top: 1.5px solid #0f172a;
            margin-top: 50px;
            padding-top: 6px;
            font-weight: 700;
            font-size: 12px;
            color: #0f172a;
        }
        .sig-block .sub {
            font-size: 10.5px;
            color: #64748b;
        }

        /* Seal Stamp */
        .seal-box {
            width: 90px;
            height: 90px;
            border: 2px dashed #0284c7;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #0284c7;
            font-size: 10px;
            font-weight: 800;
            text-align: center;
            letter-spacing: 0.5px;
            margin: 0 auto;
            text-transform: uppercase;
        }

        /* Print Styles */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 12mm;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .action-bar {
                display: none !important;
            }
            .cert-paper {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                background-image: none !important;
            }
            .cert-border {
                border: 2.5px solid #000000 !important;
                padding: 18mm 20mm !important;
            }
            .cert-border::before {
                border-color: #666 !important;
            }
            .cert-title-badge h2 {
                background: #eee !important;
                border-color: #333 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .settlement-box {
                background: #eee !important;
                border-color: #333 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <!-- SCREEN ACTION BAR -->
    <div class="action-bar">
        <a href="/admin#closures" class="btn btn-back">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        <div style="display: flex; gap: 8px;">
            <a href="/admin/closures/{{ $closure->id }}/certificate/download" class="btn btn-download">
                <i class="bi bi-download"></i> Download HTML
            </a>
            <button type="button" onclick="window.print()" class="btn btn-print">
                <i class="bi bi-printer-fill"></i> Print Certificate
            </button>
        </div>
    </div>

    <!-- MAIN CERTIFICATE PAPER -->
    <div class="cert-paper">
        <div class="cert-border">
            <!-- Header -->
            <div class="cert-header">
                <h1>Mahamilan Society</h1>
                <p class="motto">Live for Today, Hope for Tomorrow</p>
                <p class="regd">Registered under Society Registration Act · Guwahati, Assam</p>
            </div>

            <!-- Title Badge -->
            <div class="cert-title-badge">
                <h2>Account Closure & No Dues Certificate</h2>
            </div>

            <!-- Meta Reference Bar -->
            <div class="cert-meta-bar">
                <span><b>Certificate Ref:</b> MS/ACC-CLOSE/{{ str_pad($closure->id, 5, '0', STR_PAD_LEFT) }}</span>
                <span><b>Date of Issue:</b> {{ \Carbon\Carbon::parse($closure->updated_at ?? now())->format('d F Y') }}</span>
                <span><b>Account Status:</b> <span style="color:#059669; font-weight:800;">CLOSED & SETTLED</span></span>
            </div>

            <!-- Narrative Paragraph -->
            <div class="cert-narrative">
                This is to officially certify that the deposit / savings account described below, held and maintained with <b>Mahamilan Society</b> by the member named herein, has been successfully closed and liquidated following due maturity and statutory notice period completion. All final balances, accrued dividends, and dues have been fully settled and paid out.
            </div>

            <!-- Account & Member Details Grid -->
            <div class="cert-details-grid">
                <div class="cert-row">
                    <span class="label">Member Name:</span>
                    <span class="val">{{ $closure->name }}</span>
                </div>
                <div class="cert-row">
                    <span class="label">Member / Ledger ID:</span>
                    <span class="val">{{ $closure->member_no }}</span>
                </div>
                <div class="cert-row">
                    <span class="label">Account Number:</span>
                    <span class="val">{{ $closure->account_no }}</span>
                </div>
                <div class="cert-row">
                    <span class="label">Account Scheme:</span>
                    <span class="val">{{ $closure->scheme_name ?? 'Savings / Term Deposit' }}</span>
                </div>
                <div class="cert-row">
                    <span class="label">Mobile Number:</span>
                    <span class="val">{{ $closure->phone ?? '—' }}</span>
                </div>
                <div class="cert-row">
                    <span class="label">Account Opened On:</span>
                    <span class="val">{{ $closure->opened_on ? \Carbon\Carbon::parse($closure->opened_on)->format('d-m-Y') : '—' }}</span>
                </div>
                <div class="cert-row">
                    <span class="label">Closure Requested:</span>
                    <span class="val">{{ $closure->requested_on ? \Carbon\Carbon::parse($closure->requested_on)->format('d-m-Y') : '—' }}</span>
                </div>
                <div class="cert-row">
                    <span class="label">Maturity / Completed:</span>
                    <span class="val">{{ $closure->withdrawal_available_on ? \Carbon\Carbon::parse($closure->withdrawal_available_on)->format('d-m-Y') : ($closure->maturity_on ? \Carbon\Carbon::parse($closure->maturity_on)->format('d-m-Y') : date('d-m-Y')) }}</span>
                </div>
            </div>

            <!-- Settlement Amount Box -->
            <div class="settlement-box">
                <div>
                    <div class="txt">FINAL SETTLEMENT / CLOSURE AMOUNT PAID</div>
                    <small style="color:#047857;">Full and final terminal balance disbursed</small>
                </div>
                <div class="amt">&#8377; {{ number_format((float)($closure->closure_amount ?? $closure->balance), 2) }}</div>
            </div>

            <!-- Undertaking / Statement -->
            <div class="cert-undertaking">
                <b>Statutory Declaration & No-Liability Statement:</b><br>
                The Society hereby confirms that as on the date of this certificate, there are no outstanding claims, loans, liabilities, or encumbrances pending against the aforesaid account. The account stands permanently closed and deactivated in the central ledger of Mahamilan Society.
            </div>

            <!-- Signatures -->
            <div class="cert-signatures">
                <div class="sig-block">
                    <div class="sig-line">Member Signature</div>
                    <div class="sub">{{ $closure->name }}<br>(Account Holder)</div>
                </div>

                <div class="seal-box">
                    <i class="bi bi-shield-check" style="font-size: 20px;"></i>
                    <span>Official<br>Seal</span>
                </div>

                <div class="sig-block">
                    <div class="sig-line">Authorized Signatory</div>
                    <div class="sub">Secretary / Treasury Officer<br>Mahamilan Society</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
