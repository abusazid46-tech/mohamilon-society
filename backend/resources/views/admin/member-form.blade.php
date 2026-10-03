<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Membership Application Form - {{ $member->name }} ({{ $member->member_no }}) - Mahamilon Society</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Serif:ital,wght@0,400;0,700;1,400&display=swap">
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
            max-width: 820px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
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
        .btn-back { background: #e2e8f0; color: #334155; }
        .btn-back:hover { background: #cbd5e1; }

        /* Printable Form Container */
        .form-paper {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 36px 40px;
            position: relative;
        }

        /* Document Header */
        .doc-header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
            padding-right: 140px; /* leaves room for passport photo */
            position: relative;
        }
        .doc-header h1 {
            font-family: 'Noto Serif', serif;
            font-size: 24px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 0 0 4px;
            color: #0f172a;
        }
        .doc-header .motto {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #475569;
            margin: 0;
        }

        /* Passport Photo Box (Standard 35mm x 45mm Ratio) */
        .photo-box {
            position: absolute;
            top: 36px;
            right: 40px;
            width: 128px;
            height: 162px;
            border: 1.5px dashed #475569;
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: #ffffff;
            padding: 3px;
            box-sizing: border-box;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            z-index: 2;
        }
        .photo-inner {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 2px;
            background: #f8fafc;
            position: relative;
        }
        .photo-inner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 20%;
            display: block;
        }
        .photo-badge {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.75);
            color: #ffffff;
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 2px 0;
            text-align: center;
        }
        .photo-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 6px;
            height: 100%;
            width: 100%;
            box-sizing: border-box;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 2px;
            color: #475569;
        }
        .photo-placeholder i {
            font-size: 32px;
            color: #94a3b8;
            margin-bottom: 4px;
        }
        .photo-placeholder .photo-title {
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
            color: #1e293b;
        }
        .photo-placeholder .photo-sub {
            font-size: 9px;
            color: #64748b;
            line-height: 1.2;
            margin: 2px 0 4px;
        }
        .photo-placeholder .photo-dim {
            font-size: 8.5px;
            font-weight: 700;
            color: #0284c7;
            background: #e0f2fe;
            padding: 1px 6px;
            border-radius: 3px;
        }

        /* Header Details Meta */
        .header-meta {
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            gap: 12px;
            margin-bottom: 18px;
            font-size: 13px;
            padding-right: 140px; /* leaves room for passport photo */
        }
        .meta-field {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }
        .meta-field label {
            font-weight: 700;
            color: #334155;
            white-space: nowrap;
        }
        .meta-field .line {
            flex: 1;
            border-bottom: 1px dotted #0f172a;
            font-weight: 700;
            color: #0f172a;
            padding-left: 4px;
        }

        /* Instructions Box */
        .instructions-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 11px;
            color: #334155;
            margin-bottom: 16px;
        }
        .instructions-box ol {
            margin: 0;
            padding-left: 18px;
        }
        .instructions-box li {
            margin-bottom: 3px;
        }

        /* Formal Request Letter */
        .request-letter {
            font-size: 12.5px;
            line-height: 1.6;
            margin-bottom: 18px;
            text-align: justify;
        }
        .request-letter .underline-fill {
            display: inline-block;
            border-bottom: 1px solid #0f172a;
            font-weight: 700;
            padding: 0 8px;
            color: #0f172a;
        }

        /* Numbered Particulars List */
        .particulars-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }
        .part-row {
            display: flex;
            align-items: baseline;
            gap: 8px;
            font-size: 12.5px;
        }
        .part-num {
            font-weight: 700;
            min-width: 20px;
        }
        .part-label {
            font-weight: 600;
            color: #334155;
            white-space: nowrap;
        }
        .part-fill {
            flex: 1;
            border-bottom: 1px dotted #475569;
            font-weight: 700;
            color: #0f172a;
            padding-left: 6px;
        }

        /* Section Headings */
        .doc-section-title {
            text-align: center;
            font-weight: 800;
            font-size: 13px;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-top: 1.5px solid #0f172a;
            border-bottom: 1.5px solid #0f172a;
            padding: 4px 0;
            margin: 22px 0 12px;
            background: #f8fafc;
        }

        /* Signature Row */
        .sign-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 24px;
            padding-top: 10px;
        }
        .sign-block {
            text-align: center;
            min-width: 200px;
        }
        .sign-line {
            border-top: 1px solid #0f172a;
            margin-top: 36px;
            padding-top: 4px;
            font-size: 11px;
            font-weight: 700;
        }

        /* Introducers Grid */
        .introducers-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 12px;
        }
        .intro-box {
            border: 1px solid #cbd5e1;
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 11.5px;
        }
        .intro-field {
            display: flex;
            align-items: baseline;
            gap: 6px;
            margin-bottom: 6px;
        }
        .intro-field label {
            font-weight: 700;
            white-space: nowrap;
        }
        .intro-field .fill {
            flex: 1;
            border-bottom: 1px dotted #0f172a;
        }

        /* Office Use Table */
        .office-use-box {
            border: 1.5px solid #0f172a;
            padding: 12px 16px;
            margin-top: 18px;
            font-size: 11.5px;
            line-height: 1.6;
        }
        .memo-box {
            border: 1px dashed #475569;
            padding: 8px 14px;
            margin-top: 10px;
            background: #f8fafc;
        }

        /* PRINT STYLES */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 12mm;
            }
            body {
                background: #ffffff;
                padding: 0;
            }
            .action-bar, #editFormModal {
                display: none !important;
            }
            .form-paper {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }
            .doc-section-title {
                background: #eee !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .photo-box {
                top: 0 !important;
                right: 0 !important;
                width: 35mm !important;
                height: 45mm !important;
                border: 1.5px dashed #334155 !important;
                box-shadow: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .photo-inner img {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
            }
        }
    </style>
