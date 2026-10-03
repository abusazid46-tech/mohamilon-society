<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mohamilon Cooperative Society - Branch Management Console</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --navy: #092f6d;
            --navy2: #061f49;
            --red: #d71920;
            --bg: #eef3f8;
            --line: #dfe6f0;
            --muted: #64748b;
            --green: #087443;
            --top-teal: #024959;
            --top-teal-dark: #013743;
            --sidebar-bg: #0c263d;
            --sidebar-hover: #133959;
            --sidebar-active: #0284c7;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font: 14px/1.45 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: #15213a;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ================= 1. TOP NAVBAR (TEAL) ================= */
        .top-navbar {
            background: var(--top-teal);
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            color: #ffffff;
            font-size: 13px;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .nav-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .live-clock-badge {
            background: #026873;
            color: #ffffff;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 0.3px;
            padding: 4px 12px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .branch-badge-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1.5px solid #fbbf24;
            color: #ffffff;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: default;
        }
        .nav-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .lang-selector-box {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
            color: #0f172a;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .lang-selector-box select {
            border: none;
            outline: none;
            background: transparent;
            font-size: 12px;
            font-family: inherit;
            cursor: pointer;
            color: #0f172a;
        }
        .top-action-icons {
            display: flex;
            gap: 6px;
        }
        .top-action-btn {
            background: rgba(255, 255, 255, 0.16);
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.15s ease;
        }
        .top-action-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* ================= 2. SHELL & SIDEBAR ================= */
        .shell {
            display: flex;
            flex: 1;
            min-height: calc(100vh - 48px);
        }
        .side {
            width: 230px;
            background: var(--sidebar-bg);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            padding: 12px 10px;
            overflow-y: auto;
        }
        .side-brand {
            padding: 6px 12px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 8px;
        }
        .side-brand h4 {
            color: #ffffff;
            font-size: 14px;
            font-weight: 800;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .side-brand small {
            color: #94a3b8;
            font-size: 11px;
            display: block;
            margin-top: 3px;
        }
        .nav {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .nav a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #cbd5e1;
            text-decoration: none;
            padding: 9px 12px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 5px;
            transition: all 0.15s ease;
        }
        .nav-inner {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-inner i {
            font-size: 15px;
            width: 18px;
            text-align: center;
            color: #94a3b8;
        }
        .nav-arrow {
            font-size: 13px;
            font-weight: 700;
            color: #64748b;
        }
        .nav a:hover {
            background: var(--sidebar-hover);
            color: #ffffff;
        }
        .nav a:hover .nav-inner i, .nav a:hover .nav-arrow {
            color: #ffffff;
        }
        .nav a.active, .nav a.is-active {
            background: var(--sidebar-active);
            color: #ffffff;
        }
        .nav a.active .nav-inner i, .nav a.is-active .nav-inner i,
        .nav a.active .nav-arrow, .nav a.is-active .nav-arrow {
            color: #ffffff;
        }
        .logout {
            margin-top: auto;
            padding: 14px 4px 6px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
        .logout button {
            width: 100%;
            background: rgba(239, 68, 68, 0.18);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.28);
            border-radius: 6px;
            padding: 8px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.15s ease;
        }
        .logout button:hover {
            background: #ef4444;
            color: #ffffff;
        }

        /* ================= 3. MAIN CONTENT ================= */
        .main {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .content {
            padding: 14px 18px 60px;
            flex: 1;
            overflow-y: auto;
        }

        /* GREEN WELCOME STRIP */
        .welcome-strip {
            background: #0d8a59;
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(13, 138, 89, 0.2);
        }

        /* STAT CARDS (EXACT VISUAL MATCH) */
        .stat-cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }
        .stat-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 12px 14px;
            border: 1px solid var(--line);
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            transition: all 0.15s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }
        .stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 17px;
            flex-shrink: 0;
        }
        .stat-content {
            min-width: 0;
        }
        .stat-content h4 {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .stat-content span {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            display: block;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Color accents */
        .icon-blue { background: #2563eb; }
        .icon-purple { background: #7c3aed; }
        .icon-green { background: #059669; }
        .icon-orange { background: #ea580c; }
        .icon-red { background: #dc2626; }
        .icon-teal { background: #0d9488; }
        .icon-gold { background: #d97706; }
        .icon-indigo { background: #4f46e5; }
        .icon-sky { background: #0284c7; }
        .icon-slate { background: #475569; }
        .icon-rose { background: #e11d48; }
        .icon-emerald { background: #047857; }

        /* PERIOD HIGHLIGHTS ROW */
        .highlights-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }
        .highlight-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 12px 16px;
            border: 1px solid var(--line);
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }
        .highlight-card strong {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            display: block;
            line-height: 1.1;
        }
        .highlight-card span {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            margin-top: 2px;
            display: block;
        }

        /* CUSTOMER DROPDOWN STYLING (MATCHING SCREENSHOT) */
        .nav-dropdown-group {
            margin: 2px 0;
        }
        .nav-sub-items {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding-left: 14px;
            margin: 3px 0 6px 0;
        }
        .sub-nav-item {
            display: flex !important;
            align-items: center;
            gap: 9px;
            padding: 7px 12px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #94a3b8 !important;
            border-radius: 4px !important;
            text-decoration: none !important;
            transition: all 0.15s ease !important;
        }
        .sub-nav-item:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.06) !important;
        }
        .sub-nav-item.active, .sub-nav-item.is-active {
            background: #0284c7 !important;
            color: #ffffff !important;
        }
        .sub-nav-icon {
            font-weight: 800;
            font-size: 15px;
            line-height: 1;
        }
        .dropdown-chevron i {
            font-size: 11px;
            transition: transform 0.2s ease;
        }
        .dropdown-chevron.collapsed i {
            transform: rotate(-90deg);
        }

        /* CUSTOMER REPORT TABLE STYLING (MATCHING SCREENSHOT 4) */
        .report-table thead tr {
            background: #0284c7 !important;
            color: #ffffff !important;
        }
        .report-table thead th {
            color: #ffffff !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            padding: 10px 12px !important;
            text-transform: none !important;
            letter-spacing: normal !important;
        }
        .report-table tbody td {
            font-size: 12px !important;
            padding: 9px 12px !important;
        }

        /* PRESERVED STYLES FOR ALL INNER SECTIONS */
        h1{font-size:24px;margin:0}h2{font-size:20px;margin:0 0 16px}h3{font-size:16px;margin:18px 0 10px}.muted{color:var(--muted)}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px}.metric,.card{background:white;border:1px solid var(--line);border-radius:12px;box-shadow:0 4px 18px #17335a0c}.metric{padding:17px}.metric strong{display:block;font-size:25px;color:var(--navy)}.card{padding:18px;margin-top:0;overflow:auto}.section{display:none;scroll-margin-top:90px}.section.is-active{display:block}
        table{width:100%;border-collapse:collapse;min-width:780px}th,td{text-align:left;padding:10px;border-bottom:1px solid #e8edf4;vertical-align:top}th{font-size:12px;text-transform:uppercase;color:var(--muted);letter-spacing:.04em}
        input,select,textarea{padding:9px 10px;border:1px solid #cbd5e1;border-radius:7px;background:white;max-width:100%}textarea{min-height:64px}.row{display:flex;flex-wrap:wrap;gap:8px;align-items:end}.field{display:grid;gap:5px;min-width:150px}.field label{font-size:12px;color:var(--muted);font-weight:750}
        button,.button{border:0;padding:9px 12px;border-radius:7px;background:var(--navy);color:white;text-decoration:none;display:inline-block;font-weight:750;cursor:pointer}.danger{background:var(--red)}.success{background:var(--green)}.secondary{background:#64748b}
        .flash{background:#dff7e9;color:#075f38;padding:11px 14px;border-radius:8px;margin-bottom:16px}.error{background:#ffe4e5;color:#8e1015;padding:11px 14px;border-radius:8px;margin-bottom:16px}
        .badge{display:inline-block;padding:4px 8px;border-radius:20px;background:#e8eef7;color:#29405f;font-weight:800;font-size:11px;text-transform:capitalize}.badge.active,.badge.accepted,.badge.approved{background:#dff7e9;color:#087443}.badge.rejected{background:#ffe4e5;color:#a3171c}.perm-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:6px}.check{display:flex;gap:7px;align-items:flex-start;padding:6px}.check input{margin-top:3px}.split{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        @media(max-width:1024px){.stat-cards-grid{grid-template-columns:repeat(2,1fr)}.highlights-row{grid-template-columns:1fr}}
        @media(max-width:900px){.shell{flex-direction:column}.side{width:100%;height:auto;position:static}.nav{display:flex;overflow:auto}.nav a{white-space:nowrap}.stat-cards-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
@php
    $can=fn($p)=>auth()->user()->role==='super_admin'||in_array($p,$permissions,true);
    $role=str_replace('_',' ',auth()->user()->role);
    $fmt=fn($v)=>($v < 0 ? '-&#8377;' . number_format(abs((float)$v), 2) : '&#8377;' . number_format((float)$v, 2));
@endphp

<!-- TOP NAVBAR -->
<header class="top-navbar">
    <div class="nav-left">
        <span class="live-clock-badge" id="liveClock">Saturday, June 13, 2026 01:30:44 PM</span>
        <button class="branch-badge-btn"><i class="bi bi-geo-alt"></i> Branch Panel</button>
    </div>
    <div class="nav-right">
        <div class="lang-selector-box">
            <i class="bi bi-translate"></i>
            <select>
                <option selected>English</option>
                <option>Assamese</option>
                <option>Bengali</option>
                <option>Hindi</option>
            </select>
        </div>
        <div class="top-action-icons">
            <button class="top-action-btn" title="Logged in: {{ auth()->user()->name }} ({{ ucwords($role) }})"><i class="bi bi-person-fill"></i></button>
            <a href="#security" class="top-action-btn" title="Database Backup"><i class="bi bi-floppy2-fill"></i></a>
            <button class="top-action-btn" title="Notifications"><i class="bi bi-bell-fill"></i></button>
            <button class="top-action-btn" title="Full Screen" onclick="toggleFullScreen()"><i class="bi bi-arrows-fullscreen"></i></button>
        </div>
    </div>
</header>

<div class="shell">
<aside class="side">
    <div class="side-brand">
        <h4>Mohamilon Society</h4>
        <small>Operations & Finance Console</small>
    </div>
    <nav class="nav">
        <!-- 1. OVERVIEW -->
        <a href="#overview" class="active">
            <div class="nav-inner"><i class="bi bi-speedometer2"></i> <span>Overview</span></div>
            <span class="nav-arrow">&rsaquo;</span>
        </a>

        <!-- 2. CUSTOMER DROPDOWN -->
        <div class="nav-dropdown-group" id="customer-dropdown-group">
            <a href="javascript:void(0)" class="nav-dropdown-trigger" id="customer-nav-trigger" onclick="toggleNavGroup('customer-sub-items', 'customer-chevron', event)">
                <div class="nav-inner"><i class="bi bi-people-fill"></i> <span>Customer</span></div>
                <span class="nav-arrow dropdown-chevron" id="customer-chevron"><i class="bi bi-chevron-right"></i></span>
            </a>
            <div class="nav-sub-items" id="customer-sub-items" style="display: none;">
                <a href="#customer-create" class="sub-nav-item" id="nav-customer-create">
                    <span class="sub-nav-icon">+</span> <span>Create Customer</span>
                </a>
                <a href="#customer-search" class="sub-nav-item" id="nav-customer-search">
                    <i class="bi bi-search"></i> <span>Search Customer</span>
                </a>
                <a href="#customer-kyc" class="sub-nav-item" id="nav-customer-kyc">
                    <i class="bi bi-cloud-arrow-up"></i> <span>Upload KYC</span>
                </a>
                <a href="#customer-report" class="sub-nav-item" id="nav-customer-report">
                    <i class="bi bi-file-earmark-text"></i> <span>Customer Report</span>
                </a>
                <a href="#customer-nominee" class="sub-nav-item" id="nav-customer-nominee">
                    <i class="bi bi-person-heart"></i> <span>Nominee</span>
                </a>
            </div>
        </div>

        <!-- 3. ACCOUNTS & DEPOSITS DROPDOWN -->
        <div class="nav-dropdown-group" id="accounts-dropdown-group">
            <a href="javascript:void(0)" class="nav-dropdown-trigger" id="accounts-nav-trigger" onclick="toggleNavGroup('accounts-sub-items', 'accounts-chevron', event)">
                <div class="nav-inner"><i class="bi bi-wallet2"></i> <span>Accounts & Deposits</span></div>
                <span class="nav-arrow dropdown-chevron" id="accounts-chevron"><i class="bi bi-chevron-right"></i></span>
            </a>
            <div class="nav-sub-items" id="accounts-sub-items" style="display: none;">
                <a href="#accounts" class="sub-nav-item" id="nav-accounts">
                    <i class="bi bi-person-badge"></i> <span>All Accounts</span>
                </a>
                <a href="#deposits" class="sub-nav-item" id="nav-deposits">
                    <i class="bi bi-cash-coin"></i> <span>Deposits & Collection</span>
                </a>
                <a href="#fd-rd" class="sub-nav-item" id="nav-fd-rd">
                    <i class="bi bi-piggy-bank"></i> <span>FD & RD Schemes</span>
                </a>
                @if($can('account.close'))
                <a href="#closures" class="sub-nav-item" id="nav-closures">
                    <i class="bi bi-door-closed"></i> <span>Account Closure</span>
                </a>
                @endif
            </div>
        </div>

        <!-- 4. TRANSACTIONS & BILLING DROPDOWN -->
        <div class="nav-dropdown-group" id="transactions-dropdown-group">
            <a href="javascript:void(0)" class="nav-dropdown-trigger" id="transactions-nav-trigger" onclick="toggleNavGroup('transactions-sub-items', 'transactions-chevron', event)">
                <div class="nav-inner"><i class="bi bi-arrow-left-right"></i> <span>Transactions & Billing</span></div>
                <span class="nav-arrow dropdown-chevron" id="transactions-chevron"><i class="bi bi-chevron-right"></i></span>
            </a>
            <div class="nav-sub-items" id="transactions-sub-items" style="display: none;">
                <a href="#transactions" class="sub-nav-item" id="nav-transactions">
                    <i class="bi bi-journal-text"></i> <span>Post Transactions</span>
                </a>
                <a href="#payments" class="sub-nav-item" id="nav-payments">
                    <i class="bi bi-credit-card-2-front"></i> <span>Member Payments</span>
                </a>
                <a href="#receipts" class="sub-nav-item" id="nav-receipts">
                    <i class="bi bi-receipt"></i> <span>Official Receipts</span>
                </a>
                <a href="#statements" class="sub-nav-item" id="nav-statements">
                    <i class="bi bi-file-earmark-text"></i> <span>Member Statements</span>
                </a>
            </div>
        </div>

        <!-- 5. LOAN MANAGEMENT DROPDOWN (MATCHING SCREENSHOT) -->
        <div class="nav-dropdown-group" id="loans-dropdown-group">
            <a href="javascript:void(0)" class="nav-dropdown-trigger" id="loans-nav-trigger" onclick="toggleNavGroup('loans-sub-items', 'loans-chevron', event)">
                <div class="nav-inner"><i class="bi bi-bank"></i> <span>Loans</span></div>
                <span class="nav-arrow dropdown-chevron" id="loans-chevron"><i class="bi bi-chevron-right"></i></span>
            </a>
            <div class="nav-sub-items" id="loans-sub-items" style="display: none;">
                <a href="#loan-apply" class="sub-nav-item" id="nav-loan-apply">
                    <i class="bi bi-file-earmark-plus"></i> <span>Apply Loan</span>
                </a>
                <a href="#loan-fixed" class="sub-nav-item" id="nav-loan-fixed">
                    <i class="bi bi-file-earmark-lock"></i> <span>Apply Fixed Loan</span>
                </a>
                <a href="#loan-kyc" class="sub-nav-item" id="nav-loan-kyc">
                    <i class="bi bi-cloud-arrow-up"></i> <span>Upload Loan KYC</span>
                </a>
                <a href="#loan-report" class="sub-nav-item" id="nav-loan-report">
                    <i class="bi bi-table"></i> <span>Loan Report</span>
                </a>
                <a href="#loan-statement" class="sub-nav-item" id="nav-loan-statement">
                    <i class="bi bi-file-earmark-ruled"></i> <span>Loan Statement</span>
                </a>
                <a href="#loan-emi-pay" class="sub-nav-item" id="nav-loan-emi-pay">
                    <i class="bi bi-credit-card-2-front"></i> <span>Single Loan Emi Pay</span>
                </a>
                <a href="#loan-emi-due" class="sub-nav-item" id="nav-loan-emi-due">
                    <i class="bi bi-exclamation-triangle"></i> <span>EMI Due Report</span>
                </a>
                <a href="#loan-collection-report" class="sub-nav-item" id="nav-loan-collection-report">
                    <i class="bi bi-receipt-cutoff"></i> <span>Collection Report</span>
                </a>
                <a href="#loan-closer-request" class="sub-nav-item" id="nav-loan-closer-request">
                    <i class="bi bi-door-closed"></i> <span>Loan Closer Request</span>
                </a>
                <a href="#loan-closer-report" class="sub-nav-item" id="nav-loan-closer-report">
                    <i class="bi bi-check-circle"></i> <span>Loan Closer Report</span>
                </a>
            </div>
        </div>

        <!-- 6. FINANCE & TREASURY DROPDOWN -->
        @if($can('cash.manage') || $can('bank.transfer') || $can('profit_loss.manage'))
        <div class="nav-dropdown-group" id="finance-dropdown-group">
            <a href="javascript:void(0)" class="nav-dropdown-trigger" id="finance-nav-trigger" onclick="toggleNavGroup('finance-sub-items', 'finance-chevron', event)">
                <div class="nav-inner"><i class="bi bi-safe2-fill"></i> <span>Finance & Treasury</span></div>
                <span class="nav-arrow dropdown-chevron" id="finance-chevron"><i class="bi bi-chevron-right"></i></span>
            </a>
            <div class="nav-sub-items" id="finance-sub-items" style="display: none;">
                @if($can('cash.manage'))
                <a href="#cash" class="sub-nav-item" id="nav-cash">
                    <i class="bi bi-safe"></i> <span>Cash in Treasury</span>
                </a>
                @endif
                @if($can('bank.transfer'))
                <a href="#banks" class="sub-nav-item" id="nav-banks">
                    <i class="bi bi-bank2"></i> <span>Bank Accounts & Contra</span>
                </a>
                @endif
                @if($can('profit_loss.manage'))
                <a href="#expenses" class="sub-nav-item" id="nav-expenses">
                    <i class="bi bi-calculator"></i> <span>Expenses & P&L</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- 7. REPORTS & ANALYTICS -->
        @if($can('reports.view'))
        <a href="#reports">
            <div class="nav-inner"><i class="bi bi-bar-chart-line-fill"></i> <span>Reports & Analytics</span></div>
            <span class="nav-arrow">&rsaquo;</span>
        </a>
        @endif

        <!-- 8. SYSTEM & ADMINISTRATION DROPDOWN -->
        <div class="nav-dropdown-group" id="system-dropdown-group">
            <a href="javascript:void(0)" class="nav-dropdown-trigger" id="system-nav-trigger" onclick="toggleNavGroup('system-sub-items', 'system-chevron', event)">
                <div class="nav-inner"><i class="bi bi-gear-fill"></i> <span>System & Settings</span></div>
                <span class="nav-arrow dropdown-chevron" id="system-chevron"><i class="bi bi-chevron-right"></i></span>
            </a>
            <div class="nav-sub-items" id="system-sub-items" style="display: none;">
                @if(auth()->user()->role==='super_admin')
                <a href="#staff" class="sub-nav-item" id="nav-staff">
                    <i class="bi bi-person-gear"></i> <span>Staff & Permissions</span>
                </a>
                <a href="#settings" class="sub-nav-item" id="nav-settings">
                    <i class="bi bi-qr-code"></i> <span>Payment QR & Settings</span>
                </a>
                @endif
                <a href="#security" class="sub-nav-item" id="nav-security">
                    <i class="bi bi-shield-check"></i> <span>Security & Backup</span>
                </a>
            </div>
        </div>
    </nav>
    <form class="logout" method="post" action="/admin/logout">@csrf<button><i class="bi bi-box-arrow-right"></i> Sign out</button></form>
</aside>

<div class="main">
<main class="content">
    @if(session('ok'))<div class="flash">{{session('ok')}}</div>@endif
    @if(isset($errors) && $errors->any())<div class="error">{{$errors->first()}}</div>@endif

    <section id="overview" class="section is-active">
        <!-- GREEN WELCOME BANNER (MATCHING SCREENSHOT) -->
        <div class="welcome-strip">
            <span>Welcome, Mohamilon Cooperative Society &bull; Branch Panel</span>
            <span style="font-weight:600;font-size:12px;opacity:0.95">Logged in: <b>{{ auth()->user()->name }}</b> ({{ ucwords($role) }})</span>
        </div>

        <!-- Section Title for JS switcher -->
        <h2 id="section-title" style="display:none">Overview</h2>

        <!-- PERIOD FILTER BAR (PRESERVED ORIGINAL) -->
        <div style="background:white;border:1px solid var(--line);border-radius:8px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                <span style="font-weight:750;font-size:12px;margin-right:4px;color:#475569">Period Filter:</span>
                <a href="/admin?date_filter=today#overview" class="button {{ $dateFilter === 'today' ? '' : 'secondary' }}" style="padding:4px 10px;font-size:12px;border-radius:5px">Today</a>
                <a href="/admin?date_filter=yesterday#overview" class="button {{ $dateFilter === 'yesterday' ? '' : 'secondary' }}" style="padding:4px 10px;font-size:12px;border-radius:5px">Yesterday</a>
                <a href="/admin?date_filter=this_week#overview" class="button {{ $dateFilter === 'this_week' ? '' : 'secondary' }}" style="padding:4px 10px;font-size:12px;border-radius:5px">This Week</a>
                <a href="/admin?date_filter=this_month#overview" class="button {{ $dateFilter === 'this_month' ? '' : 'secondary' }}" style="padding:4px 10px;font-size:12px;border-radius:5px">This Month</a>
                <a href="/admin?date_filter=this_year#overview" class="button {{ $dateFilter === 'this_year' ? '' : 'secondary' }}" style="padding:4px 10px;font-size:12px;border-radius:5px">This Year</a>
            </div>
            <form method="get" action="/admin#overview" style="display:flex;align-items:center;gap:6px">
                <input type="hidden" name="date_filter" value="custom">
                <input type="date" name="custom_from" value="{{ $customFrom ?? '' }}" style="padding:4px 8px;font-size:12px;border-radius:5px">
                <span class="muted" style="font-size:12px">to</span>
                <input type="date" name="custom_to" value="{{ $customTo ?? '' }}" style="padding:4px 8px;font-size:12px;border-radius:5px">
                <button type="submit" style="padding:4px 12px;font-size:12px;border-radius:5px">Filter</button>
            </form>
        </div>

        <!-- 3 PERIOD HIGHLIGHTS (PRESERVED ORIGINAL) -->
        <div class="highlights-row">
            <div class="highlight-card">
                <div class="stat-icon icon-green"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <strong>{!! $fmt($filteredCollection) !!}</strong>
                    <span>Collection ({{ ucwords(str_replace('_', ' ', $dateFilter)) }})</span>
                </div>
            </div>
            <div class="highlight-card">
                <div class="stat-icon icon-red"><i class="bi bi-wallet2"></i></div>
                <div>
                    <strong>{!! $fmt($filteredExpenses) !!}</strong>
                    <span>Expenses ({{ ucwords(str_replace('_', ' ', $dateFilter)) }})</span>
                </div>
            </div>
            <div class="highlight-card">
                <div class="stat-icon icon-blue"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <strong style="color:{{ $filteredPnl['net'] < 0 ? 'var(--red)' : 'var(--green)' }}">{!! $fmt($filteredPnl['net']) !!}</strong>
                    <span>Net P&L ({{ ucwords(str_replace('_', ' ', $dateFilter)) }})</span>
                </div>
            </div>
        </div>

        <!-- 12 MASTER KPI CARDS (PRESERVED ORIGINAL METRICS WITH MATCHED CARD UI) -->
        <div class="stat-cards-grid">
            <a href="#members" style="text-decoration:none">
                <div class="stat-card">
                    <div class="stat-icon icon-blue"><i class="bi bi-people-fill"></i></div>
                    <div class="stat-content">
                        <h4>{{ $metrics['members'] }}</h4>
                        <span>Total Members</span>
                    </div>
                </div>
            </a>

            <a href="#members" style="text-decoration:none">
                <div class="stat-card">
                    <div class="stat-icon icon-orange"><i class="bi bi-person-exclamation"></i></div>
                    <div class="stat-content">
                        <h4>{{ $metrics['pending_members'] }}</h4>
                        <span>Pending Members</span>
                    </div>
                </div>
            </a>

            <a href="#accounts" style="text-decoration:none">
                <div class="stat-card">
                    <div class="stat-icon icon-green"><i class="bi bi-wallet-fill"></i></div>
                    <div class="stat-content">
                        <h4>{!! $fmt($metrics['deposits']) !!}</h4>
                        <span>Active Deposits</span>
                    </div>
                </div>
            </a>

            <a href="#payments" style="text-decoration:none">
                <div class="stat-card">
                    <div class="stat-icon icon-gold"><i class="bi bi-credit-card-2-front-fill"></i></div>
                    <div class="stat-content">
                        <h4>{{ $metrics['pending_payments'] }}</h4>
                        <span>Payments to Verify</span>
                    </div>
                </div>
            </a>

            <a href="#loans" style="text-decoration:none">
                <div class="stat-card">
                    <div class="stat-icon icon-purple"><i class="bi bi-cash-coin"></i></div>
                    <div class="stat-content">
                        <h4>{{ $metrics['pending_loans'] }}</h4>
                        <span>Open Loans</span>
                    </div>
                </div>
            </a>

            <a href="#loans" style="text-decoration:none">
                <div class="stat-card">
                    <div class="stat-icon icon-teal"><i class="bi bi-check-circle-fill"></i></div>
                    <div class="stat-content">
                        <h4>{{ $metrics['pending_repayments'] }}</h4>
                        <span>Repayments to Verify</span>
                    </div>
                </div>
            </a>

            <a href="#loans" style="text-decoration:none">
                <div class="stat-card" style="{{ $metrics['overdue_installments'] > 0 ? 'border-color:#fca5a5;background:#fff5f5' : '' }}">
                    <div class="stat-icon icon-red"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <div class="stat-content">
                        <h4 style="{{ $metrics['overdue_installments'] > 0 ? 'color:var(--red)' : '' }}">{{ $metrics['overdue_installments'] }}</h4>
                        <span>Overdue Installments</span>
                    </div>
                </div>
            </a>

            <a href="#transactions" style="text-decoration:none">
                <div class="stat-card">
                    <div class="stat-icon icon-indigo"><i class="bi bi-arrow-left-right"></i></div>
                    <div class="stat-content">
                        <h4>{{ $metrics['transactions'] }}</h4>
                        <span>Posted Transactions</span>
                    </div>
                </div>
            </a>

            <a href="#loans" style="text-decoration:none">
                <div class="stat-card">
                    <div class="stat-icon icon-slate"><i class="bi bi-bank2"></i></div>
                    <div class="stat-content">
                        <h4>{!! $fmt($metrics['total_loans']) !!}</h4>
                        <span>Sanctioned Loans</span>
                    </div>
                </div>
            </a>

            <a href="#loans" style="text-decoration:none">
                <div class="stat-card">
                    <div class="stat-icon icon-rose"><i class="bi bi-hourglass-split"></i></div>
                    <div class="stat-content">
                        <h4>{!! $fmt($metrics['outstanding_loans']) !!}</h4>
                        <span>Outstanding Loans</span>
                    </div>
                </div>
            </a>

            <a href="#banks" style="text-decoration:none">
                <div class="stat-card">
                    <div class="stat-icon icon-sky"><i class="bi bi-building"></i></div>
                    <div class="stat-content">
                        <h4>{!! $fmt($metrics['bank_cash']) !!}</h4>
                        <span>Amount in Bank</span>
                    </div>
                </div>
            </a>

            <a href="#cash" style="text-decoration:none">
                <div class="stat-card">
                    <div class="stat-icon icon-emerald"><i class="bi bi-safe2-fill"></i></div>
                    <div class="stat-content">
                        <h4>{!! $fmt($metrics['treasury_cash']) !!}</h4>
                        <span>Cash in Treasury</span>
                    </div>
                </div>
            </a>
        </div>
    </section>

        <!-- ================= CUSTOMER SUBSECTION 1: CREATE CUSTOMER (OFFICIAL MAHAMILAN SOCIETY MEMBERSHIP APPLICATION FORM) ================= -->
    <section id="customer-create" class="card section" style="padding: 24px; max-width: 1040px; margin: 0 auto;">
        <!-- Official Header with Society Motto -->
        <div style="text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 14px; margin-bottom: 18px;">
            <div style="display: inline-block; background: #e0f2fe; color: #0369a1; padding: 3px 12px; border-radius: 999px; font-size: 11px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 6px;">Official Membership Application</div>
            <h2 style="font-family: 'Noto Serif', Georgia, serif; font-size: 24px; font-weight: 800; color: #0f172a; margin: 0 0 4px; text-transform: uppercase; letter-spacing: 1.5px;">Mahamilan Society</h2>
            <div style="font-size: 12px; font-weight: 800; color: #64748b; letter-spacing: 2.5px; text-transform: uppercase;">Live for Today, Hope for Tomorrow</div>
        </div>

        <!-- Instructions Box (Matching Document Items 1, 2, 3) -->
        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #0284c7; border-radius: 6px; padding: 12px 16px; margin-bottom: 20px; font-size: 12px; color: #334155; line-height: 1.6;">
            <div style="font-weight: 800; color: #0f172a; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                <i class="bi bi-info-circle-fill" style="color: #0284c7;"></i> Instructions & Rules for Membership:
            </div>
            <ol style="margin: 0; padding-left: 20px;">
                <li>Please fill up the application form neatly and carefully.</li>
                <li>Along with the application form please attach the following documents: <b>Three Passport size Photographs</b>, <b>Residence Proof</b> (Permanent and Present), <b>Salary / Employment / Professional Proof</b>. The applicant should produce the originals while submitting the application form for verification, otherwise the application form will not be accepted.</li>
                <li>The Managing Committee keeps the right to reject a loan to any member, even though the formalities have been completed while enrolling as a member of the Society.</li>
            </ol>
        </div>

        <form method="post" action="/admin/accounts" class="customer-create-form" style="display: flex; flex-direction: column; gap: 22px;">
            @csrf
            <input type="hidden" name="member_source" value="new">

            <!-- SECTION A: FORMAL ENROLLMENT REQUEST & ACCOUNT ALLOTMENT -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="font-size: 13px; font-weight: 800; color: #0284c7; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-bank2"></i> Formal Enrollment Request & Society Allotment
                </div>

                <div style="background: #f1f5f9; border-radius: 6px; padding: 12px 14px; font-size: 12.5px; color: #334155; line-height: 1.6; margin-bottom: 16px;">
                    <b>To: The Secretary, Mahamilan Society</b><br>
                    I request you to enroll me as a member of the <b>MAHAMILAN SOCIETY</b>. I am a resident of Assam and I hereby agree to abide by the Rules and Regulations framed under the Bye-laws of the Society from time to time. I hereby declare that I am not a member of any other Cooperative Thrift & Credit Society with limited or unlimited liability.
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr 1.2fr; gap: 16px; margin-bottom: 14px;">
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Account Type / Scheme <span style="color:#dc2626">*</span>
                        </label>
                        <select name="scheme_id" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                            @foreach($schemes as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ strtoupper($s->type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Allotment of Shares <span style="color:#dc2626">*</span>
                        </label>
                        <input name="share_count" type="number" min="1" max="100" value="1" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>

                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Opening Deposit Amount (&#8377;) <span style="color:#dc2626">*</span>
                        </label>
                        <input name="opening_deposit" type="number" min="500" step="10" value="500" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-weight: 700;">
                        <span style="font-size: 10.5px; color: #64748b; margin-top: 3px; display: block;">Min. &#8377;500 per share for Savings Account</span>
                    </div>
                </div>

                <!-- Editable Dates for Back-Date Entry -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 6px; padding: 12px 14px; margin-bottom: 14px;">
                    <div>
                        <label style="font-size: 11.5px; font-weight: 800; color: #166534; display: flex; align-items: center; gap: 4px; margin-bottom: 5px;">
                            <i class="bi bi-calendar-event"></i> Admission / Joining Date (Editable for back-date entry) <span style="color:#dc2626">*</span>
                        </label>
                        <input name="date_of_joining" type="date" value="{{ date('Y-m-d') }}" required style="width: 100%; padding: 8px 12px; border: 1.5px solid #22c55e; border-radius: 6px; font-size: 13px; font-weight: 700; background: #ffffff;">
                        <span style="font-size: 10.5px; color: #15803d; margin-top: 3px; display: block;">Choose any past date for retroactive customer registration</span>
                    </div>

                    <div>
                        <label style="font-size: 11.5px; font-weight: 800; color: #166534; display: flex; align-items: center; gap: 4px; margin-bottom: 5px;">
                            <i class="bi bi-calendar-check"></i> Account Opening Date (Editable for back-date entry) <span style="color:#dc2626">*</span>
                        </label>
                        <input name="account_opening_date" id="customerCreateOpeningDate" type="date" value="{{ date('Y-m-d') }}" onchange="updateCreateAccountMaturity()" required style="width: 100%; padding: 8px 12px; border: 1.5px solid #22c55e; border-radius: 6px; font-size: 13px; font-weight: 700; background: #ffffff;">
                        <span style="font-size: 10.5px; color: #15803d; margin-top: 3px; display: block;">Effective opening date for deposit scheme & ledger records</span>
                    </div>
                </div>

                <!-- Customizable Deposit Tenure & Maturity Calculation -->
                <div style="background: #f0fdfa; border: 1.5px solid #5eead4; border-radius: 8px; padding: 14px 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 12px; font-weight: 800; color: #0f766e; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-hourglass-split"></i> Customizable Deposit Tenure & Maturity Setting
                        </span>
                        <span style="font-size: 11px; color: #0d9488; font-weight: 600;">Custom duration & auto-calculated maturity</span>
                    </div>

                    <!-- Quick Preset Buttons -->
                    <div style="display: flex; gap: 6px; align-items: center; margin-bottom: 12px; flex-wrap: wrap;">
                        <span style="font-size: 11px; font-weight: 700; color: #134e4a; margin-right: 4px;">Quick Tenure:</span>
                        <button type="button" onclick="setCreateAccountTenure(6, 'months', this)" class="tenure-pill-btn" style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 999px; border: 1px solid #14b8a6; background: white; color: #0f766e; cursor: pointer;">6 Mo</button>
                        <button type="button" onclick="setCreateAccountTenure(12, 'months', this)" class="tenure-pill-btn" style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 999px; border: 1px solid #14b8a6; background: #0d9488; color: white; cursor: pointer;">1 Year (12 Mo)</button>
                        <button type="button" onclick="setCreateAccountTenure(24, 'months', this)" class="tenure-pill-btn" style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 999px; border: 1px solid #14b8a6; background: white; color: #0f766e; cursor: pointer;">2 Years (24 Mo)</button>
                        <button type="button" onclick="setCreateAccountTenure(36, 'months', this)" class="tenure-pill-btn" style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 999px; border: 1px solid #14b8a6; background: white; color: #0f766e; cursor: pointer;">3 Years (36 Mo)</button>
                        <button type="button" onclick="setCreateAccountTenure(60, 'months', this)" class="tenure-pill-btn" style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 999px; border: 1px solid #14b8a6; background: white; color: #0f766e; cursor: pointer;">5 Years (60 Mo)</button>
                        <button type="button" onclick="setCreateAccountTenure(120, 'months', this)" class="tenure-pill-btn" style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 999px; border: 1px solid #14b8a6; background: white; color: #0f766e; cursor: pointer;">10 Years (120 Mo)</button>
                    </div>

                    <div style="display: grid; grid-template-columns: 1.2fr 1fr 1.4fr; gap: 14px; align-items: flex-start;">
                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #134e4a; display: block; margin-bottom: 5px;">
                                Deposit Tenure Duration <span style="color:#dc2626">*</span>
                            </label>
                            <div style="display: flex; gap: 6px;">
                                <input type="number" id="createAccountTenureValue" min="1" max="600" value="12" oninput="updateCreateAccountMaturity()" style="flex: 1; padding: 8px 10px; border: 1.5px solid #14b8a6; border-radius: 6px; font-size: 13px; font-weight: 700; background: white;">
                                <select id="createAccountTenureUnit" onchange="updateCreateAccountMaturity()" style="padding: 8px 8px; border: 1.5px solid #14b8a6; border-radius: 6px; font-size: 12px; font-weight: 700; background: white; color: #0f766e;">
                                    <option value="months" selected>Months</option>
                                    <option value="years">Years</option>
                                </select>
                            </div>
                            <input type="hidden" name="tenure_months" id="createAccountTenureMonths" value="12">
                            <span style="font-size: 10.5px; color: #0f766e; margin-top: 3px; display: block;">Specify duration in months or years</span>
                        </div>

                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #134e4a; display: block; margin-bottom: 5px;">
                                Custom Interest Rate (% p.a.)
                            </label>
                            <input name="custom_interest_rate" id="createAccountCustomRate" type="number" step=".01" min="0" max="30" placeholder="Default Plan Rate" oninput="updateCreateAccountMaturity()" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                            <span style="font-size: 10.5px; color: #64748b; margin-top: 3px; display: block;">Leave blank for scheme rate</span>
                        </div>

                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #134e4a; display: block; margin-bottom: 5px;">
                                Maturity Date (Editable / Auto)
                            </label>
                            <input name="maturity_date" id="createAccountMaturityDate" type="date" onchange="onManualMaturityChange()" style="width: 100%; padding: 8px 10px; border: 1.5px solid #14b8a6; border-radius: 6px; font-size: 13px; font-weight: 700; background: white;">
                            <span id="createAccountMaturityNote" style="font-size: 10.5px; color: #0f766e; margin-top: 3px; display: block;">Auto-calculated (can also set manually)</span>
                        </div>
                    </div>

                    <!-- Live Summary Preview Badge -->
                    <div id="createAccountTenurePreview" style="margin-top: 10px; padding: 8px 12px; background: #ccfbf1; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #115e59; font-weight: 600;">
                        <span><i class="bi bi-calendar4-week"></i> Effective Term: <b id="tenurePreviewTerm">12 Months (1 Year)</b></span>
                        <span><i class="bi bi-shield-check"></i> Maturity Date: <b id="tenurePreviewDate">--</b></span>
                    </div>
                </div>
            </div>

            <!-- SECTION B: APPLICANT PARTICULARS (ITEMS 1 - 5) -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="font-size: 13px; font-weight: 800; color: #0284c7; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-person-vcard"></i> 1. Applicant Particulars
                </div>

                <!-- 1. Full Name & Parent Details -->
                <div style="display: grid; grid-template-columns: 2fr 1.5fr; gap: 16px; margin-bottom: 14px;">
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            1. Full Name of Applicant <span style="color:#dc2626">*</span>
                        </label>
                        <input name="applicant_name" type="text" placeholder="e.g. Rahul Sharma" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            2. (a) S/o, W/o, D/o
                        </label>
                        <input name="parent_name" type="text" placeholder="Son / Wife / Daughter of..." style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>

                <!-- 2. Mother & Father Name -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            2. (b) Mother’s Name
                        </label>
                        <input name="mother_name" type="text" placeholder="Mother's Full Name" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            2. (c) Father’s Name
                        </label>
                        <input name="father_name" type="text" placeholder="Father's Full Name" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>

                <!-- 3. Age, DOB, Blood Group, Qualification, Marital Status -->
                <div style="display: grid; grid-template-columns: 1fr 1.2fr 1fr 1.5fr 1fr; gap: 14px;">
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            3. Age (Years)
                        </label>
                        <input name="applicant_age" type="number" min="18" max="100" placeholder="e.g. 32" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Date of Birth
                        </label>
                        <input name="applicant_date_of_birth" type="date" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            4. Blood Group
                        </label>
                        <select name="blood_group" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="O+">O+</option>
                            <option value="A+">A+</option>
                            <option value="B+">B+</option>
                            <option value="AB+">AB+</option>
                            <option value="O-">O-</option>
                            <option value="A-">A-</option>
                            <option value="B-">B-</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            5. Qualification
                        </label>
                        <input name="qualification" type="text" placeholder="e.g. Graduate / HS / Post Graduate" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Marital Status
                        </label>
                        <select name="marital_status" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="Married">Married</option>
                            <option value="Single">Single</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Divorced">Divorced</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION C: CONTACT & ADDRESSES (ITEMS 6 & 7) -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="font-size: 13px; font-weight: 800; color: #0284c7; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-geo-alt-fill"></i> 2. Addresses & Contact Information
                </div>

                <!-- 6. Permanent Address & Phone -->
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            6. Permanent Address <span style="color:#dc2626">*</span>
                        </label>
                        <input name="applicant_address" type="text" placeholder="House/Village, Post Office, City/District, Assam" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Pin Code
                        </label>
                        <input name="pincode" type="text" placeholder="e.g. 781001" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Phone No. <span style="color:#dc2626">*</span>
                        </label>
                        <input name="applicant_phone" type="tel" pattern="^[6-9][0-9]{9}$" placeholder="10-digit mobile" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>

                <!-- 7. Present/Correspondence Address & App Credentials -->
                <div style="display: grid; grid-template-columns: 2fr 1.2fr 1fr; gap: 16px;">
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            7. Present / Correspondence Address
                        </label>
                        <input name="present_address" type="text" placeholder="Present Address (Leave blank if same as permanent)" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Email Address (Optional)
                        </label>
                        <input name="applicant_email" type="email" placeholder="applicant@email.com" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Security MPIN (6 Digits) <span style="color:#dc2626">*</span>
                        </label>
                        <input name="applicant_mpin" type="password" minlength="6" maxlength="6" pattern="^[0-9]{6}$" value="123456" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>
            </div>

            <!-- SECTION D: BANKING, OCCUPATION & TAX PARTICULARS (ITEMS 8 - 15) -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="font-size: 13px; font-weight: 800; color: #0284c7; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-briefcase-fill"></i> 3. Banking, Employment & Tax Particulars
                </div>

                <!-- 8. Bank Details -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 14px;">
                    <div style="font-size: 12px; font-weight: 700; color: #1e293b; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-bank"></i> 8 (a). Primary Bank Account
                    </div>
                    <div style="display: grid; grid-template-columns: 1.5fr 1.5fr 1fr 1fr; gap: 12px; margin-bottom: 10px;">
                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                                Name of Bank
                            </label>
                            <input name="bank_name" type="text" placeholder="e.g. State Bank of India / PNB" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>
                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                                Bank Account No.
                            </label>
                            <input name="bank_account_no" type="text" placeholder="Bank Account Number" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>
                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                                IFSC Code
                            </label>
                            <input name="bank_ifsc" type="text" placeholder="e.g. SBIN0001234" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; text-transform: uppercase;">
                        </div>
                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                                Branch
                            </label>
                            <input name="bank_branch" type="text" placeholder="Branch Name" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>
                    </div>

                    <div style="font-size: 12px; font-weight: 700; color: #1e293b; margin: 12px 0 8px; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-bank2"></i> 8 (b). Secondary / Additional Bank Account (Optional)
                    </div>
                    <div style="display: grid; grid-template-columns: 1.5fr 1.5fr 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                                Name of Bank (2)
                            </label>
                            <input name="bank2_name" type="text" placeholder="e.g. HDFC / Axis / Co-op Bank" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>
                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                                Account No. (2)
                            </label>
                            <input name="bank2_account_no" type="text" placeholder="Secondary Account Number" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>
                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                                IFSC Code (2)
                            </label>
                            <input name="bank2_ifsc" type="text" placeholder="e.g. HDFC0001234" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; text-transform: uppercase;">
                        </div>
                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                                Branch (2)
                            </label>
                            <input name="bank2_branch" type="text" placeholder="Branch Name" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>
                    </div>
                </div>

                <!-- 9. Employed in & 10. Office Address -->
                <div style="display: grid; grid-template-columns: 1.2fr 2fr; gap: 16px; margin-bottom: 14px;">
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            9. Employed In
                        </label>
                        <select name="applicant_occupation" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="Govt. Service">Govt. Service</option>
                            <option value="Semi-Govt.">Semi-Govt.</option>
                            <option value="Public Ltd.">Public Ltd.</option>
                            <option value="Pvt. Ltd.">Pvt. Ltd.</option>
                            <option value="Business / Self-Employed" selected>Business / Self-Employed</option>
                            <option value="Agriculture / Cultivation">Agriculture / Cultivation</option>
                            <option value="Other">Other Profession</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            10. Office in which employed with full address
                        </label>
                        <input name="office_address" type="text" placeholder="Office / Shop / Business establishment address" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>

                <!-- 11. Designation & Salary, 14. PAN & Tax Assessee, Aadhaar -->
                <div style="display: grid; grid-template-columns: 1.5fr 1.2fr 1.3fr; gap: 16px; margin-bottom: 14px;">
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            11. Designation & Salary Drawn (all allowances)
                        </label>
                        <input name="designation_salary" type="text" placeholder="e.g. Executive, Rs. 35,000/- p.m." style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            14. PAN Number (if Tax Assessee)
                        </label>
                        <input name="applicant_pan" type="text" placeholder="ABCDE1234F" pattern="^[A-Z]{5}[0-9]{4}[A-Z]$" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; text-transform: uppercase;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Aadhaar Number (12 Digits) <span style="color:#dc2626">*</span>
                        </label>
                        <input name="applicant_aadhaar" type="text" pattern="^[0-9]{12}$" placeholder="12-digit Aadhaar" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>

                <!-- 15. Relative Member in Society -->
                <div>
                    <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                        15. State whether any of your relative is a member of this Society (if yes, give details)
                    </label>
                    <input name="relative_member_details" type="text" placeholder="No / If yes, state Name, Relation, Account No." style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                </div>
            </div>

            <!-- SECTION E: NOMINATION -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="font-size: 13px; font-weight: 800; color: #0284c7; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-person-heart"></i> 4. Nomination Clause
                </div>
                <p style="font-size: 11.5px; color: #64748b; margin: 0 0 14px; line-height: 1.5;">
                    <i>"I hereby nominate the undermentioned person as my nominee. After my death the entire amount, with interest and other benefits may be credited to his/her name."</i>
                </p>

                <div style="display: grid; grid-template-columns: 1.8fr 1.5fr 1.2fr; gap: 16px; margin-bottom: 14px;">
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Nominee Full Name (Sh./Smt/Miss) <span style="color:#dc2626">*</span>
                        </label>
                        <input name="applicant_nominee" type="text" placeholder="Nominee's Full Name" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Nominee S/o, W/o, D/o
                        </label>
                        <input name="nominee_parent" type="text" placeholder="Parent / Spouse of Nominee" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Relation with Member
                        </label>
                        <input name="nominee_relation" type="text" placeholder="e.g. Spouse / Son / Daughter" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1.2fr 1fr 2fr; gap: 16px;">
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Nominee Occupation
                        </label>
                        <input name="nominee_occupation" type="text" placeholder="e.g. Household / Service" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Nominee Age
                        </label>
                        <input name="nominee_age" type="number" min="1" max="100" placeholder="e.g. 28" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">
                            Nominee Address
                        </label>
                        <input name="nominee_address" type="text" placeholder="Residential Address of Nominee" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>
            </div>

            <!-- SECTION F: DECLARATION BY INTRODUCERS -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="font-size: 13px; font-weight: 800; color: #0284c7; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-people"></i> 5. Declaration by Introducers
                </div>
                <p style="font-size: 11.5px; color: #64748b; margin: 0 0 14px; line-height: 1.5;">
                    <i>"We hereby declare that the applicant who has signed on the document in our presence is known to us personally. We shall be responsible individually / collectively for compliance and transactions with the Society."</i>
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Introducer 1 -->
                    <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 14px; background: #f8fafc;">
                        <div style="font-weight: 800; font-size: 12px; color: #0f172a; margin-bottom: 10px;">Introducer Member 1</div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <input name="intro1_name" type="text" placeholder="1. Full Name of Introducer" style="padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                            <input name="intro1_parent" type="text" placeholder="S/o, W/o, D/o" style="padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <input name="intro1_account" type="text" placeholder="Account No." style="padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                <input name="intro1_lf" type="text" placeholder="LF No." style="padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                            </div>
                            <input name="intro1_address" type="text" placeholder="Address" style="padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                        </div>
                    </div>

                    <!-- Introducer 2 -->
                    <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 14px; background: #f8fafc;">
                        <div style="font-weight: 800; font-size: 12px; color: #0f172a; margin-bottom: 10px;">Introducer Member 2</div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <input name="intro2_name" type="text" placeholder="2. Full Name of Introducer" style="padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                            <input name="intro2_parent" type="text" placeholder="S/o, W/o, D/o" style="padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <input name="intro2_account" type="text" placeholder="Account No." style="padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                <input name="intro2_lf" type="text" placeholder="LF No." style="padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                            </div>
                            <input name="intro2_address" type="text" placeholder="Address" style="padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION G: DECLARATION & SUBMISSION -->
            <div style="background: #f8fafc; border: 1.5px solid #0f172a; border-radius: 8px; padding: 18px;">
                <div style="font-weight: 800; font-size: 13px; text-transform: uppercase; color: #0f172a; margin-bottom: 8px;">
                    Applicant Declaration
                </div>
                <p style="font-size: 11.5px; line-height: 1.6; color: #334155; text-align: justify; margin: 0 0 14px;">
                    I hereby truthfully and faithfully declare that, I am not a member of any other Cooperative Thrift & Credit Society and also I have not applied for membership with any other such Society. I am a permanent resident of Guwahati, Assam. The details about my age, occupation and other aspects given by me are correct. If anything found wrong, the Management have the right to take necessary action against me. I promise to give written information to the Society regarding any change in address of my residence, place of work or change in my office/occupation in future.
                </p>

                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px; background: white; padding: 10px 14px; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <input type="checkbox" id="acceptDeclaration" required style="width: 16px; height: 16px; accent-color: #0284c7; cursor: pointer;">
                    <label for="acceptDeclaration" style="font-size: 12px; font-weight: 700; color: #0f172a; cursor: pointer;">
                        I agree to the above declaration, bye-laws, and terms of Mahamilan Society.
                    </label>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="button" style="background: #0284c7; color: white; padding: 10px 24px; font-size: 13px; font-weight: 800; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; border: none;">
                            <i class="bi bi-person-check-fill"></i> Submit & Register Customer
                        </button>
                        <button type="reset" class="button secondary" style="padding: 10px 18px; font-size: 13px; font-weight: 700; border-radius: 6px;">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset Form
                        </button>
                    </div>

                    <div>
                        <a href="#customer-report" class="button secondary" style="padding: 10px 16px; font-size: 12px; text-decoration: none; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="bi bi-table"></i> View Customer Reports
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </section>

    <!-- ================= CUSTOMER SUBSECTION 2: SEARCH CUSTOMER (SCREENSHOT 2) ================= -->
    <section id="customer-search" class="card section" style="padding: 24px;">
        <!-- Blue Hero Bar matching Screenshot 2 -->
        <div style="background: linear-gradient(90deg, #38bdf8 0%, #0284c7 100%); padding: 22px; border-radius: 8px; color: white; text-align: center; margin-bottom: 24px; box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);">
            <h3 style="margin: 0 0 12px; font-size: 20px; font-weight: 800;">Search Customer</h3>
            <div style="max-width: 520px; margin: 0 auto; display: flex; gap: 8px;">
                <input type="text" id="custLiveSearchInput" placeholder="Enter Customer ID, Name, Phone..." onkeyup="filterSearchCustomerCards(this.value)" style="flex: 1; padding: 10px 14px; border-radius: 6px; border: none; font-size: 13px; outline: none; color: #0f172a; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <button type="button" style="background: #0369a1; color: white; border: none; padding: 0 22px; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer;">Search</button>
            </div>
        </div>

        <!-- 4 Collapsible Panels matching Screenshot 2 -->
        <div style="display: flex; flex-direction: column; gap: 14px;">
            <!-- 1. Customer Details -->
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: white;">
                <div style="background: #f8fafc; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 800; font-size: 14px; color: #0f172a; display: flex; justify-content: space-between; align-items: center;">
                    <span style="border-bottom: 2px solid #0284c7; padding-bottom: 2px;">Customer Details</span>
                    <span class="badge" style="font-size: 11px;">Total: {{ count($members) }}</span>
                </div>
                <div style="padding: 14px; max-height: 280px; overflow-y: auto;">
                    <table>
                        <thead>
                            <tr><th>Customer ID</th><th>Name</th><th>Contact</th><th>Status</th><th>KYC</th><th>MCS Score</th><th>Action</th></tr>
                        </thead>
                        <tbody id="searchCustTableBody">
                            @foreach($members as $m)
                            <tr class="search-cust-row" data-query="{{ strtolower($m->member_no . ' ' . $m->name . ' ' . $m->phone) }}">
                                <td><b>{{$m->member_no}}</b></td>
                                <td>{{$m->name}}</td>
                                <td>{{$m->phone}}</td>
                                <td><span class="badge {{$m->user_status}}">{{$m->user_status}}</span></td>
                                <td><span class="badge {{$m->kyc_status}}">{{str_replace('_',' ',$m->kyc_status)}}</span></td>
                                <td><b>{{$m->mcs_score}}</b>/900</td>
                                <td>
                                    <a href="/admin/members/{{$m->id}}" class="button" style="padding: 3px 8px; font-size: 11px; background: #0284c7;">360° Profile</a>
                                    <button type="button" data-member="{{ htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8') }}" onclick="openEditMemberModal(this)" class="button" style="padding: 3px 8px; font-size: 11px; background: #f59e0b; color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 3px; font-weight: 700;" title="Edit Customer Details & Form">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    <a href="/admin/members/{{$m->id}}/card" target="_blank" class="button secondary" style="padding: 3px 8px; font-size: 11px;">ID Card</a>
                                    <a href="/admin/members/{{$m->id}}/form" target="_blank" class="button secondary" style="padding: 3px 8px; font-size: 11px;"><i class="bi bi-printer"></i> Form</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2. Loan Accounts -->
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: white;">
                <div style="background: #f8fafc; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 800; font-size: 14px; color: #0f172a;">
                    <span style="border-bottom: 2px solid #0284c7; padding-bottom: 2px;">Loan Accounts</span>
                </div>
                <div style="padding: 14px; max-height: 220px; overflow-y: auto;">
                    <table>
                        <thead>
                            <tr><th>App No</th><th>Applicant</th><th>Requested</th><th>Sanctioned</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($loans as $l)
                            <tr>
                                <td><b>{{$l->application_no}}</b></td>
                                <td>{{$l->name}}<br><small class="muted">{{$l->member_no}}</small></td>
                                <td>&#8377;{{number_format($l->amount, 2)}}</td>
                                <td>&#8377;{{number_format($l->sanctioned_amount ?? 0, 2)}}</td>
                                <td><span class="badge {{$l->status}}">{{$l->status}}</span></td>
                                <td><a href="#loans" class="button" style="padding: 3px 8px; font-size: 11px;">View Loan</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="muted">No loan records available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Saving Accounts -->
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: white;">
                <div style="background: #f8fafc; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 800; font-size: 14px; color: #0f172a;">
                    <span style="border-bottom: 2px solid #0284c7; padding-bottom: 2px;">Saving Accounts</span>
                </div>
                <div style="padding: 14px; max-height: 220px; overflow-y: auto;">
                    <table>
                        <thead>
                            <tr><th>Account No</th><th>Member</th><th>Scheme</th><th>Balance</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($accounts->where('status','active') as $acc)
                            <tr>
                                <td><b>{{$acc->account_no}}</b></td>
                                <td>{{$acc->name}}<br><small class="muted">{{$acc->member_no}}</small></td>
                                <td>Regular Savings</td>
                                <td><b>&#8377;{{number_format($acc->balance, 2)}}</b></td>
                                <td><span class="badge active">Active</span></td>
                                <td><a href="#deposits" class="button success" style="padding: 3px 8px; font-size: 11px;">Deposit</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="muted">No saving accounts found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. Deposit Accounts -->
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: white;">
                <div style="background: #f8fafc; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 800; font-size: 14px; color: #0f172a;">
                    <span style="border-bottom: 2px solid #0284c7; padding-bottom: 2px;">Deposit Accounts</span>
                </div>
                <div style="padding: 14px; max-height: 220px; overflow-y: auto;">
                    <table>
                        <thead>
                            <tr><th>Account No</th><th>Member</th><th>Type</th><th>Deposit Amount</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($accounts->where('status','active') as $acc)
                            <tr>
                                <td><b>{{$acc->account_no}}</b></td>
                                <td>{{$acc->name}}</td>
                                <td>Fixed/Recurring Deposit</td>
                                <td>&#8377;{{number_format($acc->balance, 2)}}</td>
                                <td><span class="badge active">Active</span></td>
                                <td><a href="#fd-rd" class="button" style="padding: 3px 8px; font-size: 11px;">Manage FD/RD</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="muted">No deposit accounts found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= CUSTOMER SUBSECTION 3: UPLOAD KYC (SCREENSHOT 3) ================= -->
    <section id="customer-kyc" class="card section" style="padding: 24px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 6px;">Customer KYC Upload</h2>
            <span class="muted" style="font-size: 12px;">Upload, review, verify and approve member KYC identity documents</span>
        </div>

        <!-- 1. Filter User with Live ID & Name Search Bar -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; margin-bottom: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h4 style="font-size: 13px; font-weight: 800; color: #0284c7; margin: 0; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-search"></i> 1. Search & Filter Customer
                </h4>
                <span id="kycSearchStatus" class="muted" style="font-size: 11px;">Type Member ID or Name to filter live</span>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <div style="flex: 1; min-width: 260px; position: relative;">
                    <i class="bi bi-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 13px;"></i>
                    <input type="text" id="kycMemberSearchInput" placeholder="Search by ID (e.g. MS-2026...) or Name..."
                        oninput="filterKycMembers(this.value)"
                        onkeydown="if(event.key==='Enter'){event.preventDefault();searchKycMembers();}"
                        style="width: 100%; padding: 8px 32px 8px 34px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-weight: 500; outline: none; box-sizing: border-box;">
                    <button type="button" onclick="clearKycSearch()" id="kycClearBtn" title="Clear search"
                        style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 14px; display: none;">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
                <div style="flex: 1; min-width: 240px;">
                    <select id="kycFilterUserSelect" onchange="syncKycUser(this.value)" style="width: 100%; padding: 8px 12px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                        <option value="">-- All Customers / Choose Specific User --</option>
                        @foreach($members as $m)
                            <option value="{{$m->id}}" data-member-no="{{ strtolower($m->member_no) }}" data-name="{{ strtolower($m->name) }}" data-phone="{{ $m->phone }}">
                                {{$m->member_no}} — {{$m->name}} ({{$m->phone}})
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="button" onclick="searchKycMembers()" class="button" style="background: #0284c7; padding: 8px 16px; font-size: 12px; font-weight: 700; border-radius: 6px;">
                    <i class="bi bi-search"></i> Search
                </button>
                <button type="button" onclick="clearKycSearch()" class="button secondary" style="padding: 8px 14px; font-size: 12px; font-weight: 600; border-radius: 6px;">
                    Reset
                </button>
            </div>
        </div>

        <!-- 2. Upload KYC Document -->
        @if($can('kyc.approve'))
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
            <h4 style="font-size: 13px; font-weight: 800; color: #0284c7; margin: 0 0 10px;">3. Upload KYC Document</h4>
            <form method="post" action="/admin/kyc" enctype="multipart/form-data" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
                @csrf
                <div style="min-width: 220px;">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Choose Member</label>
                    <select name="member_id" id="kycTargetMemberSelect" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        <option value="">Choose member...</option>
                        @foreach($members as $m)
                            <option value="{{$m->id}}">{{$m->member_no}} — {{$m->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width: 180px;">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Choose Document Type</label>
                    <select name="type" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        <option value="aadhaar">Aadhaar Card</option>
                        <option value="pan">PAN Card</option>
                        <option value="photo">Member Photo</option>
                        <option value="address_proof">Address Proof / Electricity Bill</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 220px;">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">File (JPG, PNG, PDF; max 5MB)</label>
                    <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required style="width: 100%; padding: 7px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px;">
                </div>
                <button class="button success" style="padding: 9px 20px; font-size: 13px; font-weight: 800;">
                    <i class="bi bi-cloud-arrow-up"></i> Upload
                </button>
            </form>
        </div>
        @endif

        <!-- 3. Customer KYC Document Details -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
            <div style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <h4 style="font-size: 13px; font-weight: 800; color: #0284c7; margin: 0;">4. Customer KYC Document Details</h4>
                <span id="kycDocCountInfo" class="muted" style="font-size: 11px;">Total Documents: {{ count($documents) }}</span>
            </div>
            <table>
                <thead>
                    <tr style="background: #0284c7; color: white;">
                        <th style="color: white; width: 60px;">S.No</th>
                        <th style="color: white;">Document Name</th>
                        <th style="color: white;">Status</th>
                        <th style="color: white;">Preview</th>
                        <th style="color: white;">Action</th>
                    </tr>
                </thead>
                <tbody id="kycDocTableBody">
                    @forelse($documents as $idx => $doc)
                    <tr class="kyc-doc-row" data-member-id="{{ $doc->member_id }}" data-member-no="{{ strtolower($doc->member_no ?? '') }}" data-member-name="{{ strtolower($doc->name ?? '') }}">
                        <td>{{ $idx + 1 }}</td>
                        <td><b>{{ strtoupper(str_replace('_', ' ', $doc->type)) }}</b><br><small class="muted">{{ $doc->name }} ({{ $doc->member_no }})</small></td>
                        <td><span class="badge {{ $doc->status }}">{{ $doc->status }}</span></td>
                        <td>
                            <a href="/admin/kyc/{{ $doc->id }}/view" class="button secondary" style="padding: 3px 8px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;" target="_blank">
                                <i class="bi bi-eye"></i> View File
                            </a>
                        </td>
                        <td>
                            @if($can('kyc.approve') && $doc->status === 'pending')
                                <form method="post" action="/admin/kyc/{{ $doc->id }}/decision" style="display:inline">@csrf<input type="hidden" name="decision" value="approved"><button class="success" style="padding: 3px 8px; font-size: 11px;">Approve</button></form>
                                <form method="post" action="/admin/kyc/{{ $doc->id }}/decision" style="display:inline">@csrf<input type="hidden" name="decision" value="rejected"><button class="danger" style="padding: 3px 8px; font-size: 11px;">Reject</button></form>
                            @else
                                <span class="muted" style="font-size: 11px;">Reviewed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align: center; padding: 24px;" class="muted">No documents found matching the filter</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- ================= CUSTOMER SUBSECTION 4: CUSTOMER REPORT (SCREENSHOT 4) ================= -->
    <section id="customer-report" class="card section" style="padding: 24px;">
        <!-- Header -->
        <div style="border-left: 3px solid #0284c7; padding-left: 10px; margin-bottom: 14px;">
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 4px;">Customer Report Filter</h3>
            <span class="muted" style="font-size: 12px;">Use filters to view specific customer reports</span>
        </div>

        <!-- Filter Inputs -->
        <div style="display: flex; gap: 14px; margin-bottom: 16px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 220px;">
                <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Customer Id</label>
                <input type="text" id="reportCustIdInput" placeholder="CUS000001" onkeyup="filterReportRows()" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
            </div>
            <div style="flex: 2; min-width: 280px;">
                <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Aadhar/Pan/Name/Mobile No.</label>
                <input type="text" id="reportGeneralInput" placeholder="Enter Aadhar/Pan/Name/Mobile Number" onkeyup="filterReportRows()" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
            </div>
        </div>

        <!-- Rows per page & summary -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #475569;">
                <span>Rows per page:</span>
                <select id="reportRowsPerPageSelect" onchange="changeReportRows(this.value)" style="padding: 4px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; font-weight: 700;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="all">All</option>
                </select>
            </div>
            <span id="reportCountInfo" class="muted" style="font-size: 12px;">Showing {{ count($members) }} customer(s)</span>
        </div>

        <!-- Table with Bright Blue Header matching Screenshot 4 -->
        <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow-x: auto; background: white;">
            <table class="report-table" id="customerReportMainTable">
                <thead>
                    <tr style="background: #0284c7; color: white;">
                        <th style="color: white; width: 45px;">SN</th>
                        <th style="color: white; width: 60px;">Photo</th>
                        <th style="color: white;">Customer ID</th>
                        <th style="color: white;">User Name</th>
                        <th style="color: white;">Email</th>
                        <th style="color: white;">Phone</th>
                        <th style="color: white;">Aadhar</th>
                        <th style="color: white;">PAN</th>
                        <th style="color: white;">Address</th>
                        <th style="color: white;">DOB</th>
                        <th style="color: white;">Date Of Joining</th>
                        <th style="color: white; text-align: center; min-width: 185px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="customerReportTbody">
                    @foreach($members as $idx => $m)
                    <tr class="report-data-row" data-id="{{ strtolower($m->member_no) }}" data-query="{{ strtolower($m->name . ' ' . $m->phone . ' ' . ($m->email ?? '') . ' ' . ($m->address ?? '')) }}">
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <div style="width: 28px; height: 28px; border-radius: 4px; background: #0284c7; color: white; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                <i class="bi bi-eye"></i>
                            </div>
                        </td>
                        <td>
                            <b>{{ $m->member_no }}</b>
                            @if(isset($m->user_status) && $m->user_status === 'pending')
                                <br><span class="badge pending" style="font-size: 9px; padding: 1px 5px; background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">Pending</span>
                            @else
                                <br><span class="badge active" style="font-size: 9px; padding: 1px 5px; background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">Active</span>
                            @endif
                        </td>
                        <td><b>{{ $m->name }}</b></td>
                        <td>{{ $m->email ?: 'N/A' }}</td>
                        <td>{{ $m->phone }}</td>
                        @php
                            $rowAadhaar = preg_replace('/\s+/', '', (string)($m->aadhaar_no ?: $m->aadhaar_last4));
                            $fmtRowAadhaar = ($rowAadhaar && strlen($rowAadhaar) === 12) ? implode(' ', str_split($rowAadhaar, 4)) : ($rowAadhaar ?: '-');
                        @endphp
                        <td>{{ $fmtRowAadhaar }}</td>
                        <td>{{ strtoupper($m->pan_no ?: ($m->pan_last4 ?: '-')) }}</td>
                        <td style="max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $m->address ?: '-' }}</td>
                        <td>{{ $m->date_of_birth ? date('d-m-Y', strtotime($m->date_of_birth)) : '-' }}</td>
                        <td>{{ date('d-m-Y', strtotime($m->created_at ?? 'now')) }}</td>
                        <td style="text-align: center; white-space: nowrap;">
                            <div style="display: inline-flex; align-items: center; gap: 5px;">
                                @if(isset($m->user_status) && $m->user_status === 'pending')
                                <form method="post" action="/admin/members/{{ $m->id }}/decision" style="display: inline; margin: 0;">
                                    @csrf
                                    <input type="hidden" name="decision" value="approve">
                                    <button type="submit" style="padding: 3px 8px; font-size: 11px; background: #16a34a; color: white; border-radius: 4px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 3px; font-weight: 700;" title="Approve Member">
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>
                                </form>
                                @endif
                                <a href="/admin/members/{{ $m->id }}/form" target="_blank" class="button" style="padding: 3px 8px; font-size: 11px; background: #0284c7; color: white; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 3px;" title="Print Application Form">
                                    <i class="bi bi-printer"></i> Form
                                </a>
                                <button type="button" 
                                    data-member="{{ htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8') }}"
                                    data-id="{{ $m->id }}"
                                    data-member-no="{{ $m->member_no }}"
                                    data-name="{{ $m->name }}"
                                    data-phone="{{ $m->phone }}"
                                    data-email="{{ $m->email }}"
                                    data-status="{{ $m->user_status ?? $m->status ?? 'active' }}"
                                    data-kyc-status="{{ $m->kyc_status ?? 'not_submitted' }}"
                                    data-joining-date="{{ $m->created_at ? substr($m->created_at, 0, 10) : '' }}"
                                    data-dob="{{ $m->date_of_birth ? substr($m->date_of_birth, 0, 10) : '' }}"
                                    data-aadhaar="{{ $m->aadhaar_no ?? $m->aadhaar_last4 ?? '' }}"
                                    data-pan="{{ $m->pan_no ?? $m->pan_last4 ?? '' }}"
                                    data-nominee="{{ $m->nominee_name ?? '' }}"
                                    data-occupation="{{ $m->occupation ?? '' }}"
                                    data-score="{{ $m->mcs_score ?? 500 }}"
                                    data-address="{{ $m->address ?? '' }}"
                                    onclick="openEditMemberModal(this)"
                                    style="padding: 3px 8px; font-size: 11px; background: #f59e0b; color: white; border-radius: 4px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 3px; font-weight: 700;" title="Edit Customer Details & Form">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <form method="post" action="/admin/members/{{ $m->id }}/delete" onsubmit="return confirm('Are you sure you want to permanently delete customer {{ $m->member_no }} ({{ addslashes($m->name) }})? All related loans, accounts, and records will be deleted.');" style="display: inline; margin: 0;">
                                    @csrf
                                    <button type="submit" style="padding: 3px 8px; font-size: 11px; background: #ef4444; color: white; border-radius: 4px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 3px; font-weight: 700;" title="Delete Customer">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 16px;">
            <button type="button" class="button secondary" style="padding: 4px 14px; font-size: 12px; border-radius: 4px;">Previous</button>
            <span style="font-size: 12px; font-weight: 700; color: #475569;">Page 1 of 1</span>
            <button type="button" class="button secondary" style="padding: 4px 14px; font-size: 12px; border-radius: 4px;">Next</button>
        </div>


    </section>

    <!-- ================= CUSTOMER SUBSECTION 5: NOMINEE ================= -->
    <section id="customer-nominee" class="card section" style="padding: 24px;">
        <div style="border-left: 3px solid #0284c7; padding-left: 10px; margin-bottom: 14px;">
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 4px;">Customer Nominee Directory</h3>
            <span class="muted" style="font-size: 12px;">Review designated nominees, relationships, and emergency contact details</span>
        </div>

        <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow-x: auto; background: white;">
            <table class="report-table">
                <thead>
                    <tr style="background: #0284c7; color: white;">
                        <th style="color: white; width: 50px;">SN</th>
                        <th style="color: white;">Customer ID</th>
                        <th style="color: white;">Member Name</th>
                        <th style="color: white;">Nominee Name</th>
                        <th style="color: white;">Relationship</th>
                        <th style="color: white;">Contact Phone</th>
                        <th style="color: white;">Active Savings</th>
                        <th style="color: white; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($members as $idx => $m)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><b>{{ $m->member_no }}</b></td>
                        <td>{{ $m->name }}</td>
                        <td><b style="color: #0369a1;">{{ $m->nominee_name ?: 'Not Assigned' }}</b></td>
                        <td>{{ $m->nominee_name ? 'Primary Nominee' : '-' }}</td>
                        <td>{{ $m->phone }}</td>
                        <td>&#8377;{{ number_format($m->active_savings ?? 0, 2) }}</td>
                        <td style="text-align: center;">
                            <a href="/admin/members/{{ $m->id }}" class="button" style="padding: 3px 10px; font-size: 11px; background: #0284c7;">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <!-- Legacy Alias Anchor for #members -->
    <div id="members" style="display:none"></div>

    <section id="deposits" class="card section">
        <h2>Deposits and Collections</h2>
        <p class="muted">Record member daily or monthly deposits and withdrawals. All entries automatically synchronize across ledgers, cash books, and issue official receipts (Section 3 & 10).</p>

        @if($can('deposit.create'))
        <div class="split" style="margin-bottom:24px">
            <div style="background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:16px">
                <h3 style="margin-top:0">Collect Deposit</h3>
                <form method="post" action="/admin/deposits" class="field">@csrf
                    <div class="field">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            <span>Member Account</span>
                            <span class="muted" style="font-size:10.5px;">Search by ID / Name below</span>
                        </label>
                        <div style="position: relative; margin-bottom: 6px;">
                            <i class="bi bi-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 12px;"></i>
                            <input type="text" id="depositMemberQuickSearch" placeholder="Type Member ID or Name to filter accounts..." oninput="filterDepositAccountSelect(this.value)" style="width: 100%; padding: 6px 10px 6px 28px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 5px; box-sizing: border-box;">
                        </div>
                        <select name="account_id" id="depositTargetAccountSelect" required style="width:100%;">
                            <option value="">Select an active account</option>
                            @foreach($accounts->where('status','active') as $acc)
                                <option value="{{$acc->id}}" data-search="{{ strtolower($acc->account_no . ' ' . $acc->name . ' ' . $acc->member_no) }}">{{$acc->account_no}} — {{$acc->name}} ({{$acc->member_no}}) (&#8377;{{number_format($acc->balance,2)}})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="field" style="flex: 1.2;">
                            <label>Deposit Amount (&#8377;) <span style="color:#dc2626">*</span></label>
                            <input name="amount" type="number" min="0.01" step=".01" required style="font-weight: 700; font-size: 14px;">
                        </div>
                        <div class="field" style="flex: 1;">
                            <label style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-weight:700; color:#166534;"><i class="bi bi-calendar-event"></i> Payment Date</span>
                                <span class="muted" style="font-size:10px; color:#15803d;">(Back-date editable)</span>
                            </label>
                            <input name="deposit_date" type="date" value="{{ date('Y-m-d') }}" style="border: 1.5px solid #22c55e; font-weight: 700; background: #ffffff;">
                        </div>
                    </div>
                    <div class="row">
                        <div class="field"><label>Payment Mode</label><select name="payment_mode"><option value="cash">Cash</option><option value="bank">Bank</option><option value="upi">UPI</option></select></div>
                        <div class="field"><label>Bank Account</label><select name="bank_account_id"><option value="">Default Treasury</option>@foreach($bankAccounts as $b)<option value="{{$b->id}}">{{$b->bank_name}} (&#8377;{{number_format($b->current_balance,2)}})</option>@endforeach</select></div>
                    </div>
                    <div class="field"><label>Note / Reference</label><input name="note" placeholder="Daily deposit / Monthly share"></div>
                    <button class="success" style="margin-top:8px">Record Deposit & Issue Receipt</button>
                </form>
            </div>

            <div style="background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:16px">
                <h3 style="margin-top:0">Process Withdrawal (Section 10)</h3>
                <p class="muted" style="font-size:12px;margin:0 0 10px">The system checks available balance. If insufficient, transaction is blocked with warning.</p>
                <form method="post" action="/admin/withdrawals" class="field">@csrf
                    <div class="field"><label>Select Member (Step 1)</label><select id="withdrawal-member-filter" onchange="filterWithdrawalMember(this.value)">
                        <option value="">All active members...</option>
                        @foreach($members as $m)
                            <option value="{{$m->id}}">{{$m->member_no}} — {{$m->name}}</option>
                        @endforeach
                    </select></div>
                    <div class="field"><label>Member Account (Step 2)</label><select name="account_id" id="withdrawal-account-select" onchange="validateWithdrawal()" required>
                        <option value="">Select an active account</option>
                        @foreach($accounts->where('status','active') as $acc)
                            <option value="{{$acc->id}}" data-member-id="{{$acc->member_id}}" data-balance="{{$acc->balance}}">{{$acc->account_no}} — {{$acc->name}} (Available: &#8377;{{number_format($acc->balance,2)}})</option>
                        @endforeach
                    </select></div>
                    <div class="row">
                        <div class="field" style="flex: 1.2;">
                            <label>Withdrawal Amount (&#8377;) (Step 3) <span style="color:#dc2626">*</span></label>
                            <input name="amount" id="withdrawal-amount" type="number" min="0.01" step=".01" oninput="validateWithdrawal()" required style="font-weight: 700; font-size: 14px;">
                        </div>
                        <div class="field" style="flex: 1;">
                            <label style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-weight:700; color:#b91c1c;"><i class="bi bi-calendar-event"></i> Withdrawal Date</span>
                                <span class="muted" style="font-size:10px; color:#dc2626;">(Back-date editable)</span>
                            </label>
                            <input name="withdrawal_date" id="withdrawal-date" type="date" value="{{ date('Y-m-d') }}" style="border: 1.5px solid #ef4444; font-weight: 700; background: #ffffff;">
                        </div>
                    </div>
                    <div id="withdrawal-balance-msg" style="font-size:12px;margin:2px 0 6px;display:none"></div>
                    <div class="row">
                        <div class="field"><label>Payment Mode (Step 4)</label><select name="payment_mode"><option value="cash">Cash</option><option value="bank">Bank</option><option value="upi">UPI</option></select></div>
                        <div class="field"><label>Bank Account</label><select name="bank_account_id"><option value="">Default Treasury</option>@foreach($bankAccounts as $b)<option value="{{$b->id}}">{{$b->bank_name}} (&#8377;{{number_format($b->current_balance,2)}})</option>@endforeach</select></div>
                    </div>
                    <div class="field"><label>Reason (Step 5)</label><input name="reason" placeholder="Personal requirement" required></div>
                    <button id="withdrawal-submit-btn" class="danger" style="margin-top:8px">Submit Withdrawal Request</button>
                </form>
            </div>
        </div>
        <script>
            function filterWithdrawalMember(memberId) {
                const sel = document.getElementById('withdrawal-account-select');
                if (!sel) return;
                let firstMatch = null;
                for (let i = 0; i < sel.options.length; i++) {
                    const opt = sel.options[i];
                    if (!opt.value) continue;
                    if (!memberId || opt.dataset.memberId === memberId) {
                        opt.style.display = '';
                        if (!firstMatch) firstMatch = opt;
                    } else {
                        opt.style.display = 'none';
                    }
                }
                if (firstMatch && memberId) {
                    sel.value = firstMatch.value;
                } else if (!memberId) {
                    sel.value = '';
                }
                validateWithdrawal();
            }

            function validateWithdrawal() {
                const sel = document.getElementById('withdrawal-account-select');
                const amtInput = document.getElementById('withdrawal-amount');
                const msg = document.getElementById('withdrawal-balance-msg');
                const btn = document.getElementById('withdrawal-submit-btn');
                if (!sel || !amtInput || !msg || !btn) return;
                const opt = sel.options[sel.selectedIndex];
                if (!opt || !opt.dataset.balance) { msg.style.display = 'none'; return; }
                const bal = parseFloat(opt.dataset.balance) || 0;
                const req = parseFloat(amtInput.value) || 0;
                msg.style.display = 'block';
                if (req > bal) {
                    msg.innerHTML = '<b style="color:var(--red)">⛔ Transaction Blocked: Withdrawal amount (&#8377;' + req.toFixed(2) + ') exceeds available balance (&#8377;' + bal.toFixed(2) + ')</b>';
                    btn.disabled = true;
                    btn.style.opacity = '0.4';
                    btn.style.cursor = 'not-allowed';
                } else if (req > 0) {
                    msg.innerHTML = '<span style="color:var(--green);font-weight:600">✓ Sufficient balance available (Available: &#8377;' + bal.toFixed(2) + ')</span>';
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                } else {
                    msg.innerHTML = '<span class="muted">Available balance: &#8377;' + bal.toFixed(2) + '</span>';
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                }
            }
        </script>
        @endif

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; margin-bottom: 10px;">
            <h3 style="margin: 0;">Due / Overdue RD Installments</h3>
            <div style="position: relative; width: 260px;">
                <i class="bi bi-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 12px;"></i>
                <input type="text" placeholder="Search RD by ID or Name..." oninput="filterRdInstallments(this.value)" style="width: 100%; padding: 6px 10px 6px 28px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 5px; box-sizing: border-box;">
            </div>
        </div>
        <table><thead><tr style="background:#0284c7;color:white;"><th style="color:white;">Account / Member</th><th style="color:white;">Installment</th><th style="color:white;">Due Date</th><th style="color:white;">Amount</th><th style="color:white;">Status</th><th style="color:white;">Action</th></tr></thead><tbody id="rdInstallmentsTableBody">
        @forelse($rdInstallments as $inst)
        <tr class="rd-inst-row" data-search="{{ strtolower($inst->account_no . ' ' . $inst->name) }}">
            <td>{{$inst->account_no}}<br><b>{{$inst->name}}</b></td>
            <td>Installment #{{$inst->installment_no}}</td>
            <td>{{$inst->due_on}}</td>
            <td>&#8377;{{number_format($inst->amount_due, 2)}}</td>
            <td><span class="badge {{$inst->status==='overdue'?'rejected':($inst->status==='due'?'active':'')}}">{{$inst->status}}</span></td>
            <td>
                <div style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
                    @if($can('deposit.create'))
                    <form method="post" action="/admin/rd-installments/{{$inst->id}}/pay" style="display:inline; margin: 0;">@csrf
                        <select name="payment_mode" style="padding:3px;font-size:11px"><option value="cash">Cash</option><option value="bank">Bank</option></select>
                        <button class="success" style="padding:3px 7px;font-size:11px">Collect</button>
                    </form>
                    <button type="button" class="button" onclick="openAdvancePayModal({{ $inst->account_id }}, '{{ $inst->account_no }}', '{{ addslashes($inst->name) }}', {{ $inst->amount_due }}, 'daily')" style="padding: 3px 7px; font-size: 11px; background: #059669; color: white; border: none; border-radius: 4px; cursor: pointer;" title="Pay Next 10 Days at once">
                        Pay 10 Days
                    </button>
                    @else Read only @endif
                    <button type="button" class="button" onclick="openRdScheduleModal({{ $inst->account_id }})" style="padding: 3px 7px; font-size: 11px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer;" title="View Passbook & Schedule">
                        Schedule
                    </button>
                </div>
            </td>
        </tr>
        @empty<tr><td colspan="6">No scheduled or overdue RD installments.</td></tr>@endforelse
        </tbody></table>

        <!-- ================= DEPOSITS & COLLECTIONS TRANSACTION HISTORY WITH PRINT & DOWNLOAD ================= -->
        <div style="margin-top: 30px; border-top: 2px solid #e2e8f0; padding-top: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #0f172a;">Deposits & Collections History</h3>
                <span id="depositHistoryCountInfo" class="muted" style="font-size: 12px;">Total Transactions: {{ count($receipts) }}</span>
            </div>
            <p class="muted" style="margin: 0 0 14px; font-size: 12px;">Search and review recent member deposits and collections with instant receipt printing and HTML download options.</p>

            <!-- Search Bar by ID & Name for Deposits History -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; margin-bottom: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h4 style="font-size: 13px; font-weight: 800; color: #0284c7; margin: 0; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-search"></i> Search Deposits & Collections by Member ID, Name or Receipt No
                    </h4>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 280px; position: relative;">
                        <i class="bi bi-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 13px;"></i>
                        <input type="text" id="depositHistorySearchInput" placeholder="Search by Member ID (e.g. MS-...), Name, Receipt No, Category, or Mode..."
                            oninput="filterDepositHistoryTable(this.value)"
                            style="width: 100%; padding: 8px 32px 8px 34px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none; box-sizing: border-box;">
                        <button type="button" onclick="clearDepositHistorySearch()" id="depositHistoryClearBtn" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 14px; display: none;">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                    <button type="button" onclick="clearDepositHistorySearch()" class="button secondary" style="padding: 8px 14px; font-size: 12px; font-weight: 600; border-radius: 6px;">
                        Reset
                    </button>
                </div>
            </div>

            <!-- Table with Print & Download Options -->
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow-x: auto; background: white;">
                <table>
                    <thead>
                        <tr style="background: #0284c7; color: white;">
                            <th style="color: white; width: 120px;">Receipt No</th>
                            <th style="color: white;">Member ID & Name</th>
                            <th style="color: white;">Category</th>
                            <th style="color: white;">Amount</th>
                            <th style="color: white;">Mode</th>
                            <th style="color: white;">Date & Time</th>
                            <th style="color: white; text-align: center; min-width: 185px;">Receipt Options</th>
                        </tr>
                    </thead>
                    <tbody id="depositHistoryTableBody">
                        @forelse($receipts as $r)
                        @php
                            $waMsg = urlencode("Mahamilon Society Payment Receipt\nReceipt No: {$r->receipt_no}\nMember: {$r->name} ({$r->member_no})\nAmount: Rs. " . number_format((float) $r->amount, 2) . "\nDate: " . \Carbon\Carbon::parse($r->created_at)->format('d-m-Y'));
                        @endphp
                        <tr class="deposit-history-row" data-search="{{ strtolower($r->receipt_no . ' ' . $r->name . ' ' . $r->member_no . ' ' . $r->category . ' ' . $r->payment_mode) }}">
                            <td><b>{{ $r->receipt_no }}</b></td>
                            <td><b>{{ $r->name }}</b><br><small class="muted">{{ $r->member_no }}</small></td>
                            <td><span class="badge">{{ str_replace('_', ' ', $r->category) }}</span></td>
                            <td><b>&#8377;{{ number_format((float) $r->amount, 2) }}</b></td>
                            <td>{{ strtoupper($r->payment_mode) }}</td>
                            <td>{{ \Carbon\Carbon::parse($r->created_at)->format('d M Y, h:i A') }}</td>
                            <td style="text-align: center; white-space: nowrap;">
                                <div style="display: inline-flex; align-items: center; gap: 4px;">
                                    @if($can('deposit.create') || $can('withdrawal.create') || in_array(auth()->user()->role ?? '', ['super_admin','president','manager','secretary'], true))
                                    <button type="button" class="button" style="padding: 3px 8px; font-size: 11px; background: #f59e0b; color: #0f172a; display: inline-flex; align-items: center; gap: 3px; border: none; font-weight: 700; cursor: pointer; border-radius: 4px;"
                                        data-id="{{ $r->id }}"
                                        data-receipt-no="{{ $r->receipt_no }}"
                                        data-account-id="{{ $r->account_id }}"
                                        data-account-no="{{ $r->account_no ?? '' }}"
                                        data-name="{{ $r->name }}"
                                        data-member-no="{{ $r->member_no }}"
                                        data-amount="{{ (float)$r->amount }}"
                                        data-payment-mode="{{ $r->payment_mode }}"
                                        data-bank-id="{{ $r->bank_account_id ?? '' }}"
                                        data-notes="{{ $r->notes ?? '' }}"
                                        data-date="{{ \Carbon\Carbon::parse($r->created_at)->format('Y-m-d') }}"
                                        data-category="{{ $r->category }}"
                                        onclick="openEditDepositModal(this)"
                                        title="Edit / Correct Wrong Entry">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    @endif
                                    <a class="button" style="padding: 3px 8px; font-size: 11px; background: #0284c7; color: white; display: inline-flex; align-items: center; gap: 3px; text-decoration: none;" href="/admin/receipts/{{ $r->id }}/view" target="_blank" title="Print Official Receipt">
                                        <i class="bi bi-printer"></i> Print
                                    </a>
                                    <a class="button secondary" style="padding: 3px 8px; font-size: 11px; display: inline-flex; align-items: center; gap: 3px; text-decoration: none;" href="/admin/receipts/{{ $r->id }}/download" title="Download Receipt HTML">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                    <a class="button" style="padding: 3px 7px; font-size: 11px; background: #25D366; color: white; display: inline-flex; align-items: center; text-decoration: none;" href="https://wa.me/?text={{ $waMsg }}" target="_blank" title="Share via WhatsApp">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" style="text-align: center; padding: 24px;" class="muted">No deposits or collections recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section id="fd-rd" class="card section">
        <h2>Fixed & Recurring Deposits (FD & RD)</h2>
        <p class="muted">Manage term deposits with automated quarterly compound interest, maturity dates, and certificate generation (Sections 4 & 5).</p>

        @if($can('account.create'))
        <div class="split" style="margin-bottom:24px">
            <div style="background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:16px">
                <h3 style="margin-top:0">Open Fixed Deposit (FD)</h3>
                <form method="post" action="/admin/fd" class="field">@csrf
                    <div class="field"><label>Member</label><select name="member_id" required>
                        @foreach($members->where('user_status','active') as $m)<option value="{{$m->id}}">{{$m->member_no}} — {{$m->name}}</option>@endforeach
                    </select></div>
                    <div class="field"><label>Scheme</label><select name="scheme_id" required>
                        @foreach($schemes->where('type','fd') as $s)<option value="{{$s->id}}">{{$s->name}} ({{$s->annual_rate}}% p.a.)</option>@endforeach
                    </select></div>
                    <div class="row">
                        <div class="field"><label>FD Principal (&#8377;)</label><input name="principal" type="number" min="1000" step=".01" value="20000" required></div>
                        <div class="field"><label>Annual Rate (%)</label><input name="annual_rate" type="number" min="1" max="30" step=".1" value="8.0" required></div>
                    </div>
                    <div class="row">
                        <div class="field"><label>Tenure (Months)</label><input name="tenure_months" type="number" min="1" max="120" value="12" required></div>
                        <div class="field"><label>Payment Mode</label><select name="payment_mode"><option value="cash">Cash</option><option value="bank">Bank</option><option value="upi">UPI</option></select></div>
                    </div>
                    <button class="success" style="margin-top:8px">Open FD Account</button>
                </form>
            </div>

            <div style="background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:16px">
                <h3 style="margin-top:0">Open Recurring Deposit (RD)</h3>
                <form method="post" action="/admin/rd" class="field">@csrf
                    <div class="field"><label>Member</label><select name="member_id" required>
                        @foreach($members->where('user_status','active') as $m)<option value="{{$m->id}}">{{$m->member_no}} — {{$m->name}}</option>@endforeach
                    </select></div>
                    <div class="field"><label>Scheme (Daily or Monthly)</label><select name="scheme_id" id="rd-scheme-select" onchange="updateRdFormFields(this)" required>
                        @foreach($schemes->where('type','rd') as $s)
                        <option value="{{$s->id}}" data-freq="{{$s->deposit_frequency}}" data-rate="{{$s->annual_rate}}">{{$s->name}} ({{ ucfirst($s->deposit_frequency) }}, {{$s->annual_rate}}% p.a.)</option>
                        @endforeach
                    </select></div>
                    <div class="row">
                        <div class="field">
                            <label id="rd-amount-label">Deposit Amount (&#8377;)</label>
                            <input name="monthly_amount" id="rd-amount-input" type="number" min="10" step=".01" value="50" required placeholder="50">
                            <small class="muted" id="rd-amount-hint">Enter daily or monthly installment amount (e.g. ₹50/day)</small>
                        </div>
                        <div class="field">
                            <label>Annual Rate (%)</label>
                            <input name="annual_rate" id="rd-rate-input" type="number" min="1" max="30" step=".1" value="9.0" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="field">
                            <label id="rd-tenure-label">Tenure (Days or Months)</label>
                            <input name="tenure_months" id="rd-tenure-input" type="number" min="1" max="1000" value="100" required>
                            <small class="muted" id="rd-tenure-hint">For daily RD: enter days (e.g. 100, 365). For monthly RD: months (e.g. 12)</small>
                        </div>
                        <div class="field"><label>Payment Mode</label><select name="payment_mode"><option value="cash">Cash</option><option value="bank">Bank</option><option value="upi">UPI</option></select></div>
                    </div>
                    <div class="field">
                        <label>Opening Date (Back-dating permitted)</label>
                        <input name="start_date" type="date" value="{{ date('Y-m-d') }}">
                    </div>
                    <button class="success" style="margin-top:8px">Open RD & Generate Schedule</button>
                </form>
            </div>
        </div>
        @endif

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; margin-bottom: 10px;">
            <h3 style="margin: 0;">Active Fixed Deposits</h3>
            <span class="muted" style="font-size: 12px;">Total: {{ count($fdAccounts) }}</span>
        </div>
        <table><thead><tr><th>Certificate / Account</th><th>Member</th><th>Principal</th><th>Rate</th><th>Matures On</th><th>Maturity Amount</th><th>Actions</th></tr></thead><tbody>
        @forelse($fdAccounts as $fd)
        <tr>
            <td><b>{{$fd->certificate_no ?: 'FD-N/A'}}</b><br><small class="muted">{{$fd->account_no}}</small></td>
            <td>{{$fd->name}}<br><small class="muted">{{$fd->member_no}}</small></td>
            <td>&#8377;{{number_format($fd->principal,2)}}</td>
            <td>{{$fd->annual_rate}}% p.a.</td>
            <td>{{$fd->matures_on}}</td>
            <td><b>&#8377;{{number_format($fd->maturity_amount ?: $fd->balance,2)}}</b></td>
            <td>
                <div style="display: flex; gap: 4px;">
                    <a href="/admin/accounts/{{$fd->id}}/fd-certificate" target="_blank" class="button" style="padding: 3px 8px; font-size: 11px; background: #0284c7; color: white; display: inline-flex; align-items: center; gap: 3px;" title="View & Print Official FD Certificate">
                        <i class="bi bi-printer"></i> Certificate
                    </a>
                    <a href="/admin/accounts/{{$fd->id}}/fd-certificate/download" class="button" style="padding: 3px 8px; font-size: 11px; background: #f1f5f9; color: #334155; display: inline-flex; align-items: center;" title="Download Certificate HTML">
                        <i class="bi bi-download"></i>
                    </a>
                </div>
            </td>
        </tr>
        @empty<tr><td colspan="7">No active Fixed Deposits.</td></tr>@endforelse
        </tbody></table>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; margin-bottom: 10px;">
            <h3 style="margin: 0;">Active Recurring Deposits</h3>
            <span class="muted" style="font-size: 12px;">Total: {{ count($rdAccounts) }}</span>
        </div>
        <table><thead><tr><th>Account No</th><th>Type</th><th>Member</th><th>Commitment</th><th>Total Balance</th><th>Matures On</th><th>Maturity Target</th><th>Actions</th></tr></thead><tbody>
        @forelse($rdAccounts as $rd)
        <tr>
            <td><b>{{$rd->account_no}}</b><br><small class="muted">{{ $rd->scheme_name ?? 'RD' }}</small></td>
            <td>
                @if(($rd->deposit_frequency ?? '') === 'daily')
                <span class="badge" style="background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;font-weight:700;">DAILY</span>
                @else
                <span class="badge" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;font-weight:700;">MONTHLY</span>
                @endif
            </td>
            <td>{{$rd->name}}<br><small class="muted">{{$rd->member_no}}</small></td>
            <td>&#8377;{{number_format($rd->monthly_commitment,2)}}/{{ ($rd->deposit_frequency ?? '') === 'daily' ? 'day' : 'mo' }}</td>
            <td><b style="color:#059669;">&#8377;{{number_format($rd->balance,2)}}</b></td>
            <td>{{$rd->matures_on ?: 'N/A'}}</td>
            <td><b>&#8377;{{number_format($rd->maturity_amount ?: 0,2)}}</b></td>
            <td>
                <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                    <button type="button" class="button" onclick="openRdScheduleModal({{ $rd->id }})" style="padding: 3px 8px; font-size: 11px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;" title="View Complete Schedule & Passbook">
                        <i class="bi bi-calendar3"></i> Passbook
                    </button>
                    @if($can('deposit.create'))
                    <button type="button" class="button" onclick="openAdvancePayModal({{ $rd->id }}, '{{ $rd->account_no }}', '{{ addslashes($rd->name) }}', {{ $rd->monthly_commitment }}, '{{ $rd->deposit_frequency ?? 'daily' }}')" style="padding: 3px 8px; font-size: 11px; background: #059669; color: white; border: none; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;" title="Pay Next 10 Days or Custom Days in Advance">
                        <i class="bi bi-cash-stack"></i> Pay Advance / Days
                    </button>
                    @endif
                </div>
            </td>
        </tr>
        @empty<tr><td colspan="8">No active Recurring Deposits.</td></tr>@endforelse
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
                <div class="field"><label>Shares (&#8377;500/month each)</label><input name="share_count" type="number" value="{{old('share_count',1)}}" min="1" max="100"></div>
                <div class="field"><label>Custom interest % (optional)</label><input name="custom_interest_rate" value="{{old('custom_interest_rate')}}" type="number" step=".001" min="0" max="30" placeholder="Blank = plan rate"></div>
                <div class="field"><label>Deposit Tenure (Months)</label><input name="tenure_months" type="number" min="1" max="600" value="{{old('tenure_months', 12)}}" placeholder="e.g. 12, 24, 36, 60"></div>
                <div class="field"><label>Maturity Date (optional)</label><input name="maturity_date" type="date" value="{{old('maturity_date')}}"></div>
                <button id="admin-account-submit" data-existing-label="{{$can('account.approve')?'Create account & request payment':'Submit for approval'}}" data-new-label="Create applicant & account request">{{$defaultMemberSource==='new'?'Create applicant & account request':($can('account.approve')?'Create account & request payment':'Submit for approval')}}</button>
            </div>
        </form>
        @endif
        <!-- Live Accounts Search Bar by Account No, Member ID, Name -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4 style="font-size: 13px; font-weight: 800; color: #0284c7; margin: 0; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-search"></i> Search Accounts by ID, Name or Account No
                </h4>
                <span id="accountSearchCountInfo" class="muted" style="font-size: 11px;">Showing {{ count($accounts) }} account(s)</span>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 280px; position: relative;">
                    <i class="bi bi-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 13px;"></i>
                    <input type="text" id="accountSearchInput" placeholder="Search by Member Name, Member ID (e.g. MS-2026...), Account No, or Plan..."
                        oninput="filterAccountsTable(this.value)"
                        style="width: 100%; padding: 8px 32px 8px 34px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none; box-sizing: border-box;">
                    <button type="button" onclick="clearAccountSearch()" id="accountClearBtn" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 14px; display: none;">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
                <button type="button" onclick="clearAccountSearch()" class="button secondary" style="padding: 8px 14px; font-size: 12px; font-weight: 600; border-radius: 6px;">
                    Reset
                </button>
            </div>
        </div>

        <h3>Account requests and active accounts</h3>
        <table><thead><tr style="background:#0284c7;color:white;"><th style="color:white;">Member / account</th><th style="color:white;">Plan</th><th style="color:white;">Amount / shares</th><th style="color:white;">Interest</th><th style="color:white;">Workflow</th><th style="color:white;">Actions</th></tr></thead><tbody id="accountsTableBody">
        @foreach($accounts as $a)<tr class="account-row" data-search="{{ strtolower($a->name . ' ' . $a->member_no . ' ' . $a->account_no . ' ' . $a->scheme_name . ' ' . $a->status . ' ' . $a->onboarding_status) }}"><td><b>{{$a->name}}</b><br>{{$a->member_no}} · {{$a->account_no}}</td><td>{{$a->scheme_name}}</td><td>&#8377;{{number_format($a->principal,2)}}<br>{{$a->share_count}} share(s)</td><td>
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
        @forelse($payments as $p)<tr><td><b>{{$p->name}}</b><br><small class="muted">{{$p->account_no}}</small></td><td>{{$p->reference}}</td><td><b>&#8377;{{number_format($p->amount,2)}}</b></td><td>{{$p->utr ?: 'Not submitted / Cash Counter'}}</td><td><span class="badge {{$p->status}}">{{ucfirst($p->status)}}</span></td><td>
            @if($can('payment.accept') && in_array($p->status, ['submitted', 'requested']))
            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                <form method="post" action="/admin/payments/{{$p->id}}/decision" style="display:inline">
                    @csrf
                    <input type="hidden" name="decision" value="accepted">
                    <input type="hidden" name="payment_mode" value="cash">
                    <button class="button" style="padding: 4px 8px; font-size: 11px; background: #16a34a; color: white; border: none; border-radius: 4px; font-weight: 700; cursor: pointer;" title="Accept payment in Cash at counter and activate account">
                        <i class="bi bi-cash-stack"></i> Accept Cash
                    </button>
                </form>
                <form method="post" action="/admin/payments/{{$p->id}}/decision" style="display:inline">
                    @csrf
                    <input type="hidden" name="decision" value="accepted">
                    <input type="hidden" name="payment_mode" value="bank">
                    <button class="button secondary" style="padding: 4px 8px; font-size: 11px; border-radius: 4px; font-weight: 600; cursor: pointer;" title="Accept payment via Bank / Online UPI transfer">
                        <i class="bi bi-bank"></i> Accept Bank
                    </button>
                </form>
                <form method="post" action="/admin/payments/{{$p->id}}/decision" style="display:inline" onsubmit="return confirm('Reject this payment request?');">
                    @csrf
                    <input type="hidden" name="decision" value="rejected">
                    <button class="button danger" style="padding: 4px 8px; font-size: 11px; background: #ef4444; color: white; border: none; border-radius: 4px; font-weight: 600; cursor: pointer;">
                        Reject
                    </button>
                </form>
            </div>
            @else
                <span class="muted" style="font-size: 11px;">Processed ({{ ucfirst($p->status) }})</span>
            @endif
        </td></tr>@empty<tr><td colspan="6" style="text-align:center;padding:16px;">No payment requests.</td></tr>@endforelse</tbody></table>
    </section>

    <section id="receipts" class="card section">
        <h2>Official Payment Receipts</h2>
        <p class="muted">Every deposit, EMI, FD, and withdrawal automatically generates a standardized receipt with unique receipt numbers (Section 18).</p>
        <table><thead><tr><th>Receipt No</th><th>Member</th><th>Category</th><th>Amount</th><th>Mode</th><th>Date</th><th>Actions</th></tr></thead><tbody>
        @forelse($receipts as $r)
        @php
            $waMsg = urlencode("Mahamilon Society Payment Receipt\nReceipt No: {$r->receipt_no}\nMember: {$r->name} ({$r->member_no})\nAmount: Rs. " . number_format((float) $r->amount, 2) . "\nDate: " . \Carbon\Carbon::parse($r->created_at)->format('d-m-Y'));
        @endphp
        <tr>
            <td><b>{{$r->receipt_no}}</b></td>
            <td>{{$r->name}}<br><small class="muted">{{$r->member_no}}</small></td>
            <td><span class="badge">{{str_replace('_',' ',$r->category)}}</span></td>
            <td><b>&#8377;{{number_format($r->amount,2)}}</b></td>
            <td>{{strtoupper($r->payment_mode)}}</td>
            <td>{{\Carbon\Carbon::parse($r->created_at)->format('d M Y, h:i A')}}</td>
            <td>
                <div style="display:inline-flex;align-items:center;gap:4px;">
                    @if($can('deposit.create') || $can('withdrawal.create') || in_array(auth()->user()->role ?? '', ['super_admin','president','manager','secretary'], true))
                    <button type="button" class="button" style="padding:4px 8px;font-size:11px;background:#f59e0b;color:#0f172a;font-weight:700;border:none;border-radius:4px;cursor:pointer;"
                        data-id="{{ $r->id }}"
                        data-receipt-no="{{ $r->receipt_no }}"
                        data-account-id="{{ $r->account_id }}"
                        data-account-no="{{ $r->account_no ?? '' }}"
                        data-name="{{ $r->name }}"
                        data-member-no="{{ $r->member_no }}"
                        data-amount="{{ (float)$r->amount }}"
                        data-payment-mode="{{ $r->payment_mode }}"
                        data-bank-id="{{ $r->bank_account_id ?? '' }}"
                        data-notes="{{ $r->notes ?? '' }}"
                        data-date="{{ \Carbon\Carbon::parse($r->created_at)->format('Y-m-d') }}"
                        data-category="{{ $r->category }}"
                        onclick="openEditDepositModal(this)"
                        title="Edit / Correct Wrong Entry">
                        <i class="bi bi-pencil-square"></i> Edit
                    </button>
                    @endif
                    <a class="button secondary" style="padding:4px 8px;font-size:11px" href="/admin/receipts/{{$r->id}}/view" target="_blank">View / Print</a>
                    <a class="button secondary" style="padding:4px 8px;font-size:11px" href="/admin/receipts/{{$r->id}}/download">Download</a>
                    <a class="button" style="padding:4px 8px;font-size:11px;background:#25D366" href="https://wa.me/?text={{$waMsg}}" target="_blank">WhatsApp</a>
                </div>
            </td>
        </tr>
        @empty<tr><td colspan="7">No receipts issued yet.</td></tr>@endforelse
        </tbody></table>
    </section>

    <section id="statements" class="card section">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px">
            <div>
                <h2 style="margin:0">Member Account Statements</h2>
                <p class="muted" style="margin:4px 0 0">Workflow: Select Member → Select Date Range → Select Account → Generate (Section 19).</p>
            </div>
            @if($stmtMemberId)
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a class="button" href="/admin/statements/{{ $stmtMemberId }}/print?from_date={{ $stmtFrom }}&to_date={{ $stmtTo }}&account_id={{ $stmtAccountId }}" target="_blank">🖨️ Print / Save PDF</a>
                <a class="button secondary" href="/admin/statements/{{ $stmtMemberId }}/export?from_date={{ $stmtFrom }}&to_date={{ $stmtTo }}&account_id={{ $stmtAccountId }}">📊 Export Excel (CSV)</a>
            </div>
            @endif
        </div>

        <!-- 4-Step Generator Form -->
        <form method="get" action="/admin" class="row" style="background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:16px;margin-bottom:20px;align-items:flex-end;gap:12px">
            <div class="field" style="flex:1.5;min-width:240px">
                <label>1. Select Member</label>
                <select name="stmt_member_id" required>
                    <option value="">-- Choose Member --</option>
                    @foreach($members as $m)
                        <option value="{{ $m->id }}" {{ (string)$stmtMemberId === (string)$m->id ? 'selected' : '' }}>
                            {{ $m->name }} ({{ $m->member_no }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="flex:1;min-width:130px">
                <label>2. From Date</label>
                <input type="date" name="stmt_from" value="{{ $stmtFrom }}">
            </div>
            <div class="field" style="flex:1;min-width:130px">
                <label>3. To Date</label>
                <input type="date" name="stmt_to" value="{{ $stmtTo }}">
            </div>
            <div class="field" style="flex:1.2;min-width:180px">
                <label>4. Select Account</label>
                <select name="stmt_account_id">
                    <option value="all" {{ $stmtAccountId === 'all' ? 'selected' : '' }}>All Accounts (Consolidated)</option>
                    @if($stmtMemberId && isset($statementData['accounts']))
                        @foreach($statementData['accounts'] as $acc)
                            <option value="{{ $acc->id }}" {{ (string)$stmtAccountId === (string)$acc->id ? 'selected' : '' }}>
                                {{ $acc->account_no }} ({{ $acc->scheme_name }})
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <button style="padding:10px 18px">Generate Statement</button>
        </form>

        @if($statementData)
            @php
                $s = $statementData['statement'];
                $sm = $statementData['member'];
            @endphp
            <div style="background:#f0f5ff;border:1px solid #93b4f0;border-radius:8px;padding:12px 16px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                <div>
                    <b style="font-size:15px;color:var(--navy)">{{ $sm->name }}</b> ({{ $sm->member_no }})
                    <span style="color:var(--muted);font-size:12px;margin-left:8px">Account: {{ $statementData['accountLabel'] }}</span>
                </div>
                <div style="font-size:12px;font-weight:700;color:var(--navy)">
                    Period: {{ \Carbon\Carbon::parse($stmtFrom)->format('d M Y') }} to {{ \Carbon\Carbon::parse($stmtTo)->format('d M Y') }}
                </div>
            </div>

            <!-- 8-Metric Scorecard Grid -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:10px;margin-bottom:20px">
                <div style="background:white;border:1px solid var(--line);border-radius:8px;padding:10px;text-align:center">
                    <small class="muted" style="display:block;font-size:10px;text-transform:uppercase;font-weight:700">1. Opening Bal</small>
                    <b style="font-size:15px;color:var(--navy)">&#8377;{{ number_format($s['opening_balance'], 2) }}</b>
                </div>
                <div style="background:white;border:1px solid var(--line);border-radius:8px;padding:10px;text-align:center">
                    <small class="muted" style="display:block;font-size:10px;text-transform:uppercase;font-weight:700">2. Deposit</small>
                    <b style="font-size:15px;color:var(--green)">+&#8377;{{ number_format($s['deposits'], 2) }}</b>
                </div>
                <div style="background:white;border:1px solid var(--line);border-radius:8px;padding:10px;text-align:center">
                    <small class="muted" style="display:block;font-size:10px;text-transform:uppercase;font-weight:700">3. Withdrawal</small>
                    <b style="font-size:15px;color:var(--red)">-&#8377;{{ number_format($s['withdrawals'], 2) }}</b>
                </div>
                <div style="background:white;border:1px solid var(--line);border-radius:8px;padding:10px;text-align:center">
                    <small class="muted" style="display:block;font-size:10px;text-transform:uppercase;font-weight:700">4. Loan</small>
                    <b style="font-size:15px">&#8377;{{ number_format($s['loans'], 2) }}</b>
                </div>
                <div style="background:white;border:1px solid var(--line);border-radius:8px;padding:10px;text-align:center">
                    <small class="muted" style="display:block;font-size:10px;text-transform:uppercase;font-weight:700">5. EMI</small>
                    <b style="font-size:15px">&#8377;{{ number_format($s['emis'], 2) }}</b>
                </div>
                <div style="background:white;border:1px solid var(--line);border-radius:8px;padding:10px;text-align:center">
                    <small class="muted" style="display:block;font-size:10px;text-transform:uppercase;font-weight:700">6. Interest</small>
                    <b style="font-size:15px;color:var(--green)">+&#8377;{{ number_format($s['interest'], 2) }}</b>
                </div>
                <div style="background:white;border:1px solid var(--line);border-radius:8px;padding:10px;text-align:center">
                    <small class="muted" style="display:block;font-size:10px;text-transform:uppercase;font-weight:700">7. Fine/Penalty</small>
                    <b style="font-size:15px;color:#b45309">&#8377;{{ number_format($s['fines'], 2) }}</b>
                </div>
                <div style="background:#eefaf2;border:1px solid #86efac;border-radius:8px;padding:10px;text-align:center">
                    <small class="muted" style="display:block;font-size:10px;text-transform:uppercase;font-weight:700">8. Closing Bal</small>
                    <b style="font-size:16px;color:var(--green)">&#8377;{{ number_format($s['closing_balance'], 2) }}</b>
                </div>
            </div>

            <!-- Ledger Table -->
            <h3>Itemized Statement Ledger</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference / Receipt</th>
                        <th>Account</th>
                        <th>Particulars</th>
                        <th>Mode</th>
                        <th style="text-align:right">Debit (Dr)</th>
                        <th style="text-align:right">Credit (Cr)</th>
                        <th style="text-align:right">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background:#f8fafc">
                        <td>{{ \Carbon\Carbon::parse($stmtFrom)->format('d M Y') }}</td>
                        <td colspan="4"><i>Opening Balance B/F</i></td>
                        <td>-</td>
                        <td>-</td>
                        <td style="text-align:right;font-weight:750">&#8377;{{ number_format($s['opening_balance'], 2) }}</td>
                    </tr>
                    @forelse($s['entries'] as $entry)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($entry['date'])->format('d M Y') }}</td>
                        <td>
                            <b>{{ $entry['reference'] }}</b>
                            @if(!empty($entry['receipt_no']))
                                <br><small class="muted">RCP: {{ $entry['receipt_no'] }}</small>
                            @endif
                        </td>
                        <td>{{ $entry['account_no'] }}</td>
                        <td>{{ $entry['description'] }}</td>
                        <td><span class="badge">{{ $entry['payment_mode'] }}</span></td>
                        <td style="text-align:right;color:var(--red);font-weight:700">
                            {{ $entry['debit'] > 0 ? '&#8377;'.number_format($entry['debit'], 2) : '-' }}
                        </td>
                        <td style="text-align:right;color:var(--green);font-weight:700">
                            {{ $entry['credit'] > 0 ? '&#8377;'.number_format($entry['credit'], 2) : '-' }}
                        </td>
                        <td style="text-align:right;font-weight:750;color:var(--navy)">
                            &#8377;{{ number_format($entry['running_balance'], 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:18px">No ledger entries for the selected range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <div style="text-align:center;padding:36px 20px;background:#f8fafc;border:1px dashed var(--line);border-radius:10px">
                <p class="muted" style="margin:0 0 10px;font-size:14px">Select a member above and click <b>Generate Statement</b> to view the full 8-metric scorecard and itemized transaction ledger.</p>
            </div>
        @endif
    </section>

    <section id="transactions" class="card section">
        <h2>Accounts and transactions</h2>
        @if($can('transaction.create'))
        <h3>Post account transaction</h3>
        <form method="post" action="/admin/transactions" class="row">@csrf
            <div class="field"><label>Active account</label><select name="account_id">@foreach($accounts->where('status','active') as $a)<option value="{{$a->id}}">{{$a->account_no}} — {{$a->name}} (&#8377;{{$a->balance}})</option>@endforeach</select></div>
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
            <div class="metric"><strong style="color:var(--green)">&#8377;{{number_format($transactionSummary['credits'],2)}}</strong><span>Filtered credits</span></div>
            <div class="metric"><strong style="color:var(--red)">&#8377;{{number_format($transactionSummary['debits'],2)}}</strong><span>Filtered debits</span></div>
            <div class="metric"><strong>&#8377;{{number_format($transactionSummary['net'],2)}}</strong><span>Filtered net movement</span></div>
        </div>
        <div class="row" style="justify-content:space-between;margin:16px 0 6px"><b>Results</b><span class="muted">Showing {{$transactions->firstItem()??0}}–{{$transactions->lastItem()??0}} of {{$transactions->total()}}</span></div>
        <table><thead><tr><th>Date</th><th>Member / account</th><th>Description</th><th>Direction</th><th>Amount</th><th>Status</th></tr></thead><tbody>
        @forelse($transactions as $t)<tr><td>{{$t->posted_at}}</td><td><b>{{$t->name}}</b><br>{{$t->member_no}} · {{$t->account_no}}</td><td>{{$t->description}}<br><small>{{$t->reference}}</small></td><td><span class="badge {{$t->direction==='credit'?'active':'rejected'}}">{{$t->direction}}</span></td><td>&#8377;{{number_format($t->amount,2)}}</td><td><span class="badge {{$t->status}}">{{$t->status}}</span></td></tr>@empty<tr><td colspan="6">No transactions match the selected filters.</td></tr>@endforelse
        </tbody></table>
        @if($transactions->hasPages())<div class="row" style="justify-content:flex-end;margin-top:14px">@if($transactions->onFirstPage())<span class="button secondary" style="opacity:.5">Previous</span>@else<a class="button secondary" href="{{$transactions->previousPageUrl()}}#transactions">Previous</a>@endif <span class="muted" style="align-self:center">Page {{$transactions->currentPage()}} of {{$transactions->lastPage()}}</span> @if($transactions->hasMorePages())<a class="button" href="{{$transactions->nextPageUrl()}}#transactions">Next</a>@else<span class="button secondary" style="opacity:.5">Next</span>@endif</div>@endif
    </section>

    @if($can('cash.manage'))
    <section id="cash" class="card section">
        <h2>Cash management</h2>
        <p class="muted">Bank and treasury totals come from an auditable cash ledger. Transfers create matching debit and credit entries.</p>
        <div class="grid">
            <div class="metric"><strong>&#8377;{{number_format($metrics['bank_cash'],2)}}</strong><span>Total amount in bank</span></div>
            <div class="metric"><strong>&#8377;{{number_format($metrics['treasury_cash'],2)}}</strong><span>Total cash in treasury</span></div>
            <div class="metric"><strong>&#8377;{{number_format($metrics['total_loans'],2)}}</strong><span>Total sanctioned loan amount</span></div>
            <div class="metric"><strong>&#8377;{{number_format($metrics['outstanding_loans'],2)}}</strong><span>Loan principal outstanding</span></div>
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
        @forelse($cashEntries as $entry)<tr><td>{{$entry->occurred_on}}</td><td>{{ucfirst($entry->location)}}</td><td><span class="badge">{{$entry->direction}}</span></td><td>{{str_replace('_',' ',$entry->category)}}</td><td>&#8377;{{number_format($entry->amount,2)}}</td><td>{{$entry->note}}<br><small>{{$entry->reference}}</small></td><td>{{$entry->actor??'System'}}</td></tr>
        @empty<tr><td colspan="7">No cash entries.</td></tr>@endforelse
        </tbody></table>
    </section>
    @endif

    @if($can('bank.transfer'))
    <section id="banks" class="card section">
        <h2>Bank Management & Contra Transfers</h2>
        <p class="muted">Manage society bank accounts and execute Contra transfers (Cash to Bank, Bank to Cash, and Inter-Bank) as per Section 12.</p>

        <div class="split" style="margin-bottom:24px">
            <div style="background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:16px">
                <h3 style="margin-top:0">Add Society Bank Account</h3>
                <form method="post" action="/admin/bank-accounts" class="field">@csrf
                    <div class="field"><label>Bank Name</label><input name="bank_name" placeholder="State Bank of India" required></div>
                    <div class="field"><label>Account Number</label><input name="account_number" placeholder="123456789012" required></div>
                    <div class="row">
                        <div class="field"><label>IFSC Code</label><input name="ifsc_code" placeholder="SBIN0001234"></div>
                        <div class="field"><label>Branch Name</label><input name="branch_name" placeholder="Main Branch"></div>
                    </div>
                    <div class="field"><label>Opening Balance (&#8377;)</label><input name="opening_balance" type="number" min="0" step=".01" value="0.00"></div>
                    <button class="success" style="margin-top:8px">Add Bank Account</button>
                </form>
            </div>

            <div style="background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:16px">
                <h3 style="margin-top:0">Execute Bank Contra Transfer</h3>
                <form method="post" action="/admin/bank-transfers" class="field">@csrf
                    <div class="field"><label>Transfer Type</label><select name="type" required>
                        <option value="cash_to_bank">Cash Deposit to Bank (Treasury → Bank)</option>
                        <option value="bank_to_cash">Bank Withdrawal to Cash (Bank → Treasury)</option>
                        <option value="bank_to_bank">Inter-Bank Transfer (Bank A → Bank B)</option>
                    </select></div>
                    <div class="field"><label>Transfer Amount (&#8377;)</label><input name="amount" type="number" min="1" step=".01" required></div>
                    <div class="row">
                        <div class="field"><label>Source Bank (if bank out)</label><select name="source_bank_id">
                            <option value="">None / Cash</option>
                            @foreach($bankAccounts as $b)<option value="{{$b->id}}">{{$b->bank_name}} (&#8377;{{number_format($b->current_balance,2)}})</option>@endforeach
                        </select></div>
                        <div class="field"><label>Target Bank (if bank in)</label><select name="target_bank_id">
                            <option value="">None / Cash</option>
                            @foreach($bankAccounts as $b)<option value="{{$b->id}}">{{$b->bank_name}} (&#8377;{{number_format($b->current_balance,2)}})</option>@endforeach
                        </select></div>
                    </div>
                    <div class="field"><label>Transfer Note</label><input name="note" placeholder="Cheque / Counter deposit"></div>
                    <button style="margin-top:8px">Execute Transfer</button>
                </form>
            </div>
        </div>

        <h3>Society Bank Accounts</h3>
        <table><thead><tr><th>Bank Name</th><th>Account Number</th><th>IFSC</th><th>Branch</th><th>Current Balance</th><th>Status</th></tr></thead><tbody>
        @forelse($bankAccounts as $b)
        <tr>
            <td><b>{{$b->bank_name}}</b></td>
            <td>{{$b->account_number}}</td>
            <td>{{$b->ifsc_code ?: 'N/A'}}</td>
            <td>{{$b->branch_name ?: 'N/A'}}</td>
            <td><b>&#8377;{{number_format($b->current_balance, 2)}}</b></td>
            <td><span class="badge active">{{$b->status}}</span></td>
        </tr>
        @empty<tr><td colspan="6">No bank accounts registered.</td></tr>@endforelse
        </tbody></table>
    </section>
    @endif

    @if($can('profit_loss.manage'))
    <section id="expenses" class="card section">
        <h2>Expenses & Profit/Loss</h2>
        <p class="muted">Track society expenditures with mandatory bill attachments and automated net P&L balance (Sections 16 & 17).</p>
        <div class="grid" style="margin-bottom:20px">
            @foreach($profitLoss as $period=>$summary)
            <div class="metric"><strong style="color:{{$summary['net']<0?'var(--red)':'var(--green)'}}">&#8377;{{number_format($summary['net'],2)}}</strong><span>{{ucfirst($period)}} profit / loss</span><br><small class="muted">Income &#8377;{{number_format($summary['income'],2)}} · Expense &#8377;{{number_format($summary['expense'],2)}}</small></div>
            @endforeach
        </div>

        <h3>Record Society Expense (Section 16)</h3>
        <form method="post" action="/admin/expenses" enctype="multipart/form-data" class="row" style="background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:16px;margin-bottom:24px">@csrf
            <div class="field"><label>Category</label><select name="category" required>
                <option value="office_rent">Office Rent</option>
                <option value="staff_salary">Staff Salary</option>
                <option value="electricity_bill">Electricity & Utilities</option>
                <option value="printing_stationery">Printing & Stationery</option>
                <option value="software_it">Software & IT</option>
                <option value="other_expense">Other Expense</option>
            </select></div>
            <div class="field"><label>Amount (&#8377;)</label><input name="amount" type="number" min="1" step=".01" required></div>
            <div class="field"><label>Payment Mode</label><select name="payment_mode"><option value="cash">Cash</option><option value="bank">Bank</option></select></div>
            <div class="field"><label>Bank Account (if Bank)</label><select name="bank_account_id"><option value="">Treasury Cash</option>@foreach($bankAccounts as $b)<option value="{{$b->id}}">{{$b->bank_name}} (&#8377;{{number_format($b->current_balance,2)}})</option>@endforeach</select></div>
            <div class="field"><label>Upload Bill (PDF, JPG, PNG)</label><input type="file" name="bill" accept=".pdf,.jpg,.jpeg,.png"></div>
            <div class="field" style="min-width:240px"><label>Expense Note</label><input name="note" placeholder="Invoice details / Purpose" required></div>
            <button class="danger">Record Expense</button>
        </form>

        <h3>Income / Expense Ledger</h3>
        <table><thead><tr><th>Date</th><th>Type</th><th>Category</th><th>Amount</th><th>Note</th><th>Staff</th></tr></thead><tbody>
        @forelse($profitLossEntries as $entry)<tr><td>{{$entry->occurred_on}}</td><td><span class="badge {{$entry->entry_type==='income'?'active':'rejected'}}">{{$entry->entry_type}}</span></td><td>{{str_replace('_',' ',$entry->category)}}</td><td>&#8377;{{number_format($entry->amount,2)}}</td><td>{{$entry->note}}</td><td>{{$entry->actor??'System'}}</td></tr>
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
        <tr><td>{{$account->name}}<br>{{$account->account_no}}</td><td>&#8377;{{number_format($account->balance,2)}}</td><td>{{$account->opened_on}}</td><td>
            @if($can('account.close'))<form method="post" action="/admin/accounts/{{$account->id}}/maturity" class="row">@csrf<input name="matures_on" type="date" value="{{$account->matures_on}}" required><button>Save</button></form>
            @else {{$account->matures_on}} @endif
        </td><td>@if($closure)<span class="badge {{$closure->status==='completed'?'active':''}}">{{str_replace('_',' ',$closure->status)}}</span><br>Withdrawal: {{$closure->withdrawal_available_on}}
            @if($closure->status === 'completed')
                <div style="margin-top: 4px; display: flex; gap: 4px;">
                    <a href="/admin/closures/{{ $closure->id }}/certificate" target="_blank" class="button" style="padding: 2px 6px; font-size: 10px; background: #0284c7; color: white; display: inline-flex; align-items: center; gap: 2px; text-decoration: none;"><i class="bi bi-printer"></i> Cert</a>
                    <a href="/admin/closures/{{ $closure->id }}/certificate/download" class="button secondary" style="padding: 2px 6px; font-size: 10px; display: inline-flex; align-items: center; gap: 2px; text-decoration: none;"><i class="bi bi-download"></i></a>
                </div>
            @endif
        @else No closure request @endif</td><td>
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
        <!-- Search Bar for Closure History -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; margin-bottom: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); margin-top: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4 style="font-size: 13px; font-weight: 800; color: #0284c7; margin: 0; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-search"></i> Search Closure Records by ID, Name or Account No
                </h4>
                <span id="closureSearchCountInfo" class="muted" style="font-size: 11px;">Showing {{ count($closures) }} record(s)</span>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 280px; position: relative;">
                    <i class="bi bi-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 13px;"></i>
                    <input type="text" id="closureSearchInput" placeholder="Search by Member Name, Member ID (e.g. MS-...), Account No, or Status..."
                        oninput="filterClosureHistoryTable(this.value)"
                        style="width: 100%; padding: 8px 32px 8px 34px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none; box-sizing: border-box;">
                    <button type="button" onclick="clearClosureSearch()" id="closureClearBtn" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 14px; display: none;">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
                <button type="button" onclick="clearClosureSearch()" class="button secondary" style="padding: 8px 14px; font-size: 12px; font-weight: 600; border-radius: 6px;">
                    Reset
                </button>
            </div>
        </div>

        <h3>Closure history & Settlement Certificates</h3>
        <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow-x: auto; background: white;">
            <table>
                <thead>
                    <tr style="background:#0284c7;color:white;">
                        <th style="color:white;">Member / account</th>
                        <th style="color:white;">Requested</th>
                        <th style="color:white;">Maturity</th>
                        <th style="color:white;">Withdrawal available</th>
                        <th style="color:white;">Amount</th>
                        <th style="color:white;">Status</th>
                        <th style="color:white; text-align: center; min-width: 175px;">Certificate Options</th>
                    </tr>
                </thead>
                <tbody id="closureHistoryTableBody">
                    @forelse($closures as $closure)
                    <tr class="closure-row" data-search="{{ strtolower($closure->name . ' ' . ($closure->member_no ?? '') . ' ' . $closure->account_no . ' ' . $closure->status) }}">
                        <td><b>{{$closure->name}}</b><br><small class="muted">{{$closure->member_no ?? ''}} · {{$closure->account_no}}</small></td>
                        <td>{{$closure->requested_on}}</td>
                        <td>{{$closure->maturity_on}}</td>
                        <td>{{$closure->withdrawal_available_on}}</td>
                        <td><b>&#8377;{{number_format($closure->closure_amount??$closure->balance,2)}}</b></td>
                        <td><span class="badge {{$closure->status==='completed'?'active':''}}">{{str_replace('_',' ',$closure->status)}}</span></td>
                        <td style="text-align: center; white-space: nowrap;">
                            <div style="display: inline-flex; align-items: center; gap: 4px;">
                                <a href="/admin/closures/{{ $closure->id }}/certificate" target="_blank" class="button" style="padding: 3px 8px; font-size: 11px; background: #0284c7; color: white; display: inline-flex; align-items: center; gap: 3px; text-decoration: none;" title="Print Official Closure Certificate">
                                    <i class="bi bi-printer"></i> Print
                                </a>
                                <a href="/admin/closures/{{ $closure->id }}/certificate/download" class="button secondary" style="padding: 3px 8px; font-size: 11px; display: inline-flex; align-items: center; gap: 3px; text-decoration: none;" title="Download Certificate HTML">
                                    <i class="bi bi-download"></i> Download
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align: center; padding: 24px;" class="muted">No account closure requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endif

    @if($can('reports.view'))
    <section id="reports" class="card section">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px">
            <div>
                <h2 style="margin:0">Comprehensive Operational Reports</h2>
                <p class="muted" style="margin:4px 0 0">Workflow: Filter → Search → View → Generate → Export (PDF / Excel / Print) · Section 20</p>
            </div>
            @if($activeReport)
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a class="button" href="/admin/reports/print?{{ http_build_query(request()->query()) }}" target="_blank">🖨️ Print / Save as PDF</a>
                <a class="button secondary" href="/admin/reports/{{ $activeReportType }}.csv?{{ http_build_query(request()->query()) }}">📊 Export to Excel (CSV)</a>
            </div>
            @endif
        </div>

        <!-- 7-Point Filter Engine -->
        <form method="get" action="/admin" style="background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:16px;margin-bottom:20px">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:14px">
                <div class="field">
                    <label>Report Type</label>
                    <select name="report_type">
                        <option value="transactions" {{ $activeReportType === 'transactions' ? 'selected' : '' }}>Account Transactions</option>
                        <option value="receipts" {{ $activeReportType === 'receipts' ? 'selected' : '' }}>Payment Receipts & Collections</option>
                        <option value="loans" {{ $activeReportType === 'loans' ? 'selected' : '' }}>Loans & Outstanding</option>
                        <option value="accounts" {{ $activeReportType === 'accounts' ? 'selected' : '' }}>Accounts & Balances</option>
                        <option value="cash" {{ $activeReportType === 'cash' ? 'selected' : '' }}>Cash & Vault Ledger</option>
                        <option value="profit-loss" {{ $activeReportType === 'profit-loss' ? 'selected' : '' }}>Profit & Loss (P&L)</option>
                        <option value="members" {{ $activeReportType === 'members' ? 'selected' : '' }}>Member Registry & KYC</option>
                    </select>
                </div>
                <div class="field">
                    <label>From Date</label>
                    <input type="date" name="from" value="{{ request('from') }}">
                </div>
                <div class="field">
                    <label>To Date</label>
                    <input type="date" name="to" value="{{ request('to') }}">
                </div>
                <div class="field">
                    <label>Member</label>
                    <select name="member_id">
                        <option value="">All Members</option>
                        @foreach($members as $m)
                            <option value="{{ $m->id }}" {{ (string)request('member_id') === (string)$m->id ? 'selected' : '' }}>
                                {{ $m->name }} ({{ $m->member_no }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Staff / Actor</label>
                    <select name="staff_id">
                        <option value="">All Staff</option>
                        @foreach($staffUsers as $su)
                            <option value="{{ $su->id }}" {{ (string)request('staff_id') === (string)$su->id ? 'selected' : '' }}>
                                {{ $su->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Payment Mode</label>
                    <select name="payment_mode">
                        <option value="all">All Modes</option>
                        <option value="cash" {{ request('payment_mode') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank" {{ request('payment_mode') === 'bank' ? 'selected' : '' }}>Bank</option>
                        <option value="upi" {{ request('payment_mode') === 'upi' ? 'selected' : '' }}>UPI</option>
                    </select>
                </div>
                <div class="field">
                    <label>Status</label>
                    <select name="status">
                        <option value="all">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="posted" {{ request('status') === 'posted' ? 'selected' : '' }}>Posted / Completed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending / Submitted</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="field">
                    <label>Min Amount (&#8377;)</label>
                    <input type="number" step="0.01" min="0" name="amount_min" value="{{ request('amount_min') }}" placeholder="0.00">
                </div>
                <div class="field">
                    <label>Max Amount (&#8377;)</label>
                    <input type="number" step="0.01" min="0" name="amount_max" value="{{ request('amount_max') }}" placeholder="No limit">
                </div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                <button type="submit" style="padding:9px 18px">🔍 Search & View Report</button>
                <a href="/admin#reports" class="button secondary" style="padding:9px 14px">Reset Filters</a>
            </div>
        </form>

        @if($activeReport)
            <!-- Summary Stats Bar -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px">
                <div style="background:#f0f5ff;border:1px solid #c7d9f9;border-radius:8px;padding:12px;text-align:center">
                    <small class="muted" style="font-size:10.5px;text-transform:uppercase;font-weight:750">Total Records</small>
                    <strong style="font-size:18px;color:var(--navy);display:block">{{ number_format($activeReport['totalRecords']) }}</strong>
                </div>
                <div style="background:#f0f5ff;border:1px solid #c7d9f9;border-radius:8px;padding:12px;text-align:center">
                    <small class="muted" style="font-size:10.5px;text-transform:uppercase;font-weight:750">Total Volume / Turnover</small>
                    <strong style="font-size:18px;color:var(--navy);display:block">&#8377;{{ number_format($activeReport['totalAmount'], 2) }}</strong>
                </div>
                <div style="background:#f0f5ff;border:1px solid #c7d9f9;border-radius:8px;padding:12px;text-align:center">
                    <small class="muted" style="font-size:10.5px;text-transform:uppercase;font-weight:750">Average Transaction</small>
                    <strong style="font-size:18px;color:var(--navy);display:block">&#8377;{{ number_format($activeReport['avgAmount'], 2) }}</strong>
                </div>
            </div>

            <!-- Report Data Table -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin:16px 0 8px">
                <h3 style="margin:0">{{ $activeReport['title'] }}</h3>
                <span class="badge" style="font-size:11px">{{ $activeReport['totalRecords'] }} Records Loaded</span>
            </div>
            <table>
                <thead>
                    <tr>
                        @foreach($activeReport['headers'] as $h)
                            <th>{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeReport['rows'] as $row)
                    <tr>
                        @foreach((array)$row as $val)
                            <td>{{ $val }}</td>
                        @endforeach
                    </tr>
                    @empty
                    <tr><td colspan="{{ count($activeReport['headers']) }}" style="text-align:center;padding:24px;color:var(--muted)">No records matched the selected criteria.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        <h3 style="margin-top:28px">All-Member Operational Summary</h3>
        <table><thead><tr><th>Member</th><th>Contact</th><th>Status / KYC</th><th>MCS</th><th>Accounts</th><th>Balance</th><th>Loans</th><th>Loan outstanding</th></tr></thead><tbody>
        @foreach($memberReports as $report)<tr><td><a href="/admin/members/{{$report->id}}" style="color:var(--navy);font-weight:700;text-decoration:none">{{$report->name}}</a><br>{{$report->member_no}}</td><td>{{$report->phone}}</td><td>{{$report->status}} / {{$report->kyc_status}}</td><td>{{$report->mcs_score}}/900</td><td>{{$report->account_count}}</td><td>&#8377;{{number_format($report->account_balance,2)}}</td><td>{{$report->loan_count}}</td><td>&#8377;{{number_format($report->loan_outstanding,2)}}</td></tr>@endforeach
        </tbody></table>
    </section>
    @endif

    
    <!-- ================= LOAN SUBSECTION 1: APPLY LOAN (MATCHING SCREENSHOT 100%) ================= -->
    <section id="loan-apply" class="card section" style="padding: 24px; max-width: 1080px; margin: 0 auto;">
        <!-- Header from Screenshot: Loan Application Form -->
        <div style="margin-bottom: 24px;">
            <h2 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 4px;">Loan Application Form</h2>
            <span class="muted" style="font-size: 13px;">Apply for standard, personal, business, or emergency health credit lines</span>
        </div>

        @if(isset($errors) && $errors->loanApply && $errors->loanApply->any())
        <div class="error" role="alert" style="margin-bottom: 18px;">
            <b>Loan application could not be submitted:</b>
            <ul style="margin:6px 0 0;padding-left:20px">@foreach($errors->loanApply->all() as $error)<li>{{$error}}</li>@endforeach</ul>
        </div>
        @endif

        <form method="post" action="/admin/loans/apply" enctype="multipart/form-data" id="loanApplyMainForm">
            @csrf

            <!-- Card 1: Loan Options & Schemes (Matching Screenshot) -->
            <div style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; background: white; margin-bottom: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="border-bottom: 2px solid #0284c7; padding-bottom: 6px; margin-bottom: 18px;">
                    <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">Loan Options & Schemes</h3>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Loan Type <span style="color:#dc2626">*</span></label>
                        <select name="loan_type" id="loan-apply-type" onchange="onLoanTypeChange()" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="">Select Loan Type</option>
                            <option value="personal" @selected(old('loan_type')==='personal') selected>Personal Loan</option>
                            <option value="business" @selected(old('loan_type')==='business')>Business Loan</option>
                            <option value="emergency" @selected(old('loan_type')==='emergency')>Emergency Health Loan (3-Day SLA)</option>
                        </select>
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Loan Scheme</label>
                        <select id="loan-apply-scheme" onchange="onLoanSchemeChange()" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="standard">Standard Member Credit Scheme (12% p.a.)</option>
                            <option value="business">Commercial & Trade Expansion Scheme (14% p.a.)</option>
                            <option value="emergency">Emergency Relief Assistance (10% p.a.)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Card 2: Loan Details (Matching Screenshot 8-grid layout) -->
            <div style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; background: white; margin-bottom: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="border-bottom: 2px solid #0284c7; padding-bottom: 6px; margin-bottom: 18px;">
                    <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">Loan Details</h3>
                </div>

                <!-- Grid Row 1: Min Amt, Max Amt, Interest Rate, ROI Frequency -->
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">Minimum Loan Amount</label>
                        <input type="text" id="loan-detail-min-amt" value="&#8377;1,000" readonly style="width: 100%; padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: #f8fafc; font-weight: 700; color: #0f172a;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">Maximum Loan Amount</label>
                        <input type="text" id="loan-detail-max-amt" value="&#8377;5,00,000 (85% Cap)" readonly style="width: 100%; padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: #f8fafc; font-weight: 700; color: #0f172a;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">Loan Interest Rate (%)</label>
                        <input type="text" id="loan-detail-rate" value="12.00% p.a." readonly style="width: 100%; padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: #f8fafc; font-weight: 700; color: #0284c7;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">ROI Frequency</label>
                        <input type="text" value="Monthly" readonly style="width: 100%; padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: #f8fafc; color: #334155;">
                    </div>
                </div>

                <!-- Grid Row 2: ROI Type, Fixed Loan Frequency, Is Fixed Tenure, Max Tenure -->
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">ROI Type</label>
                        <input type="text" value="Reducing Balance" readonly style="width: 100%; padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: #f8fafc; color: #334155;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">Fixed Loan Frequency</label>
                        <input type="text" value="Monthly" readonly style="width: 100%; padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: #f8fafc; color: #334155;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">Is Fixed Tenure</label>
                        <input type="text" value="No" readonly style="width: 100%; padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: #f8fafc; color: #334155;">
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">Maximum Tenure (Months)</label>
                        <input type="text" value="120 Months" readonly style="width: 100%; padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; background: #f8fafc; font-weight: 700; color: #0f172a;">
                    </div>
                </div>
            </div>

            <!-- Card 3: Borrower Application Specifics -->
            <div style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; background: white; margin-bottom: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="border-bottom: 2px solid #0284c7; padding-bottom: 6px; margin-bottom: 18px;">
                    <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">Borrower & Disbursement Specifics</h3>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Select Member <span style="color:#dc2626">*</span></label>
                    <select name="member_id" id="loan-apply-member" onchange="updateLoanApplyLimits()" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                        <option value="">Select an active member...</option>
                        @foreach($members as $m)
                        @php $maxCap = round(((float)$m->active_savings) * 0.85, 2); @endphp
                        <option value="{{$m->id}}" data-savings="{{(float)$m->active_savings}}" data-max-cap="{{$maxCap}}" data-kyc="{{$m->kyc_status}}" @selected((string)old('member_id')===(string)$m->id)>
                            {{$m->member_no}} — {{$m->name}} (Active Savings: &#8377;{{number_format($m->active_savings, 2)}} · Max 85% Loan Cap: &#8377;{{number_format($maxCap, 2)}})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Requested Loan Amount (&#8377;) <span style="color:#dc2626">*</span></label>
                        <input name="amount" id="loan-apply-amount" type="number" min="1000" step=".01" value="{{old('amount')}}" oninput="updateLoanApplyLimits()" placeholder="Enter amount (min 1,000)" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-weight: 700;">
                    </div>
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Tenure (Months) <span style="color:#dc2626">*</span></label>
                        <input name="tenure_months" type="number" min="1" max="120" value="{{old('tenure_months', 12)}}" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">EMI Frequency <span style="color:#dc2626">*</span></label>
                        <select name="emi_frequency" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="monthly" @selected(old('emi_frequency','monthly')==='monthly')>Monthly</option>
                            <option value="weekly" @selected(old('emi_frequency')==='weekly')>Weekly</option>
                            <option value="daily" @selected(old('emi_frequency')==='daily')>Daily</option>
                        </select>
                    </div>
                </div>

                <div id="loan-apply-limit-hint" style="font-size:12px;margin:2px 0 14px;display:none;padding:8px 12px;border-radius:6px;background:#f8fafc;border:1px solid #e2e8f0;"></div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Purpose of Loan <span style="color:#dc2626">*</span></label>
                    <input name="purpose" value="{{old('purpose')}}" placeholder="e.g. Small business working capital / Home renovation / Agriculture / Education" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                </div>

                <!-- Guarantor Details -->
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:14px; margin-bottom:16px;">
                    <div style="font-weight: 800; font-size: 12px; color: #0f172a; margin-bottom: 10px; display:flex; align-items:center; gap:6px;">
                        <i class="bi bi-shield-check"></i> Guarantor Information
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Guarantor Full Name</label>
                            <input name="guarantor_name" value="{{old('guarantor_name')}}" placeholder="Full Name" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Guarantor Mobile</label>
                            <input name="guarantor_phone" value="{{old('guarantor_phone')}}" placeholder="10-digit mobile" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Relationship</label>
                            <input name="guarantor_relation" value="{{old('guarantor_relation')}}" placeholder="e.g. Brother / Colleague" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                        </div>
                    </div>
                </div>

                <!-- Loan KYC Document Upload -->
                <div style="margin-bottom: 20px;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Loan Supporting Documents (PDF, JPG, PNG)</label>
                    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; background: white;">
                    <small class="muted" style="font-size: 11px; display: block; margin-top: 4px;">Attach income proof, promissory note, or guarantor verification if available (max 5MB).</small>
                </div>

                <div>
                    <button type="submit" id="loan-apply-btn" class="button" style="background: #0284c7; color: white; padding: 10px 24px; font-size: 13px; font-weight: 800; border-radius: 6px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="bi bi-file-earmark-plus"></i> Submit Loan Application (Initial Status: Pending)
                    </button>
                </div>
            </div>
        </form>
    </section>

    <!-- ================= LOAN SUBSECTION 2: APPLY FIXED LOAN / PRE-APPROVED OFFERS ================= -->
    <section id="loan-fixed" class="card section" style="padding: 24px; max-width: 900px; margin: 0 auto;">
        <div style="margin-bottom: 20px;">
            <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 4px;">Apply Fixed Loan / Issue Pre-Approved Offer</h2>
            <span class="muted" style="font-size: 13px;">Offer an individually tailored fixed-tenure loan to eligible members based on active savings & MCS score.</span>
        </div>

        @if(isset($errors) && $errors->loanOffer && $errors->loanOffer->any())
        <div class="error" role="alert" style="margin-bottom: 16px;">
            <b>Loan offer could not be created:</b>
            <ul style="margin:6px 0 0;padding-left:20px">@foreach($errors->loanOffer->all() as $error)<li>{{$error}}</li>@endforeach</ul>
        </div>
        @endif

        <div style="background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <form method="post" action="/admin/loans/offers">
                @csrf
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Select Eligible Member <span style="color:#dc2626">*</span></label>
                    <select id="loan-offer-member" name="member_id" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                        <option value="">Select an eligible member</option>
                        @foreach($members as $m)
                        @php $maximumOffer = round(((float)$m->active_savings) * 0.85, 2); @endphp
                        <option value="{{$m->id}}" data-savings="{{number_format((float)$m->active_savings,2,'.','')}}" data-max-offer="{{number_format($maximumOffer,2,'.','')}}" @selected((string)old('member_id')===(string)$m->id) @disabled($maximumOffer<1000)>
                            {{$m->member_no}} — {{$m->name}} · Savings &#8377;{{number_format($m->active_savings,2)}} · Max Pre-Approved &#8377;{{number_format($maximumOffer,2)}}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Loan Type</label>
                        <select name="loan_type" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="personal">Personal Loan</option>
                            <option value="business">Business Loan</option>
                            <option value="emergency">Emergency Health Loan</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">EMI Frequency</label>
                        <select name="emi_frequency" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="monthly">Monthly</option>
                            <option value="weekly">Weekly</option>
                            <option value="daily">Daily</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Offer Amount (&#8377;) <span style="color:#dc2626">*</span></label>
                        <input id="loan-offer-amount" name="amount" type="number" min="1000" step=".01" value="{{old('amount')}}" placeholder="Amount" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-weight: 700;">
                    </div>
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Tenure (Months) <span style="color:#dc2626">*</span></label>
                        <input name="tenure_months" type="number" min="1" max="120" value="{{old('tenure_months', 12)}}" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Validity (Days) <span style="color:#dc2626">*</span></label>
                        <input name="offer_days" value="{{old('offer_days',14)}}" type="number" min="1" max="90" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Offer Purpose Note</label>
                    <input name="purpose" value="{{old('purpose', 'Pre-approved credit line')}}" placeholder="Pre-approved credit line" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                </div>

                <button type="submit" class="button" style="background: #0284c7; color: white; padding: 10px 24px; font-size: 13px; font-weight: 800; border-radius: 6px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="bi bi-file-earmark-lock"></i> Issue Pre-Approved Offer
                </button>
            </form>
        </div>
    </section>

    <!-- ================= LOAN SUBSECTION 3: UPLOAD LOAN KYC ================= -->
    <section id="loan-kyc" class="card section" style="padding: 24px;">
        <div style="border-left: 3px solid #0284c7; padding-left: 10px; margin-bottom: 18px;">
            <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 4px;">Upload Loan KYC & Documents Verification</h2>
            <span class="muted" style="font-size: 12px;">Verify borrower identity, collateral, promissory notes, and supporting proof</span>
        </div>

        <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow-x: auto; background: white;">
            <table>
                <thead>
                    <tr style="background: #0284c7; color: white;">
                        <th style="color: white;">App No</th>
                        <th style="color: white;">Borrower Name</th>
                        <th style="color: white;">Contact</th>
                        <th style="color: white;">Loan Amount</th>
                        <th style="color: white;">Guarantor Details</th>
                        <th style="color: white;">Document File</th>
                        <th style="color: white;">KYC Status</th>
                        <th style="color: white;">Quick Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $l)
                    <tr>
                        <td><b>{{$l->application_no}}</b></td>
                        <td><b>{{$l->name}}</b> ({{$l->member_no}})</td>
                        <td>{{$l->phone}}</td>
                        <td><b>&#8377;{{number_format($l->amount, 2)}}</b></td>
                        <td>
                            @if($l->guarantor_name)
                                <b>{{$l->guarantor_name}}</b><br>
                                <small class="muted">{{$l->guarantor_phone}} · {{$l->guarantor_relation}}</small>
                            @else
                                <span class="muted">No guarantor</span>
                            @endif
                        </td>
                        <td>
                            @if($l->document_path)
                                <a href="/admin/loans/{{$l->id}}/document" target="_blank" class="button secondary" style="padding: 4px 10px; font-size: 11px;">
                                    <i class="bi bi-file-earmark-pdf"></i> Download Doc
                                </a>
                            @else
                                <span class="muted" style="font-size: 11px;">No file uploaded</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{$l->kyc_status==='approved'?'active':'rejected'}}">{{ucfirst($l->kyc_status)}}</span>
                        </td>
                        <td>
                            @if($l->status === 'submitted')
                            <form method="post" action="/admin/loans/{{$l->id}}/verify" style="display:inline">@csrf
                                <input type="hidden" name="decision" value="verified">
                                <button class="button success" style="padding:4px 10px;font-size:11px;">✓ Verify KYC</button>
                            </form>
                            @else
                            <span class="badge" style="font-size: 11px;">{{ucfirst($l->status)}}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center;padding:16px;">No loans currently require KYC verification.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- ================= LOAN SUBSECTION 4: LOAN REPORT & WORKFLOW ================= -->
    <section id="loan-report" class="card section" style="padding: 24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:18px;">
            <div style="border-left: 3px solid #0284c7; padding-left: 10px;">
                <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 4px;">Loan Report & Verification Workflow</h2>
                <span class="muted" style="font-size: 12px;">6-Point verification, manager approval, sanctioning, and disbursement</span>
            </div>
            @if($can('interest.manage'))
            <form method="post" action="/admin/finance/run">@csrf<button class="button secondary" style="font-size:12px"><i class="bi bi-gear"></i> Run Financial Automation</button></form>
            @endif
        </div>

        <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow-x: auto; background: white;">
            <table>
                <thead>
                    <tr style="background: #0284c7; color: white;">
                        <th style="color: white;">Application / Member</th>
                        <th style="color: white;">Type / Purpose</th>
                        <th style="color: white;">Requested / Sanctioned</th>
                        <th style="color: white; min-width: 210px;">6-Point Verification</th>
                        <th style="color: white;">Status & SLA</th>
                        <th style="color: white; min-width: 210px;">Action & Decision</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($loans as $l)
                <tr>
                    <td>
                        <b>{{$l->name}}</b><br>
                        <small class="muted">{{$l->member_no}} · {{$l->phone}}</small><br>
                        <b>{{$l->application_no}}</b>
                    </td>
                    <td>
                        <b>{{ucfirst($l->loan_type)}}</b> ({{ucfirst($l->emi_frequency)}})<br>
                        <small class="muted">Tenure: {{$l->tenure_months}} mos</small><br>
                        <span style="font-size:12px">{{$l->purpose}}</span>
                    </td>
                    <td>
                        Requested: <b>&#8377;{{number_format($l->amount,2)}}</b><br>
                        @if($l->sanctioned_amount)
                            Sanctioned: <b style="color:var(--green)">&#8377;{{number_format($l->sanctioned_amount,2)}}</b><br>
                            Rate: {{number_format($l->annual_interest_rate ?: \App\Services\LoanSchedule::defaultRate($l->loan_type), 2)}}% p.a.
                        @endif
                        @if($l->outstanding_principal !== null)
                            <br><small class="muted">Bal: &#8377;{{number_format($l->outstanding_principal,2)}}</small>
                        @endif
                    </td>
                    <td style="font-size:12px;line-height:1.5">
                        <div>1. <b>KYC:</b> <span class="badge {{$l->kyc_status==='approved'?'active':'rejected'}}">{{$l->kyc_status}}</span></div>
                        <div>2. <b>Deposits:</b> &#8377;{{number_format($l->member_savings, 2)}} (85% cap: &#8377;{{number_format($l->member_savings * 0.85, 2)}})</div>
                        <div>3. <b>Previous Loans:</b> {{$l->previous_loans_count}}</div>
                        <div>4. <b>Outstanding Debt:</b> &#8377;{{number_format($l->existing_outstanding, 2)}}</div>
                        <div>5. <b>Guarantor:</b> {{$l->guarantor_name ?: 'None'}} @if($l->guarantor_phone)({{$l->guarantor_phone}} - {{$l->guarantor_relation}})@endif</div>
                        <div>6. <b>Documents:</b>
                            @if($l->document_path)
                                <a href="/admin/loans/{{$l->id}}/document" target="_blank" style="color:#0284c7;font-weight:700">📄 Download Doc</a>
                            @else
                                <span class="muted">No file</span>
                            @endif
                        </div>
                        @if($l->verified_by)
                            <div style="margin-top:4px;color:var(--green);font-size:11px">
                                ✓ Verified by {{$l->verifier_name ?? 'Staff'}} on {{\Carbon\Carbon::parse($l->verified_at)->format('d M, h:i A')}}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{$l->status==='disbursed'||$l->status==='approved'?'active':($l->status==='rejected'?'rejected':'')}}">
                            {{$l->status === 'under_review' ? 'Verified (Under Review)' : ($l->status === 'submitted' ? 'Pending Verification' : ucfirst($l->status))}}
                        </span>
                        @if($l->loan_type==='emergency' && in_array($l->status,['submitted','under_review']))
                            <br><b style="color:var(--red);font-size:11px">SLA: {{\Carbon\Carbon::parse($l->created_at)->addDays(3)->format('d M Y')}}</b>
                        @endif
                        @if($l->is_offer)
                            <br><small class="badge" style="background:#fef3c7;color:#92400e">Direct Offer</small>
                        @endif
                        @if($l->decision_note)
                            <div style="font-size:11px;color:var(--muted);margin-top:4px">Note: {{$l->decision_note}}</div>
                        @endif
                    </td>
                    <td>
                        <!-- Loan Verification Action -->
                        @if($l->status === 'submitted')
                            <div style="display:flex;flex-direction:column;gap:6px">
                                <form method="post" action="/admin/loans/{{$l->id}}/verify" style="display:inline">@csrf
                                    <input type="hidden" name="decision" value="verified">
                                    <button class="button success" style="padding:5px 9px;font-size:11px;width:100%">✓ Mark Verified</button>
                                </form>
                                <form method="post" action="/admin/loans/{{$l->id}}/verify" style="display:inline" onsubmit="const r = prompt('Enter mandatory rejection reason:'); if(!r) return false; this.verification_note.value = r;">@csrf
                                    <input type="hidden" name="decision" value="rejected">
                                    <input type="hidden" name="verification_note" value="">
                                    <button class="button danger" style="padding:5px 9px;font-size:11px;width:100%">✗ Reject</button>
                                </form>
                            </div>
                        <!-- Loan Approval Action -->
                        @elseif($l->status === 'under_review')
                            @if($can('loan.approve'))
                            <form method="post" action="/admin/loans/{{$l->id}}/decision" style="display:grid;gap:4px;background:#f1f5f9;padding:8px;border-radius:6px">@csrf
                                <input type="hidden" name="decision" value="approved">
                                <div style="display:flex;gap:4px">
                                    <input name="sanctioned_amount" type="number" value="{{$l->amount}}" style="width:100px;font-size:11px;padding:4px" title="Sanctioned Amount" placeholder="Amount" required>
                                    <input name="annual_interest_rate" type="number" step=".001" value="{{\App\Services\LoanSchedule::defaultRate($l->loan_type)}}" style="width:65px;font-size:11px;padding:4px" title="Rate %" placeholder="Rate %" required>
                                </div>
                                <button class="button success" style="padding:5px 8px;font-size:11px">Approve Loan</button>
                            </form>
                            @endif
                            @if($can('loan.reject'))
                            <form method="post" action="/admin/loans/{{$l->id}}/decision" style="margin-top:5px" onsubmit="const r = prompt('Enter mandatory rejection reason:'); if(!r) return false; this.note.value = r;">@csrf
                                <input type="hidden" name="decision" value="rejected">
                                <input type="hidden" name="note" value="">
                                <button class="button danger" style="padding:4px 8px;font-size:11px;width:100%">Reject Loan</button>
                            </form>
                            @endif
                        <!-- Loan Disbursement Action -->
                        @elseif($l->status === 'approved' && $can('loan.disburse'))
                            <form method="post" action="/admin/loans/{{$l->id}}/disburse" style="display:grid;gap:4px;background:#f0fdf4;border:1px solid #bbf7d0;padding:8px;border-radius:6px">@csrf
                                <b style="font-size:11px;color:var(--green)">Loan Disbursement</b>
                                <div class="row" style="gap:4px">
                                    <select name="payment_mode" style="padding:4px 6px;font-size:11px;flex:1"><option value="cash">Cash</option><option value="bank">Bank</option></select>
                                    <select name="bank_account_id" style="padding:4px 6px;font-size:11px;flex:1"><option value="">Treasury</option>@foreach($bankAccounts as $b)<option value="{{$b->id}}">{{$b->bank_name}}</option>@endforeach</select>
                                </div>
                                <div class="row" style="gap:4px">
                                    <input name="disbursed_amount" type="number" step=".01" value="{{$l->sanctioned_amount ?: $l->amount}}" style="font-size:11px;padding:4px;flex:1" title="Confirm Amount" required>
                                    <button class="button success" style="padding:4px 8px;font-size:11px">Disburse & Issue Receipt</button>
                                </div>
                            </form>
                        @elseif($l->status === 'disbursed')
                            <span style="color:var(--green);font-weight:700;font-size:12px">✓ Active Disbursed</span>
                        @else
                            <span class="muted" style="font-size:12px">{{ucfirst($l->status)}}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:16px;">No loan applications registered.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- ================= LOAN SUBSECTION 5: LOAN STATEMENT ================= -->
    <section id="loan-statement" class="card section" style="padding: 24px;">
        <div style="border-left: 3px solid #0284c7; padding-left: 10px; margin-bottom: 18px;">
            <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 4px;">Loan Statement & 8-Field EMI Schedule</h2>
            <span class="muted" style="font-size: 12px;">Detailed repayment schedules, interest breakdown, and installment balances</span>
        </div>

        <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow-x: auto; background: white;">
            <table>
                <thead>
                    <tr style="background: #0284c7; color: white;">
                        <th style="color: white;">Member / Loan</th>
                        <th style="color: white;">1. EMI Number</th>
                        <th style="color: white;">2. Due Date</th>
                        <th style="color: white;">3. Principal</th>
                        <th style="color: white;">4. Interest</th>
                        <th style="color: white;">5. Penalty</th>
                        <th style="color: white;">6. Total Payable</th>
                        <th style="color: white;">7. Paid Amount</th>
                        <th style="color: white;">8. Balance</th>
                        <th style="color: white;">Status</th>
                        <th style="color: white;">Fine Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($installments as $installment)
                <tr>
                    <td><b>{{$installment->name}}</b><br><small class="muted">{{$installment->application_no}}</small></td>
                    <td><b>#{{$installment->installment_no}}</b></td>
                    <td>{{$installment->due_on}}</td>
                    <td>&#8377;{{number_format($installment->principal_due, 2)}}</td>
                    <td>&#8377;{{number_format($installment->interest_due, 2)}}</td>
                    <td>&#8377;{{number_format($installment->penalty_due, 2)}}</td>
                    <td><b>&#8377;{{number_format($installment->total_payable, 2)}}</b></td>
                    <td>&#8377;{{number_format($installment->paid_amount, 2)}}</td>
                    <td><b style="color:{{$installment->total_outstanding > 0 ? 'var(--red)' : 'var(--green)'}}">&#8377;{{number_format($installment->total_outstanding, 2)}}</b></td>
                    <td><span class="badge {{$installment->status==='paid'?'active':($installment->status==='overdue'?'rejected':'')}}">{{$installment->status}}</span></td>
                    <td>
                        @if($can('loan.fine') && \Carbon\Carbon::parse($installment->due_on)->isBefore(today()))
                        <form method="post" action="/admin/loan-installments/{{$installment->id}}/fine" class="row" style="gap:4px">@csrf
                            <input name="penalty_amount" type="number" min="0" step=".01" value="{{$installment->penalty_due ?: ($settings['default_overdue_fine_amount']??0)}}" style="width:75px;padding:3px;font-size:11px" required>
                            <input name="note" value="{{$installment->penalty_note}}" placeholder="Fine note" style="width:110px;padding:3px;font-size:11px">
                            <button class="button" style="padding:3px 7px;font-size:11px">Save</button>
                        </form>
                        @else <span class="muted" style="font-size:11px">Normal</span> @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" style="text-align:center;padding:16px;">No scheduled or due installments found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- ================= LOAN SUBSECTION 6: SINGLE LOAN EMI PAY ================= -->
    <section id="loan-emi-pay" class="card section" style="padding: 24px; max-width: 900px; margin: 0 auto;">
        <div style="border-left: 3px solid #0284c7; padding-left: 10px; margin-bottom: 18px;">
            <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 4px;">Single Loan EMI Payment Counter</h2>
            <span class="muted" style="font-size: 12px;">Direct counter EMI collection with automatic principal/interest allocation and receipt generation</span>
        </div>

        @if($can('loan.repayment'))
        <div style="background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <form method="post" action="" id="counter-emi-form" onsubmit="event.preventDefault(); const loanId = document.getElementById('emi-loan-select').value; if(!loanId){alert('Please select an active disbursed loan'); return false;} this.action='/admin/loans/' + loanId + '/emi-payment'; this.submit();">
                @csrf
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Select Disbursed Loan <span style="color:#dc2626">*</span></label>
                    <select id="emi-loan-select" onchange="onEmiLoanChange()" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                        <option value="">Select active loan...</option>
                        @foreach($loans->where('status', 'disbursed') as $dl)
                        <option value="{{$dl->id}}" data-member="{{$dl->name}} ({{$dl->member_no}})" data-outstanding="{{$dl->outstanding_principal}}">
                            {{$dl->application_no}} — {{$dl->name}} ({{$dl->member_no}}) · Outstanding Principal: &#8377;{{number_format($dl->outstanding_principal, 2)}}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div id="emi-loan-info-bar" style="font-size:13px;margin:10px 0 16px;display:none;color:#0284c7;font-weight:700;background:#f0f9ff;padding:10px 14px;border-radius:6px;border:1px solid #bae6fd;"></div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Collection Amount (&#8377;) <span style="color:#dc2626">*</span></label>
                        <input name="amount" id="emi-collect-amount" type="number" min="1" step=".01" placeholder="Enter collection amount" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-weight: 700;">
                    </div>
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Payment Mode <span style="color:#dc2626">*</span></label>
                        <select name="payment_mode" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="cash">Cash Counter</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="upi">UPI / QR Code</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Bank Account (if Bank / UPI)</label>
                        <select name="bank_account_id" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                            <option value="">Treasury Cash</option>
                            @foreach($bankAccounts as $b)<option value="{{$b->id}}">{{$b->bank_name}} (&#8377;{{number_format($b->current_balance,2)}})</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">UTR / Reference (Optional)</label>
                        <input name="utr" placeholder="Cheque / UTR #" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>

                <button type="submit" class="button" style="background: #0284c7; color: white; padding: 10px 24px; font-size: 13px; font-weight: 800; border-radius: 6px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="bi bi-cash-coin"></i> Collect EMI & Issue Official Receipt
                </button>
            </form>
        </div>
        @endif
    </section>

    <!-- ================= LOAN SUBSECTION 7: EMI DUE REPORT ================= -->
    <section id="loan-emi-due" class="card section" style="padding: 24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:18px;">
            <div style="border-left: 3px solid #dc2626; padding-left: 10px;">
                <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 4px;">EMI Due & Overdue Report</h2>
                <span class="muted" style="font-size: 12px;">Overdue tracking with 1-click WhatsApp, SMS & App Push automated reminders</span>
            </div>
            <span class="badge rejected" style="font-size: 13px; padding: 6px 14px;">
                {{$installments->where('days_overdue', '>', 0)->count()}} Overdue Accounts
            </span>
        </div>

        <div style="border: 1px solid #fecdd3; border-radius: 8px; overflow-x: auto; background: white;">
            <table>
                <thead>
                    <tr style="background: #ffe4e6; color: #9f1239;">
                        <th>Member</th>
                        <th>Loan Number</th>
                        <th>EMI Number</th>
                        <th>Due Date</th>
                        <th>Days Overdue</th>
                        <th>Penalty</th>
                        <th>Total Outstanding</th>
                        <th>Reminder Options</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($overdueInstallments as $ov)
                @php
                    $cleanPhone = preg_replace('/[^0-9]/', '', $ov->phone);
                    $waText = urlencode("Dear {$ov->name}, your EMI #{$ov->installment_no} for loan {$ov->application_no} of Rs. " . number_format($ov->total_outstanding, 2) . " was due on {$ov->due_on} and is {$ov->days_overdue} days overdue. Please pay at the earliest. - Mahamilon Society");
                    $smsText = urlencode("Dear {$ov->name}, EMI #{$ov->installment_no} for loan {$ov->application_no} (Rs. " . number_format($ov->total_outstanding, 2) . ") is {$ov->days_overdue} days overdue. - Mahamilon Society");
                @endphp
                <tr>
                    <td><b>{{$ov->name}}</b><br><small class="muted">{{$ov->member_no}} · {{$ov->phone}}</small></td>
                    <td><b>{{$ov->application_no}}</b></td>
                    <td><b>#{{$ov->installment_no}}</b></td>
                    <td>{{$ov->due_on}}</td>
                    <td><span class="badge rejected" style="font-size:12px">⚠️ {{$ov->days_overdue}} Days Overdue</span></td>
                    <td><b>&#8377;{{number_format($ov->penalty_due, 2)}}</b></td>
                    <td><b style="color:var(--red);font-size:14px">&#8377;{{number_format($ov->total_outstanding, 2)}}</b></td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <a class="button" style="padding:4px 8px;font-size:11px;background:#25D366;color:white;text-decoration:none" href="https://wa.me/91{{$cleanPhone}}?text={{$waText}}" target="_blank">
                                🟢 WhatsApp
                            </a>
                            <a class="button secondary" style="padding:4px 8px;font-size:11px;text-decoration:none" href="sms:{{$ov->phone}}?body={{$smsText}}">
                                📱 SMS
                            </a>
                            <form method="post" action="/admin/loan-installments/{{$ov->id}}/remind" style="display:inline">@csrf
                                <button class="button" style="padding:4px 8px;font-size:11px;background:#0284c7;color:white;">🔔 Push</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:20px;color:#047857">✓ No overdue loan installments at this time. All loans are current!</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- ================= LOAN SUBSECTION 8: COLLECTION REPORT ================= -->
    <section id="loan-collection-report" class="card section" style="padding: 24px;">
        <div style="border-left: 3px solid #0284c7; padding-left: 10px; margin-bottom: 18px;">
            <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 4px;">Loan Collection & Repayments Report</h2>
            <span class="muted" style="font-size: 12px;">Audit of received borrower installments, online QR transactions, and verification decisions</span>
        </div>

        <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow-x: auto; background: white;">
            <table>
                <thead>
                    <tr style="background: #0284c7; color: white;">
                        <th style="color: white;">Member / Loan</th>
                        <th style="color: white;">Reference</th>
                        <th style="color: white;">Amount</th>
                        <th style="color: white;">UTR / Mode</th>
                        <th style="color: white;">Status</th>
                        <th style="color: white;">Decision</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($repayments as $repayment)
                <tr>
                    <td><b>{{$repayment->name}}</b><br><small class="muted">{{$repayment->application_no}}</small></td>
                    <td>{{$repayment->reference}}</td>
                    <td><b>&#8377;{{number_format($repayment->amount,2)}}</b></td>
                    <td>{{$repayment->utr ?: 'Counter / Cash'}}</td>
                    <td><span class="badge {{$repayment->status}}">{{ucfirst($repayment->status)}}</span></td>
                    <td>
                        @if($can('loan.repayment') && $repayment->status==='submitted')
                        <div style="display:flex;gap:4px;">
                            <form method="post" action="/admin/loan-repayments/{{$repayment->id}}/decision" style="display:inline">@csrf
                                <input type="hidden" name="decision" value="accepted">
                                <button class="button success" style="padding:4px 8px;font-size:11px;">✓ Accept</button>
                            </form>
                            <form method="post" action="/admin/loan-repayments/{{$repayment->id}}/decision" style="display:inline">@csrf
                                <input type="hidden" name="decision" value="rejected">
                                <button class="button danger" style="padding:4px 8px;font-size:11px;">✗ Reject</button>
                            </form>
                        </div>
                        @else
                            <span class="muted" style="font-size:11px">Processed / Verified</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:16px;">No online loan repayments pending review.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- ================= LOAN SUBSECTION 9: LOAN CLOSER REQUEST ================= -->
    <section id="loan-closer-request" class="card section" style="padding: 24px; max-width: 900px; margin: 0 auto;">
        <div style="border-left: 3px solid #0284c7; padding-left: 10px; margin-bottom: 18px;">
            <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 4px;">Loan Closer / Foreclosure Request</h2>
            <span class="muted" style="font-size: 12px;">Process early loan settlement, calculate final payoff amount, and issue No Objection Certificate (NOC)</span>
        </div>

        <div style="background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Select Loan for Closer Request <span style="color:#dc2626">*</span></label>
                <select id="loan-closer-select" onchange="onCloserLoanChange()" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                    <option value="">Select an active loan...</option>
                    @foreach($loans->where('status', 'disbursed') as $dl)
                    <option value="{{$dl->id}}" data-outstanding="{{$dl->outstanding_principal}}" data-name="{{$dl->name}}" data-no="{{$dl->application_no}}">
                        {{$dl->application_no}} — {{$dl->name}} · Outstanding Principal: &#8377;{{number_format($dl->outstanding_principal, 2)}}
                    </option>
                    @endforeach
                </select>
            </div>

            <div id="closer-preview-box" style="display:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:14px;margin-bottom:18px;">
                <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:14px;font-size:12.5px;">
                    <div><span class="muted">Borrower:</span> <b id="closer-borrower-name">-</b></div>
                    <div><span class="muted">Loan App:</span> <b id="closer-app-no">-</b></div>
                    <div><span class="muted">Principal Outstanding:</span> <b id="closer-principal-amt" style="color:#dc2626;">-</b></div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Settlement Payoff Amount (&#8377;)</label>
                    <input type="number" id="closer-payoff-amt" step=".01" placeholder="Calculated payoff" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-weight: 700;">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Settlement Mode</label>
                    <select style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: white;">
                        <option value="cash">Cash Counter</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="savings_adjustment">Adjust from Active Savings Balance</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Closure Remarks / Reason</label>
                <input placeholder="e.g. Borrower requested full prepayment / Term completed" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
            </div>

            <button type="button" onclick="alert('Loan Closer Request submitted to Managing Committee for NOC generation!')" class="button" style="background: #0284c7; color: white; padding: 10px 24px; font-size: 13px; font-weight: 800; border-radius: 6px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                <i class="bi bi-door-closed"></i> Submit Loan Closer Request
            </button>
        </div>
    </section>

    <!-- ================= LOAN SUBSECTION 10: LOAN CLOSER REPORT ================= -->
    <section id="loan-closer-report" class="card section" style="padding: 24px;">
        <div style="border-left: 3px solid #0284c7; padding-left: 10px; margin-bottom: 18px;">
            <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 4px;">Loan Closer Report & Settlement Archive</h2>
            <span class="muted" style="font-size: 12px;">Directory of fully settled, closed, and matured loan accounts with NOC certificates</span>
        </div>

        <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow-x: auto; background: white;">
            <table>
                <thead>
                    <tr style="background: #0284c7; color: white;">
                        <th style="color: white;">Loan No</th>
                        <th style="color: white;">Member Name</th>
                        <th style="color: white;">Sanctioned Principal</th>
                        <th style="color: white;">Total Recovered</th>
                        <th style="color: white;">Status</th>
                        <th style="color: white;">NOC Certificate</th>
                    </tr>
                </thead>
                <tbody>
                    @php $closedLoans = $loans->whereIn('status', ['closed', 'settled', 'completed']); @endphp
                    @forelse($closedLoans as $cl)
                    <tr>
                        <td><b>{{$cl->application_no}}</b></td>
                        <td><b>{{$cl->name}}</b> ({{$cl->member_no}})</td>
                        <td>&#8377;{{number_format($cl->sanctioned_amount ?: $cl->amount, 2)}}</td>
                        <td><b style="color:var(--green)">&#8377;{{number_format($cl->amount, 2)}}</b></td>
                        <td><span class="badge active">Closed / Settled</span></td>
                        <td>
                            <button type="button" class="button secondary" style="padding: 3px 8px; font-size: 11px;">
                                <i class="bi bi-file-earmark-check"></i> Print NOC
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:24px;color:#64748b;">
                            <i class="bi bi-info-circle" style="font-size:18px;display:block;margin-bottom:6px;"></i>
                            All active loans are currently undergoing scheduled EMI collections. No loans currently marked as closed.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Loan JavaScript Helpers -->
    <script>
        function onLoanTypeChange() {
            const type = document.getElementById('loan-apply-type')?.value;
            const rateInput = document.getElementById('loan-detail-rate');
            const schemeSel = document.getElementById('loan-apply-scheme');
            if (rateInput) {
                if (type === 'business') {
                    rateInput.value = '14.00% p.a.';
                    if (schemeSel) schemeSel.value = 'business';
                } else if (type === 'emergency') {
                    rateInput.value = '10.00% p.a.';
                    if (schemeSel) schemeSel.value = 'emergency';
                } else {
                    rateInput.value = '12.00% p.a.';
                    if (schemeSel) schemeSel.value = 'standard';
                }
            }
            if (typeof updateLoanApplyLimits === 'function') updateLoanApplyLimits();
        }

        function onLoanSchemeChange() {
            const scheme = document.getElementById('loan-apply-scheme')?.value;
            const typeSel = document.getElementById('loan-apply-type');
            if (typeSel && scheme) {
                if (scheme === 'business') typeSel.value = 'business';
                else if (scheme === 'emergency') typeSel.value = 'emergency';
                else typeSel.value = 'personal';
                onLoanTypeChange();
            }
        }

        function onCloserLoanChange() {
            const sel = document.getElementById('loan-closer-select');
            const box = document.getElementById('closer-preview-box');
            if (!sel || !box) return;
            const opt = sel.options[sel.selectedIndex];
            if (!opt || !opt.value) { box.style.display = 'none'; return; }
            box.style.display = 'block';
            document.getElementById('closer-borrower-name').textContent = opt.dataset.name || '-';
            document.getElementById('closer-app-no').textContent = opt.dataset.no || '-';
            const bal = parseFloat(opt.dataset.outstanding) || 0;
            document.getElementById('closer-principal-amt').textContent = '₹' + bal.toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('closer-payoff-amt').value = bal.toFixed(2);
        }
    </script>


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
            <div class="field"><label>Default overdue fine (&#8377;)</label><input name="default_overdue_fine_amount" type="number" min="0" step=".01" value="{{$settings['default_overdue_fine_amount']??0}}" required></div>
            <button>Save finance settings</button>
        </form>
    </section>
    @endif

    <section id="security" class="card section">
        <h2>Security and recent audit</h2>
        <form method="post" action="/admin/password" class="row">@csrf<div class="field"><label>Current password</label><input name="current_password" type="password" required></div><div class="field"><label>New password (12+)</label><input name="password" type="password" minlength="12" required></div><div class="field"><label>Confirm password</label><input name="password_confirmation" type="password" minlength="12" required></div><button>Change password</button></form>
        @if($can('backup.manage'))
        <div style="background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:16px;margin:20px 0">
            <h3 style="margin-top:0">System Database Backup (Section 27)</h3>
            <p class="muted">Generate and securely download a complete SQL dump of the live Mahamilon Society database, including all members, ledger accounts, cash transactions, and receipts.</p>
            <a href="/admin/database/backup" class="button" style="background:var(--navy);color:white;font-weight:750;padding:10px 18px;display:inline-flex;align-items:center;gap:6px"><span>📥</span> Download Database Backup (.sql)</a>
        </div>
        @endif
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
        if (requestedId === 'members') requestedId = 'customer-report';
        if (requestedId === 'loans') requestedId = 'loan-apply';
        const groupMapping = {
            'customer-create': { sub: 'customer-sub-items', chev: 'customer-chevron' },
            'customer-search': { sub: 'customer-sub-items', chev: 'customer-chevron' },
            'customer-kyc': { sub: 'customer-sub-items', chev: 'customer-chevron' },
            'customer-report': { sub: 'customer-sub-items', chev: 'customer-chevron' },
            'customer-nominee': { sub: 'customer-sub-items', chev: 'customer-chevron' },

            'accounts': { sub: 'accounts-sub-items', chev: 'accounts-chevron' },
            'deposits': { sub: 'accounts-sub-items', chev: 'accounts-chevron' },
            'fd-rd': { sub: 'accounts-sub-items', chev: 'accounts-chevron' },
            'closures': { sub: 'accounts-sub-items', chev: 'accounts-chevron' },

            'transactions': { sub: 'transactions-sub-items', chev: 'transactions-chevron' },
            'payments': { sub: 'transactions-sub-items', chev: 'transactions-chevron' },
            'receipts': { sub: 'transactions-sub-items', chev: 'transactions-chevron' },
            'statements': { sub: 'transactions-sub-items', chev: 'transactions-chevron' },

            'loans': { sub: 'loans-sub-items', chev: 'loans-chevron' },
            'loan-apply': { sub: 'loans-sub-items', chev: 'loans-chevron' },
            'loan-fixed': { sub: 'loans-sub-items', chev: 'loans-chevron' },
            'loan-kyc': { sub: 'loans-sub-items', chev: 'loans-chevron' },
            'loan-report': { sub: 'loans-sub-items', chev: 'loans-chevron' },
            'loan-statement': { sub: 'loans-sub-items', chev: 'loans-chevron' },
            'loan-emi-pay': { sub: 'loans-sub-items', chev: 'loans-chevron' },
            'loan-emi-due': { sub: 'loans-sub-items', chev: 'loans-chevron' },
            'loan-collection-report': { sub: 'loans-sub-items', chev: 'loans-chevron' },
            'loan-closer-request': { sub: 'loans-sub-items', chev: 'loans-chevron' },
            'loan-closer-report': { sub: 'loans-sub-items', chev: 'loans-chevron' },

            'cash': { sub: 'finance-sub-items', chev: 'finance-chevron' },
            'banks': { sub: 'finance-sub-items', chev: 'finance-chevron' },
            'expenses': { sub: 'finance-sub-items', chev: 'finance-chevron' },

            'staff': { sub: 'system-sub-items', chev: 'system-chevron' },
            'settings': { sub: 'system-sub-items', chev: 'system-chevron' },
            'security': { sub: 'system-sub-items', chev: 'system-chevron' },
        };

        if (requestedId && groupMapping[requestedId]) {
            const group = groupMapping[requestedId];
            const subItems = document.getElementById(group.sub);
            const chev = document.getElementById(group.chev);
            if (subItems) subItems.style.display = 'flex';
            if (chev) chev.innerHTML = '<i class="bi bi-chevron-down"></i>';
        }
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
        loanOfferLimit.textContent = `Maximum &#8377;${maximum.toLocaleString('en-IN', {minimumFractionDigits: 2})} (85% of &#8377;${savings.toLocaleString('en-IN', {minimumFractionDigits: 2})} active savings).`;
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
<script>
    function toggleNavGroup(subItemsId, chevronId, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const subItems = document.getElementById(subItemsId);
        const chevron = document.getElementById(chevronId);
        if (subItems) {
            const isCurrentlyHidden = subItems.style.display === 'none' || window.getComputedStyle(subItems).display === 'none';
            subItems.style.display = isCurrentlyHidden ? 'flex' : 'none';
            if (chevron) {
                chevron.innerHTML = isCurrentlyHidden ? '<i class="bi bi-chevron-down"></i>' : '<i class="bi bi-chevron-right"></i>';
            }
        }
    }

    function toggleCustomerMenu(e) {
        toggleNavGroup('customer-sub-items', 'customer-chevron', e);
    }

    function filterKycMembers(query) {
        const q = (query || '').toLowerCase().trim();
        const select = document.getElementById('kycFilterUserSelect');
        const clearBtn = document.getElementById('kycClearBtn');
        const statusText = document.getElementById('kycSearchStatus');
        if (clearBtn) clearBtn.style.display = q ? 'inline-block' : 'none';

        if (select) {
            const options = select.querySelectorAll('option');
            let matchCount = 0;
            let firstMatchVal = '';
            options.forEach((opt, idx) => {
                if (idx === 0) return;
                const memNo = (opt.getAttribute('data-member-no') || '').toLowerCase();
                const name = (opt.getAttribute('data-name') || '').toLowerCase();
                const text = opt.textContent.toLowerCase();
                const matches = !q || memNo.includes(q) || name.includes(q) || text.includes(q);
                opt.style.display = matches ? '' : 'none';
                if (matches) {
                    matchCount++;
                    if (!firstMatchVal) firstMatchVal = opt.value;
                }
            });
            if (q) {
                if (statusText) statusText.textContent = 'Found ' + matchCount + ' matching customer(s)';
                if (matchCount === 1) {
                    select.value = firstMatchVal;
                    syncKycUser(firstMatchVal);
                }
            } else {
                if (statusText) statusText.textContent = 'Type Member ID or Name to filter live';
            }
        }
        filterKycDocumentTable(q, select ? select.value : '');
    }

    function searchKycMembers() {
        const q = (document.getElementById('kycMemberSearchInput')?.value || '').toLowerCase().trim();
        filterKycMembers(q);
        const select = document.getElementById('kycFilterUserSelect');
        if (select && select.value) {
            syncKycUser(select.value);
        }
    }

    function clearKycSearch() {
        const input = document.getElementById('kycMemberSearchInput');
        if (input) input.value = '';
        const select = document.getElementById('kycFilterUserSelect');
        if (select) select.value = '';
        filterKycMembers('');
        syncKycUser('');
    }

    function filterKycDocumentTable(searchQuery, selectedMemberId) {
        const rows = document.querySelectorAll('#customer-kyc table tbody tr.kyc-doc-row');
        let visibleCount = 0;
        rows.forEach(row => {
            const memId = row.getAttribute('data-member-id');
            const memNo = (row.getAttribute('data-member-no') || '').toLowerCase();
            const memName = (row.getAttribute('data-member-name') || '').toLowerCase();
            const text = row.textContent.toLowerCase();

            const matchesMember = !selectedMemberId || memId === selectedMemberId;
            const matchesQuery = !searchQuery || memNo.includes(searchQuery) || memName.includes(searchQuery) || text.includes(searchQuery);

            const show = matchesMember && matchesQuery;
            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
        const countInfo = document.getElementById('kycDocCountInfo');
        if (countInfo) countInfo.textContent = 'Showing ' + visibleCount + ' document(s)';
    }

    function syncKycUser(memberId) {
        const target = document.getElementById('kycTargetMemberSelect');
        if (target && memberId) target.value = memberId;
        const q = (document.getElementById('kycMemberSearchInput')?.value || '').toLowerCase().trim();
        filterKycDocumentTable(q, memberId);
    }

    function openEditMemberModal(m) {
        let data = {};
        if (m instanceof HTMLElement) {
            if (m.getAttribute('data-member')) {
                try {
                    let raw = m.getAttribute('data-member');
                    raw = raw.replace(/&quot;/g, '"').replace(/&amp;/g, '&');
                    data = JSON.parse(raw);
                } catch(e) {
                    console.error('Failed to parse member data:', e);
                }
            } else if (m.dataset.id) {
                data = {
                    id: m.dataset.id,
                    member_no: m.dataset.memberNo || '',
                    name: m.dataset.name || '',
                    phone: m.dataset.phone || '',
                    email: m.dataset.email || '',
                    status: m.dataset.status || 'active',
                    kyc_status: m.dataset.kycStatus || 'not_submitted',
                    created_at: m.dataset.joiningDate || '',
                    date_of_birth: m.dataset.dob || '',
                    aadhaar_last4: m.dataset.aadhaar || '',
                    pan_last4: m.dataset.pan || '',
                    aadhaar_no: m.dataset.aadhaar || '',
                    pan_no: m.dataset.pan || '',
                    nominee_name: m.dataset.nominee || '',
                    occupation: m.dataset.occupation || '',
                    mcs_score: m.dataset.score || 500,
                    address: m.dataset.address || ''
                };
            }
        } else if (typeof m === 'string') {
            try {
                let raw = m.replace(/&quot;/g, '"').replace(/&amp;/g, '&');
                data = JSON.parse(raw);
            } catch(e) {}
        } else if (typeof m === 'object' && m !== null) {
            data = m;
        }

        if (!data || !data.id) return;
        const modal = document.getElementById('editCustomerModal');
        if (!modal) return;
        
        const titleEl = document.getElementById('editCustomerModalTitle');
        if (titleEl) titleEl.textContent = 'Edit Customer: ' + (data.member_no || '') + ' (' + (data.name || '') + ')';
        
        const formEl = document.getElementById('editMemberForm');
        if (formEl) formEl.action = '/admin/members/' + data.id + '/update';
        
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val !== undefined && val !== null ? val : '';
        };

        // Section 1: Account
        setVal('editMemberNo', data.member_no);
        setVal('editMemberName', data.name);
        setVal('editMemberStatus', data.user_status || data.status || 'active');
        setVal('editMemberKycStatus', data.kyc_status || 'not_submitted');
        setVal('editMemberJoiningDate', data.created_at ? data.created_at.substring(0, 10) : '');

        // Section 2: Parents & Personal
        setVal('editParentName', data.parent_name);
        setVal('editMotherName', data.mother_name);
        setVal('editFatherName', data.father_name);
        setVal('editMemberDob', data.date_of_birth ? data.date_of_birth.substring(0, 10) : '');
        setVal('editMemberAge', data.age);
        setVal('editBloodGroup', data.blood_group);
        setVal('editQualification', data.qualification);
        setVal('editMaritalStatus', data.marital_status || 'Married');

        // Section 3: Contact
        setVal('editMemberPhone', data.phone);
        setVal('editMemberEmail', data.email);
        setVal('editMemberMpin', '');
        setVal('editMemberAddress', data.address);
        setVal('editPresentAddress', data.present_address);
        setVal('editPincode', data.pincode);

        // Section 4: Banking
        setVal('editBankName', data.bank_name);
        setVal('editBankAccountNo', data.bank_account_no);
        setVal('editBankIfsc', data.bank_ifsc);
        setVal('editBankBranch', data.bank_branch);
        setVal('editBank2Name', data.bank2_name);
        setVal('editBank2AccountNo', data.bank2_account_no);
        setVal('editBank2Ifsc', data.bank2_ifsc);
        setVal('editBank2Branch', data.bank2_branch);

        // Section 5: Employment & Tax
        setVal('editMemberOccupation', data.occupation);
        setVal('editOfficeAddress', data.office_address);
        setVal('editDesignationSalary', data.designation_salary);
        setVal('editMemberAadhaar', data.aadhaar_no || data.aadhaar || data.aadhaar_last4);
        setVal('editMemberPan', data.pan_no || data.pan || data.pan_last4);
        setVal('editMemberScore', data.mcs_score || 500);
        setVal('editRelativeMemberDetails', data.relative_member_details);

        // Section 6: Nominee
        setVal('editNomineeName', data.nominee_name);
        setVal('editNomineeParent', data.nominee_parent);
        setVal('editNomineeRelation', data.nominee_relation);
        setVal('editNomineeAge', data.nominee_age);
        setVal('editNomineeOccupation', data.nominee_occupation);
        setVal('editNomineeAddress', data.nominee_address);

        // Section 7: Introducers
        setVal('editIntro1Name', data.intro1_name);
        setVal('editIntro1Parent', data.intro1_parent);
        setVal('editIntro1Account', data.intro1_account);
        setVal('editIntro1Lf', data.intro1_lf);
        setVal('editIntro1Address', data.intro1_address);
        setVal('editIntro2Name', data.intro2_name);
        setVal('editIntro2Parent', data.intro2_parent);
        setVal('editIntro2Account', data.intro2_account);
        setVal('editIntro2Lf', data.intro2_lf);
        setVal('editIntro2Address', data.intro2_address);

        modal.style.display = 'flex';
        modal.style.zIndex = '99999';
    }

    function closeEditMemberModal() {
        const modal = document.getElementById('editCustomerModal');
        if (modal) modal.style.display = 'none';
    }

    function filterAccountsTable(query) {
        const q = (query || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#accountsTableBody tr.account-row');
        const clearBtn = document.getElementById('accountClearBtn');
        if (clearBtn) clearBtn.style.display = q ? 'inline-block' : 'none';
        let count = 0;
        rows.forEach(r => {
            const text = (r.getAttribute('data-search') || r.textContent).toLowerCase();
            const matches = !q || text.includes(q);
            r.style.display = matches ? '' : 'none';
            if (matches) count++;
        });
        const info = document.getElementById('accountSearchCountInfo');
        if (info) info.textContent = 'Showing ' + count + ' account(s)';
    }

    function clearAccountSearch() {
        const input = document.getElementById('accountSearchInput');
        if (input) input.value = '';
        filterAccountsTable('');
    }

    function filterDepositAccountSelect(query) {
        const q = (query || '').toLowerCase().trim();
        const sel = document.getElementById('depositTargetAccountSelect');
        if (!sel) return;
        for (let i = 0; i < sel.options.length; i++) {
            const opt = sel.options[i];
            if (!opt.value) continue;
            const searchAttr = (opt.getAttribute('data-search') || opt.textContent).toLowerCase();
            opt.style.display = (!q || searchAttr.includes(q)) ? '' : 'none';
        }
    }

    function filterRdInstallments(query) {
        const q = (query || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#rdInstallmentsTableBody tr.rd-inst-row');
        rows.forEach(r => {
            const text = (r.getAttribute('data-search') || r.textContent).toLowerCase();
            r.style.display = (!q || text.includes(q)) ? '' : 'none';
        });
    }

    function filterDepositHistoryTable(query) {
        const q = (query || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#depositHistoryTableBody tr.deposit-history-row');
        const clearBtn = document.getElementById('depositHistoryClearBtn');
        if (clearBtn) clearBtn.style.display = q ? 'inline-block' : 'none';
        let count = 0;
        rows.forEach(r => {
            const text = (r.getAttribute('data-search') || r.textContent).toLowerCase();
            const matches = !q || text.includes(q);
            r.style.display = matches ? '' : 'none';
            if (matches) count++;
        });
        const info = document.getElementById('depositHistoryCountInfo');
        if (info) info.textContent = 'Showing ' + count + ' receipt(s)';
    }

    function clearDepositHistorySearch() {
        const input = document.getElementById('depositHistorySearchInput');
        if (input) input.value = '';
        filterDepositHistoryTable('');
    }

    function filterClosureHistoryTable(query) {
        const q = (query || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#closureHistoryTableBody tr.closure-row');
        const clearBtn = document.getElementById('closureClearBtn');
        if (clearBtn) clearBtn.style.display = q ? 'inline-block' : 'none';
        let count = 0;
        rows.forEach(r => {
            const text = (r.getAttribute('data-search') || r.textContent).toLowerCase();
            const matches = !q || text.includes(q);
            r.style.display = matches ? '' : 'none';
            if (matches) count++;
        });
        const info = document.getElementById('closureSearchCountInfo');
        if (info) info.textContent = 'Showing ' + count + ' record(s)';
    }

    function clearClosureSearch() {
        const input = document.getElementById('closureSearchInput');
        if (input) input.value = '';
        filterClosureHistoryTable('');
    }

    function filterSearchCustomerCards(query) {
        const q = (query || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.search-cust-row');
        rows.forEach(r => {
            const data = (r.getAttribute('data-query') || '').toLowerCase();
            r.style.display = (!q || data.includes(q)) ? '' : 'none';
        });
    }

    function filterReportRows() {
        const idVal = (document.getElementById('reportCustIdInput')?.value || '').toLowerCase().trim();
        const genVal = (document.getElementById('reportGeneralInput')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.report-data-row');
        let count = 0;
        rows.forEach(r => {
            const id = (r.getAttribute('data-id') || '').toLowerCase();
            const q = (r.getAttribute('data-query') || '').toLowerCase();
            const matchId = !idVal || id.includes(idVal);
            const matchGen = !genVal || q.includes(genVal);
            const show = matchId && matchGen;
            r.style.display = show ? '' : 'none';
            if (show) count++;
        });
        const info = document.getElementById('reportCountInfo');
        if (info) info.textContent = 'Showing ' + count + ' customer(s)';
    }

    function changeReportRows(val) {
        const rows = document.querySelectorAll('.report-data-row');
        const limit = val === 'all' ? rows.length : parseInt(val, 10);
        let count = 0;
        rows.forEach((r, i) => {
            if (i < limit) {
                r.style.display = '';
                count++;
            } else {
                r.style.display = 'none';
            }
        });
        const info = document.getElementById('reportCountInfo');
        if (info) info.textContent = 'Showing ' + count + ' customer(s)';
    }

    function updateClock() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        const el = document.getElementById('liveClock');
        if (el) el.textContent = now.toLocaleString('en-US', options);
    }
    setInterval(updateClock, 1000);
    updateClock();

    function toggleFullScreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }


    function updateRdFormFields(sel) {
        const opt = sel.options[sel.selectedIndex];
        const freq = (opt.getAttribute('data-freq') || 'monthly').toLowerCase();
        const rate = opt.getAttribute('data-rate');
        const isDaily = freq === 'daily';

        const amountLabel = document.getElementById('rd-amount-label');
        const amountInput = document.getElementById('rd-amount-input');
        const amountHint = document.getElementById('rd-amount-hint');
        const tenureLabel = document.getElementById('rd-tenure-label');
        const tenureInput = document.getElementById('rd-tenure-input');
        const tenureHint = document.getElementById('rd-tenure-hint');
        const rateInput = document.getElementById('rd-rate-input');

        if (rate && rateInput) rateInput.value = rate;

        if (isDaily) {
            if (amountLabel) amountLabel.textContent = 'Daily Deposit Amount (₹)';
            if (amountInput) {
                amountInput.min = '10';
                amountInput.value = amountInput.value && parseFloat(amountInput.value) <= 100 ? amountInput.value : '50';
                amountInput.placeholder = '50';
            }
            if (amountHint) amountHint.textContent = 'Enter daily collection amount (e.g. ₹50/day)';
            if (tenureLabel) tenureLabel.textContent = 'Tenure (Days)';
            if (tenureInput) {
                tenureInput.min = '1';
                tenureInput.max = '1000';
                tenureInput.value = tenureInput.value && parseInt(tenureInput.value) > 12 ? tenureInput.value : '100';
            }
            if (tenureHint) tenureHint.textContent = 'Enter tenure in total days (e.g. 100 days, 365 days)';
        } else {
            if (amountLabel) amountLabel.textContent = 'Monthly Deposit Amount (₹)';
            if (amountInput) {
                amountInput.min = '100';
                amountInput.value = amountInput.value && parseFloat(amountInput.value) >= 100 ? amountInput.value : '1000';
                amountInput.placeholder = '1000';
            }
            if (amountHint) amountHint.textContent = 'Enter monthly deposit amount (e.g. ₹1,000/month)';
            if (tenureLabel) tenureLabel.textContent = 'Tenure (Months)';
            if (tenureInput) {
                tenureInput.min = '1';
                tenureInput.max = '120';
                tenureInput.value = tenureInput.value && parseInt(tenureInput.value) <= 120 ? tenureInput.value : '12';
            }
            if (tenureHint) tenureHint.textContent = 'Enter tenure in months (e.g. 12 months)';
        }
    }

    let currentAdvCommitment = 50;
    let currentAdvFreq = 'daily';

    function openAdvancePayModal(accountId, accountNo, memberName, commitment, freq) {
        const modal = document.getElementById('advanceRdPayModal');
        if (!modal) return;
        currentAdvCommitment = parseFloat(commitment) || 50;
        currentAdvFreq = (freq || 'daily').toLowerCase();

        document.getElementById('advanceRdPayForm').action = '/admin/accounts/' + accountId + '/rd-advance-pay';
        document.getElementById('advAccountDisplay').textContent = accountNo + ' (' + memberName + ')';
        document.getElementById('advRateLabel').textContent = currentAdvFreq === 'daily' ? 'Daily Commitment:' : 'Monthly Commitment:';
        document.getElementById('advRateDisplay').innerHTML = '&#8377;' + currentAdvCommitment.toFixed(2) + ' / ' + (currentAdvFreq === 'daily' ? 'day' : 'month');
        document.getElementById('advCountLabel').textContent = currentAdvFreq === 'daily' ? 'Number of Days to Pay in Advance:' : 'Number of Months / Installments to Pay:';

        document.getElementById('advDaysCount').value = 10;
        calculateAdvancePayTotal();
        modal.style.display = 'flex';
    }

    function closeAdvancePayModal() {
        const modal = document.getElementById('advanceRdPayModal');
        if (modal) modal.style.display = 'none';
    }

    function setAdvDays(n) {
        document.getElementById('advDaysCount').value = n;
        calculateAdvancePayTotal();
    }

    function calculateAdvancePayTotal() {
        const count = parseInt(document.getElementById('advDaysCount').value, 10) || 0;
        const total = count * currentAdvCommitment;
        document.getElementById('advTotalDisplay').innerHTML = '&#8377;' + total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function toggleAdvBankSelect(mode) {
        const field = document.getElementById('advBankField');
        if (field) field.style.display = (mode === 'bank' || mode === 'upi') ? 'block' : 'none';
    }

    let allScheduleItems = [];

    function openRdScheduleModal(accountId) {
        const modal = document.getElementById('rdScheduleModal');
        if (!modal) return;
        modal.style.display = 'flex';

        const tbody = document.getElementById('schedTableBody');
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 25px; color: #64748b;"><i class="bi bi-hourglass-split"></i> Loading RD schedule from database...</td></tr>';

        fetch('/admin/accounts/' + accountId + '/rd-schedule')
            .then(res => res.json())
            .then(data => {
                const acc = data.account;
                const summ = data.summary;
                allScheduleItems = data.installments;

                document.getElementById('schedModalTitle').textContent = 'Passbook & Schedule: ' + acc.account_no + ' (' + acc.member_name + ')';
                document.getElementById('schedModalSubtitle').textContent = acc.scheme_name + ' • Rate: ' + (acc.custom_interest_rate || acc.default_rate) + '% p.a. • Matures: ' + (acc.matures_on || 'N/A');

                const percent = summ.total_count > 0 ? Math.round((summ.paid_count / summ.total_count) * 100) : 0;
                const unit = (summ.frequency || '').toLowerCase() === 'daily' ? 'days' : 'installments';

                document.getElementById('schedSummaryBox').innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                        <span style="font-size: 13px; font-weight: 800; color: #0369a1;">Payment Progress: ${summ.paid_count} of ${summ.total_count} ${unit} completed (${percent}%)</span>
                        <span style="font-size: 12px; color: #64748b;">Next Due: ${summ.next_installment ? '#' + summ.next_installment.installment_no + ' on ' + summ.next_installment.due_on : 'Fully Settled'}</span>
                    </div>
                    <div style="background: #e2e8f0; border-radius: 999px; height: 10px; overflow: hidden; margin-bottom: 12px;">
                        <div style="background: linear-gradient(90deg, #0284c7, #10b981); height: 100%; width: ${percent}%; border-radius: 999px; transition: width 0.3s ease;"></div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; text-align: center;">
                        <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 10px;">
                            <div style="font-size: 10.5px; color: #64748b; font-weight: 700; text-transform: uppercase;">Commitment</div>
                            <b style="font-size: 14px; color: #0f172a;">&#8377;${summ.commitment.toFixed(2)}/${(summ.frequency || '').toLowerCase() === 'daily' ? 'day' : 'mo'}</b>
                        </div>
                        <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 10px;">
                            <div style="font-size: 10.5px; color: #64748b; font-weight: 700; text-transform: uppercase;">Current Balance</div>
                            <b style="font-size: 14px; color: #059669;">&#8377;${summ.current_balance.toLocaleString('en-IN', {minimumFractionDigits: 2})}</b>
                        </div>
                        <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 10px;">
                            <div style="font-size: 10.5px; color: #64748b; font-weight: 700; text-transform: uppercase;">Remaining</div>
                            <b style="font-size: 14px; color: #d97706;">${summ.remaining_count} ${unit}</b>
                        </div>
                        <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 10px;">
                            <div style="font-size: 10.5px; color: #64748b; font-weight: 700; text-transform: uppercase;">Maturity Target</div>
                            <b style="font-size: 14px; color: #0284c7;">&#8377;${summ.target_amount.toLocaleString('en-IN', {minimumFractionDigits: 2})}</b>
                        </div>
                    </div>
                `;

                document.getElementById('countAll').textContent = summ.total_count;
                document.getElementById('countPaid').textContent = summ.paid_count;
                document.getElementById('countDue').textContent = summ.due_count;
                document.getElementById('countScheduled').textContent = summ.total_count - summ.paid_count - summ.due_count;

                const quickBtnWrap = document.getElementById('schedQuickPayBtnWrap');
                if (summ.remaining_count > 0) {
                    quickBtnWrap.innerHTML = `
                        <button type="button" onclick="closeRdScheduleModal(); openAdvancePayModal(${acc.id}, '${acc.account_no}', '${acc.member_name.replace(/'/g, "\\'")}', ${acc.monthly_commitment}, '${acc.deposit_frequency}');" class="button success" style="padding: 4px 12px; font-size: 11.5px; font-weight: 700;">
                            <i class="bi bi-cash-stack"></i> Pay Advance Days
                        </button>
                    `;
                } else {
                    quickBtnWrap.innerHTML = '<span class="badge active" style="font-size: 11px;">Matured / All Paid</span>';
                }

                renderScheduleTable(allScheduleItems);
            })
            .catch(err => {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #ef4444;">Failed to load schedule. ' + err.message + '</td></tr>';
            });
    }

    function closeRdScheduleModal() {
        const modal = document.getElementById('rdScheduleModal');
        if (modal) modal.style.display = 'none';
    }

    function filterScheduleStatus(status, btn) {
        document.querySelectorAll('.sched-tab').forEach(b => {
            b.style.background = '';
            b.style.color = '';
            b.classList.remove('active');
        });
        if (btn) {
            btn.style.background = '#0284c7';
            btn.style.color = 'white';
            btn.classList.add('active');
        }

        if (status === 'all') {
            renderScheduleTable(allScheduleItems);
        } else if (status === 'paid') {
            renderScheduleTable(allScheduleItems.filter(i => i.status === 'paid'));
        } else if (status === 'due') {
            renderScheduleTable(allScheduleItems.filter(i => i.status === 'due' || i.status === 'overdue'));
        } else if (status === 'scheduled') {
            renderScheduleTable(allScheduleItems.filter(i => i.status === 'scheduled'));
        }
    }

    function renderScheduleTable(items) {
        const tbody = document.getElementById('schedTableBody');
        if (!items || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 18px; color: #64748b;">No installments found matching this filter.</td></tr>';
            return;
        }

        const csrfToken = document.querySelector('input[name="_token"]')?.value || '';

        tbody.innerHTML = items.map(inst => {
            let badgeStyle = 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;';
            if (inst.status === 'paid') {
                badgeStyle = 'background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-weight: 700;';
            } else if (inst.status === 'overdue') {
                badgeStyle = 'background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; font-weight: 700;';
            } else if (inst.status === 'due') {
                badgeStyle = 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 700;';
            }

            const paidDateStr = inst.paid_at ? inst.paid_at.substring(0, 10) : '-';
            const actionCol = inst.status === 'paid'
                ? `<span style="color: #059669; font-weight: 700;"><i class="bi bi-check-circle-fill"></i> Settled (${paidDateStr})</span>`
                : `<form method="post" action="/admin/rd-installments/${inst.id}/pay" style="display:inline; margin:0;" onsubmit="return confirm('Collect single installment #${inst.installment_no} (₹${parseFloat(inst.amount_due).toFixed(2)})?');">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="payment_mode" value="cash">
                    <button class="button success" style="padding: 2px 7px; font-size: 11px;">Collect Now</button>
                   </form>`;

            return `
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 6px 10px; font-weight: 700;">#${inst.installment_no}</td>
                    <td style="padding: 6px 10px;">${inst.due_on}</td>
                    <td style="padding: 6px 10px; font-weight: 600;">&#8377;${parseFloat(inst.amount_due).toFixed(2)}</td>
                    <td style="padding: 6px 10px; color: ${parseFloat(inst.penalty_due) > 0 ? '#b91c1c' : '#64748b'};">&#8377;${parseFloat(inst.penalty_due).toFixed(2)}</td>
                    <td style="padding: 6px 10px; font-weight: 700; color: #059669;">&#8377;${parseFloat(inst.paid_amount).toFixed(2)}</td>
                    <td style="padding: 6px 10px;"><span class="badge" style="${badgeStyle}; padding: 2px 6px; font-size: 10px; text-transform: uppercase;">${inst.status}</span></td>
                    <td style="padding: 6px 10px;">${actionCol}</td>
                </tr>
            `;
        }).join('');
    }

</script>

    <!-- ================= ADVANCE RD PAYMENT MODAL ================= -->
    <div id="advanceRdPayModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(2px);">
        <div style="background: white; border-radius: 12px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); border: 1px solid #e2e8f0;">
            <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f0fdf4; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <div>
                    <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #166534;" id="advModalTitle">Collect RD Advance Installments</h3>
                    <span class="muted" style="font-size: 11px;">Pay consecutive days or months in one single transaction with instant receipt</span>
                </div>
                <button type="button" onclick="closeAdvancePayModal()" style="background: none; border: none; font-size: 22px; color: #64748b; cursor: pointer; line-height: 1;">&times;</button>
            </div>

            <form id="advanceRdPayForm" method="post" action="" style="padding: 20px;">
                @csrf
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span class="muted" style="font-size: 12px;">Account / Member:</span>
                        <b id="advAccountDisplay" style="font-size: 13px; color: #0f172a;">-</b>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span class="muted" style="font-size: 12px;" id="advRateLabel">Daily Rate:</span>
                        <b id="advRateDisplay" style="font-size: 13px; color: #0284c7;">&#8377;0.00</b>
                    </div>
                </div>

                <div class="field" style="margin-bottom: 14px;">
                    <label style="font-weight: 700; color: #0f172a;" id="advCountLabel">Number of Days / Installments to Pay</label>
                    <div style="display: flex; gap: 8px; align-items: center; margin-top: 4px;">
                        <input type="number" id="advDaysCount" name="days_count" value="10" min="1" max="365" required oninput="calculateAdvancePayTotal()" style="padding: 8px 12px; font-size: 15px; font-weight: 700; border: 1.5px solid #cbd5e1; border-radius: 6px; width: 120px;">
                        <div style="display: flex; gap: 4px;">
                            <button type="button" onclick="setAdvDays(1)" class="button" style="padding: 4px 8px; font-size: 11px;">1 Day</button>
                            <button type="button" onclick="setAdvDays(5)" class="button" style="padding: 4px 8px; font-size: 11px;">5 Days</button>
                            <button type="button" onclick="setAdvDays(10)" class="button" style="padding: 4px 8px; font-size: 11px; background: #0284c7; color: white;">10 Days</button>
                            <button type="button" onclick="setAdvDays(30)" class="button" style="padding: 4px 8px; font-size: 11px;">30 Days</button>
                        </div>
                    </div>
                    <small class="muted" style="font-size: 11px; margin-top: 4px; display: block;">e.g. Enter 10 to pay next 10 consecutive daily installments in sequence</small>
                </div>

                <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 14px; text-align: center; margin-bottom: 16px;">
                    <span style="font-size: 12px; font-weight: 700; color: #065f46; text-transform: uppercase;">Total Collection Amount</span>
                    <div id="advTotalDisplay" style="font-size: 26px; font-weight: 800; color: #059669; margin-top: 2px;">&#8377;500.00</div>
                </div>

                <div class="row" style="margin-bottom: 14px;">
                    <div class="field" style="flex: 1;">
                        <label>Payment Mode</label>
                        <select name="payment_mode" id="advPaymentMode" onchange="toggleAdvBankSelect(this.value)">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                    <div class="field" style="flex: 1;">
                        <label>Payment Date (Back-dating permitted)</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="field" id="advBankField" style="display: none; margin-bottom: 14px;">
                    <label>Deposit To Bank Account</label>
                    <select name="bank_account_id">
                        @foreach($bankAccounts as $b)
                        <option value="{{ $b->id }}">{{ $b->bank_name }} - {{ $b->account_number }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px;">
                    <button type="button" onclick="closeAdvancePayModal()" class="button secondary" style="padding: 8px 16px;">Cancel</button>
                    <button type="submit" class="button success" style="padding: 8px 22px; font-weight: 800; font-size: 13px;">
                        <i class="bi bi-check-circle"></i> Collect Payment & Issue Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= RD SCHEDULE & PASSBOOK MODAL ================= -->
    <div id="rdScheduleModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(2px);">
        <div style="background: white; border-radius: 12px; width: 100%; max-width: 860px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); border: 1px solid #e2e8f0; display: flex; flex-direction: column;">
            <div style="padding: 16px 22px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #0284c7;" id="schedModalTitle">RD Installment Passbook & Schedule</h3>
                    <span class="muted" style="font-size: 11.5px;" id="schedModalSubtitle">Complete payment schedule, status breakdown, and installment tracking</span>
                </div>
                <button type="button" onclick="closeRdScheduleModal()" style="background: none; border: none; font-size: 24px; color: #64748b; cursor: pointer; line-height: 1;">&times;</button>
            </div>

            <div style="padding: 20px; overflow-y: auto;">
                <!-- Summary Stats Card -->
                <div id="schedSummaryBox" style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 14px 18px; margin-bottom: 16px;">
                    <!-- Filled dynamically via JS -->
                </div>

                <!-- Filter tabs -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; gap: 6px;">
                        <button type="button" class="button sched-tab active" onclick="filterScheduleStatus('all', this)" style="padding: 4px 10px; font-size: 11px; background: #0284c7; color: white;">All (<span id="countAll">0</span>)</button>
                        <button type="button" class="button sched-tab" onclick="filterScheduleStatus('paid', this)" style="padding: 4px 10px; font-size: 11px;">Paid (<span id="countPaid">0</span>)</button>
                        <button type="button" class="button sched-tab" onclick="filterScheduleStatus('due', this)" style="padding: 4px 10px; font-size: 11px;">Due / Overdue (<span id="countDue">0</span>)</button>
                        <button type="button" class="button sched-tab" onclick="filterScheduleStatus('scheduled', this)" style="padding: 4px 10px; font-size: 11px;">Scheduled (<span id="countScheduled">0</span>)</button>
                    </div>
                    <div id="schedQuickPayBtnWrap"></div>
                </div>

                <!-- Installments Table -->
                <div style="max-height: 400px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px;">
                    <table style="margin: 0; width: 100%;">
                        <thead style="position: sticky; top: 0; background: #0284c7; color: white;">
                            <tr>
                                <th style="color: white; padding: 8px 10px;">#</th>
                                <th style="color: white; padding: 8px 10px;">Due Date</th>
                                <th style="color: white; padding: 8px 10px;">Amount Due</th>
                                <th style="color: white; padding: 8px 10px;">Fine</th>
                                <th style="color: white; padding: 8px 10px;">Paid Amount</th>
                                <th style="color: white; padding: 8px 10px;">Status</th>
                                <th style="color: white; padding: 8px 10px;">Payment Date</th>
                            </tr>
                        </thead>
                        <tbody id="schedTableBody">
                            <tr><td colspan="7" style="text-align: center; padding: 20px;">Loading schedule...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= EDIT CUSTOMER / APPLICATION FORM DETAILS MODAL ================= -->
    <div id="editCustomerModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); z-index: 99999; justify-content: center; align-items: center; padding: 20px;">
        <div style="background: white; border-radius: 12px; width: 100%; max-width: 900px; max-height: 90vh; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); overflow: hidden; display: flex; flex-direction: column;">
            <div style="background: linear-gradient(135deg, #0284c7, #0369a1); padding: 16px 22px; color: white; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="bi bi-pencil-square" style="font-size: 20px;"></i>
                    <div>
                        <h3 id="editCustomerModalTitle" style="margin: 0; font-size: 16px; font-weight: 800; color: white;">Edit Customer & Application Form</h3>
                        <p style="margin: 2px 0 0; font-size: 11.5px; color: #e0f2fe;">Update membership details, parents, banking, nominee, and introducer records</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditMemberModal()" style="background: none; border: none; color: white; font-size: 26px; cursor: pointer; line-height: 1;">&times;</button>
            </div>

            <form id="editMemberForm" method="post" action="" style="padding: 22px; margin: 0; overflow-y: auto; flex: 1;">
                @csrf

                <!-- SECTION 1: ACCOUNT & STATUS -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0284c7; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-person-badge"></i> 1. Account & Membership Status
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1.5fr 1fr 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Member No <span style="color:#dc2626">*</span></label>
                            <input type="text" name="member_no" id="editMemberNo" required style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; font-weight: 700;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Customer Full Name <span style="color:#dc2626">*</span></label>
                            <input type="text" name="name" id="editMemberName" required style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; font-weight: 700;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Account Status <span style="color:#dc2626">*</span></label>
                            <select name="status" id="editMemberStatus" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; background: white;">
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="suspended">Suspended</option>
                                <option value="blocked">Blocked</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">KYC Status <span style="color:#dc2626">*</span></label>
                            <select name="kyc_status" id="editMemberKycStatus" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; background: white;">
                                <option value="not_submitted">Not Submitted</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Joining Date</label>
                            <input type="date" name="created_at" id="editMemberJoiningDate" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: PARENTS & PERSONAL DETAILS -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0284c7; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-people-fill"></i> 2. Family & Personal Particulars
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 10px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">2(a). S/o, W/o, D/o</label>
                            <input type="text" name="parent_name" id="editParentName" placeholder="Son / Wife / Daughter of..." style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">2(b). Mother’s Name</label>
                            <input type="text" name="mother_name" id="editMotherName" placeholder="Mother's Full Name" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">2(c). Father’s Name</label>
                            <input type="text" name="father_name" id="editFatherName" placeholder="Father's Full Name" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1.2fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="editMemberDob" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Age (Years)</label>
                            <input type="number" name="age" id="editMemberAge" min="1" max="120" placeholder="e.g. 32" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Blood Group</label>
                            <select name="blood_group" id="editBloodGroup" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; background: white;">
                                <option value="">Select</option>
                                <option value="O+">O+</option>
                                <option value="A+">A+</option>
                                <option value="B+">B+</option>
                                <option value="AB+">AB+</option>
                                <option value="O-">O-</option>
                                <option value="A-">A-</option>
                                <option value="B-">B-</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Qualification</label>
                            <input type="text" name="qualification" id="editQualification" placeholder="e.g. Graduate / HS" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Marital Status</label>
                            <select name="marital_status" id="editMaritalStatus" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; background: white;">
                                <option value="Married">Married</option>
                                <option value="Single">Single</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Divorced">Divorced</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: CONTACT & ADDRESSES -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0284c7; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-geo-alt"></i> 3. Contact & Addresses
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1.2fr 1fr; gap: 12px; margin-bottom: 10px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Mobile Phone <span style="color:#dc2626">*</span></label>
                            <input type="text" name="phone" id="editMemberPhone" required style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Email Address</label>
                            <input type="email" name="email" id="editMemberEmail" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Reset MPIN (6-digit, optional)</label>
                            <input type="password" name="mpin" id="editMemberMpin" maxlength="6" pattern="^[0-9]{6}$" placeholder="Leave blank to keep" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 2fr 2fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Permanent Address <span style="color:#dc2626">*</span></label>
                            <input type="text" name="address" id="editMemberAddress" required style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Present / Correspondence Address</label>
                            <input type="text" name="present_address" id="editPresentAddress" placeholder="Present Address" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Pin Code</label>
                            <input type="text" name="pincode" id="editPincode" placeholder="e.g. 781001" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: BANKING PARTICULARS (ALL BANKS) -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0284c7; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-bank"></i> 4. Banking Particulars (Multiple Banks)
                    </div>
                    <!-- Bank 1 -->
                    <div style="margin-bottom: 10px;">
                        <span style="font-size: 11px; font-weight: 700; color: #1e293b; display: block; margin-bottom: 4px;">Primary Bank Account</span>
                        <div style="display: grid; grid-template-columns: 1.5fr 1.5fr 1fr 1fr; gap: 10px;">
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">Bank Name</label>
                                <input type="text" name="bank_name" id="editBankName" placeholder="e.g. State Bank of India" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">Account Number</label>
                                <input type="text" name="bank_account_no" id="editBankAccountNo" placeholder="Bank Account Number" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">IFSC Code</label>
                                <input type="text" name="bank_ifsc" id="editBankIfsc" placeholder="e.g. SBIN0001234" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; text-transform: uppercase;">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">Branch</label>
                                <input type="text" name="bank_branch" id="editBankBranch" placeholder="Branch Name" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                            </div>
                        </div>
                    </div>
                    <!-- Bank 2 -->
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: #1e293b; display: block; margin-bottom: 4px;">Secondary Bank Account (Optional)</span>
                        <div style="display: grid; grid-template-columns: 1.5fr 1.5fr 1fr 1fr; gap: 10px;">
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">Bank 2 Name</label>
                                <input type="text" name="bank2_name" id="editBank2Name" placeholder="e.g. HDFC / Axis Bank" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">Account Number</label>
                                <input type="text" name="bank2_account_no" id="editBank2AccountNo" placeholder="Account Number" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">IFSC Code</label>
                                <input type="text" name="bank2_ifsc" id="editBank2Ifsc" placeholder="e.g. HDFC0001234" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; text-transform: uppercase;">
                            </div>
                            <div>
                                <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">Branch</label>
                                <input type="text" name="bank2_branch" id="editBank2Branch" placeholder="Branch Name" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 5: EMPLOYMENT & TAX -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0284c7; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-briefcase"></i> 5. Employment, Tax & Score
                    </div>
                    <div style="display: grid; grid-template-columns: 1.2fr 2fr 1.5fr; gap: 12px; margin-bottom: 10px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Employed In / Occupation</label>
                            <input type="text" name="occupation" id="editMemberOccupation" placeholder="e.g. Govt / Business / Private" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Office Address</label>
                            <input type="text" name="office_address" id="editOfficeAddress" placeholder="Office / Shop Address" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Designation & Salary Drawn</label>
                            <input type="text" name="designation_salary" id="editDesignationSalary" placeholder="e.g. Executive, Rs. 35,000" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1.5fr; gap: 12px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Aadhaar Number</label>
                            <input type="text" name="aadhaar" id="editMemberAadhaar" placeholder="12-digit Aadhaar" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">PAN Number</label>
                            <input type="text" name="pan" id="editMemberPan" placeholder="ABCDE1234F" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; text-transform: uppercase;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">MCS Credit Score (300-900)</label>
                            <input type="number" name="mcs_score" id="editMemberScore" min="300" max="900" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px; font-weight: 700;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Relative in Society (if any)</label>
                            <input type="text" name="relative_member_details" id="editRelativeMemberDetails" placeholder="Name, Relation, A/c No." style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                    </div>
                </div>

                <!-- SECTION 6: NOMINEE -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0284c7; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-person-heart"></i> 6. Nomination Clause
                    </div>
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 0.8fr 1fr; gap: 12px; margin-bottom: 10px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Nominee Full Name <span style="color:#dc2626">*</span></label>
                            <input type="text" name="nominee_name" id="editNomineeName" required style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Nominee S/o, W/o, D/o</label>
                            <input type="text" name="nominee_parent" id="editNomineeParent" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Relationship</label>
                            <input type="text" name="nominee_relation" id="editNomineeRelation" placeholder="e.g. Spouse / Son" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Nominee Age</label>
                            <input type="number" name="nominee_age" id="editNomineeAge" min="1" max="120" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Occupation</label>
                            <input type="text" name="nominee_occupation" id="editNomineeOccupation" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                        </div>
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Nominee Address</label>
                        <input type="text" name="nominee_address" id="editNomineeAddress" placeholder="Residential Address of Nominee" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 12px;">
                    </div>
                </div>

                <!-- SECTION 7: INTRODUCERS -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 800; color: #0284c7; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-people"></i> 7. Introducer Declarations
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <!-- Introducer 1 -->
                        <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; background: white;">
                            <div style="font-size: 11.5px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Introducer Member 1</div>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <input type="text" name="intro1_name" id="editIntro1Name" placeholder="1. Full Name of Introducer" style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                <input type="text" name="intro1_parent" id="editIntro1Parent" placeholder="S/o, W/o, D/o" style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                    <input type="text" name="intro1_account" id="editIntro1Account" placeholder="Account No." style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                    <input type="text" name="intro1_lf" id="editIntro1Lf" placeholder="LF No." style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                </div>
                                <input type="text" name="intro1_address" id="editIntro1Address" placeholder="Address" style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                            </div>
                        </div>
                        <!-- Introducer 2 -->
                        <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; background: white;">
                            <div style="font-size: 11.5px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Introducer Member 2</div>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <input type="text" name="intro2_name" id="editIntro2Name" placeholder="2. Full Name of Introducer" style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                <input type="text" name="intro2_parent" id="editIntro2Parent" placeholder="S/o, W/o, D/o" style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                    <input type="text" name="intro2_account" id="editIntro2Account" placeholder="Account No." style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                    <input type="text" name="intro2_lf" id="editIntro2Lf" placeholder="LF No." style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                                </div>
                                <input type="text" name="intro2_address" id="editIntro2Address" placeholder="Address" style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FORM ACTIONS -->
                <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                    <button type="button" onclick="closeEditMemberModal()" class="button secondary" style="padding: 8px 18px; font-size: 13px;">Cancel</button>
                    <button type="submit" class="button" style="padding: 8px 22px; font-size: 13px; background: #0284c7; color: white; font-weight: 700;">
                        <i class="bi bi-check-circle-fill"></i> Save & Update Customer Form
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= EDIT DEPOSIT / WRONG ENTRY CORRECTION MODAL ================= -->
    <div id="editDepositModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); z-index: 99999; justify-content: center; align-items: center; padding: 20px;">
        <div style="background: white; border-radius: 12px; width: 100%; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); overflow: hidden;">
            <div style="background: linear-gradient(135deg, #f59e0b, #d97706); padding: 16px 20px; color: white; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-pencil-square" style="font-size: 20px;"></i>
                    <h3 id="editModalTitle" style="margin: 0; font-size: 16px; font-weight: 800; color: white;">Edit Wrong Entry / Correct Transaction</h3>
                </div>
                <button type="button" onclick="closeEditDepositModal()" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
            </div>

            <form id="editDepositForm" method="post" action="" style="padding: 20px; margin: 0;">
                @csrf
                <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 12px; color: #92400e; line-height: 1.5;">
                    <i class="bi bi-exclamation-triangle-fill" style="margin-right: 4px;"></i>
                    Modifying this entry will adjust the account balance, ledger entry, cash book/bank statement, and official receipt records accordingly with an audit trail.
                </div>

                <!-- Receipt Info Header -->
                <div style="display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; font-size: 12px;">
                    <div>
                        <span class="muted" style="font-size: 11px; display: block;">Receipt No:</span>
                        <b id="editModalReceiptNo" style="color: #0369a1;">--</b>
                    </div>
                    <div>
                        <span class="muted" style="font-size: 11px; display: block;">Member:</span>
                        <b id="editModalMemberName">--</b>
                    </div>
                    <div>
                        <span class="muted" style="font-size: 11px; display: block;">Category:</span>
                        <span id="editModalCategory" class="badge">--</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label id="editModalAmountLabel" style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">
                            Amount (&#8377;) <span style="color:#dc2626">*</span>
                        </label>
                        <input type="number" name="amount" id="editModalAmount" step="0.01" min="0.01" required oninput="calculateEditDiff()" style="width: 100%; padding: 8px 12px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 14px; font-weight: 700; box-sizing: border-box;">
                        <div id="editModalDiffBadge" style="font-size: 11px; margin-top: 3px; font-weight: 600;"></div>
                    </div>

                    <div>
                        <label id="editModalDateLabel" style="font-size: 12px; font-weight: 700; color: #166534; display: block; margin-bottom: 4px;">
                            <i class="bi bi-calendar-event"></i> Entry Date <span style="color:#dc2626">*</span>
                        </label>
                        <input type="date" name="deposit_date" id="editModalDate" required style="width: 100%; padding: 8px 12px; border: 1.5px solid #22c55e; border-radius: 6px; font-size: 13px; font-weight: 700; box-sizing: border-box;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">
                            Payment Mode <span style="color:#dc2626">*</span>
                        </label>
                        <select name="payment_mode" id="editModalMode" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">
                            Bank Account (if Bank/UPI)
                        </label>
                        <select name="bank_account_id" id="editModalBank" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                            <option value="">Default Treasury</option>
                            @foreach($bankAccounts as $b)
                                <option value="{{ $b->id }}">{{ $b->bank_name }} (&#8377;{{ number_format($b->current_balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">
                        Note / Description
                    </label>
                    <input type="text" name="note" id="editModalNote" placeholder="e.g. Daily deposit / Monthly collection" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 18px;">
                    <label style="font-size: 12px; font-weight: 700; color: #991b1b; display: block; margin-bottom: 4px;">
                        Reason for Correction / Details <span style="color:#dc2626">*</span>
                    </label>
                    <input type="text" name="correction_reason" id="editModalReason" required placeholder="e.g. Operator entered wrong amount / typo in deposit amount" style="width: 100%; padding: 8px 12px; border: 1.5px solid #f87171; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                    <span class="muted" style="font-size: 11px;">Documented in the official audit trail for regulatory compliance.</span>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                    <button type="button" onclick="closeEditDepositModal()" class="button secondary" style="padding: 8px 16px; font-size: 13px;">Cancel</button>
                    <button type="submit" class="button" style="padding: 8px 20px; font-size: 13px; background: #f59e0b; color: #0f172a; font-weight: 800; border: none; border-radius: 6px; cursor: pointer;">
                        <i class="bi bi-check2-circle"></i> Save Correction & Adjust Ledgers
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts for Customizable Deposit Tenure and Wrong Entry Correction -->
    <script>
        function setCreateAccountTenure(val, unit, btn) {
            const valInput = document.getElementById('createAccountTenureValue');
            const unitSelect = document.getElementById('createAccountTenureUnit');
            if (valInput) valInput.value = val;
            if (unitSelect) unitSelect.value = unit;

            // Highlight selected button
            document.querySelectorAll('.tenure-pill-btn').forEach(b => {
                b.style.background = 'white';
                b.style.color = '#0f766e';
            });
            if (btn) {
                btn.style.background = '#0d9488';
                btn.style.color = 'white';
            }
            updateCreateAccountMaturity();
        }

        function updateCreateAccountMaturity() {
            const valInput = document.getElementById('createAccountTenureValue');
            const unitSelect = document.getElementById('createAccountTenureUnit');
            const hiddenMonths = document.getElementById('createAccountTenureMonths');
            const openingInput = document.getElementById('customerCreateOpeningDate');
            const maturityInput = document.getElementById('createAccountMaturityDate');
            const termPreview = document.getElementById('tenurePreviewTerm');
            const datePreview = document.getElementById('tenurePreviewDate');
            const noteSpan = document.getElementById('createAccountMaturityNote');

            if (!valInput || !unitSelect) return;

            let val = parseInt(valInput.value, 10);
            if (isNaN(val) || val < 1) val = 1;
            const unit = unitSelect.value;
            const totalMonths = unit === 'years' ? val * 12 : val;

            if (hiddenMonths) hiddenMonths.value = totalMonths;

            const openingStr = openingInput && openingInput.value ? openingInput.value : new Date().toISOString().slice(0, 10);
            const parts = openingStr.split('-');
            const year = parseInt(parts[0], 10);
            const month = parseInt(parts[1], 10) - 1;
            const day = parseInt(parts[2], 10);

            // Add months to date
            const targetYear = year + Math.floor((month + totalMonths) / 12);
            const targetMonth = (month + totalMonths) % 12;
            const maxDaysInTarget = new Date(targetYear, targetMonth + 1, 0).getDate();
            const targetDay = Math.min(day, maxDaysInTarget);

            const pad = n => String(n).padStart(2, '0');
            const maturityStr = targetYear + '-' + pad(targetMonth + 1) + '-' + pad(targetDay);

            if (maturityInput) maturityInput.value = maturityStr;

            const yearsDec = (totalMonths / 12).toFixed(1).replace('.0', '');
            const termLabel = totalMonths + ' Month' + (totalMonths > 1 ? 's' : '') + (totalMonths >= 12 ? ' (' + yearsDec + ' Year' + (yearsDec != 1 ? 's' : '') + ')' : '');

            if (termPreview) termPreview.textContent = termLabel;
            if (datePreview) datePreview.textContent = maturityStr;
            if (noteSpan) noteSpan.textContent = 'Auto-calculated: ' + termLabel + ' from opening date';
        }

        function onManualMaturityChange() {
            const openingInput = document.getElementById('customerCreateOpeningDate');
            const maturityInput = document.getElementById('createAccountMaturityDate');
            const valInput = document.getElementById('createAccountTenureValue');
            const unitSelect = document.getElementById('createAccountTenureUnit');
            const hiddenMonths = document.getElementById('createAccountTenureMonths');
            const termPreview = document.getElementById('tenurePreviewTerm');
            const datePreview = document.getElementById('tenurePreviewDate');
            const noteSpan = document.getElementById('createAccountMaturityNote');

            if (!openingInput || !maturityInput || !maturityInput.value) return;

            const d1 = new Date(openingInput.value || new Date());
            const d2 = new Date(maturityInput.value);

            let diffMonths = (d2.getFullYear() - d1.getFullYear()) * 12 + (d2.getMonth() - d1.getMonth());
            if (d2.getDate() < d1.getDate()) diffMonths -= 1;
            if (diffMonths < 1) diffMonths = 1;

            if (hiddenMonths) hiddenMonths.value = diffMonths;
            if (valInput) valInput.value = diffMonths;
            if (unitSelect) unitSelect.value = 'months';

            document.querySelectorAll('.tenure-pill-btn').forEach(b => {
                b.style.background = 'white';
                b.style.color = '#0f766e';
            });

            const yearsDec = (diffMonths / 12).toFixed(1).replace('.0', '');
            const termLabel = diffMonths + ' Months (' + yearsDec + ' Yrs)';
            if (termPreview) termPreview.textContent = termLabel;
            if (datePreview) datePreview.textContent = maturityInput.value;
            if (noteSpan) noteSpan.textContent = 'Custom date set manually (~' + diffMonths + ' months)';
        }

        function openEditDepositModal(r) {
            let data = {};
            if (r instanceof HTMLElement) {
                if (r.dataset.id) {
                    data = {
                        id: r.dataset.id,
                        receipt_no: r.dataset.receiptNo || ('#' + r.dataset.id),
                        account_id: r.dataset.accountId,
                        account_no: r.dataset.accountNo || '',
                        name: r.dataset.name || '',
                        member_no: r.dataset.memberNo || '',
                        amount: parseFloat(r.dataset.amount) || 0,
                        payment_mode: r.dataset.paymentMode || 'cash',
                        bank_account_id: r.dataset.bankId || '',
                        notes: r.dataset.notes || '',
                        date: r.dataset.date || new Date().toISOString().slice(0, 10),
                        category: r.dataset.category || 'deposit'
                    };
                } else if (r.getAttribute('data-tx')) {
                    try {
                        let raw = r.getAttribute('data-tx');
                        raw = raw.replace(/&quot;/g, '"').replace(/&amp;/g, '&');
                        data = JSON.parse(raw);
                    } catch(e) {
                        console.error('Failed to parse tx data:', e);
                    }
                }
            } else if (typeof r === 'string') {
                try {
                    let raw = r.replace(/&quot;/g, '"').replace(/&amp;/g, '&');
                    data = JSON.parse(raw);
                } catch(e) {
                    console.error('Failed to parse string r:', e);
                }
            } else if (typeof r === 'object' && r !== null) {
                data = r;
            }

            if (!data || !data.id) {
                console.error('No valid transaction data found to edit:', r);
                return;
            }

            const modal = document.getElementById('editDepositModal');
            const form = document.getElementById('editDepositForm');
            if (!modal || !form) {
                console.error('editDepositModal or editDepositForm not found in DOM');
                return;
            }

            const isWithdrawal = (data.category || '').toLowerCase() === 'withdrawal';
            form.dataset.isWithdrawal = isWithdrawal ? '1' : '0';
            form.dataset.oldAmount = data.amount || 0;
            form.action = isWithdrawal ? ('/admin/withdrawals/' + data.id + '/update') : ('/admin/deposits/' + data.id + '/update');

            const title = document.getElementById('editModalTitle');
            if (title) title.textContent = isWithdrawal ? 'Edit Wrong Entry / Correct Withdrawal' : 'Edit Wrong Entry / Correct Deposit';

            const amtLabel = document.getElementById('editModalAmountLabel');
            if (amtLabel) amtLabel.innerHTML = (isWithdrawal ? 'Withdrawal Amount (&#8377;)' : 'Deposit Amount (&#8377;)') + ' <span style="color:#dc2626">*</span>';

            const dateLabel = document.getElementById('editModalDateLabel');
            if (dateLabel) dateLabel.innerHTML = '<i class="bi bi-calendar-event"></i> ' + (isWithdrawal ? 'Withdrawal Date' : 'Payment Date') + ' <span style="color:#dc2626">*</span>';

            const receiptNoEl = document.getElementById('editModalReceiptNo');
            if (receiptNoEl) receiptNoEl.textContent = data.receipt_no || ('#' + data.id);

            const memberNameEl = document.getElementById('editModalMemberName');
            if (memberNameEl) memberNameEl.textContent = (data.name || 'Member') + (data.member_no ? ' (' + data.member_no + ')' : '');

            const catEl = document.getElementById('editModalCategory');
            if (catEl) catEl.textContent = (data.category || 'deposit').replace(/_/g, ' ');

            const amtInput = document.getElementById('editModalAmount');
            if (amtInput) amtInput.value = (parseFloat(data.amount) || 0).toFixed(2);

            const dateInput = document.getElementById('editModalDate');
            if (dateInput) dateInput.value = data.date || new Date().toISOString().slice(0, 10);

            const modeInput = document.getElementById('editModalMode');
            if (modeInput) modeInput.value = (data.payment_mode || 'cash').toLowerCase();

            const bankSelect = document.getElementById('editModalBank');
            if (bankSelect) bankSelect.value = data.bank_account_id || '';

            const noteInput = document.getElementById('editModalNote');
            if (noteInput) noteInput.value = data.notes || '';

            const reasonInput = document.getElementById('editModalReason');
            if (reasonInput) reasonInput.value = '';

            calculateEditDiff();
            modal.style.display = 'flex';
            modal.style.zIndex = '999999';
        }

        function openEditTransactionModal(r) {
            openEditDepositModal(r);
        }

        function closeEditDepositModal() {
            const modal = document.getElementById('editDepositModal');
            if (modal) modal.style.display = 'none';
        }

        function calculateEditDiff() {
            const form = document.getElementById('editDepositForm');
            const amtInput = document.getElementById('editModalAmount');
            const badge = document.getElementById('editModalDiffBadge');
            if (!form || !amtInput || !badge) return;

            const oldAmt = parseFloat(form.dataset.oldAmount) || 0;
            const newAmt = parseFloat(amtInput.value) || 0;
            const isWithdrawal = form.dataset.isWithdrawal === '1';

            if (isWithdrawal) {
                if (newAmt > oldAmt + 0.001) {
                    const extra = newAmt - oldAmt;
                    badge.innerHTML = '<span style="color:#dc2626;"><i class="bi bi-arrow-down-circle-fill"></i> Higher withdrawal (+&#8377;' + extra.toFixed(2) + '): will debit an extra &#8377;' + extra.toFixed(2) + ' from account balance</span>';
                } else if (newAmt < oldAmt - 0.001) {
                    const refund = oldAmt - newAmt;
                    badge.innerHTML = '<span style="color:#16a34a;"><i class="bi bi-arrow-up-circle-fill"></i> Reduced withdrawal (-&#8377;' + refund.toFixed(2) + '): will restore &#8377;' + refund.toFixed(2) + ' back to account balance</span>';
                } else {
                    badge.innerHTML = '<span style="color:#64748b;">No change in withdrawal amount</span>';
                }
            } else {
                const diff = newAmt - oldAmt;
                if (diff > 0.001) {
                    badge.innerHTML = '<span style="color:#16a34a;"><i class="bi bi-arrow-up-circle-fill"></i> +&#8377;' + diff.toFixed(2) + ' (Will credit account balance)</span>';
                } else if (diff < -0.001) {
                    badge.innerHTML = '<span style="color:#dc2626;"><i class="bi bi-arrow-down-circle-fill"></i> -&#8377;' + Math.abs(diff).toFixed(2) + ' (Will debit account balance)</span>';
                } else {
                    badge.innerHTML = '<span style="color:#64748b;">No change in deposit amount</span>';
                }
            }
        }

        // Close on ESC key or outside click
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditDepositModal();
            }
        });

        document.getElementById('editDepositModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditDepositModal();
            }
        });

        window.addEventListener('DOMContentLoaded', function() {
            updateCreateAccountMaturity();
        });
    </script>
</body></html>