</head>
<body>

    <!-- SCREEN ONLY ACTION BAR -->
    <div class="action-bar">
        <a href="/admin#customer-report" class="btn btn-back">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        <div style="display: flex; align-items: center; gap: 10px;">
            @if(session('ok'))
                <span style="background: #16a34a; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700;">
                    <i class="bi bi-check-circle-fill"></i> {{ session('ok') }}
                </span>
            @endif
            @if(isset($errors) && $errors->any())
                <span style="background: #dc2626; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700;">
                    <i class="bi bi-exclamation-triangle-fill"></i> {{ $errors->first() }}
                </span>
            @endif
            <button type="button" onclick="openEditFormModal()" class="btn" style="background: #f59e0b; color: white; font-weight: 700;">
                <i class="bi bi-pencil-square"></i> Edit Form Details
            </button>
            <button type="button" onclick="window.print()" class="btn btn-print">
                <i class="bi bi-printer-fill"></i> Print Application Form
            </button>
        </div>
    </div>

    <!-- MAIN PRINTABLE PAPER -->
    <div class="form-paper">
        <!-- Photo Box on Top Right (Standard Passport Format 3.5cm x 4.5cm) -->
        <div class="photo-box">
            @if(isset($photo) && $photo)
                <div class="photo-inner">
                    <img src="/admin/kyc/{{ $photo->id }}/view" alt="Passport Photo">
                    <div class="photo-badge">PASSPORT PHOTO</div>
                </div>
            @else
                <div class="photo-placeholder">
                    <i class="bi bi-person-bounding-box"></i>
                    <span class="photo-title">Affix Recent</span>
                    <span class="photo-sub">Passport Photograph</span>
                    <span class="photo-dim">3.5 cm &times; 4.5 cm</span>
                </div>
            @endif
        </div>

        <!-- HEADER -->
        <div class="doc-header">
            <h1>Mahamilan Society</h1>
            <p class="motto">Live for Today, Hope for Tomorrow</p>
        </div>

        <!-- HEADER META -->
        <div class="header-meta">
            <div class="meta-field">
                <label>Name:</label>
                <span class="line">{{ $member->name }}</span>
            </div>
            <div class="meta-field">
                <label>Ledger No.:</label>
                <span class="line">{{ $member->member_no }}</span>
            </div>
            <div class="meta-field">
                <label>Address:</label>
                <span class="line">{{ $member->address }}</span>
            </div>
            <div class="meta-field">
                <label>Pin Code:</label>
                <span class="line">{{ $member->pincode ?? '781001' }}</span>
            </div>
        </div>

        <!-- INSTRUCTIONS -->
        <div class="instructions-box">
            <ol>
                <li>Please fill up the application form neatly and carefully.</li>
                <li>Along with the application form please attach the following documents: Three Passport size Photographs, Residence Proof (Permanent and Present), Salary/Employment/Professional Proof. The applicant should produce the originals while submitting the application form for verification, otherwise the application form will not be accepted.</li>
                <li>The Managing Committee keeps the right to reject a loan to any member, even though the formalities have been completed while enrolling as a member of the Society.</li>
            </ol>
        </div>

        <!-- FORMAL REQUEST LETTER -->
        <div class="request-letter">
            <p style="margin: 0 0 6px;"><b>To</b><br>The Secretary</p>
            <p style="margin: 0;">
                I request you to enroll me as a member of the <b>MAHAMILAN SOCIETY</b>. I am a resident of Assam and I hereby agree to abide the Rules and Regulations framed under the Bye-laws of the Society from time to time. I hereby declare that I am not a member of any other Cooperative Thrift & Credit Society with limited or, unlimited liability. I request you to allot me <span class="underline-fill">{{ $account->share_count ?? 1 }}</span> Shares and I hereby agree that Deposit of Rs. <span class="underline-fill">&#8377;{{ number_format($account->principal ?? 500, 2) }}</span> with the Society.<br>
                <b>Account Type &ndash;</b> <span class="underline-fill">{{ $account->scheme_name ?? 'Savings Account' }}</span>
            </p>
        </div>

        <p style="font-weight: 700; margin: 0 0 10px; font-size: 13px;">My particulars are given below:</p>

        <!-- NUMBERED PARTICULARS (1 - 15) -->
        <div class="particulars-list">
            <div class="part-row">
                <span class="part-num">1.</span>
                <span class="part-label">Full Name:</span>
                <span class="part-fill">{{ $member->name }}</span>
            </div>

            <div class="part-row">
                <span class="part-num">2.</span>
                <span class="part-label">(a) S/o, W/o, D/o:</span>
                <span class="part-fill">{{ $member->parent_name ?: ($member->father_name ?: '—') }}</span>
                <span class="part-label">(b) Mother’s Name:</span>
                <span class="part-fill">{{ $member->mother_name ?: '—' }}</span>
                <span class="part-label">(c) Father’s Name:</span>
                <span class="part-fill">{{ $member->father_name ?: ($member->parent_name ?: '—') }}</span>
            </div>

            <div class="part-row">
                <span class="part-num">3.</span>
                <span class="part-label">Age (in years):</span>
                <span class="part-fill" style="max-width: 100px;">{{ $member->age ?? (isset($member->date_of_birth) ? \Carbon\Carbon::parse($member->date_of_birth)->age : '—') }}</span>
                <span class="part-label">Date of Birth:</span>
                <span class="part-fill">{{ $member->date_of_birth ? date('d-m-Y', strtotime($member->date_of_birth)) : '—' }}</span>
                <span class="part-label">Blood Group:</span>
                <span class="part-fill" style="max-width: 100px;">{{ $member->blood_group ?? 'O+' }}</span>
            </div>

            <div class="part-row">
                <span class="part-num">5.</span>
                <span class="part-label">Educational Qualification:</span>
                <span class="part-fill">{{ $member->qualification ?? 'Graduate' }}</span>
                <span class="part-label">Marital Status:</span>
                <span class="part-fill">{{ $member->marital_status ?? 'Married' }}</span>
            </div>

            <div class="part-row">
                <span class="part-num">6.</span>
                <span class="part-label">Permanent Address:</span>
                <span class="part-fill">{{ $member->address }}</span>
                <span class="part-label">PHONE NO:</span>
                <span class="part-fill" style="max-width: 160px;">{{ $member->phone }}</span>
            </div>

            <div class="part-row">
                <span class="part-num">7.</span>
                <span class="part-label">Present/Correspondence Address:</span>
                <span class="part-fill">{{ $member->present_address ?? $member->address }}</span>
            </div>

            <div class="part-row">
                <span class="part-num">8.</span>
                <span class="part-label">Name of Bank:</span>
                <span class="part-fill">{{ $member->bank_name ?: '—' }}</span>
                <span class="part-label">Account No.:</span>
                <span class="part-fill">{{ $member->bank_account_no ?: '—' }}</span>
                <span class="part-label">IFSC:</span>
                <span class="part-fill" style="max-width: 120px;">{{ $member->bank_ifsc ?: '—' }}</span>
                @if(!empty($member->bank_branch))
                <span class="part-label">Branch:</span>
                <span class="part-fill" style="max-width: 120px;">{{ $member->bank_branch }}</span>
                @endif
            </div>

            @if(!empty($member->bank2_name) || !empty($member->bank2_account_no))
            <div class="part-row">
                <span class="part-num"></span>
                <span class="part-label">Secondary Bank:</span>
                <span class="part-fill">{{ $member->bank2_name ?: '—' }}</span>
                <span class="part-label">Account No.:</span>
                <span class="part-fill">{{ $member->bank2_account_no ?: '—' }}</span>
                <span class="part-label">IFSC:</span>
                <span class="part-fill" style="max-width: 120px;">{{ $member->bank2_ifsc ?: '—' }}</span>
                @if(!empty($member->bank2_branch))
                <span class="part-label">Branch:</span>
                <span class="part-fill" style="max-width: 120px;">{{ $member->bank2_branch }}</span>
                @endif
            </div>
            @endif

            <div class="part-row">
                <span class="part-num">9.</span>
                <span class="part-label">Employed in [Govt. Service/Semi-Govt./Public Ltd./Pvt. Ltd.]:</span>
                <span class="part-fill">{{ $member->occupation ?? 'Business / Self-Employed' }}</span>
            </div>

            <div class="part-row">
                <span class="part-num">10.</span>
                <span class="part-label">Office in which employed with full address:</span>
                <span class="part-fill">{{ $member->office_address ?: '—' }}</span>
            </div>

            <div class="part-row">
                <span class="part-num">11.</span>
                <span class="part-label">Designation & Salary Drawn (including all allowances):</span>
                <span class="part-fill">{{ $member->designation_salary ?: '—' }}</span>
            </div>

            <div class="part-row">
                <span class="part-num">14.</span>
                <span class="part-label">Are you an Income Tax Assessee:</span>
                <span class="part-fill" style="max-width: 80px;">{{ ($member->pan_no || $member->pan_last4) ? 'Yes' : 'No' }}</span>
                <span class="part-label">PAN No.:</span>
                <span class="part-fill" style="max-width: 160px;">{{ $member->pan_no ?: ($member->pan_last4 ?: '—') }}</span>
                <span class="part-label">Aadhaar No.:</span>
                <span class="part-fill">{{ $member->aadhaar_no ?: ($member->aadhaar_last4 ?: '—') }}</span>
            </div>

            <div class="part-row">
                <span class="part-num">15.</span>
                <span class="part-label">State whether any of your relative is a member of this Society:</span>
                <span class="part-fill">{{ $member->relative_member_details ?: 'No' }}</span>
            </div>
        </div>

        <div class="sign-row" style="margin-top: 16px;">
            <div>
                <b>Date:</b> {{ date('d-m-Y', strtotime($member->created_at ?? 'now')) }}
            </div>
            <div class="sign-block">
                <div class="sign-line">Signature of Applicant<br><b>{{ $member->name }}</b></div>
            </div>
        </div>

        <!-- DECLARATION -->
        <div class="doc-section-title">Declaration</div>
        <p style="font-size: 11.5px; line-height: 1.6; text-align: justify; margin: 0 0 14px;">
            I, <b>{{ $member->name }}</b> S/o, D/o, W/o <b>{{ $member->parent_name ?? $member->father_name ?? '—' }}</b> hereby truthfully and faithfully declare that, I am not a member of any other Cooperative Thrift & Credit Society and also I have not applied for the membership with any other such Society. I am a permanent resident of Guwahati, Assam. The details about my age, occupation and other aspects given by me are correct. If anything found wrong, the Management have the right to take necessary action against me. I promise to give written information to the Society regarding any change in address of my residence, place of work or change in my office/occupation in future.
        </p>
        <div class="sign-row" style="margin-top: 0;">
            <div>
                <b>Date:</b> {{ date('d-m-Y', strtotime($member->created_at ?? 'now')) }}
            </div>
            <div class="sign-block">
                <div class="sign-line">Signature of Applicant<br><b>{{ $member->name }}</b></div>
            </div>
        </div>

        <!-- NOMINATION -->
        <div class="doc-section-title">Nomination</div>
        <p style="font-size: 11.5px; line-height: 1.6; text-align: justify; margin: 0 0 14px;">
            I hereby nominate Sh./Smt/Miss <span class="underline-fill">{{ $member->nominee_name ?: '—' }}</span>, S/o, W/o, D/o <span class="underline-fill">{{ $member->nominee_parent ?: '—' }}</span> Relation <span class="underline-fill">{{ $member->nominee_relation ?: 'Nominee' }}</span> Occupation <span class="underline-fill">{{ $member->nominee_occupation ?: '—' }}</span> Address <span class="underline-fill">{{ $member->nominee_address ?: ($member->address ?: '—') }}</span> Age <span class="underline-fill">{{ $member->nominee_age ? $member->nominee_age . ' Years' : 'Adult' }}</span> as my nominee. After my death the entire amount, with interest and other benefits may be credited to his/her name.
        </p>
        <div class="sign-row" style="margin-top: 0;">
            <div>
                <b>Date:</b> {{ date('d-m-Y', strtotime($member->created_at ?? 'now')) }}
            </div>
            <div class="sign-block">
                <div class="sign-line">Signature of Applicant<br><b>{{ $member->name }}</b></div>
            </div>
        </div>

        <!-- DECLARATION BY INTRODUCERS -->
        <div class="doc-section-title">Declaration by Introducers</div>
        <p style="font-size: 11px; line-height: 1.5; text-align: justify; margin: 0 0 8px;">
            We hereby declare that Sh./Smt./Miss <b>{{ $member->name }}</b> S/o, W/o, D/o <b>{{ $member->parent_name ?: ($member->father_name ?: '—') }}</b> who has signed on the document in 3 places in our presence is known to us personally and keeping in view of the Bye-laws and Aims and Objectives of the Society, we are of the opinion that he/she is fit to be member of the Society. To the best of our knowledge the information furnished by him/her is correct. We shall be responsible individually/collectively for the compliance and all the transactions with the Society.
        </p>

        <div class="introducers-grid">
            <div class="intro-box">
                <div class="intro-field"><label>1. Full Name:</label><span class="fill">{{ $member->intro1_name ?: '—' }}</span></div>
                <div class="intro-field"><label>S/o, W/o, D/o:</label><span class="fill">{{ $member->intro1_parent ?: '—' }}</span></div>
                <div class="intro-field"><label>Address:</label><span class="fill">{{ $member->intro1_address ?: '—' }}</span></div>
                <div class="intro-field"><label>Account No.:</label><span class="fill">{{ $member->intro1_account ?: '—' }}</span><label style="margin-left: 8px;">LF No.:</label><span class="fill">{{ $member->intro1_lf ?: '—' }}</span></div>
                <div class="sign-line" style="margin-top: 24px;">Member’s Signature</div>
            </div>

            <div class="intro-box">
                <div class="intro-field"><label>2. Full Name:</label><span class="fill">{{ $member->intro2_name ?: '—' }}</span></div>
                <div class="intro-field"><label>S/o, W/o, D/o:</label><span class="fill">{{ $member->intro2_parent ?: '—' }}</span></div>
                <div class="intro-field"><label>Address:</label><span class="fill">{{ $member->intro2_address ?: '—' }}</span></div>
                <div class="intro-field"><label>Account No.:</label><span class="fill">{{ $member->intro2_account ?: '—' }}</span><label style="margin-left: 8px;">LF No.:</label><span class="fill">{{ $member->intro2_lf ?: '—' }}</span></div>
                <div class="sign-line" style="margin-top: 24px;">Member’s Signature</div>
            </div>
        </div>

        <!-- DECLARATION BY MC MEMBER -->
        <div class="doc-section-title">Declaration by MC Member</div>
        <p style="font-size: 11px; line-height: 1.5; text-align: justify; margin: 0 0 10px;">
            I recommend Sh/Smt/Miss <b>{{ $member->name }}</b> for the membership with the Society. I know personally the above applicant. I have seen the Ration Card (No. ____________) and Bank Pass Book (A/c No. <b>{{ $member->bank_account_no ?: 'Verified' }}</b>) and all other information furnished by him are verified by me.
        </p>
        <div style="text-align: right; margin-top: 24px;">
            <div style="display: inline-block; text-align: center; width: 300px; border-top: 1px solid #0f172a; padding-top: 4px; font-size: 11px; font-weight: 700;">
                Signature of Managing Committee Member
            </div>
        </div>

        <!-- FOR OFFICE USE ONLY -->
        <div class="office-use-box">
            <div style="font-weight: 800; font-size: 12px; text-transform: uppercase; margin-bottom: 6px;">For Office Use Only</div>
            <p style="margin: 0 0 6px;">
                Application Received on <b>{{ date('d-m-Y', strtotime($member->created_at ?? 'now')) }}</b> by Hand/Courier vide Receipt No. <b>REC-{{ str_pad($member->id, 5, '0', STR_PAD_LEFT) }}</b>.
            </p>
            <p style="margin: 0 0 10px;">
                The applicant has submitted the necessary documents with application and found complete and Recommend to <b>Accept</b> the Application.
            </p>
            <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                <div style="text-align: center; width: 220px; border-top: 1px solid #0f172a; padding-top: 4px; font-weight: 700;">
                    Name & Signature of Receiver with Date
                </div>
                <div style="text-align: center; width: 160px; border-top: 1px solid #0f172a; padding-top: 4px; font-weight: 700;">
                    Secretary
                </div>
            </div>

            <p style="margin: 14px 0 6px;">
                The Managing Committee in its meeting held on <b>{{ date('d-m-Y', strtotime($member->created_at ?? 'now')) }}</b> has unanimously in favour of the application hence accepted the application.
            </p>
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 18px;">
                <div>
                    As per the Resolution No. <b>RES-{{ date('Y') }}-{{ $member->id }}</b> dated <b>{{ date('d-m-Y', strtotime($member->created_at ?? 'now')) }}</b> the applicant is admitted as a member of the Society.
                </div>
                <div style="text-align: center; width: 160px; border-top: 1px solid #0f172a; padding-top: 4px; font-weight: 700;">
                    President
                </div>
            </div>

            <!-- MEMO PAYMENT DETAILS -->
            <div class="memo-box">
                <b style="letter-spacing: 1px;">M E M O &bull; Payment Details</b><br>
                Receipt No: <b>REC-{{ str_pad($member->id, 5, '0', STR_PAD_LEFT) }}</b> &nbsp;&bull;&nbsp;
                Amount: <b>&#8377;{{ number_format($account->principal ?? 500, 2) }}</b> &nbsp;&bull;&nbsp;
                Date: <b>{{ date('d-m-Y', strtotime($member->created_at ?? 'now')) }}</b> &nbsp;&bull;&nbsp;
                Payment Mode: <b>Cash / Society Treasury</b>
            </div>
        </div>
    </div>
    <!-- ================= EDIT FORM DETAILS MODAL ================= -->
    <div id="editFormModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); z-index: 99999; justify-content: center; align-items: center; padding: 20px;">
        <div style="background: white; border-radius: 12px; width: 100%; max-width: 900px; max-height: 90vh; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); overflow: hidden; display: flex; flex-direction: column;">
            <div style="background: linear-gradient(135deg, #0284c7, #0369a1); padding: 16px 22px; color: white; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="bi bi-pencil-square" style="font-size: 20px;"></i>
                    <div>
                        <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: white;">Edit Customer Application Form</h3>
                        <p style="margin: 2px 0 0; font-size: 11.5px; color: #e0f2fe;">Editing: {{ $member->member_no }} — {{ $member->name }}</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditFormModal()" style="background: none; border: none; color: white; font-size: 26px; cursor: pointer; line-height: 1;">&times;</button>
            </div>

            <form method="post" action="/admin/members/{{ $member->id }}/update" style="padding: 22px; margin: 0; overflow-y: auto; flex: 1;">
                @csrf

                <!-- SECTION 1: ACCOUNT & STATUS -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0284c7; text-transform: uppercase; margin-bottom: 10px;">
                        1. Account & Membership Status
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1.5fr 1fr 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Member No <span style="color:#dc2626">*</span></label>
                            <input type="text" name="member_no" value="{{ $member->member_no }}" required style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; font-weight: 700;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Customer Full Name <span style="color:#dc2626">*</span></label>
                            <input type="text" name="name" value="{{ $member->name }}" required style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; font-weight: 700;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Account Status <span style="color:#dc2626">*</span></label>
                            <select name="status" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; background: white;">
                                <option value="active" {{ ($member->user_status ?? $member->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ ($member->user_status ?? $member->status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="suspended" {{ ($member->user_status ?? $member->status ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="blocked" {{ ($member->user_status ?? $member->status ?? '') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">KYC Status <span style="color:#dc2626">*</span></label>
                            <select name="kyc_status" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; background: white;">
                                <option value="not_submitted" {{ ($member->kyc_status ?? 'not_submitted') === 'not_submitted' ? 'selected' : '' }}>Not Submitted</option>
                                <option value="pending" {{ ($member->kyc_status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ ($member->kyc_status ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ ($member->kyc_status ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Joining Date</label>
                            <input type="date" name="created_at" value="{{ $member->created_at ? substr($member->created_at, 0, 10) : '' }}" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: PARENTS & PERSONAL DETAILS -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0284c7; text-transform: uppercase; margin-bottom: 10px;">
                        2. Family & Personal Particulars
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 10px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">2(a). S/o, W/o, D/o</label>
                            <input type="text" name="parent_name" value="{{ $member->parent_name }}" placeholder="Son / Wife / Daughter of..." style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">2(b). Mother’s Name</label>
                            <input type="text" name="mother_name" value="{{ $member->mother_name }}" placeholder="Mother's Full Name" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">2(c). Father’s Name</label>
                            <input type="text" name="father_name" value="{{ $member->father_name }}" placeholder="Father's Full Name" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1.2fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Date of Birth</label>
                            <input type="date" name="date_of_birth" value="{{ $member->date_of_birth ? substr($member->date_of_birth, 0, 10) : '' }}" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Age (Years)</label>
                            <input type="number" name="age" value="{{ $member->age }}" min="1" max="120" placeholder="e.g. 32" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Blood Group</label>
                            <select name="blood_group" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; background: white;">
                                <option value="">Select</option>
                                @foreach(['O+','A+','B+','AB+','O-','A-','B-','AB-'] as $bg)
                                <option value="{{ $bg }}" {{ ($member->blood_group ?? '') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Qualification</label>
                            <input type="text" name="qualification" value="{{ $member->qualification }}" placeholder="e.g. Graduate / HS" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Marital Status</label>
                            <select name="marital_status" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; background: white;">
                                @foreach(['Married','Single','Widowed','Divorced'] as $ms)
                                <option value="{{ $ms }}" {{ ($member->marital_status ?? 'Married') === $ms ? 'selected' : '' }}>{{ $ms }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: CONTACT & ADDRESSES -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0284c7; text-transform: uppercase; margin-bottom: 10px;">
                        3. Contact & Addresses
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1.2fr 1fr; gap: 12px; margin-bottom: 10px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Mobile Phone <span style="color:#dc2626">*</span></label>
                            <input type="text" name="phone" value="{{ $member->phone }}" required style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Email Address</label>
                            <input type="email" name="email" value="{{ $member->email }}" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Reset MPIN (6-digit, optional)</label>
                            <input type="password" name="mpin" maxlength="6" pattern="^[0-9]{6}$" placeholder="Leave blank to keep" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 2fr 2fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Permanent Address <span style="color:#dc2626">*</span></label>
                            <input type="text" name="address" value="{{ $member->address }}" required style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Present / Correspondence Address</label>
                            <input type="text" name="present_address" value="{{ $member->present_address }}" placeholder="Present Address" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Pin Code</label>
                            <input type="text" name="pincode" value="{{ $member->pincode }}" placeholder="e.g. 781001" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: BANKING PARTICULARS (ALL BANKS) -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0284c7; text-transform: uppercase; margin-bottom: 10px;">
                        4. Banking Particulars (Multiple Banks)
                    </div>
                    <!-- Bank 1 -->
                    <div style="margin-bottom: 10px;">
                        <span style="font-size: 11px; font-weight: 700; color: #1e293b; display: block; margin-bottom: 4px;">Primary Bank Account</span>
                        <div style="display: grid; grid-template-columns: 1.5fr 1.5fr 1fr 1fr; gap: 10px;">
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">Bank Name</label>
                                <input type="text" name="bank_name" value="{{ $member->bank_name }}" placeholder="e.g. State Bank of India" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">Account Number</label>
                                <input type="text" name="bank_account_no" value="{{ $member->bank_account_no }}" placeholder="Bank Account Number" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">IFSC Code</label>
                                <input type="text" name="bank_ifsc" value="{{ $member->bank_ifsc }}" placeholder="e.g. SBIN0001234" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; text-transform: uppercase;">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">Branch</label>
                                <input type="text" name="bank_branch" value="{{ $member->bank_branch }}" placeholder="Branch Name" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                            </div>
                        </div>
                    </div>
                    <!-- Bank 2 -->
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: #1e293b; display: block; margin-bottom: 4px;">Secondary Bank Account (Optional)</span>
                        <div style="display: grid; grid-template-columns: 1.5fr 1.5fr 1fr 1fr; gap: 10px;">
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">Bank 2 Name</label>
                                <input type="text" name="bank2_name" value="{{ $member->bank2_name }}" placeholder="e.g. HDFC / Axis Bank" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">Account Number</label>
                                <input type="text" name="bank2_account_no" value="{{ $member->bank2_account_no }}" placeholder="Account Number" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">IFSC Code</label>
                                <input type="text" name="bank2_ifsc" value="{{ $member->bank2_ifsc }}" placeholder="e.g. HDFC0001234" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; text-transform: uppercase;">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">Branch</label>
                                <input type="text" name="bank2_branch" value="{{ $member->bank2_branch }}" placeholder="Branch Name" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 5: EMPLOYMENT & TAX -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0284c7; text-transform: uppercase; margin-bottom: 10px;">
                        5. Employment, Tax & Score
                    </div>
                    <div style="display: grid; grid-template-columns: 1.2fr 2fr 1.5fr; gap: 12px; margin-bottom: 10px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Employed In / Occupation</label>
                            <input type="text" name="occupation" value="{{ $member->occupation }}" placeholder="e.g. Govt / Business / Private" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Office Address</label>
                            <input type="text" name="office_address" value="{{ $member->office_address }}" placeholder="Office / Shop Address" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Designation & Salary Drawn</label>
                            <input type="text" name="designation_salary" value="{{ $member->designation_salary }}" placeholder="e.g. Executive, Rs. 35,000" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1.5fr; gap: 12px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Aadhaar Number</label>
                            <input type="text" name="aadhaar" value="{{ $member->aadhaar_no ?? $member->aadhaar_last4 }}" placeholder="12-digit Aadhaar Number" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">PAN Number</label>
                            <input type="text" name="pan" value="{{ $member->pan_no ?? $member->pan_last4 }}" placeholder="10-digit PAN (e.g. ABCDE1234F)" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; text-transform: uppercase;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">MCS Credit Score (300-900)</label>
                            <input type="number" name="mcs_score" value="{{ $member->mcs_score ?? 500 }}" min="300" max="900" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; font-weight: 700;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Relative in Society (if any)</label>
                            <input type="text" name="relative_member_details" value="{{ $member->relative_member_details }}" placeholder="Name, Relation, A/c No." style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                    </div>
                </div>

                <!-- SECTION 6: NOMINEE -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0284c7; text-transform: uppercase; margin-bottom: 10px;">
                        6. Nomination Clause
                    </div>
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 0.8fr 1fr; gap: 12px; margin-bottom: 10px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Nominee Full Name <span style="color:#dc2626">*</span></label>
                            <input type="text" name="nominee_name" value="{{ $member->nominee_name }}" required style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Nominee S/o, W/o, D/o</label>
                            <input type="text" name="nominee_parent" value="{{ $member->nominee_parent }}" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Relationship</label>
                            <input type="text" name="nominee_relation" value="{{ $member->nominee_relation }}" placeholder="e.g. Spouse / Son" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Nominee Age</label>
                            <input type="number" name="nominee_age" value="{{ $member->nominee_age }}" min="1" max="120" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Occupation</label>
                            <input type="text" name="nominee_occupation" value="{{ $member->nominee_occupation }}" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Nominee Address</label>
                        <input type="text" name="nominee_address" value="{{ $member->nominee_address }}" placeholder="Residential Address of Nominee" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                    </div>
                </div>

                <!-- SECTION 7: INTRODUCERS -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0284c7; text-transform: uppercase; margin-bottom: 10px;">
                        7. Introducer Declarations
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <!-- Introducer 1 -->
                        <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; background: white;">
                            <div style="font-size: 11.5px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Introducer Member 1</div>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <input type="text" name="intro1_name" value="{{ $member->intro1_name }}" placeholder="1. Full Name of Introducer" style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                <input type="text" name="intro1_parent" value="{{ $member->intro1_parent }}" placeholder="S/o, W/o, D/o" style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                    <input type="text" name="intro1_account" value="{{ $member->intro1_account }}" placeholder="Account No." style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                    <input type="text" name="intro1_lf" value="{{ $member->intro1_lf }}" placeholder="LF No." style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                </div>
                                <input type="text" name="intro1_address" value="{{ $member->intro1_address }}" placeholder="Address" style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                            </div>
                        </div>
                        <!-- Introducer 2 -->
                        <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; background: white;">
                            <div style="font-size: 11.5px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Introducer Member 2</div>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <input type="text" name="intro2_name" value="{{ $member->intro2_name }}" placeholder="2. Full Name of Introducer" style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                <input type="text" name="intro2_parent" value="{{ $member->intro2_parent }}" placeholder="S/o, W/o, D/o" style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                    <input type="text" name="intro2_account" value="{{ $member->intro2_account }}" placeholder="Account No." style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                    <input type="text" name="intro2_lf" value="{{ $member->intro2_lf }}" placeholder="LF No." style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                </div>
                                <input type="text" name="intro2_address" value="{{ $member->intro2_address }}" placeholder="Address" style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FORM ACTIONS -->
                <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                    <button type="button" onclick="closeEditFormModal()" class="btn" style="background: #e2e8f0; color: #334155; padding: 8px 18px; font-size: 13px;">Cancel</button>
                    <button type="submit" class="btn" style="background: #0284c7; color: white; padding: 8px 22px; font-size: 13px; font-weight: 700;">
                        <i class="bi bi-check-circle-fill"></i> Save & Update Form
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditFormModal() {
            const m = document.getElementById('editFormModal');
            if (m) m.style.display = 'flex';
        }
        function closeEditFormModal() {
            const m = document.getElementById('editFormModal');
            if (m) m.style.display = 'none';
        }
        document.getElementById('editFormModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeEditFormModal();
        });
    </script>
</body>
</html>
