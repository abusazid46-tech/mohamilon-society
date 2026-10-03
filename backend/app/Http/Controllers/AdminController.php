<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\{CreditScore, DepositCalculator, FinanceLedger, FinancialAutomation, LoanSchedule, MemberNotifier, ReceiptService, SequenceService, StaffPermissions, TransactionManager};
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, Schema, Storage, Validator};
use Illuminate\Support\Str;

class AdminController extends Controller
{
    private function ensureMemberSchema(): void
    {
        try {
            Schema::table('members', function (Blueprint $table) {
                if (!Schema::hasColumn('members', 'parent_name')) $table->string('parent_name', 150)->nullable();
                if (!Schema::hasColumn('members', 'mother_name')) $table->string('mother_name', 150)->nullable();
                if (!Schema::hasColumn('members', 'father_name')) $table->string('father_name', 150)->nullable();
                if (!Schema::hasColumn('members', 'age')) $table->unsignedSmallInteger('age')->nullable();
                if (!Schema::hasColumn('members', 'blood_group')) $table->string('blood_group', 10)->nullable();
                if (!Schema::hasColumn('members', 'qualification')) $table->string('qualification', 120)->nullable();
                if (!Schema::hasColumn('members', 'marital_status')) $table->string('marital_status', 30)->nullable();
                if (!Schema::hasColumn('members', 'present_address')) $table->text('present_address')->nullable();
                if (!Schema::hasColumn('members', 'pincode')) $table->string('pincode', 20)->nullable();
                if (!Schema::hasColumn('members', 'bank_name')) $table->string('bank_name', 150)->nullable();
                if (!Schema::hasColumn('members', 'bank_account_no')) $table->string('bank_account_no', 50)->nullable();
                if (!Schema::hasColumn('members', 'bank_ifsc')) $table->string('bank_ifsc', 25)->nullable();
                if (!Schema::hasColumn('members', 'bank_branch')) $table->string('bank_branch', 120)->nullable();
                if (!Schema::hasColumn('members', 'bank2_name')) $table->string('bank2_name', 150)->nullable();
                if (!Schema::hasColumn('members', 'bank2_account_no')) $table->string('bank2_account_no', 50)->nullable();
                if (!Schema::hasColumn('members', 'bank2_ifsc')) $table->string('bank2_ifsc', 25)->nullable();
                if (!Schema::hasColumn('members', 'bank2_branch')) $table->string('bank2_branch', 120)->nullable();
                if (!Schema::hasColumn('members', 'office_address')) $table->text('office_address')->nullable();
                if (!Schema::hasColumn('members', 'designation_salary')) $table->string('designation_salary', 150)->nullable();
                if (!Schema::hasColumn('members', 'relative_member_details')) $table->string('relative_member_details', 255)->nullable();
                if (!Schema::hasColumn('members', 'nominee_parent')) $table->string('nominee_parent', 150)->nullable();
                if (!Schema::hasColumn('members', 'nominee_relation')) $table->string('nominee_relation', 80)->nullable();
                if (!Schema::hasColumn('members', 'nominee_occupation')) $table->string('nominee_occupation', 120)->nullable();
                if (!Schema::hasColumn('members', 'nominee_age')) $table->unsignedSmallInteger('nominee_age')->nullable();
                if (!Schema::hasColumn('members', 'nominee_address')) $table->text('nominee_address')->nullable();
                if (!Schema::hasColumn('members', 'intro1_name')) $table->string('intro1_name', 150)->nullable();
                if (!Schema::hasColumn('members', 'intro1_parent')) $table->string('intro1_parent', 150)->nullable();
                if (!Schema::hasColumn('members', 'intro1_account')) $table->string('intro1_account', 50)->nullable();
                if (!Schema::hasColumn('members', 'intro1_lf')) $table->string('intro1_lf', 50)->nullable();
                if (!Schema::hasColumn('members', 'intro1_address')) $table->text('intro1_address')->nullable();
                if (!Schema::hasColumn('members', 'intro2_name')) $table->string('intro2_name', 150)->nullable();
                if (!Schema::hasColumn('members', 'intro2_parent')) $table->string('intro2_parent', 150)->nullable();
                if (!Schema::hasColumn('members', 'intro2_account')) $table->string('intro2_account', 50)->nullable();
                if (!Schema::hasColumn('members', 'intro2_lf')) $table->string('intro2_lf', 50)->nullable();
                if (!Schema::hasColumn('members', 'intro2_address')) $table->text('intro2_address')->nullable();
                if (!Schema::hasColumn('members', 'aadhaar_no')) $table->string('aadhaar_no', 25)->nullable();
                if (!Schema::hasColumn('members', 'pan_no')) $table->string('pan_no', 20)->nullable();
            });
        } catch (\Throwable $e) {
            // Ignore schema migration conflicts
        }
    }
    private function staff(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['super_admin','president','manager','secretary','agent'], true), 403);
    }

    private function permit(Request $request, string $permission): void
    {
        $this->staff($request);
        abort_unless(StaffPermissions::allows($request->user(), $permission), 403, 'This action is not assigned to your staff account.');
    }

    private function audit(Request $request, string $action, string $type, ?int $id, mixed $before = null, mixed $after = null): void
    {
        DB::table('audit_logs')->insert([
            'actor_id' => $request->user()->id, 'action' => $action, 'entity_type' => $type,
            'entity_id' => $id, 'before' => $before ? json_encode($before) : null,
            'after' => $after ? json_encode($after) : null, 'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500), 'created_at' => now(),
        ]);
    }

    private function accountNumber(): string
    {
        do {
            $number = 'AC'.now()->format('ymd').str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (DB::table('accounts')->where('account_no', $number)->exists());
        return $number;
    }

    private function requestOpeningPayment(Request $request, object $account): void
    {
        $upi = DB::table('society_settings')->where('key', 'upi_id')->value('value') ?: 'mahamilon@upi';
        $payee = DB::table('society_settings')->where('key', 'payee_name')->value('value') ?: 'Mahamilon Society';
        $reference = 'PAY'.now()->format('ymd').str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        $uri = 'upi://pay?'.http_build_query([
            'pa' => $upi, 'pn' => $payee, 'am' => number_format((float) $account->principal, 2, '.', ''),
            'cu' => 'INR', 'tn' => 'Opening '.$account->account_no, 'tr' => $reference,
        ], '', '&', PHP_QUERY_RFC3986);
        $paymentId = DB::table('payments')->insertGetId([
            'reference' => $reference, 'account_id' => $account->id, 'amount' => $account->principal,
            'purpose' => 'account_opening', 'upi_uri' => $uri, 'status' => 'requested',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('accounts')->where('id', $account->id)->update([
            'onboarding_status' => 'payment_required', 'approved_by' => ($request->user()?->id ?: 1),
            'approved_at' => now(), 'updated_at' => now(),
        ]);
        MemberNotifier::send(
            MemberNotifier::memberUserId($account->member_id),
            'payment',
            'Opening payment required',
            'Account '.$account->account_no.' is approved. Pay ₹'.number_format((float) $account->principal, 2).' using the QR code to activate it.',
            'opening-payment-requested-'.$paymentId,
            '/accounts'
        );
    }

    public function loginForm() { return view('admin.login'); }

    public function login(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::attempt($data)) return back()->withErrors(['email' => 'Invalid credentials'])->onlyInput('email');
        if ($request->user()->status !== 'active' || $request->user()->role === 'member') {
            Auth::logout();
            return back()->withErrors(['email' => 'Staff access denied']);
        }
        $request->session()->regenerate();
        return redirect('/admin');
    }

    public function logout(Request $request)
    {
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return redirect('/admin/login');
    }

    public function changePassword(Request $request)
    {
        $this->staff($request);
        $data = $request->validate(['current_password' => 'required|current_password', 'password' => 'required|string|min:12|confirmed']);
        $request->user()->update(['password' => Hash::make($data['password'])]);
        $this->audit($request, 'staff.password_changed', 'user', $request->user()->id, null, ['changed' => true]);
        return back()->with('ok', 'Password changed successfully');
    }

    public function dashboard(Request $request)
    {
        $this->staff($request);
        FinancialAutomation::activateClosureWithdrawals();
        FinancialAutomation::markOverdueInstallments();
        $permissions = StaffPermissions::for($request->user());
        $members = DB::table('members')->join('users','users.id','=','members.user_id')
            ->select('members.*','users.name','users.phone','users.email','users.status as user_status')
            ->selectRaw("(SELECT COALESCE(SUM(balance),0) FROM accounts WHERE accounts.member_id=members.id AND accounts.status='active') AS active_savings")
            ->latest('members.id')->limit(100)->get();
        $today = Carbon::today();
        $profitLoss = [
            'daily' => FinanceLedger::profitLossSummary($today->toDateString(), $today->toDateString()),
            'weekly' => FinanceLedger::profitLossSummary($today->copy()->startOfWeek()->toDateString(), $today->copy()->endOfWeek()->toDateString()),
            'monthly' => FinanceLedger::profitLossSummary($today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()),
            'yearly' => FinanceLedger::profitLossSummary($today->copy()->startOfYear()->toDateString(), $today->copy()->endOfYear()->toDateString()),
        ];

        // Date Filter for Dashboard KPIs (Section 1)
        $dateFilter = $request->query('date_filter', 'today');
        $customFrom = $request->query('custom_from');
        $customTo = $request->query('custom_to');
        [$filterStart, $filterEnd] = match ($dateFilter) {
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'this_week' => [Carbon::today()->startOfWeek(), Carbon::today()->endOfWeek()],
            'this_month' => [Carbon::today()->startOfMonth(), Carbon::today()->endOfMonth()],
            'this_year' => [Carbon::today()->startOfYear(), Carbon::today()->endOfYear()],
            'custom' => [
                $customFrom ? Carbon::parse($customFrom)->startOfDay() : Carbon::today()->startOfDay(),
                $customTo ? Carbon::parse($customTo)->endOfDay() : Carbon::today()->endOfDay()
            ],
            default => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
        };

        $filteredCollection = (float) DB::table('ledger_entries')
            ->where('direction', 'credit')
            ->where('status', 'posted')
            ->whereBetween('posted_at', [$filterStart, $filterEnd])
            ->sum('amount');

        $filteredExpenses = (float) DB::table('profit_loss_entries')
            ->where('entry_type', 'expense')
            ->whereBetween('occurred_on', [$filterStart->toDateString(), $filterEnd->toDateString()])
            ->sum('amount');

        $filteredPnl = FinanceLedger::profitLossSummary($filterStart->toDateString(), $filterEnd->toDateString());

        $transactionFilters = $request->validate([
            'tx_search' => 'nullable|string|max:100',
            'tx_account_id' => 'nullable|integer|exists:accounts,id',
            'tx_direction' => 'nullable|in:credit,debit',
            'tx_status' => 'nullable|in:pending,posted,reversed',
            'tx_from' => 'nullable|date', 'tx_to' => 'nullable|date',
            'tx_min_amount' => 'nullable|numeric|min:0', 'tx_max_amount' => 'nullable|numeric|min:0',
            'tx_sort' => 'nullable|in:newest,oldest,amount_high,amount_low',
        ]);
        abort_if(($transactionFilters['tx_from'] ?? null) && ($transactionFilters['tx_to'] ?? null)
            && Carbon::parse($transactionFilters['tx_to'])->isBefore(Carbon::parse($transactionFilters['tx_from'])), 422, 'The transaction end date must be on or after the start date.');
        abort_if(isset($transactionFilters['tx_min_amount'], $transactionFilters['tx_max_amount'])
            && (float) $transactionFilters['tx_max_amount'] < (float) $transactionFilters['tx_min_amount'], 422, 'The maximum transaction amount must be at least the minimum amount.');
        $transactionQuery = DB::table('ledger_entries')->join('accounts','accounts.id','=','ledger_entries.account_id')
            ->join('members','members.id','=','accounts.member_id')->join('users','users.id','=','members.user_id')
            ->when($transactionFilters['tx_search'] ?? null, function ($query, $search) {
                $term = '%'.addcslashes(trim($search), '%_\\').'%';
                $query->where(function ($match) use ($term) {
                    $match->where('accounts.account_no', 'like', $term)
                        ->orWhere('members.member_no', 'like', $term)
                        ->orWhere('users.name', 'like', $term)
                        ->orWhere('ledger_entries.reference', 'like', $term)
                        ->orWhere('ledger_entries.description', 'like', $term);
                });
            })
            ->when($transactionFilters['tx_account_id'] ?? null, fn ($query, $accountId) => $query->where('ledger_entries.account_id', $accountId))
            ->when($transactionFilters['tx_direction'] ?? null, fn ($query, $direction) => $query->where('ledger_entries.direction', $direction))
            ->when($transactionFilters['tx_status'] ?? null, fn ($query, $status) => $query->where('ledger_entries.status', $status))
            ->when($transactionFilters['tx_from'] ?? null, fn ($query, $from) => $query->whereDate('ledger_entries.posted_at', '>=', $from))
            ->when($transactionFilters['tx_to'] ?? null, fn ($query, $to) => $query->whereDate('ledger_entries.posted_at', '<=', $to))
            ->when($transactionFilters['tx_min_amount'] ?? null, fn ($query, $minimum) => $query->where('ledger_entries.amount', '>=', $minimum))
            ->when($transactionFilters['tx_max_amount'] ?? null, fn ($query, $maximum) => $query->where('ledger_entries.amount', '<=', $maximum));
        $transactionCredits = (float) (clone $transactionQuery)->where('ledger_entries.direction', 'credit')->sum('ledger_entries.amount');
        $transactionDebits = (float) (clone $transactionQuery)->where('ledger_entries.direction', 'debit')->sum('ledger_entries.amount');
        match ($transactionFilters['tx_sort'] ?? 'newest') {
            'oldest' => $transactionQuery->orderBy('ledger_entries.posted_at')->orderBy('ledger_entries.id'),
            'amount_high' => $transactionQuery->orderByDesc('ledger_entries.amount')->orderByDesc('ledger_entries.id'),
            'amount_low' => $transactionQuery->orderBy('ledger_entries.amount')->orderByDesc('ledger_entries.id'),
            default => $transactionQuery->orderByDesc('ledger_entries.posted_at')->orderByDesc('ledger_entries.id'),
        };
        $filteredTransactions = $transactionQuery
            ->select('ledger_entries.*','accounts.account_no','members.member_no','users.name')
            ->paginate(50, ['*'], 'transactions_page')->withQueryString();

        $stmtMemberId = $request->query('stmt_member_id') ?: $request->query('member_id');
        $stmtFrom = $request->query('stmt_from', now()->startOfMonth()->toDateString());
        $stmtTo = $request->query('stmt_to', today()->toDateString());
        $stmtAccountId = $request->query('stmt_account_id', 'all');
        $statementData = null;
        if ($stmtMemberId) {
            try {
                $statementData = $this->calculateStatement((int) $stmtMemberId, $stmtFrom, $stmtTo, $stmtAccountId);
            } catch (\Throwable $e) {
                $statementData = null;
            }
        }

        $activeReportType = $request->query('report_type', 'transactions');
        $reportFilterActive = $request->has('report_type');
        $activeReport = null;
        if ($reportFilterActive || in_array($activeReportType, ['transactions','receipts','loans','accounts','cash','profit-loss','members'], true)) {
            try {
                $activeReport = $this->queryReportData($request, $activeReportType);
            } catch (\Throwable $e) {
                $activeReport = null;
            }
        }

        return view('admin.dashboard', [
            'permissions' => $permissions, 'permissionDefinitions' => StaffPermissions::DEFINITIONS,
            'metrics' => [
                'members' => DB::table('members')->count(),
                'pending_members' => DB::table('users')->where('role', 'member')->where('status', 'pending')->count(),
                'deposits' => DB::table('accounts')->where('status', 'active')->sum('balance'),
                'pending_payments' => DB::table('payments')->where('status', 'submitted')->count(),
                'pending_loans' => DB::table('loan_applications')->whereIn('status', ['submitted','under_review'])->count(),
                'pending_repayments' => DB::table('loan_repayments')->where('status','submitted')->count(),
                'overdue_installments' => DB::table('loan_installments')->where('status','overdue')->count(),
                'transactions' => DB::table('ledger_entries')->where('status', 'posted')->count(),
                'total_loans' => DB::table('loan_applications')->whereIn('status',['approved','disbursed','closed'])->sum('sanctioned_amount'),
                'outstanding_loans' => DB::table('loan_applications')->whereIn('status',['approved','disbursed'])->sum('outstanding_principal'),
                'bank_cash' => FinanceLedger::cashBalance('bank'),
                'treasury_cash' => FinanceLedger::cashBalance('treasury'),
            ],
            'profitLoss' => $profitLoss,
            'transactionFilters' => $transactionFilters,
            'transactionSummary' => ['credits' => $transactionCredits, 'debits' => $transactionDebits, 'net' => $transactionCredits - $transactionDebits],
            'transactionAccountOptions' => DB::table('accounts')->join('members','members.id','=','accounts.member_id')
                ->join('users','users.id','=','members.user_id')->select('accounts.id','accounts.account_no','accounts.status','members.member_no','users.name')
                ->orderBy('users.name')->orderBy('accounts.account_no')->limit(1000)->get(),
            'staffUsers' => User::where('role', '!=', 'member')->orderBy('name')->get(),
            'members' => $members,
            'schemes' => DB::table('schemes')->where('active', true)->orderBy('name')->get(),
            'accounts' => DB::table('accounts')->join('members','members.id','=','accounts.member_id')
                ->join('users','users.id','=','members.user_id')->join('schemes','schemes.id','=','accounts.scheme_id')
                ->select('accounts.*','users.name','members.member_no','schemes.name as scheme_name','schemes.annual_rate',
                    DB::raw('COALESCE(accounts.custom_interest_rate, schemes.annual_rate) as effective_interest_rate'))
                ->latest('accounts.id')->limit(100)->get(),
            'payments' => DB::table('payments')->join('accounts','accounts.id','=','payments.account_id')
                ->join('members','members.id','=','accounts.member_id')->join('users','users.id','=','members.user_id')
                ->select('payments.*','accounts.account_no','users.name')->latest('payments.id')->limit(100)->get(),
            'transactions' => $filteredTransactions,
            'loans' => DB::table('loan_applications')->join('members','members.id','=','loan_applications.member_id')
                ->join('users','users.id','=','members.user_id')
                ->leftJoin('users as verifier', 'verifier.id', '=', 'loan_applications.verified_by')
                ->select(
                    'loan_applications.*',
                    'users.name', 'users.phone', 'users.email', 'members.member_no', 'members.mcs_score', 'members.kyc_status',
                    'verifier.name as verifier_name',
                    DB::raw("(SELECT COALESCE(SUM(balance),0) FROM accounts WHERE accounts.member_id=members.id AND accounts.status='active') as member_savings"),
                    DB::raw("(SELECT COALESCE(SUM(outstanding_principal),0) FROM loan_applications la WHERE la.member_id=members.id AND la.status IN ('approved','disbursed') AND la.id != loan_applications.id) as existing_outstanding"),
                    DB::raw("(SELECT COUNT(*) FROM loan_applications la WHERE la.member_id=members.id AND la.id != loan_applications.id) as previous_loans_count")
                )
                ->latest('loan_applications.id')->limit(100)->get(),
            'repayments' => DB::table('loan_repayments')->join('loan_applications','loan_applications.id','=','loan_repayments.loan_id')
                ->join('members','members.id','=','loan_applications.member_id')->join('users','users.id','=','members.user_id')
                ->select('loan_repayments.*','loan_applications.application_no','users.name')
                ->latest('loan_repayments.id')->limit(100)->get(),
            'installments' => $installments = DB::table('loan_installments')->join('loan_applications','loan_applications.id','=','loan_installments.loan_id')
                ->join('members','members.id','=','loan_applications.member_id')->join('users','users.id','=','members.user_id')
                ->select(
                    'loan_installments.*',
                    'loan_applications.application_no',
                    'loan_applications.outstanding_principal',
                    'users.name', 'users.phone', 'members.member_no',
                    DB::raw('(loan_installments.principal_due + loan_installments.interest_due + loan_installments.penalty_due) as total_payable'),
                    DB::raw('(loan_installments.principal_due + loan_installments.interest_due + loan_installments.penalty_due - loan_installments.paid_amount) as total_outstanding'),
                    DB::raw('CASE WHEN loan_installments.due_on < CURRENT_DATE THEN DATEDIFF(CURRENT_DATE, loan_installments.due_on) ELSE 0 END as days_overdue')
                )
                ->whereIn('loan_installments.status',['due','partial','overdue'])->orderBy('loan_installments.due_on')->limit(100)->get(),
            'overdueInstallments' => $installments->filter(fn($i) => (int)$i->days_overdue > 0 || $i->status === 'overdue')->values(),
            'cashEntries' => DB::table('cash_ledger_entries')->leftJoin('users','users.id','=','cash_ledger_entries.created_by')
                ->select('cash_ledger_entries.*','users.name as actor')->latest('cash_ledger_entries.id')->limit(100)->get(),
            'profitLossEntries' => DB::table('profit_loss_entries')->leftJoin('users','users.id','=','profit_loss_entries.created_by')
                ->select('profit_loss_entries.*','users.name as actor')->latest('profit_loss_entries.id')->limit(100)->get(),
            'closures' => DB::table('account_closures')->join('accounts','accounts.id','=','account_closures.account_id')
                ->join('members','members.id','=','accounts.member_id')->join('users','users.id','=','members.user_id')
                ->select('account_closures.*','accounts.account_no','accounts.balance','accounts.opened_on','users.name','members.member_no','members.id as member_id')
                ->latest('account_closures.id')->limit(100)->get(),
            'memberReports' => DB::table('members')->join('users','users.id','=','members.user_id')
                ->select('members.id','members.member_no','members.kyc_status','members.mcs_score','users.name','users.phone','users.status')
                ->selectRaw('(SELECT COUNT(*) FROM accounts WHERE accounts.member_id=members.id) AS account_count')
                ->selectRaw("(SELECT COALESCE(SUM(balance),0) FROM accounts WHERE accounts.member_id=members.id AND accounts.status='active') AS account_balance")
                ->selectRaw('(SELECT COUNT(*) FROM loan_applications WHERE loan_applications.member_id=members.id) AS loan_count')
                ->selectRaw("(SELECT COALESCE(SUM(outstanding_principal),0) FROM loan_applications WHERE loan_applications.member_id=members.id AND loan_applications.status IN ('approved','disbursed')) AS loan_outstanding")
                ->orderBy('users.name')->limit(500)->get(),
            'documents' => DB::table('kyc_documents')->join('members','members.id','=','kyc_documents.member_id')
                ->join('users','users.id','=','members.user_id')->select('kyc_documents.*','users.name','members.member_no')
                ->latest('kyc_documents.id')->limit(100)->get(),
            'dateFilter' => $dateFilter,
            'customFrom' => $customFrom,
            'customTo' => $customTo,
            'filterStart' => $filterStart,
            'filterEnd' => $filterEnd,
            'filteredCollection' => $filteredCollection,
            'filteredExpenses' => $filteredExpenses,
            'filteredPnl' => $filteredPnl,
            'bankAccounts' => DB::table('bank_accounts')->orderBy('bank_name')->get(),
            'receipts' => DB::table('receipts')->join('members','members.id','=','receipts.member_id')
                ->join('users','users.id','=','members.user_id')
                ->leftJoin('accounts','accounts.id','=','receipts.account_id')
                ->select('receipts.*','users.name','members.member_no','accounts.account_no')->latest('receipts.id')->limit(100)->get(),
            'rdInstallments' => DB::table('rd_installments')->join('accounts','accounts.id','=','rd_installments.account_id')
                ->join('members','members.id','=','accounts.member_id')->join('users','users.id','=','members.user_id')
                ->select('rd_installments.*','accounts.account_no','users.name')
                ->whereIn('rd_installments.status',['scheduled','due','partial','overdue'])
                ->orderBy('rd_installments.due_on')->limit(100)->get(),
            'fdAccounts' => DB::table('accounts')->join('schemes','schemes.id','=','accounts.scheme_id')
                ->join('members','members.id','=','accounts.member_id')->join('users','users.id','=','members.user_id')
                ->where('schemes.type','fd')->select('accounts.*','users.name','members.member_no','schemes.annual_rate','schemes.name as scheme_name')
                ->latest('accounts.id')->limit(100)->get(),
            'rdAccounts' => DB::table('accounts')->join('schemes','schemes.id','=','accounts.scheme_id')
                ->join('members','members.id','=','accounts.member_id')->join('users','users.id','=','members.user_id')
                ->where('schemes.type','rd')->select('accounts.*','users.name','members.member_no','schemes.annual_rate','schemes.name as scheme_name','schemes.deposit_frequency')
                ->latest('accounts.id')->limit(100)->get(),
            'settings' => DB::table('society_settings')->pluck('value', 'key'),
            'auditLogs' => DB::table('audit_logs')->leftJoin('users','users.id','=','audit_logs.actor_id')
                ->select('audit_logs.*','users.name as actor')->latest('audit_logs.id')->limit(30)->get(),
            'stmtMemberId' => $stmtMemberId,
            'stmtFrom' => $stmtFrom,
            'stmtTo' => $stmtTo,
            'stmtAccountId' => $stmtAccountId,
            'statementData' => $statementData,
            'activeReportType' => $activeReportType,
            'activeReport' => $activeReport,
            'reportFilterActive' => $reportFilterActive,
        ]);
    }

    public function createStaff(Request $request)
    {
        abort_unless($request->user()->role === 'super_admin', 403);
        $data = $request->validate([
            'name' => 'required|string|max:120', 'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:12', 'role' => 'required|in:super_admin,president,manager,secretary,agent',
            'permissions' => 'nullable|array', 'permissions.*' => 'in:'.implode(',', array_keys(StaffPermissions::DEFINITIONS)),
        ]);
        $user = User::create([
            'name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password']),
            'role' => $data['role'], 'permissions' => $data['role'] === 'super_admin' ? null : ($data['permissions'] ?? []), 'status' => 'active',
        ]);
        $this->audit($request, 'staff.created', 'user', $user->id, null, $user->only(['name','email','role','permissions']));
        return back()->with('ok', 'Staff account created');
    }

    public function updateStaff(Request $request, int $id)
    {
        abort_unless($request->user()->role === 'super_admin', 403);
        $data = $request->validate([
            'role' => 'required|in:super_admin,president,manager,secretary,agent', 'status' => 'required|in:active,suspended',
            'permissions' => 'nullable|array', 'permissions.*' => 'in:'.implode(',', array_keys(StaffPermissions::DEFINITIONS)),
        ]);
        $user = User::where('role', '!=', 'member')->findOrFail($id);
        abort_if($user->id === $request->user()->id && ($data['role'] !== 'super_admin' || $data['status'] !== 'active'), 422, 'You cannot demote or suspend your own account.');
        $before = $user->only(['role','status','permissions']);
        $user->update(['role' => $data['role'], 'status' => $data['status'], 'permissions' => $data['role'] === 'super_admin' ? null : ($data['permissions'] ?? [])]);
        $this->audit($request, 'staff.updated', 'user', $user->id, $before, $user->only(['role','status','permissions']));
        return back()->with('ok', 'Staff permissions updated');
    }

    public function memberDecision(Request $request, int $id)
    {
        $this->permit($request, 'member.approve');
        $data = $request->validate(['decision' => 'required|in:approve,reject']);
        $member = DB::table('members')->where('id', $id)->firstOrFail();
        $adminId = $request->user()?->id ?: 1;
        $after = [
            'approved_at' => $data['decision'] === 'approve' ? now() : null,
            'approved_by' => $adminId,
            'kyc_status' => $data['decision'] === 'approve' ? 'approved' : $member->kyc_status,
            'updated_at' => now(),
        ];
        DB::transaction(function () use ($request, $id, $member, $after, $data, $adminId) {
            DB::table('members')->where('id', $id)->update($after);
            DB::table('users')->where('id', $member->user_id)->update(['status' => $data['decision'] === 'approve' ? 'active' : 'pending', 'updated_at' => now()]);
            if ($data['decision'] === 'approve') {
                DB::table('kyc_documents')->where('member_id', $id)->where('status', 'pending')->update([
                    'status' => 'approved',
                    'reviewed_by' => $adminId,
                    'reviewed_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->audit($request, 'member.'.$data['decision'], 'member', $id, $member, $after);
        });
        return back()->with('ok', $data['decision'] === 'approve' ? 'Member approved successfully' : 'Member decision saved');
    }

    public function createAccount(Request $request)
    {
        $this->permit($request, 'account.create');
        $this->ensureMemberSchema();
        $data = $request->validate([
            'member_source' => 'required|in:existing,new',
            'member_id' => 'nullable|required_if:member_source,existing|exists:members,id',
            'applicant_name' => 'nullable|required_if:member_source,new|string|max:120',
            'parent_name' => 'nullable|string|max:150',
            'mother_name' => 'nullable|string|max:150',
            'father_name' => 'nullable|string|max:150',
            'applicant_age' => 'nullable|integer|min:1|max:120',
            'applicant_date_of_birth' => 'nullable|date',
            'blood_group' => 'nullable|string|max:10',
            'qualification' => 'nullable|string|max:120',
            'marital_status' => 'nullable|string|max:30',
            'applicant_phone' => ['nullable','required_if:member_source,new','regex:/^[6-9][0-9]{9}$/','unique:users,phone'],
            'applicant_email' => 'nullable|email|max:255|unique:users,email',
            'applicant_mpin' => 'nullable|required_if:member_source,new|digits:6',
            'applicant_address' => 'nullable|required_if:member_source,new|string|max:1000',
            'present_address' => 'nullable|string|max:1000',
            'pincode' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:150',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:25',
            'bank_branch' => 'nullable|string|max:120',
            'bank2_name' => 'nullable|string|max:150',
            'bank2_account_no' => 'nullable|string|max:50',
            'bank2_ifsc' => 'nullable|string|max:25',
            'bank2_branch' => 'nullable|string|max:120',
            'applicant_occupation' => 'nullable|string|max:120',
            'office_address' => 'nullable|string|max:1000',
            'designation_salary' => 'nullable|string|max:150',
            'applicant_aadhaar' => 'nullable|required_if:member_source,new|digits:12',
            'applicant_pan' => ['nullable','regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'relative_member_details' => 'nullable|string|max:255',
            'applicant_nominee' => 'nullable|required_if:member_source,new|string|max:120',
            'nominee_parent' => 'nullable|string|max:150',
            'nominee_relation' => 'nullable|string|max:80',
            'nominee_occupation' => 'nullable|string|max:120',
            'nominee_age' => 'nullable|integer|min:1|max:120',
            'nominee_address' => 'nullable|string|max:1000',
            'intro1_name' => 'nullable|string|max:150',
            'intro1_parent' => 'nullable|string|max:150',
            'intro1_account' => 'nullable|string|max:50',
            'intro1_lf' => 'nullable|string|max:50',
            'intro1_address' => 'nullable|string|max:1000',
            'intro2_name' => 'nullable|string|max:150',
            'intro2_parent' => 'nullable|string|max:150',
            'intro2_account' => 'nullable|string|max:50',
            'intro2_lf' => 'nullable|string|max:50',
            'intro2_address' => 'nullable|string|max:1000',
            'date_of_joining' => 'nullable|date',
            'account_opening_date' => 'nullable|date',
            'scheme_id' => 'required|exists:schemes,id',
            'opening_deposit' => 'required|numeric|min:1', 'share_count' => 'nullable|integer|min:1|max:100',
            'custom_interest_rate' => 'nullable|numeric|min:0|max:30',
            'tenure_months' => 'nullable|integer|min:1|max:600',
            'maturity_date' => 'nullable|date',
        ]);
        $isNewApplicant = $data['member_source'] === 'new';
        $member = null;
        if (! $isNewApplicant) {
            $member = DB::table('members')->join('users', 'users.id', '=', 'members.user_id')
                ->where('members.id', $data['member_id'])
                ->select('members.*', 'users.status as user_status')
                ->firstOrFail();
            abort_unless($member->user_status === 'active', 422, 'The member must be approved and active before an account can be created.');
            abort_unless($member->kyc_status === 'approved', 422, 'Approved KYC is required before an account can be created.');
        }

        $scheme = DB::table('schemes')->where('id', $data['scheme_id'])->where('active', true)->firstOrFail();
        $shares = (int) ($data['share_count'] ?? 1);
        $minimum = in_array($scheme->code, ['SAVINGS','RD-MONTHLY'], true) ? 500 * $shares : (float) $scheme->minimum_amount;
        abort_if((float) $data['opening_deposit'] < $minimum, 422, 'Opening deposit is below the required minimum.');
        $canApprove = ! $isNewApplicant && StaffPermissions::allows($request->user(), 'account.approve');
        $accountNo = $this->accountNumber();

        DB::transaction(function () use ($request, $data, $scheme, $shares, $canApprove, $accountNo, $isNewApplicant, $member) {
            $memberId = $member?->id;
            $memberNo = $member?->member_no;
            $joiningDate = !empty($data['date_of_joining']) ? Carbon::parse($data['date_of_joining']) : now();
            $accountOpeningDate = !empty($data['account_opening_date']) ? Carbon::parse($data['account_opening_date']) : $joiningDate;

            // Determine deposit tenure and maturity date
            $tenureMonths = !empty($data['tenure_months']) ? (int) $data['tenure_months'] : ($scheme->term_months ? (int) $scheme->term_months : null);
            $maturesOn = null;
            if (!empty($data['maturity_date'])) {
                $maturesOn = Carbon::parse($data['maturity_date'])->toDateString();
            } elseif ($tenureMonths) {
                $maturesOn = $accountOpeningDate->copy()->addMonthsNoOverflow($tenureMonths)->toDateString();
            }

            // Calculate estimated maturity amount if tenure and rate are present
            $rate = isset($data['custom_interest_rate']) && $data['custom_interest_rate'] !== null && $data['custom_interest_rate'] !== ''
                ? (float) $data['custom_interest_rate']
                : (float) $scheme->annual_rate;
            $maturityAmount = null;
            if ($tenureMonths && $rate > 0) {
                if ($scheme->type === 'fd') {
                    $calc = DepositCalculator::calculateFd((float)$data['opening_deposit'], $rate, $tenureMonths, $accountOpeningDate->toDateString());
                    $maturityAmount = $calc['maturity_amount'];
                } elseif ($scheme->type === 'rd') {
                    $totalPrincipal = (float)$data['opening_deposit'] * $tenureMonths;
                    $profit = round((float)$data['opening_deposit'] * (($tenureMonths * ($tenureMonths + 1)) / 24) * ($rate / 100), 2);
                    $maturityAmount = round($totalPrincipal + $profit, 2);
                } else {
                    $t = $tenureMonths / 12;
                    $maturityAmount = round((float)$data['opening_deposit'] * pow(1 + ($rate / 100), $t), 2);
                }
            }

            if ($isNewApplicant) {
                $user = User::create([
                    'name' => $data['applicant_name'], 'email' => $data['applicant_email'] ?? null,
                    'phone' => $data['applicant_phone'], 'password' => Hash::make(Str::random(48)),
                    'mpin_hash' => Hash::make($data['applicant_mpin']), 'role' => 'member', 'status' => 'pending',
                ]);
                DB::table('users')->where('id', $user->id)->update([
                    'created_at' => $joiningDate,
                    'updated_at' => $joiningDate,
                ]);

                $memberNo = 'MS-'.$joiningDate->format('Y').'-'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
                $memberData = [
                    'user_id' => $user->id,
                    'member_no' => $memberNo,
                    'address' => $data['applicant_address'],
                    'present_address' => $data['present_address'] ?? $data['applicant_address'],
                    'pincode' => $data['pincode'] ?? null,
                    'nominee_name' => $data['applicant_nominee'],
                    'nominee_parent' => $data['nominee_parent'] ?? null,
                    'nominee_relation' => $data['nominee_relation'] ?? null,
                    'nominee_occupation' => $data['nominee_occupation'] ?? null,
                    'nominee_age' => !empty($data['nominee_age']) ? (int)$data['nominee_age'] : null,
                    'nominee_address' => $data['nominee_address'] ?? null,
                    'parent_name' => $data['parent_name'] ?? null,
                    'mother_name' => $data['mother_name'] ?? null,
                    'father_name' => $data['father_name'] ?? null,
                    'age' => !empty($data['applicant_age']) ? (int)$data['applicant_age'] : null,
                    'blood_group' => $data['blood_group'] ?? null,
                    'qualification' => $data['qualification'] ?? null,
                    'marital_status' => $data['marital_status'] ?? null,
                    'bank_name' => $data['bank_name'] ?? null,
                    'bank_account_no' => $data['bank_account_no'] ?? null,
                    'bank_ifsc' => !empty($data['bank_ifsc']) ? strtoupper(trim($data['bank_ifsc'])) : null,
                    'bank_branch' => $data['bank_branch'] ?? null,
                    'bank2_name' => $data['bank2_name'] ?? null,
                    'bank2_account_no' => $data['bank2_account_no'] ?? null,
                    'bank2_ifsc' => !empty($data['bank2_ifsc']) ? strtoupper(trim($data['bank2_ifsc'])) : null,
                    'bank2_branch' => $data['bank2_branch'] ?? null,
                    'occupation' => $data['applicant_occupation'] ?? null,
                    'office_address' => $data['office_address'] ?? null,
                    'designation_salary' => $data['designation_salary'] ?? null,
                    'relative_member_details' => $data['relative_member_details'] ?? null,
                    'intro1_name' => $data['intro1_name'] ?? null,
                    'intro1_parent' => $data['intro1_parent'] ?? null,
                    'intro1_account' => $data['intro1_account'] ?? null,
                    'intro1_lf' => $data['intro1_lf'] ?? null,
                    'intro1_address' => $data['intro1_address'] ?? null,
                    'intro2_name' => $data['intro2_name'] ?? null,
                    'intro2_parent' => $data['intro2_parent'] ?? null,
                    'intro2_account' => $data['intro2_account'] ?? null,
                    'intro2_lf' => $data['intro2_lf'] ?? null,
                    'intro2_address' => $data['intro2_address'] ?? null,
                    'date_of_birth' => $data['applicant_date_of_birth'] ?? null,
                    'aadhaar_no' => !empty($data['applicant_aadhaar']) ? preg_replace('/\D/', '', $data['applicant_aadhaar']) : null,
                    'aadhaar_last4' => !empty($data['applicant_aadhaar']) ? substr(preg_replace('/\D/', '', $data['applicant_aadhaar']), -4) : null,
                    'pan_no' => !empty($data['applicant_pan']) ? strtoupper(trim($data['applicant_pan'])) : null,
                    'pan_last4' => !empty($data['applicant_pan']) ? substr(strtoupper(trim($data['applicant_pan'])), -4) : null,
                    'kyc_status' => 'not_submitted',
                    'mcs_score' => 500,
                    'created_at' => $joiningDate,
                    'updated_at' => $joiningDate,
                ];

                $existingCols = Schema::getColumnListing('members');
                $memberId = DB::table('members')->insertGetId(array_intersect_key($memberData, array_flip($existingCols)));

                $this->audit($request, 'member.created_by_staff', 'member', $memberId, null, [
                    'member_no' => $memberNo, 'name' => $data['applicant_name'],
                    'phone' => $data['applicant_phone'], 'status' => 'pending', 'kyc_status' => 'not_submitted',
                ]);
            }

            $id = DB::table('accounts')->insertGetId([
                'account_no' => $accountNo, 'member_id' => $memberId, 'scheme_id' => $data['scheme_id'],
                'share_count' => $shares, 'monthly_commitment' => in_array($scheme->code, ['SAVINGS','RD-MONTHLY'], true) ? 500 * $shares : 0,
                'principal' => $data['opening_deposit'], 'balance' => 0, 'custom_interest_rate' => $data['custom_interest_rate'] ?? null,
                'maturity_amount' => $maturityAmount,
                'status' => 'pending', 'onboarding_status' => 'awaiting_review',
                'opened_on' => $accountOpeningDate->toDateString(),
                'matures_on' => $maturesOn,
                'created_at' => $accountOpeningDate, 'updated_at' => $accountOpeningDate,
            ]);
            $account = DB::table('accounts')->where('id', $id)->firstOrFail();
            if ($canApprove) {
                $this->requestOpeningPayment($request, $account);
            }
            $this->audit($request, 'account.created_by_staff', 'account', $id, null, [
                'account_no' => $accountNo, 'member_id' => $memberId, 'member_no' => $memberNo,
                'scheme_id' => $data['scheme_id'], 'opening_deposit' => $data['opening_deposit'],
                'share_count' => $shares, 'custom_interest_rate' => $data['custom_interest_rate'] ?? null,
                'tenure_months' => $tenureMonths, 'matures_on' => $maturesOn, 'maturity_amount' => $maturityAmount,
                'onboarding_status' => $canApprove ? 'payment_required' : 'awaiting_review',
            ]);
        });

        if ($isNewApplicant) {
            return back()->with('ok', 'New applicant, member profile and account '.$accountNo.' created. Complete KYC and member approval before requesting payment.');
        }
        return back()->with('ok', $canApprove
            ? 'Real account '.$accountNo.' created and approved. The member can now pay by QR to activate it.'
            : 'Account '.$accountNo.' created and submitted for approval.');
    }

    public function accountDecision(Request $request, int $id)
    {
        $decision = $request->validate(['decision' => 'required|in:approve,reject', 'note' => 'nullable|string|max:1000']);
        $this->permit($request, $decision['decision'] === 'approve' ? 'account.approve' : 'account.reject');
        $account = DB::table('accounts')->where('id', $id)->first();
        if (!$account) {
            return back()->with('error', 'Account not found.');
        }
        if ($account->status !== 'pending' || $account->onboarding_status !== 'awaiting_review') {
            return back()->with('error', 'Account is not awaiting review.');
        }

        if ($decision['decision'] === 'approve') {
            $member = DB::table('members')->join('users', 'users.id', '=', 'members.user_id')
                ->where('members.id', $account->member_id)
                ->select('members.id as member_id', 'members.kyc_status', 'users.id as user_id', 'users.status as user_status')->first();

            if (!$member) {
                return back()->with('error', 'Associated member profile not found.');
            }

            $adminId = $request->user()?->id ?: 1;

            // Auto-activate member profile and KYC if not yet active/approved so the admin workflow is seamless
            if ($member->user_status !== 'active') {
                DB::table('users')->where('id', $member->user_id)->update([
                    'status' => 'active',
                    'updated_at' => now(),
                ]);
            }
            if ($member->kyc_status !== 'approved') {
                DB::table('members')->where('id', $member->member_id)->update([
                    'kyc_status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => $adminId,
                    'updated_at' => now(),
                ]);
            }
            DB::table('kyc_documents')->where('member_id', $member->member_id)->where('status', 'pending')->update([
                'status' => 'approved',
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);

            DB::transaction(fn () => $this->requestOpeningPayment($request, $account));
        } else {
            DB::table('accounts')->where('id', $id)->update([
                'status' => 'rejected',
                'onboarding_status' => 'rejected',
                'decision_note' => $decision['note'] ?? null,
                'updated_at' => now(),
            ]);
        }
        $this->audit($request, 'account.'.$decision['decision'], 'account', $id, $account, $decision);
        return back()->with('ok', $decision['decision'] === 'approve' ? 'Account approved; payment request sent to member' : 'Account rejected');
    }

    public function updateInterest(Request $request, int $id)
    {
        $this->permit($request, 'interest.manage');
        $data = $request->validate(['custom_interest_rate' => 'nullable|numeric|min:0|max:30']);
        $old = DB::table('accounts')->find($id);
        DB::table('accounts')->where('id', $id)->update(['custom_interest_rate' => $data['custom_interest_rate'] ?? null, 'updated_at' => now()]);
        $this->audit($request, 'account.interest_updated', 'account', $id, $old, $data);
        return back()->with('ok', 'Account interest rate updated');
    }

    public function paymentDecision(Request $request, int $id)
    {
        $this->permit($request, 'payment.accept');
        $data = $request->validate([
            'decision' => 'required|in:accepted,rejected',
            'note' => 'nullable|string|max:1000',
            'payment_mode' => 'nullable|in:cash,bank',
        ]);
        $payment = DB::table('payments')->where('id', $id)->firstOrFail();
        abort_unless(in_array($payment->status, ['submitted', 'requested']), 409, 'Payment is not awaiting verification.');

        $mode = $data['payment_mode'] ?? ($request->input('payment_mode') ?: 'cash');
        $location = $mode === 'cash' ? 'treasury' : 'bank';
        $adminId = $request->user()?->id ?: 1;

        DB::transaction(function () use ($request, $payment, $data, $mode, $location, $adminId) {
            DB::table('payments')->where('id', $payment->id)->update([
                'status' => $data['decision'],
                'review_note' => $data['note'] ?? ($mode === 'cash' ? 'Accepted in Cash at counter' : 'Accepted via Online Bank transfer'),
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);

            if ($data['decision'] === 'accepted') {
                $termMonths = (int) (DB::table('accounts')->join('schemes','schemes.id','=','accounts.scheme_id')
                    ->where('accounts.id',$payment->account_id)->value('schemes.term_months')
                    ?: DB::table('society_settings')->where('key','default_account_term_months')->value('value')
                    ?: 12);
                $openedOn = today();
                DB::table('accounts')->where('id', $payment->account_id)->update([
                    'status' => 'active',
                    'onboarding_status' => 'active',
                    'balance' => $payment->amount,
                    'opened_on' => $openedOn,
                    'matures_on' => $openedOn->copy()->addMonthsNoOverflow($termMonths)->subDay(),
                    'updated_at' => now(),
                ]);
                DB::table('ledger_entries')->insert([
                    'reference' => (string) Str::uuid(),
                    'account_id' => $payment->account_id,
                    'direction' => 'credit',
                    'amount' => $payment->amount,
                    'description' => 'Verified opening payment ('.strtoupper($mode).') '.$payment->reference,
                    'status' => 'posted',
                    'created_by' => $adminId,
                    'posted_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $account = DB::table('accounts')->where('id', $payment->account_id)->first();
                $receipt = ReceiptService::generate(
                    $account->member_id,
                    (float)$payment->amount,
                    'account_opening_deposit',
                    $mode,
                    $payment->account_id,
                    null,
                    $payment->reference,
                    'Verified opening payment ('.strtoupper($mode).') '.$payment->reference,
                    $adminId
                );
                FinanceLedger::cash(
                    $location,
                    'credit',
                    'member_deposit',
                    (float)$payment->amount,
                    'Verified opening payment ('.strtoupper($mode).') '.$payment->reference,
                    $adminId,
                    'payment',
                    $payment->id
                );
                MemberNotifier::send(
                    MemberNotifier::memberUserId($account->member_id),
                    'payment',
                    'Account activated',
                    'Your opening payment was verified ('.strtoupper($mode).') and account '.$account->account_no.' is active. Receipt: '.$receipt->receipt_no,
                    'opening-payment-accepted-'.$payment->id,
                    '/accounts'
                );
            } else {
                DB::table('accounts')->where('id', $payment->account_id)->update([
                    'onboarding_status' => 'payment_required',
                    'updated_at' => now()
                ]);
                $account = DB::table('accounts')->where('id', $payment->account_id)->first();
                MemberNotifier::send(
                    MemberNotifier::memberUserId($account->member_id),
                    'payment',
                    'Payment needs correction',
                    'The opening payment for '.$account->account_no.' was rejected. Check details and submit again.',
                    'opening-payment-rejected-'.$payment->id,
                    '/accounts'
                );
            }
        });

        $this->audit($request, 'payment.'.$data['decision'], 'payment', $id, $payment, array_merge($data, ['payment_mode' => $mode]));
        return back()->with('ok', $data['decision'] === 'accepted' ? 'Payment accepted as '.strtoupper($mode).' & account activated' : 'Payment rejected');
    }

    public function postTransaction(Request $request)
    {
        $this->permit($request, 'transaction.create');
        $data = $request->validate([
            'account_id' => 'required|exists:accounts,id', 'direction' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:1', 'description' => 'required|string|max:255',
        ]);
        $account = DB::table('accounts')->where('id', $data['account_id'])->where('status', 'active')->lockForUpdate()->first();
        abort_unless($account, 422, 'Only active accounts can receive transactions.');
        abort_if($data['direction'] === 'debit' && (float) $data['amount'] > (float) $account->balance, 422, 'Insufficient account balance.');
        DB::transaction(function () use ($request, $data, $account) {
            $balance = (float) $account->balance + ($data['direction'] === 'credit' ? (float) $data['amount'] : -(float) $data['amount']);
            DB::table('accounts')->where('id', $account->id)->update(['balance' => $balance, 'updated_at' => now()]);
            $id = DB::table('ledger_entries')->insertGetId([
                'reference' => (string) Str::uuid(), 'account_id' => $account->id, 'direction' => $data['direction'],
                'amount' => $data['amount'], 'description' => $data['description'], 'status' => 'posted',
                'created_by' => $request->user()->id, 'posted_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->audit($request, 'transaction.posted', 'ledger_entry', $id, null, $data);
        });
        CreditScore::recalculate($account->member_id);
        return back()->with('ok', 'Transaction posted');
    }

    public function loanDecision(Request $request, int $id)
    {
        $data = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'sanctioned_amount' => 'nullable|numeric|min:1',
            'annual_interest_rate' => 'nullable|numeric|min:0|max:36',
            'note' => 'required_if:decision,rejected|nullable|string|max:1000',
        ], [
            'note.required_if' => 'A rejection reason is mandatory when rejecting a loan application.',
        ]);
        $this->permit($request, $data['decision'] === 'approved' ? 'loan.approve' : 'loan.reject');
        $loan = DB::table('loan_applications')->where('id', $id)->firstOrFail();
        abort_unless(in_array($loan->status, ['submitted','under_review'], true), 409);
        $savings = (float) DB::table('accounts')->where('member_id', $loan->member_id)->where('status', 'active')->sum('balance');
        $maximum = round($savings * .85, 2);
        $sanctioned = $data['decision'] === 'approved' ? (float) ($data['sanctioned_amount'] ?? $loan->amount) : null;
        abort_if($sanctioned !== null && $sanctioned > $maximum, 422, 'Sanctioned amount cannot exceed 85% of savings (₹'.number_format($maximum, 2).').');
        $after = [
            'status' => $data['decision'], 'sanctioned_amount' => $sanctioned,
            'decision_note' => $data['note'] ?? null, 'decided_by' => $request->user()->id,
            'decided_at' => now(), 'updated_at' => now(),
        ];
        DB::table('loan_applications')->where('id', $id)->update($after);
        if ($data['decision']==='approved') {
            $rate = (float)($data['annual_interest_rate'] ?? LoanSchedule::defaultRate($loan->loan_type));
            LoanSchedule::generate($id,$sanctioned,$rate);
        }
        CreditScore::recalculate($loan->member_id);
        MemberNotifier::send(MemberNotifier::memberUserId($loan->member_id),'loan_decision',
            $data['decision']==='approved'?'Loan approved':'Loan rejected',
            $data['decision']==='approved'
                ? 'Loan '.$loan->application_no.' was approved for ₹'.number_format($sanctioned,2).'. Your EMI schedule is ready.'
                : 'Loan '.$loan->application_no.' was rejected.'.(($data['note']??null)?' '.$data['note']:''),
            'loan-decision-'.$id.'-'.$data['decision'],'/loan');
        $this->audit($request, 'loan.'.$data['decision'], 'loan_application', $id, $loan, $after);
        return back()->with('ok', 'Loan decision saved');
    }

    public function repaymentDecision(Request $request, int $id)
    {
        $this->permit($request,'loan.repayment');
        $data = $request->validate(['decision'=>'required|in:accepted,rejected','note'=>'nullable|string|max:1000']);
        $repayment = DB::table('loan_repayments')->where('id',$id)->firstOrFail();
        abort_unless($repayment->status==='submitted',409,'Repayment is not awaiting review.');
        $loan = DB::table('loan_applications')->where('id',$repayment->loan_id)->firstOrFail();
        $allocation = DB::transaction(function () use ($request,$repayment,$data,$loan) {
            DB::table('loan_repayments')->where('id',$repayment->id)->update([
                'status'=>$data['decision'],'review_note'=>$data['note']??null,'reviewed_by'=>$request->user()->id,
                'reviewed_at'=>now(),'updated_at'=>now(),
            ]);
            if ($data['decision'] !== 'accepted') return null;
            $allocation = LoanSchedule::applyRepayment($loan->id,(float)$repayment->amount);
            DB::table('loan_repayments')->where('id',$repayment->id)->update([
                'principal_component'=>$allocation['principal'],
                'interest_component'=>$allocation['interest'],
                'penalty_component'=>$allocation['penalty'],
            ]);
            if ($allocation['principal'] > 0) {
                FinanceLedger::cash('bank','credit','loan_principal_recovery',$allocation['principal'],
                    'Principal repayment '.$repayment->reference,$request->user()->id,'loan_repayment',$repayment->id);
            }
            $income = $allocation['interest'] + $allocation['penalty'];
            if ($income > 0) {
                FinanceLedger::cash('bank','credit','loan_income',$income,
                    'Interest and fine income '.$repayment->reference,$request->user()->id,'loan_repayment',$repayment->id);
                FinanceLedger::profitLoss('income','loan_interest_and_fines',$income,
                    'Recognized from repayment '.$repayment->reference,$request->user()->id,'loan_repayment',$repayment->id);
            }
            if ($data['decision'] === 'accepted') {
                ReceiptService::generate(
                    $loan->member_id,
                    (float)$repayment->amount,
                    'emi_payment',
                    $repayment->payment_mode ?? 'bank',
                    null,
                    $loan->id,
                    $repayment->reference,
                    "Repayment accepted. Principal: ₹{$allocation['principal']}, Interest: ₹{$allocation['interest']}, Penalty: ₹{$allocation['penalty']}",
                    $request->user()->id
                );
            }
            return $allocation;
        });
        CreditScore::recalculate($loan->member_id);
        MemberNotifier::send(MemberNotifier::memberUserId($loan->member_id),'loan_repayment',
            $data['decision']==='accepted'?'Repayment accepted':'Repayment rejected',
            'Repayment '.$repayment->reference.' for ₹'.number_format($repayment->amount,2).' was '.$data['decision'].'.',
            'repayment-'.$repayment->id.'-'.$data['decision'],'/loan');
        $this->audit($request,'loan.repayment_'.$data['decision'],'loan_repayment',$id,$repayment,$data);
        return back()->with('ok','Repayment decision saved');
    }

    public function runFinancialAutomation(Request $request)
    {
        $this->permit($request,'interest.manage');
        $result = FinancialAutomation::run();
        $this->audit($request,'finance.automation_run','system',null,null,$result);
        return back()->with('ok','Financial automation completed: '.json_encode($result));
    }

    public function cashEntry(Request $request)
    {
        $this->permit($request, 'cash.manage');
        $data = $request->validate([
            'action' => 'required|in:credit,debit,transfer',
            'location' => 'required|in:bank,treasury',
            'destination_location' => 'nullable|required_if:action,transfer|in:bank,treasury|different:location',
            'category' => 'required_unless:action,transfer|string|max:60|regex:/^[a-z0-9_ -]+$/i',
            'amount' => 'required|numeric|min:0.01|max:1000000000',
            'profit_loss_class' => 'nullable|in:none,income,expense',
            'note' => 'required|string|max:1000',
            'occurred_on' => 'required|date',
        ]);
        $amount = (float) $data['amount'];
        DB::transaction(function () use ($request, $data, $amount) {
            if ($data['action'] === 'transfer') {
                abort_if(FinanceLedger::cashBalance($data['location']) < $amount, 422, 'Insufficient cash at the source location.');
                FinanceLedger::cash($data['location'],'debit','transfer',$amount,$data['note'],$request->user()->id,'cash_transfer',null,$data['occurred_on']);
                FinanceLedger::cash($data['destination_location'],'credit','transfer',$amount,$data['note'],$request->user()->id,'cash_transfer',null,$data['occurred_on']);
                return;
            }
            if ($data['action'] === 'debit') {
                abort_if(FinanceLedger::cashBalance($data['location']) < $amount, 422, 'Insufficient cash at this location.');
            }
            $cashId = FinanceLedger::cash(
                $data['location'], $data['action'], Str::slug($data['category'],'_'), $amount,
                $data['note'], $request->user()->id, 'manual_cash', null, $data['occurred_on']
            );
            $class = $data['profit_loss_class'] ?? 'none';
            if ($class !== 'none') {
                FinanceLedger::profitLoss($class, Str::slug($data['category'],'_'), $amount, $data['note'],
                    $request->user()->id, 'cash_ledger_entry', $cashId, $data['occurred_on']);
            }
        });
        $this->audit($request, 'cash.entry_posted', 'cash_ledger_entry', null, null, $data);
        return back()->with('ok', 'Cash entry posted');
    }

    public function profitLossEntry(Request $request)
    {
        $this->permit($request, 'profit_loss.manage');
        $data = $request->validate([
            'entry_type' => 'required|in:income,expense',
            'category' => 'required|string|max:60|regex:/^[a-z0-9_ -]+$/i',
            'amount' => 'required|numeric|min:0.01|max:1000000000',
            'note' => 'required|string|max:1000',
            'occurred_on' => 'required|date',
        ]);
        $id = FinanceLedger::profitLoss(
            $data['entry_type'], Str::slug($data['category'],'_'), (float)$data['amount'],
            $data['note'], $request->user()->id, 'manual_adjustment', null, $data['occurred_on']
        );
        $this->audit($request, 'profit_loss.entry_posted', 'profit_loss_entry', $id, null, $data);
        return back()->with('ok', 'Profit/loss entry posted');
    }

    public function applyInstallmentFine(Request $request, int $id)
    {
        $this->permit($request, 'loan.fine');
        $data = $request->validate([
            'penalty_amount' => 'required|numeric|min:0|max:1000000',
            'note' => 'nullable|string|max:1000',
        ]);
        $installment = DB::table('loan_installments')->where('id',$id)->firstOrFail();
        abort_unless(Carbon::parse($installment->due_on)->isBefore(today()) || $installment->status === 'overdue', 422, 'A fine can only be set after the EMI due date.');
        $before = ['penalty_due'=>$installment->penalty_due,'penalty_note'=>$installment->penalty_note];
        DB::table('loan_installments')->where('id',$id)->update([
            'penalty_due'=>$data['penalty_amount'],
            'penalty_note'=>$data['note'] ?? null,
            'penalty_applied_by'=>$request->user()->id,
            'penalty_applied_at'=>now(),
            'status'=>'overdue',
            'updated_at'=>now(),
        ]);
        $loan = DB::table('loan_applications')->where('id',$installment->loan_id)->first();
        $amount = (float)$data['penalty_amount'];
        MemberNotifier::send(MemberNotifier::memberUserId($loan->member_id),'loan_fine',
            $amount > 0 ? 'EMI fine updated' : 'EMI fine waived',
            $amount > 0
                ? 'A fine of ₹'.number_format($amount,2).' applies to installment '.$installment->installment_no.'. '.($data['note'] ?? '')
                : 'The fine for installment '.$installment->installment_no.' was waived.',
            'installment-fine-'.$id.'-'.now()->timestamp,'/loan');
        $this->audit($request,'loan.fine_updated','loan_installment',$id,$before,$data);
        return back()->with('ok', $amount > 0 ? 'Custom EMI fine applied' : 'EMI fine waived');
    }

    public function updateAccountMaturity(Request $request, int $id)
    {
        $this->permit($request, 'account.close');
        $data = $request->validate(['matures_on'=>'required|date']);
        $account = DB::table('accounts')->where('id',$id)->where('status','active')->firstOrFail();
        abort_if($account->opened_on && Carbon::parse($data['matures_on'])->isBefore(Carbon::parse($account->opened_on)), 422, 'Maturity cannot be before the opening date.');
        DB::table('accounts')->where('id',$id)->update(['matures_on'=>$data['matures_on'],'updated_at'=>now()]);
        $this->audit($request,'account.maturity_updated','account',$id,['matures_on'=>$account->matures_on],$data);
        return back()->with('ok','Account maturity date updated');
    }

    public function accountClosure(Request $request, int $id)
    {
        $this->permit($request, 'account.close');
        $data = $request->validate([
            'action'=>'required|in:request,reject,complete',
            'note'=>'nullable|string|max:1000',
            'payout_location'=>'nullable|required_if:action,complete|in:bank,treasury',
        ]);
        $account = DB::table('accounts')->where('id',$id)->lockForUpdate()->firstOrFail();
        if ($data['action'] === 'request') {
            abort_unless($account->status === 'active', 422, 'Only active accounts can enter closure.');
            abort_unless($account->matures_on && !Carbon::parse($account->matures_on)->isFuture(), 422, 'The account has not reached maturity.');
            abort_if(DB::table('account_closures')->where('account_id',$id)->whereIn('status',['notice_period','withdrawal_available'])->exists(), 409, 'A closure request is already open.');
            $days = max(0, (int)(DB::table('society_settings')->where('key','closure_notice_days')->value('value') ?: 30));
            $closureId = DB::table('account_closures')->insertGetId([
                'account_id'=>$id,'status'=>$days === 0 ? 'withdrawal_available' : 'notice_period',
                'requested_on'=>today(),'maturity_on'=>$account->matures_on,
                'withdrawal_available_on'=>today()->addDays($days),
                'note'=>$data['note'] ?? null,'requested_by'=>$request->user()->id,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
            MemberNotifier::send(MemberNotifier::memberUserId($account->member_id),'account_closure',
                'Account closure notice started',
                'Closure for '.$account->account_no.' has started. Withdrawal becomes available on '.today()->addDays($days)->format('d M Y').'.',
                'account-closure-requested-'.$closureId,'/accounts');
            $this->audit($request,'account.closure_requested','account_closure',$closureId,null,$data);
            return back()->with('ok','Closure notice started. Withdrawal activates after '.$days.' day(s).');
        }
        $closure = DB::table('account_closures')->where('account_id',$id)
            ->whereIn('status',['notice_period','withdrawal_available'])->latest('id')->lockForUpdate()->firstOrFail();
        if ($data['action'] === 'reject') {
            DB::table('account_closures')->where('id',$closure->id)->update([
                'status'=>'rejected','note'=>$data['note'] ?? $closure->note,
                'reviewed_by'=>$request->user()->id,'reviewed_at'=>now(),'updated_at'=>now(),
            ]);
            MemberNotifier::send(MemberNotifier::memberUserId($account->member_id),'account_closure',
                'Account closure rejected','The closure request for '.$account->account_no.' was rejected. '.($data['note']??''),
                'account-closure-rejected-'.$closure->id,'/accounts');
            $this->audit($request,'account.closure_rejected','account_closure',$closure->id,$closure,$data);
            return back()->with('ok','Closure request rejected');
        }
        abort_unless(!Carbon::parse($closure->withdrawal_available_on)->isFuture(), 422, 'The 30-day withdrawal notice is still active.');
        $amount = (float)$account->balance;
        abort_if(FinanceLedger::cashBalance($data['payout_location']) < $amount, 422, 'Insufficient cash at the selected payout location.');
        DB::transaction(function () use ($request,$account,$closure,$amount,$data) {
            if ($amount > 0) {
                DB::table('ledger_entries')->insert([
                    'reference'=>(string)Str::uuid(),'account_id'=>$account->id,'direction'=>'debit','amount'=>$amount,
                    'description'=>'Account closure withdrawal','status'=>'posted','created_by'=>$request->user()->id,
                    'posted_at'=>now(),'created_at'=>now(),'updated_at'=>now(),
                ]);
                FinanceLedger::cash($data['payout_location'],'debit','account_closure',$amount,
                    'Closure payout '.$account->account_no,$request->user()->id,'account_closure',$closure->id);
            }
            DB::table('accounts')->where('id',$account->id)->update(['balance'=>0,'status'=>'closed','updated_at'=>now()]);
            DB::table('account_closures')->where('id',$closure->id)->update([
                'status'=>'completed','closure_amount'=>$amount,'reviewed_by'=>$request->user()->id,
                'reviewed_at'=>now(),'completed_by'=>$request->user()->id,'completed_at'=>now(),'updated_at'=>now(),
            ]);
        });
        MemberNotifier::send(MemberNotifier::memberUserId($account->member_id),'account_closure',
            'Account closed','Account '.$account->account_no.' was closed and ₹'.number_format($amount,2).' was released.',
            'account-closure-completed-'.$closure->id,'/accounts');
        $this->audit($request,'account.closure_completed','account_closure',$closure->id,$closure,['amount'=>$amount]+$data);
        return back()->with('ok','Account closed and withdrawal posted');
    }

    public function viewClosureCertificate(int $id)
    {
        $closure = DB::table('account_closures')
            ->join('accounts', 'accounts.id', '=', 'account_closures.account_id')
            ->leftJoin('schemes', 'schemes.id', '=', 'accounts.scheme_id')
            ->join('members', 'members.id', '=', 'accounts.member_id')
            ->join('users', 'users.id', '=', 'members.user_id')
            ->where('account_closures.id', $id)
            ->select('account_closures.*', 'accounts.account_no', 'accounts.balance', 'accounts.opened_on', 'schemes.name as scheme_name', 'users.name', 'users.phone', 'users.email', 'members.member_no', 'members.address')
            ->firstOrFail();

        return view('admin.closure-certificate', ['closure' => $closure]);
    }

    public function downloadClosureCertificate(int $id)
    {
        $closure = DB::table('account_closures')
            ->join('accounts', 'accounts.id', '=', 'account_closures.account_id')
            ->leftJoin('schemes', 'schemes.id', '=', 'accounts.scheme_id')
            ->join('members', 'members.id', '=', 'accounts.member_id')
            ->join('users', 'users.id', '=', 'members.user_id')
            ->where('account_closures.id', $id)
            ->select('account_closures.*', 'accounts.account_no', 'accounts.balance', 'accounts.opened_on', 'schemes.name as scheme_name', 'users.name', 'users.phone', 'users.email', 'members.member_no', 'members.address')
            ->firstOrFail();

        $html = view('admin.closure-certificate', ['closure' => $closure])->render();
        $safeNo = str_replace(['/', '\\', ' '], '-', $closure->account_no);
        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => "attachment; filename=\"Closure-Certificate-{$safeNo}.html\"",
        ]);
    }

    public function updateFinanceSettings(Request $request)
    {
        abort_unless($request->user()->role === 'super_admin', 403);
        $data = $request->validate([
            'default_account_term_months'=>'required|integer|min:1|max:120',
            'closure_notice_days'=>'required|integer|min:0|max:365',
            'default_overdue_fine_amount'=>'required|numeric|min:0|max:1000000',
        ]);
        foreach ($data as $key=>$value) {
            DB::table('society_settings')->updateOrInsert(['key'=>$key],[
                'value'=>(string)$value,'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        $this->audit($request,'settings.finance_updated','society_settings',null,null,$data);
        return back()->with('ok','Finance and closure settings updated');
    }

    public function exportReport(Request $request, string $type)
    {
        $this->permit($request, 'reports.view');
        abort_unless(in_array($type, ['members', 'accounts', 'loans', 'transactions', 'cash', 'profit-loss', 'receipts'], true), 404);

        $reportData = $this->queryReportData($request, $type);
        $headers = $reportData['headers'];
        $rows = $reportData['rows'];
        $filename = 'mahamilon-' . $type . '-' . today()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                $values = array_values((array) $row);
                $values = array_map(fn($value) => is_string($value) && preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value, $values);
                fputcsv($out, $values);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function printReport(Request $request)
    {
        $this->permit($request, 'reports.view');
        $type = $request->query('report_type', 'transactions');
        abort_unless(in_array($type, ['members', 'accounts', 'loans', 'transactions', 'cash', 'profit-loss', 'receipts'], true), 404);

        $reportData = $this->queryReportData($request, $type);

        $activeMemberName = !empty($reportData['filters']['member_id'])
            ? DB::table('users')->join('members', 'members.user_id', '=', 'users.id')->where('members.id', $reportData['filters']['member_id'])->value('users.name')
            : null;

        $activeStaffName = !empty($reportData['filters']['staff_id'])
            ? DB::table('users')->where('id', $reportData['filters']['staff_id'])->value('name')
            : null;

        return view('admin.report-print', [
            'reportTitle' => $reportData['title'],
            'type' => $type,
            'headers' => $reportData['headers'],
            'rows' => $reportData['rows'],
            'totalRecords' => $reportData['totalRecords'],
            'totalAmount' => $reportData['totalAmount'],
            'avgAmount' => $reportData['avgAmount'],
            'filters' => $reportData['filters'],
            'activeMemberName' => $activeMemberName,
            'activeStaffName' => $activeStaffName,
        ]);
    }

    public function queryReportData(Request $request, string $type): array
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $memberId = $request->query('member_id');
        $staffId = $request->query('staff_id');
        $paymentMode = $request->query('payment_mode');
        $status = $request->query('status');
        $minAmount = $request->query('amount_min');
        $maxAmount = $request->query('amount_max');

        $filters = [
            'from' => $from,
            'to' => $to,
            'member_id' => $memberId,
            'staff_id' => $staffId,
            'payment_mode' => $paymentMode,
            'status' => $status,
            'amount_min' => $minAmount,
            'amount_max' => $maxAmount,
        ];

        $title = match ($type) {
            'members' => 'Member Registry & KYC Status Report',
            'accounts' => 'Member Accounts & Balances Report',
            'loans' => 'Loans, Approvals & Outstanding Report',
            'receipts' => 'Payment Receipts & Collections Report',
            'cash' => 'Cash & Vault Movement Ledger Report',
            'profit-loss' => 'Profit & Loss (Income & Expense) Report',
            default => 'Account Transactions & Ledger Movement Report',
        };

        [$headers, $rows, $totalAmount] = match ($type) {
            'members' => (function () use ($memberId, $status) {
                $q = DB::table('members')->join('users', 'users.id', '=', 'members.user_id')
                    ->when($memberId, fn($query) => $query->where('members.id', $memberId))
                    ->when($status && $status !== 'all', fn($query) => $query->where('users.status', $status))
                    ->select('members.member_no', 'users.name', 'users.phone', 'users.status', 'members.kyc_status', 'members.mcs_score')
                    ->selectRaw('(SELECT COUNT(*) FROM accounts WHERE accounts.member_id=members.id) as account_count')
                    ->selectRaw("(SELECT COALESCE(SUM(balance),0) FROM accounts WHERE accounts.member_id=members.id AND accounts.status='active') as account_balance")
                    ->selectRaw('(SELECT COUNT(*) FROM loan_applications WHERE loan_applications.member_id=members.id) as loan_count')
                    ->selectRaw("(SELECT COALESCE(SUM(outstanding_principal),0) FROM loan_applications WHERE loan_applications.member_id=members.id AND loan_applications.status IN ('approved','disbursed')) as loan_outstanding")
                    ->orderBy('users.name')->get();
                $tot = (float) $q->sum('account_balance');
                return [
                    ['Member No', 'Name', 'Phone', 'Status', 'KYC', 'MCS', 'Accounts', 'Active Balance (INR)', 'Loans', 'Loan Outstanding (INR)'],
                    $q,
                    $tot
                ];
            })(),
            'accounts' => (function () use ($memberId, $status, $minAmount, $maxAmount) {
                $q = DB::table('accounts')->join('members', 'members.id', '=', 'accounts.member_id')
                    ->join('users', 'users.id', '=', 'members.user_id')
                    ->join('schemes', 'schemes.id', '=', 'accounts.scheme_id')
                    ->when($memberId, fn($query) => $query->where('accounts.member_id', $memberId))
                    ->when($status && $status !== 'all', fn($query) => $query->where('accounts.status', $status))
                    ->when($minAmount, fn($query) => $query->where('accounts.balance', '>=', (float) $minAmount))
                    ->when($maxAmount, fn($query) => $query->where('accounts.balance', '<=', (float) $maxAmount))
                    ->select('accounts.account_no', 'users.name', 'schemes.name as scheme', 'accounts.status', 'accounts.opened_on', 'accounts.matures_on', 'accounts.balance')
                    ->selectRaw('COALESCE(accounts.custom_interest_rate, schemes.annual_rate) as rate')
                    ->orderBy('accounts.id')->get();
                $tot = (float) $q->sum('balance');
                return [
                    ['Account No', 'Member', 'Scheme', 'Status', 'Opened On', 'Matures On', 'Balance (INR)', 'Rate %'],
                    $q,
                    $tot
                ];
            })(),
            'loans' => (function () use ($from, $to, $memberId, $status, $minAmount, $maxAmount) {
                $q = DB::table('loan_applications')->join('members', 'members.id', '=', 'loan_applications.member_id')
                    ->join('users', 'users.id', '=', 'members.user_id')
                    ->when($from, fn($query) => $query->whereDate('loan_applications.created_at', '>=', $from))
                    ->when($to, fn($query) => $query->whereDate('loan_applications.created_at', '<=', $to))
                    ->when($memberId, fn($query) => $query->where('loan_applications.member_id', $memberId))
                    ->when($status && $status !== 'all', fn($query) => $query->where('loan_applications.status', $status))
                    ->when($minAmount, fn($query) => $query->where('loan_applications.amount', '>=', (float) $minAmount))
                    ->when($maxAmount, fn($query) => $query->where('loan_applications.amount', '<=', (float) $maxAmount))
                    ->select('loan_applications.application_no', 'users.name', 'loan_applications.loan_type', 'loan_applications.emi_frequency',
                        'loan_applications.amount', 'loan_applications.sanctioned_amount', 'loan_applications.outstanding_principal',
                        'loan_applications.annual_interest_rate', 'loan_applications.status', 'loan_applications.created_at')
                    ->orderBy('loan_applications.id')->get();
                $tot = (float) $q->sum('outstanding_principal');
                return [
                    ['Application No', 'Member', 'Loan Type', 'EMI Freq', 'Requested (INR)', 'Sanctioned (INR)', 'Outstanding (INR)', 'Rate %', 'Status', 'Applied On'],
                    $q,
                    $tot
                ];
            })(),
            'receipts' => (function () use ($from, $to, $memberId, $staffId, $paymentMode, $minAmount, $maxAmount) {
                $q = DB::table('receipts')->join('members', 'members.id', '=', 'receipts.member_id')
                    ->join('users', 'users.id', '=', 'members.user_id')
                    ->leftJoin('users as staff', 'staff.id', '=', 'receipts.issued_by')
                    ->when($from, fn($query) => $query->whereDate('receipts.created_at', '>=', $from))
                    ->when($to, fn($query) => $query->whereDate('receipts.created_at', '<=', $to))
                    ->when($memberId, fn($query) => $query->where('receipts.member_id', $memberId))
                    ->when($staffId, fn($query) => $query->where('receipts.issued_by', $staffId))
                    ->when($paymentMode && $paymentMode !== 'all', fn($query) => $query->where('receipts.payment_mode', $paymentMode))
                    ->when($minAmount, fn($query) => $query->where('receipts.amount', '>=', (float) $minAmount))
                    ->when($maxAmount, fn($query) => $query->where('receipts.amount', '<=', (float) $maxAmount))
                    ->select('receipts.receipt_no', 'users.name as member_name', 'receipts.category', 'receipts.payment_mode',
                        'receipts.amount', 'receipts.transaction_reference', 'receipts.created_at', 'staff.name as issued_by_name')
                    ->orderByDesc('receipts.id')->get();
                $tot = (float) $q->sum('amount');
                return [
                    ['Receipt No', 'Member', 'Category', 'Mode', 'Amount (INR)', 'Reference', 'Date & Time', 'Issued By'],
                    $q,
                    $tot
                ];
            })(),
            'cash' => (function () use ($from, $to, $minAmount, $maxAmount) {
                $q = DB::table('cash_ledger_entries')
                    ->when($from, fn($query) => $query->whereDate('occurred_on', '>=', $from))
                    ->when($to, fn($query) => $query->whereDate('occurred_on', '<=', $to))
                    ->when($minAmount, fn($query) => $query->where('amount', '>=', (float) $minAmount))
                    ->when($maxAmount, fn($query) => $query->where('amount', '<=', (float) $maxAmount))
                    ->select('reference', 'location', 'direction', 'category', 'amount', 'note', 'occurred_on')
                    ->orderByDesc('id')->get();
                $tot = (float) $q->sum('amount');
                return [
                    ['Reference', 'Location', 'Direction', 'Category', 'Amount (INR)', 'Note', 'Date'],
                    $q,
                    $tot
                ];
            })(),
            'profit-loss' => (function () use ($from, $to, $status, $minAmount, $maxAmount) {
                $q = DB::table('profit_loss_entries')
                    ->when($from, fn($query) => $query->whereDate('occurred_on', '>=', $from))
                    ->when($to, fn($query) => $query->whereDate('occurred_on', '<=', $to))
                    ->when($status && $status !== 'all', fn($query) => $query->where('entry_type', $status))
                    ->when($minAmount, fn($query) => $query->where('amount', '>=', (float) $minAmount))
                    ->when($maxAmount, fn($query) => $query->where('amount', '<=', (float) $maxAmount))
                    ->select('reference', 'entry_type', 'category', 'amount', 'note', 'occurred_on')
                    ->orderByDesc('id')->get();
                $tot = (float) $q->sum('amount');
                return [
                    ['Reference', 'Type', 'Category', 'Amount (INR)', 'Note', 'Date'],
                    $q,
                    $tot
                ];
            })(),
            default => (function () use ($from, $to, $memberId, $staffId, $status, $minAmount, $maxAmount) {
                $q = DB::table('ledger_entries')->join('accounts', 'accounts.id', '=', 'ledger_entries.account_id')
                    ->join('members', 'members.id', '=', 'accounts.member_id')
                    ->join('users', 'users.id', '=', 'members.user_id')
                    ->when($from, fn($query) => $query->whereDate('ledger_entries.posted_at', '>=', $from))
                    ->when($to, fn($query) => $query->whereDate('ledger_entries.posted_at', '<=', $to))
                    ->when($memberId, fn($query) => $query->where('members.id', $memberId))
                    ->when($staffId, fn($query) => $query->where('ledger_entries.created_by', $staffId))
                    ->when($status && $status !== 'all', fn($query) => $query->where('ledger_entries.status', $status))
                    ->when($minAmount, fn($query) => $query->where('ledger_entries.amount', '>=', (float) $minAmount))
                    ->when($maxAmount, fn($query) => $query->where('ledger_entries.amount', '<=', (float) $maxAmount))
                    ->select('ledger_entries.reference', 'users.name as member_name', 'accounts.account_no', 'ledger_entries.direction',
                        'ledger_entries.amount', 'ledger_entries.description', 'ledger_entries.status', 'ledger_entries.posted_at')
                    ->orderByDesc('ledger_entries.id')->get();
                $tot = (float) $q->sum('amount');
                return [
                    ['Reference', 'Member', 'Account No', 'Direction', 'Amount (INR)', 'Description', 'Status', 'Posted At'],
                    $q,
                    $tot
                ];
            })(),
        };

        $totalRecords = count($rows);
        $avgAmount = $totalRecords > 0 ? round($totalAmount / $totalRecords, 2) : 0.0;

        return [
            'title' => $title,
            'type' => $type,
            'headers' => $headers,
            'rows' => $rows,
            'totalRecords' => $totalRecords,
            'totalAmount' => $totalAmount,
            'avgAmount' => $avgAmount,
            'filters' => $filters,
        ];
    }

    public function createLoanOffer(Request $request)
    {
        $this->permit($request, 'loan.offer');
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id', 'loan_type' => 'required|in:personal,business,emergency',
            'emi_frequency' => 'required|in:daily,weekly,monthly', 'amount' => 'required|numeric|min:1000',
            'tenure_months' => 'required|integer|min:1|max:120', 'purpose' => 'required|string|max:1000',
            'offer_days' => 'required|integer|min:1|max:90',
        ]);
        if ($validator->fails()) {
            return redirect('/admin#loans')->withErrors($validator, 'loanOffer')->withInput();
        }
        $data = $validator->validated();
        $member = DB::table('members')->find($data['member_id']);
        $savings = (float) DB::table('accounts')->where('member_id', $member->id)->where('status', 'active')->sum('balance');
        $maximumOffer = round($savings * .85, 2);
        if ((float) $data['amount'] > $maximumOffer) {
            return redirect('/admin#loans')->withErrors([
                'amount' => 'Offer amount cannot exceed ₹'.number_format($maximumOffer, 2).' (85% of this member\'s active savings of ₹'.number_format($savings, 2).').',
            ], 'loanOffer')->withInput();
        }
        $score = CreditScore::recalculate($member->id);
        $id = DB::table('loan_applications')->insertGetId([
            'application_no' => 'LO'.now()->format('ymd').str_pad((string) random_int(1,999999),6,'0',STR_PAD_LEFT),
            'member_id' => $member->id, 'loan_type' => $data['loan_type'], 'emi_frequency' => $data['emi_frequency'],
            'amount' => $data['amount'], 'monthly_income' => 0, 'tenure_months' => $data['tenure_months'],
            'purpose' => $data['purpose'], 'mcs_snapshot' => $score, 'is_offer' => true,
            'offer_expires_at' => now()->addDays($data['offer_days']), 'status' => 'submitted',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->audit($request, 'loan.offer_created', 'loan_application', $id, null, $data);
        return back()->with('ok', 'Individual loan offer created');
    }

    public function createLoanApplication(Request $request)
    {
        $this->staff($request);
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'loan_type' => 'required|in:personal,business,emergency',
            'amount' => 'required|numeric|min:1000',
            'tenure_months' => 'required|integer|min:1|max:120',
            'emi_frequency' => 'nullable|in:daily,weekly,monthly',
            'purpose' => 'required|string|max:1000',
            'guarantor_name' => 'nullable|string|max:150',
            'guarantor_phone' => 'nullable|string|max:30',
            'guarantor_relation' => 'nullable|string|max:80',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
        if ($validator->fails()) {
            return redirect('/admin#loans')->withErrors($validator, 'loanApply')->withInput();
        }
        $data = $validator->validated();

        $savings = (float) DB::table('accounts')->where('member_id', $data['member_id'])->where('status', 'active')->sum('balance');
        $maximumOffer = round($savings * 0.85, 2);
        if ($data['loan_type'] !== 'emergency' && (float) $data['amount'] > $maximumOffer) {
            return redirect('/admin#loans')->withErrors([
                'amount' => 'Requested loan amount cannot exceed ₹' . number_format($maximumOffer, 2) . ' (85% of active savings of ₹' . number_format($savings, 2) . ').',
            ], 'loanApply')->withInput();
        }

        $docPath = null;
        if ($request->hasFile('document')) {
            $docPath = $request->file('document')->store('loan-documents', 'private');
        }

        $appNo = SequenceService::nextLoanApplicationNo();
        $score = CreditScore::recalculate((int)$data['member_id']);

        $id = DB::table('loan_applications')->insertGetId([
            'application_no' => $appNo,
            'member_id' => $data['member_id'],
            'loan_type' => $data['loan_type'],
            'emi_frequency' => $data['emi_frequency'] ?? 'monthly',
            'amount' => $data['amount'],
            'monthly_income' => 0,
            'tenure_months' => $data['tenure_months'],
            'purpose' => $data['purpose'],
            'guarantor_name' => $data['guarantor_name'] ?? null,
            'guarantor_phone' => $data['guarantor_phone'] ?? null,
            'guarantor_relation' => $data['guarantor_relation'] ?? null,
            'document_path' => $docPath,
            'mcs_snapshot' => $score,
            'status' => 'submitted', // Initial status: Pending
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->audit($request, 'loan.applied', 'loan_application', $id, null, $data);
        return back()->with('ok', "Loan application {$appNo} submitted successfully. Initial status: Pending verification.");
    }

    public function verifyLoanApplication(Request $request, int $id)
    {
        $this->staff($request);
        $data = $request->validate([
            'decision' => 'required|in:verified,rejected',
            'verification_note' => 'required_if:decision,rejected|nullable|string|max:1000',
        ], [
            'verification_note.required_if' => 'A rejection reason is mandatory when rejecting loan verification.',
        ]);
        $loan = DB::table('loan_applications')->where('id', $id)->firstOrFail();
        abort_unless(in_array($loan->status, ['submitted', 'under_review'], true), 409, 'Loan cannot be verified in current status.');

        $newStatus = $data['decision'] === 'verified' ? 'under_review' : 'rejected';
        DB::table('loan_applications')->where('id', $id)->update([
            'status' => $newStatus,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'verification_note' => $data['verification_note'] ?? ($data['decision'] === 'verified' ? '6-Point verification passed (KYC, Deposit history, Previous loans, Outstanding, Guarantor, Documents).' : 'Verification rejected.'),
            'decision_note' => $data['decision'] === 'rejected' ? ($data['verification_note'] ?? 'Verification rejected.') : $loan->decision_note,
            'updated_at' => now(),
        ]);

        $this->audit($request, 'loan.' . $data['decision'], 'loan_application', $id, null, $data);
        return back()->with('ok', "Loan {$loan->application_no} marked as " . ucfirst($data['decision']) . ".");
    }

    public function sendEmiReminderNotification(Request $request, int $id)
    {
        $this->staff($request);
        $inst = DB::table('loan_installments')
            ->join('loan_applications', 'loan_applications.id', '=', 'loan_installments.loan_id')
            ->join('members', 'members.id', '=', 'loan_applications.member_id')
            ->where('loan_installments.id', $id)
            ->select('loan_installments.*', 'loan_applications.application_no', 'members.user_id')
            ->firstOrFail();

        $days = Carbon::parse($inst->due_on)->isPast() ? Carbon::parse($inst->due_on)->diffInDays(today()) : 0;
        $totalOutstanding = (float)$inst->principal_due + (float)$inst->interest_due + (float)$inst->penalty_due - (float)$inst->paid_amount;

        MemberNotifier::send(
            $inst->user_id,
            'loan_overdue',
            'Overdue EMI Reminder',
            "Your loan {$inst->application_no} installment #{$inst->installment_no} of ₹" . number_format($totalOutstanding, 2) . " is {$days} days overdue. Please pay at your earliest to avoid additional penalty.",
            'emi-reminder-' . $id . '-' . time(),
            '/loan'
        );

        $this->audit($request, 'emi.reminder_sent', 'loan_installment', $id);
        return back()->with('ok', "App push notification reminder sent for installment #{$inst->installment_no}.");
    }

    public function downloadLoanDocument(Request $request, int $id)
    {
        $this->staff($request);
        $loan = DB::table('loan_applications')->where('id', $id)->firstOrFail();
        abort_unless($loan->document_path && Storage::disk('private')->exists($loan->document_path), 404, 'Document not found.');
        $this->audit($request, 'loan.document_downloaded', 'loan_application', $id);
        return Storage::disk('private')->download($loan->document_path, "loan_{$loan->application_no}_document." . pathinfo($loan->document_path, PATHINFO_EXTENSION));
    }

    public function downloadKyc(Request $request, int $id)
    {
        $this->staff($request);
        $document = DB::table('kyc_documents')->where('id', $id)->firstOrFail();
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);
        $this->audit($request, 'kyc.downloaded', 'kyc_document', $id, null, ['type' => $document->type]);
        return Storage::disk($document->disk)->download($document->path, basename($document->path), ['Content-Type' => $document->mime]);
    }

    public function viewKyc(Request $request, int $id)
    {
        $this->staff($request);
        $document = DB::table('kyc_documents')->where('id', $id)->firstOrFail();
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);
        $this->audit($request, 'kyc.viewed', 'kyc_document', $id, null, ['type' => $document->type]);
        return Storage::disk($document->disk)->response($document->path, basename($document->path), [
            'Content-Type' => $document->mime,
            'Content-Disposition' => 'inline',
        ]);
    }

    public function downloadMemberKycFolder(Request $request, int $id)
    {
        $this->staff($request);
        $member = DB::table('members')->join('users', 'users.id', '=', 'members.user_id')
            ->where('members.id', $id)->select('members.id', 'members.member_no', 'users.name')->firstOrFail();
        $documents = DB::table('kyc_documents')->where('member_id', $id)->orderBy('type')->orderBy('id')->get();
        abort_if($documents->isEmpty(), 404, 'This member has no KYC documents.');

        Storage::disk('local')->makeDirectory('kyc-downloads');
        $zipPath = Storage::disk('local')->path('kyc-downloads/'.Str::uuid().'.zip');
        $zip = new \ZipArchive();
        abort_unless($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true, 500, 'Unable to create KYC download.');

        $folder = trim((string) preg_replace('/[^A-Za-z0-9._-]+/', '_', $member->member_no.'-'.$member->name), '_');
        $manifest = [
            'Mahamilon Society KYC Folder',
            'Member: '.$member->name,
            'Member number: '.$member->member_no,
            'Generated: '.now()->toIso8601String(),
            '',
            'Documents:',
        ];
        $added = 0;
        foreach ($documents as $index => $document) {
            $disk = Storage::disk($document->disk);
            if (! $disk->exists($document->path)) {
                $manifest[] = '- '.str_replace('_', ' ', $document->type).' (file missing)';
                continue;
            }
            $extension = strtolower(pathinfo($document->path, PATHINFO_EXTENSION)) ?: 'bin';
            $entryName = sprintf('%s/%02d_%s_%d.%s', $folder, $index + 1, $document->type, $document->id, $extension);
            $zip->addFile($disk->path($document->path), $entryName);
            $manifest[] = '- '.str_replace('_', ' ', $document->type).' | status: '.$document->status.' | uploaded: '.$document->created_at.' | SHA-256: '.$document->sha256;
            $added++;
        }
        $zip->addFromString($folder.'/manifest.txt', implode(PHP_EOL, $manifest).PHP_EOL);
        $zip->close();

        if ($added === 0) {
            @unlink($zipPath);
            abort(404, 'The KYC records exist, but their files are unavailable.');
        }
        $this->audit($request, 'kyc.folder_downloaded', 'member', $id, null, ['document_count' => $added]);
        return response()->download($zipPath, $folder.'-KYC.zip', ['Content-Type' => 'application/zip'])->deleteFileAfterSend(true);
    }

    public function memberKycDecision(Request $request, int $id)
    {
        $this->permit($request, 'kyc.approve');
        $data = $request->validate(['decision' => 'required|in:approved,rejected', 'note' => 'nullable|string|max:1000']);
        $member = DB::table('members')->where('id', $id)->firstOrFail();
        $pendingIds = DB::table('kyc_documents')->where('member_id', $id)->where('status', 'pending')->pluck('id');
        abort_if($pendingIds->isEmpty(), 409, 'This member has no pending KYC documents.');

        DB::transaction(function () use ($request, $id, $pendingIds, $data) {
            DB::table('kyc_documents')->whereIn('id', $pendingIds)->update([
                'status' => $data['decision'], 'review_note' => $data['note'] ?? null,
                'reviewed_by' => $request->user()->id, 'reviewed_at' => now(), 'updated_at' => now(),
            ]);
            $pending = DB::table('kyc_documents')->where('member_id', $id)->where('status', 'pending')->exists();
            $rejected = DB::table('kyc_documents')->where('member_id', $id)->where('status', 'rejected')->exists();
            DB::table('members')->where('id', $id)->update([
                'kyc_status' => $rejected ? 'rejected' : ($pending ? 'pending' : 'approved'), 'updated_at' => now(),
            ]);
            $this->audit($request, 'kyc.folder_'.$data['decision'], 'member', $id, null, [
                'document_ids' => $pendingIds->all(), 'note' => $data['note'] ?? null,
            ]);
        });

        return back()->with('ok', 'All pending KYC documents for '.$member->member_no.' were '.$data['decision'].'.');
    }

    public function uploadKyc(Request $request)
    {
        $this->permit($request, 'kyc.approve');
        $data = $request->validate([
            'member_id' => 'required|exists:members,id', 'type' => 'required|in:aadhaar,pan,photo,address_proof',
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        $file = $request->file('document');
        $path = $file->store('kyc/'.$data['member_id'], 'private');
        $id = DB::table('kyc_documents')->insertGetId([
            'member_id' => $data['member_id'], 'type' => $data['type'], 'disk' => 'private', 'path' => $path,
            'sha256' => hash_file('sha256', $file->getRealPath()), 'mime' => $file->getMimeType(),
            'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('members')->where('id', $data['member_id'])->update(['kyc_status' => 'pending', 'updated_at' => now()]);
        $this->audit($request, 'kyc.uploaded_by_staff', 'kyc_document', $id, null, $data);
        return back()->with('ok', 'KYC document uploaded');
    }

    public function kycDecision(Request $request, int $id)
    {
        $this->permit($request, 'kyc.approve');
        $data = $request->validate(['decision' => 'required|in:approved,rejected', 'note' => 'nullable|string|max:1000']);
        $document = DB::table('kyc_documents')->where('id', $id)->firstOrFail();
        DB::table('kyc_documents')->where('id', $id)->update([
            'status' => $data['decision'], 'review_note' => $data['note'] ?? null,
            'reviewed_by' => $request->user()->id, 'reviewed_at' => now(), 'updated_at' => now(),
        ]);
        $pending = DB::table('kyc_documents')->where('member_id', $document->member_id)->where('status', 'pending')->exists();
        $rejected = DB::table('kyc_documents')->where('member_id', $document->member_id)->where('status', 'rejected')->exists();
        DB::table('members')->where('id', $document->member_id)->update([
            'kyc_status' => $rejected ? 'rejected' : ($pending ? 'pending' : 'approved'), 'updated_at' => now(),
        ]);
        $this->audit($request, 'kyc.'.$data['decision'], 'kyc_document', $id, $document, $data);
        return back()->with('ok', 'KYC decision saved');
    }

    public function updateSettings(Request $request)
    {
        abort_unless($request->user()->role === 'super_admin', 403);
        $data = $request->validate(['upi_id' => 'required|string|max:255', 'payee_name' => 'required|string|max:255']);
        foreach ($data as $key => $value) {
            DB::table('society_settings')->updateOrInsert(['key' => $key], ['value' => $value, 'created_at' => now(), 'updated_at' => now()]);
        }
        $this->audit($request, 'settings.payment_updated', 'society_settings', null, null, $data);
        return back()->with('ok', 'Payment settings updated');
    }

    public function recordDeposit(Request $request)
    {
        $this->permit($request, 'deposit.create');
        $data = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'deposit_date' => 'nullable|date',
            'payment_mode' => 'required|in:cash,bank,upi',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'note' => 'nullable|string|max:255',
        ]);
        $result = TransactionManager::recordDeposit(
            (int) $data['account_id'],
            (float) $data['amount'],
            $data['payment_mode'],
            isset($data['bank_account_id']) ? (int) $data['bank_account_id'] : null,
            $data['note'] ?? null,
            $request->user()->id,
            $data['deposit_date'] ?? null
        );
        $this->audit($request, 'deposit.recorded', 'account', $data['account_id'], null, $result);
        return back()->with('ok', "Deposit of ₹" . number_format($data['amount'], 2) . " recorded. Receipt No: {$result['receipt_no']}");
    }

    public function updateTransaction(Request $request, int $id)
    {
        $this->staff($request);
        abort_unless(
            StaffPermissions::allows($request->user(), 'deposit.create') ||
            StaffPermissions::allows($request->user(), 'withdrawal.create') ||
            in_array($request->user()->role, ['super_admin','president','manager','secretary'], true),
            403,
            'This action is not assigned to your staff account.'
        );

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'deposit_date' => 'nullable|date',
            'transaction_date' => 'nullable|date',
            'withdrawal_date' => 'nullable|date',
            'payment_mode' => 'required|in:cash,bank,upi',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'note' => 'nullable|string|max:255',
            'correction_reason' => 'nullable|string|max:255',
        ]);

        $receipt = DB::table('receipts')->where('id', $id)->first();
        abort_unless($receipt, 404, 'Receipt / Transaction entry not found.');

        // Verify account exists
        $account = DB::table('accounts')->where('id', $receipt->account_id)->lockForUpdate()->first();
        abort_unless($account, 404, 'Linked account not found.');

        $entryDateStr = $data['transaction_date'] 
            ?? $data['deposit_date'] 
            ?? $data['withdrawal_date'] 
            ?? Carbon::parse($receipt->created_at)->toDateString();

        $isWithdrawal = $receipt->category === 'withdrawal';
        $oldAmount = (float) $receipt->amount;
        $newAmount = (float) $data['amount'];

        // Balance adjustment:
        // For Deposit: increased deposit adds to balance (+), reduced deposit deducts from balance (-)
        // For Withdrawal: increased withdrawal deducts from balance (-), reduced withdrawal restores balance (+)
        $diff = $isWithdrawal ? ($oldAmount - $newAmount) : ($newAmount - $oldAmount);

        // Verify balance doesn't drop below zero
        if ($diff < 0 && ((float) $account->balance + $diff) < 0) {
            $actionVerb = $isWithdrawal ? 'increase withdrawal' : 'reduce deposit';
            return back()->with('error', "Cannot {$actionVerb} by ₹" . number_format(abs($diff), 2) . ". Account balance would become negative (current balance: ₹" . number_format($account->balance, 2) . ").");
        }

        $beforeState = [
            'type' => $receipt->category,
            'amount' => $receipt->amount,
            'payment_mode' => $receipt->payment_mode,
            'date' => $receipt->created_at,
            'notes' => $receipt->notes,
            'account_balance' => $account->balance,
        ];

        DB::transaction(function () use ($request, $receipt, $account, $data, $oldAmount, $newAmount, $diff, $isWithdrawal, $entryDateStr) {
            $newDate = Carbon::parse($entryDateStr);

            // 1. Adjust Account Balance
            if ($diff != 0) {
                DB::table('accounts')->where('id', $account->id)->increment('balance', $diff, ['updated_at' => now()]);
            }

            // 2. Update Member Ledger Entry
            $direction = $isWithdrawal ? 'debit' : 'credit';
            if ($receipt->transaction_reference) {
                $ledger = DB::table('ledger_entries')->where('reference', $receipt->transaction_reference)->first();
                if ($ledger) {
                    DB::table('ledger_entries')->where('id', $ledger->id)->update([
                        'amount' => $newAmount,
                        'description' => $data['note'] ?: ($isWithdrawal ? "Withdrawal: {$account->account_no}" : $ledger->description),
                        'posted_at' => $newDate,
                        'created_at' => $newDate,
                        'updated_at' => now(),
                    ]);
                }
            } else {
                $ledger = DB::table('ledger_entries')
                    ->where('account_id', $account->id)
                    ->where('direction', $direction)
                    ->where('amount', $oldAmount)
                    ->whereDate('posted_at', Carbon::parse($receipt->created_at)->toDateString())
                    ->first();
                if ($ledger) {
                    DB::table('ledger_entries')->where('id', $ledger->id)->update([
                        'amount' => $newAmount,
                        'description' => $data['note'] ?: $ledger->description,
                        'posted_at' => $newDate,
                        'created_at' => $newDate,
                        'updated_at' => now(),
                    ]);
                }
            }

            // 3. Update or adjust Cash / Bank ledger
            $newLocation = in_array($data['payment_mode'], ['bank', 'upi'], true) ? 'bank' : 'treasury';

            // Find matching cash ledger entry if any
            $cle = null;
            if ($receipt->transaction_reference) {
                $cle = DB::table('cash_ledger_entries')
                    ->where('note', 'like', '%' . $receipt->transaction_reference . '%')
                    ->first();
            }
            if (! $cle) {
                $cle = DB::table('cash_ledger_entries')
                    ->where('source_type', 'account')
                    ->where('source_id', $account->id)
                    ->where('amount', $oldAmount)
                    ->where('direction', $direction)
                    ->whereDate('occurred_on', Carbon::parse($receipt->created_at)->toDateString())
                    ->first();
            }

            if ($cle) {
                $prefix = $isWithdrawal ? "Withdrawal from {$account->account_no}" : "Deposit to {$account->account_no}";
                DB::table('cash_ledger_entries')->where('id', $cle->id)->update([
                    'location' => $newLocation,
                    'amount' => $newAmount,
                    'note' => ($data['note'] ? $data['note'] . " - " : "") . "{$prefix} (Ref: {$receipt->transaction_reference})",
                    'occurred_on' => $newDate->toDateString(),
                    'bank_account_id' => ($newLocation === 'bank' && !empty($data['bank_account_id'])) ? (int) $data['bank_account_id'] : null,
                    'updated_at' => now(),
                ]);
            } else if ($diff != 0) {
                $cleCategory = $isWithdrawal ? 'withdrawal_correction' : 'deposit_correction';
                $cleDirection = $diff > 0 ? 'credit' : 'debit';
                FinanceLedger::cash(
                    $newLocation,
                    $cleDirection,
                    $cleCategory,
                    abs($diff),
                    "Correction for {$receipt->category} receipt {$receipt->receipt_no}: " . ($data['correction_reason'] ?? 'Wrong entry correction'),
                    $request->user()->id,
                    'account',
                    $account->id,
                    $newDate->toDateString()
                );
            }

            // If bank account balance needs updating
            if (!empty($data['bank_account_id']) && $newLocation === 'bank' && $diff != 0) {
                DB::table('bank_accounts')->where('id', (int) $data['bank_account_id'])->increment('current_balance', $diff, ['updated_at' => now()]);
            }

            // 4. Update the receipt
            DB::table('receipts')->where('id', $receipt->id)->update([
                'amount' => $newAmount,
                'payment_mode' => $data['payment_mode'],
                'notes' => $data['note'] ?? $receipt->notes,
                'created_at' => $newDate,
                'updated_at' => now(),
            ]);
        });

        $afterState = [
            'type' => $receipt->category,
            'amount' => $newAmount,
            'payment_mode' => $data['payment_mode'],
            'date' => $entryDateStr,
            'notes' => $data['note'] ?? $receipt->notes,
            'correction_reason' => $data['correction_reason'] ?? 'Not specified',
            'balance_diff' => $diff,
            'new_account_balance' => (float) $account->balance + $diff,
        ];

        $auditAction = $isWithdrawal ? 'withdrawal.corrected' : 'deposit.corrected';
        $this->audit($request, $auditAction, 'receipt', $receipt->id, $beforeState, $afterState);
        CreditScore::recalculate($receipt->member_id);

        $typeLabel = $isWithdrawal ? 'Withdrawal' : 'Deposit';
        $diffMsg = $diff != 0 ? " (Account balance adjusted by ₹" . number_format($diff, 2) . ")" : "";
        return back()->with('ok', "Wrong entry for {$typeLabel} receipt {$receipt->receipt_no} has been corrected successfully{$diffMsg}.");
    }

    public function updateDeposit(Request $request, int $id)
    {
        return $this->updateTransaction($request, $id);
    }

    public function recordWithdrawal(Request $request)
    {
        $this->permit($request, 'withdrawal.create');
        $data = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'withdrawal_date' => 'nullable|date',
            'payment_mode' => 'required|in:cash,bank,upi',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'reason' => 'nullable|string|max:255',
        ]);
        try {
            $result = TransactionManager::recordWithdrawal(
                (int) $data['account_id'],
                (float) $data['amount'],
                $data['payment_mode'],
                isset($data['bank_account_id']) ? (int) $data['bank_account_id'] : null,
                $data['reason'] ?? null,
                $request->user()->id,
                $data['withdrawal_date'] ?? null
            );
            $this->audit($request, 'withdrawal.recorded', 'account', $data['account_id'], null, $result);
            return back()->with('ok', "Withdrawal of ₹" . number_format($data['amount'], 2) . " debited. Receipt No: {$result['receipt_no']}");
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }
    }

    public function disburseLoan(Request $request, int $id)
    {
        $this->permit($request, 'loan.disburse');
        $data = $request->validate([
            'disbursed_amount' => 'required|numeric|min:1',
            'payment_mode' => 'required|in:cash,bank,upi',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
        ]);
        $result = TransactionManager::recordLoanDisbursement(
            $id,
            (float) $data['disbursed_amount'],
            $data['payment_mode'],
            isset($data['bank_account_id']) ? (int) $data['bank_account_id'] : null,
            $request->user()->id
        );
        $this->audit($request, 'loan.disbursed', 'loan_application', $id, null, $result);
        return back()->with('ok', "Loan disbursed successfully. Receipt No: {$result['receipt_no']}");
    }

    public function recordEmiPayment(Request $request, int $id)
    {
        $this->permit($request, 'loan.repayment');
        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_mode' => 'required|in:cash,bank,upi',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'utr' => 'nullable|string|max:80',
        ]);
        $result = TransactionManager::recordEmiPayment(
            $id,
            (float) $data['amount'],
            $data['payment_mode'],
            isset($data['bank_account_id']) ? (int) $data['bank_account_id'] : null,
            $data['utr'] ?? null,
            $request->user()->id
        );
        $this->audit($request, 'loan.emi_paid', 'loan_application', $id, null, $result);
        return back()->with('ok', "EMI payment of ₹" . number_format($data['amount'], 2) . " accepted. Receipt No: {$result['receipt_no']}");
    }

    public function createFd(Request $request)
    {
        $this->permit($request, 'account.create');
        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'scheme_id' => 'required|exists:schemes,id',
            'principal' => 'required|numeric|min:1000',
            'annual_rate' => 'required|numeric|min:1|max:30',
            'tenure_months' => 'required|integer|min:1|max:120',
            'payment_mode' => 'required|in:cash,bank,upi',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'opening_date' => 'nullable|date',
        ]);
        $result = TransactionManager::openFdAccount(
            (int) $data['member_id'],
            (int) $data['scheme_id'],
            (float) $data['principal'],
            (float) $data['annual_rate'],
            (int) $data['tenure_months'],
            $data['payment_mode'],
            isset($data['bank_account_id']) ? (int) $data['bank_account_id'] : null,
            $data['opening_date'] ?? null,
            $request->user()->id
        );
        $this->audit($request, 'fd.created', 'account', $result['account_id'], null, $result);
        return back()->with('ok', "Fixed Deposit created ({$result['certificate_no']}). Maturity: ₹" . number_format($result['maturity_amount'], 2));
    }

    public function createRd(Request $request)
    {
        $this->permit($request, 'account.create');
        $scheme = DB::table('schemes')->where('id', $request->input('scheme_id'))->firstOrFail();
        $isDaily = ($scheme->deposit_frequency ?? '') === 'daily';

        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'scheme_id' => 'required|exists:schemes,id',
            'monthly_amount' => 'required|numeric|min:' . ($isDaily ? '10' : '100'),
            'annual_rate' => 'required|numeric|min:1|max:30',
            'tenure_months' => 'required|integer|min:1|max:' . ($isDaily ? '1000' : '120'),
            'payment_mode' => 'required|in:cash,bank,upi',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'start_date' => 'nullable|date',
        ]);
        $result = TransactionManager::openRdAccount(
            (int) $data['member_id'],
            (int) $data['scheme_id'],
            (float) $data['monthly_amount'],
            (float) $data['annual_rate'],
            (int) $data['tenure_months'],
            $data['payment_mode'],
            isset($data['bank_account_id']) ? (int) $data['bank_account_id'] : null,
            $data['start_date'] ?? null,
            $request->user()->id,
            $scheme->deposit_frequency,
            $isDaily ? (int) $data['tenure_months'] : null
        );
        $this->audit($request, 'rd.created', 'account', $result['account_id'], null, $result);
        $unit = $isDaily ? 'days' : 'months';
        return back()->with('ok', "Recurring Deposit account created ({$result['account_no']}). Installments scheduled: {$data['tenure_months']} {$unit}");
    }

    public function payRdInstallment(Request $request, int $id)
    {
        $this->permit($request, 'deposit.create');
        $data = $request->validate([
            'payment_mode' => 'required|in:cash,bank,upi',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payment_date' => 'nullable|date',
        ]);
        $result = TransactionManager::payRdInstallment(
            $id,
            $data['payment_mode'],
            isset($data['bank_account_id']) ? (int) $data['bank_account_id'] : null,
            $request->user()->id,
            $data['payment_date'] ?? null
        );
        $this->audit($request, 'rd.installment_paid', 'rd_installment', $id, null, $result);
        return back()->with('ok', "RD Installment collected. Receipt No: {$result['receipt_no']}");
    }

    public function payRdAdvance(Request $request, int $id)
    {
        $this->permit($request, 'deposit.create');
        $data = $request->validate([
            'days_count' => 'required|integer|min:1|max:365',
            'payment_mode' => 'required|in:cash,bank,upi',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payment_date' => 'nullable|date',
        ]);
        $result = TransactionManager::payBulkRdInstallments(
            $id,
            (int) $data['days_count'],
            $data['payment_mode'],
            isset($data['bank_account_id']) ? (int) $data['bank_account_id'] : null,
            $request->user()->id,
            $data['payment_date'] ?? null
        );
        $this->audit($request, 'rd.advance_paid', 'account', $id, null, $result);
        return back()->with('ok', "RD Collection recorded for {$result['count']} installments ({$result['range']}). Total Paid: ₹" . number_format($result['total_amount'], 2) . ". Receipt No: {$result['receipt_no']}");
    }

    public function getRdSchedule(int $id)
    {
        $account = DB::table('accounts')
            ->join('schemes', 'schemes.id', '=', 'accounts.scheme_id')
            ->join('members', 'members.id', '=', 'accounts.member_id')
            ->join('users', 'users.id', '=', 'members.user_id')
            ->where('accounts.id', $id)
            ->where('schemes.type', 'rd')
            ->select(
                'accounts.*',
                'schemes.name as scheme_name',
                'schemes.deposit_frequency',
                'schemes.annual_rate as default_rate',
                'users.name as member_name',
                'users.phone',
                'members.member_no'
            )
            ->firstOrFail();

        $installments = DB::table('rd_installments')
            ->where('account_id', $id)
            ->orderBy('installment_no', 'asc')
            ->get();

        $paidCount = $installments->where('status', 'paid')->count();
        $totalCount = $installments->count();
        $dueCount = $installments->whereIn('status', ['due', 'overdue'])->count();
        $totalPaidAmount = $installments->where('status', 'paid')->sum('paid_amount');
        $nextUnpaid = $installments->firstWhere('status', '!==', 'paid');

        return response()->json([
            'account' => $account,
            'installments' => $installments,
            'summary' => [
                'total_count' => $totalCount,
                'paid_count' => $paidCount,
                'remaining_count' => $totalCount - $paidCount,
                'due_count' => $dueCount,
                'total_paid_amount' => (float) $totalPaidAmount,
                'target_amount' => (float) $account->maturity_amount,
                'current_balance' => (float) $account->balance,
                'commitment' => (float) $account->monthly_commitment,
                'frequency' => $account->deposit_frequency,
                'next_installment' => $nextUnpaid ? [
                    'id' => $nextUnpaid->id,
                    'installment_no' => $nextUnpaid->installment_no,
                    'due_on' => $nextUnpaid->due_on,
                    'amount_due' => (float) $nextUnpaid->amount_due,
                    'status' => $nextUnpaid->status,
                ] : null,
            ]
        ]);
    }

    public function viewFdCertificate(int $id)
    {
        $account = DB::table('accounts')
            ->join('schemes', 'schemes.id', '=', 'accounts.scheme_id')
            ->join('members', 'members.id', '=', 'accounts.member_id')
            ->join('users', 'users.id', '=', 'members.user_id')
            ->where('accounts.id', $id)
            ->where('schemes.type', 'fd')
            ->select(
                'accounts.*',
                'schemes.name as scheme_name',
                'schemes.annual_rate as default_rate',
                'schemes.deposit_frequency',
                'users.name as member_name',
                'users.phone',
                'users.email',
                'members.member_no',
                'members.address',
                'members.nominee_name'
            )
            ->firstOrFail();

        $societyName = DB::table('society_settings')->where('key', 'payee_name')->value('value') ?? 'Mahamilon Society';
        return view('admin.fd-certificate', ['account' => $account, 'societyName' => $societyName]);
    }

    public function downloadFdCertificate(int $id)
    {
        $account = DB::table('accounts')
            ->join('schemes', 'schemes.id', '=', 'accounts.scheme_id')
            ->join('members', 'members.id', '=', 'accounts.member_id')
            ->join('users', 'users.id', '=', 'members.user_id')
            ->where('accounts.id', $id)
            ->where('schemes.type', 'fd')
            ->select(
                'accounts.*',
                'schemes.name as scheme_name',
                'schemes.annual_rate as default_rate',
                'schemes.deposit_frequency',
                'users.name as member_name',
                'users.phone',
                'users.email',
                'members.member_no',
                'members.address',
                'members.nominee_name'
            )
            ->firstOrFail();

        $societyName = DB::table('society_settings')->where('key', 'payee_name')->value('value') ?? 'Mahamilon Society';
        $html = view('admin.fd-certificate', ['account' => $account, 'societyName' => $societyName])->render();
        $filename = 'FD-Certificate-' . ($account->certificate_no ?: $account->account_no) . '.html';

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function createBankAccount(Request $request)
    {
        $this->permit($request, 'cash.manage');
        $data = $request->validate([
            'bank_name' => 'required|string|max:120',
            'account_number' => 'required|string|max:50|unique:bank_accounts,account_number',
            'ifsc_code' => 'nullable|string|max:20',
            'branch_name' => 'nullable|string|max:120',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);
        $opening = (float) ($data['opening_balance'] ?? 0);
        $id = DB::table('bank_accounts')->insertGetId([
            'bank_name' => $data['bank_name'],
            'account_number' => $data['account_number'],
            'ifsc_code' => $data['ifsc_code'] ?? null,
            'branch_name' => $data['branch_name'] ?? null,
            'opening_balance' => $opening,
            'current_balance' => $opening,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->audit($request, 'bank_account.created', 'bank_account', $id, null, $data);
        return back()->with('ok', "Bank account {$data['bank_name']} added successfully");
    }

    public function bankTransfer(Request $request)
    {
        $this->permit($request, 'bank.transfer');
        $data = $request->validate([
            'type' => 'required|in:cash_to_bank,bank_to_cash,bank_to_bank',
            'amount' => 'required|numeric|min:1',
            'source_bank_id' => 'nullable|exists:bank_accounts,id',
            'target_bank_id' => 'nullable|exists:bank_accounts,id',
            'note' => 'nullable|string|max:255',
        ]);
        try {
            $result = TransactionManager::recordBankTransfer(
                $data['type'],
                (float) $data['amount'],
                isset($data['source_bank_id']) ? (int) $data['source_bank_id'] : null,
                isset($data['target_bank_id']) ? (int) $data['target_bank_id'] : null,
                $data['note'] ?? null,
                $request->user()->id
            );
            $this->audit($request, 'bank.transfer', 'bank_account', $data['source_bank_id'] ?? $data['target_bank_id'], null, $result);
            return back()->with('ok', "Transfer of ₹" . number_format($data['amount'], 2) . " processed successfully");
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }
    }

    public function recordExpense(Request $request)
    {
        $this->permit($request, 'profit_loss.manage');
        $data = $request->validate([
            'category' => 'required|string|max:60',
            'amount' => 'required|numeric|min:1',
            'payment_mode' => 'required|in:cash,bank',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'bill' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'note' => 'nullable|string|max:255',
        ]);
        $billDisk = null;
        $billPath = null;
        if ($request->hasFile('bill')) {
            $billDisk = 'private';
            $billPath = $request->file('bill')->store('expenses/' . now()->format('Ym'), 'private');
        }
        $id = TransactionManager::recordExpense(
            $data['category'],
            (float) $data['amount'],
            $data['payment_mode'],
            isset($data['bank_account_id']) ? (int) $data['bank_account_id'] : null,
            $billDisk,
            $billPath,
            $data['note'] ?? null,
            $request->user()->id
        );
        $this->audit($request, 'expense.recorded', 'profit_loss_entry', $id, null, $data);
        return back()->with('ok', "Expense of ₹" . number_format($data['amount'], 2) . " recorded");
    }

    public function viewReceipt(int $id)
    {
        $receipt = ReceiptService::getDetails($id);
        abort_unless($receipt, 404, 'Receipt not found');
        return view('admin.receipt', ['receipt' => $receipt]);
    }

    public function downloadReceipt(int $id)
    {
        $receipt = ReceiptService::getDetails($id);
        abort_unless($receipt, 404, 'Receipt not found');
        $html = view('admin.receipt', ['receipt' => $receipt])->render();
        $safeNo = str_replace(['/', '\\'], '-', $receipt->receipt_no);
        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => "attachment; filename=\"receipt-{$safeNo}.html\"",
        ]);
    }

    public function viewStatement(Request $request)
    {
        $this->staff($request);
        $memberId = $request->query('member_id');
        $from = $request->query('from_date', now()->startOfMonth()->toDateString());
        $to = $request->query('to_date', today()->toDateString());
        $acc = $request->query('account_id', 'all');
        return redirect("/admin?stmt_member_id={$memberId}&stmt_from={$from}&stmt_to={$to}&stmt_account_id={$acc}#statements");
    }

    public function printStatement(int $id, Request $request)
    {
        $this->staff($request);
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', today()->toDateString());
        $accountId = $request->input('account_id', 'all');

        $res = $this->calculateStatement($id, $fromDate, $toDate, $accountId);

        return view('admin.statement-print', [
            'member' => $res['member'],
            'accountLabel' => $res['accountLabel'],
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'accountId' => $accountId,
            'statement' => $res['statement'],
        ]);
    }

    public function exportStatement(int $id, Request $request)
    {
        $this->staff($request);
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', today()->toDateString());
        $accountId = $request->input('account_id', 'all');

        $res = $this->calculateStatement($id, $fromDate, $toDate, $accountId);
        $member = $res['member'];
        $statement = $res['statement'];
        $safeMemberNo = str_replace(['/', '\\'], '-', $member->member_no);
        $filename = "statement-{$safeMemberNo}-" . today()->format('Ymd') . ".csv";

        return response()->streamDownload(function () use ($member, $res, $fromDate, $toDate, $statement) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['MAHAMILON SOCIETY - MEMBER ACCOUNT STATEMENT']);
            fputcsv($out, ['Member Name', $member->name, 'Member No', $member->member_no]);
            fputcsv($out, ['Statement Period', "{$fromDate} to {$toDate}", 'Account', $res['accountLabel']]);
            fputcsv($out, []);
            fputcsv($out, ['FINANCIAL SCORECARD (SECTION 19)']);
            fputcsv($out, ['Metric', 'Amount (INR)']);
            fputcsv($out, ['1. Opening Balance', number_format($statement['opening_balance'], 2, '.', '')]);
            fputcsv($out, ['2. Deposit', number_format($statement['deposits'], 2, '.', '')]);
            fputcsv($out, ['3. Withdrawal', number_format($statement['withdrawals'], 2, '.', '')]);
            fputcsv($out, ['4. Loan (Disbursed)', number_format($statement['loans'], 2, '.', '')]);
            fputcsv($out, ['5. EMI (Repayments)', number_format($statement['emis'], 2, '.', '')]);
            fputcsv($out, ['6. Interest (Credited)', number_format($statement['interest'], 2, '.', '')]);
            fputcsv($out, ['7. Fine / Penalty', number_format($statement['fines'], 2, '.', '')]);
            fputcsv($out, ['8. Closing Balance', number_format($statement['closing_balance'], 2, '.', '')]);
            fputcsv($out, []);
            fputcsv($out, ['ITEMIZED TRANSACTION LEDGER']);
            fputcsv($out, ['Date', 'Reference', 'Receipt No', 'Account / Loan', 'Particulars', 'Payment Mode', 'Debit (Dr)', 'Credit (Cr)', 'Running Balance']);
            fputcsv($out, [$fromDate, 'B/F', '', $res['accountLabel'], 'Opening Balance B/F', '-', '', '', number_format($statement['opening_balance'], 2, '.', '')]);
            foreach ($statement['entries'] as $e) {
                fputcsv($out, [
                    $e['date'],
                    $e['reference'],
                    $e['receipt_no'] ?? '',
                    $e['account_no'],
                    $e['description'],
                    $e['payment_mode'],
                    $e['debit'] > 0 ? number_format($e['debit'], 2, '.', '') : '',
                    $e['credit'] > 0 ? number_format($e['credit'], 2, '.', '') : '',
                    number_format($e['running_balance'], 2, '.', ''),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function calculateStatement(int $memberId, ?string $fromDate = null, ?string $toDate = null, string $accountId = 'all'): array
    {
        $fromDate = $fromDate ?: now()->startOfMonth()->toDateString();
        $toDate = $toDate ?: today()->toDateString();

        $member = DB::table('members')->join('users', 'users.id', '=', 'members.user_id')
            ->where('members.id', $memberId)
            ->select('members.*', 'users.name', 'users.phone', 'users.email')
            ->firstOrFail();

        $accounts = DB::table('accounts')->join('schemes', 'schemes.id', '=', 'accounts.scheme_id')
            ->where('accounts.member_id', $memberId)
            ->select('accounts.*', 'schemes.name as scheme_name', 'schemes.type as scheme_type')
            ->get();

        $accountIds = $accounts->pluck('id')->all();
        $targetAccountIds = ($accountId !== 'all' && is_numeric($accountId))
            ? array_values(array_intersect([(int) $accountId], $accountIds))
            : $accountIds;

        $targetAccount = ($accountId !== 'all' && is_numeric($accountId))
            ? $accounts->firstWhere('id', (int) $accountId)
            : null;

        $accountLabel = $targetAccount
            ? "{$targetAccount->account_no} ({$targetAccount->scheme_name} - " . strtoupper($targetAccount->scheme_type) . ")"
            : "All Member Accounts (Consolidated)";

        // 1. Opening Balance (Prior to $fromDate)
        $priorCredits = count($targetAccountIds) > 0
            ? (float) DB::table('ledger_entries')
                ->whereIn('account_id', $targetAccountIds)
                ->where('status', 'posted')
                ->whereDate('posted_at', '<', $fromDate)
                ->where('direction', 'credit')
                ->sum('amount')
            : 0.0;

        $priorDebits = count($targetAccountIds) > 0
            ? (float) DB::table('ledger_entries')
                ->whereIn('account_id', $targetAccountIds)
                ->where('status', 'posted')
                ->whereDate('posted_at', '<', $fromDate)
                ->where('direction', 'debit')
                ->sum('amount')
            : 0.0;

        $openingBalance = round($priorCredits - $priorDebits, 2);

        // 2. Period Deposits
        $deposits = count($targetAccountIds) > 0
            ? (float) DB::table('ledger_entries')
                ->whereIn('account_id', $targetAccountIds)
                ->where('status', 'posted')
                ->whereDate('posted_at', '>=', $fromDate)
                ->whereDate('posted_at', '<=', $toDate)
                ->where('direction', 'credit')
                ->where('description', 'not like', '%Interest%')
                ->sum('amount')
            : 0.0;

        // 3. Period Withdrawals
        $withdrawals = count($targetAccountIds) > 0
            ? (float) DB::table('ledger_entries')
                ->whereIn('account_id', $targetAccountIds)
                ->where('status', 'posted')
                ->whereDate('posted_at', '>=', $fromDate)
                ->whereDate('posted_at', '<=', $toDate)
                ->where('direction', 'debit')
                ->sum('amount')
            : 0.0;

        // 4. Period Loans Disbursed
        $loans = (float) DB::table('loan_applications')
            ->where('member_id', $memberId)
            ->whereIn('status', ['approved', 'disbursed', 'closed'])
            ->whereDate('updated_at', '>=', $fromDate)
            ->whereDate('updated_at', '<=', $toDate)
            ->sum('sanctioned_amount');

        // 5. Period EMI Repayments
        $emis = (float) DB::table('loan_repayments')
            ->join('loan_applications', 'loan_applications.id', '=', 'loan_repayments.loan_id')
            ->where('loan_applications.member_id', $memberId)
            ->where('loan_repayments.status', 'accepted')
            ->whereDate('loan_repayments.created_at', '>=', $fromDate)
            ->whereDate('loan_repayments.created_at', '<=', $toDate)
            ->sum('loan_repayments.amount');

        // 6. Period Interest Credited
        $interestPostings = count($targetAccountIds) > 0
            ? (float) DB::table('interest_postings')
                ->whereIn('account_id', $targetAccountIds)
                ->whereDate('period_end', '>=', $fromDate)
                ->whereDate('period_end', '<=', $toDate)
                ->sum('interest_amount')
            : 0.0;

        $ledgerInterest = count($targetAccountIds) > 0
            ? (float) DB::table('ledger_entries')
                ->whereIn('account_id', $targetAccountIds)
                ->where('status', 'posted')
                ->whereDate('posted_at', '>=', $fromDate)
                ->whereDate('posted_at', '<=', $toDate)
                ->where('direction', 'credit')
                ->where('description', 'like', '%Interest%')
                ->sum('amount')
            : 0.0;

        $interest = round(max($interestPostings, $ledgerInterest), 2);

        // 7. Fine / Penalty
        $loanFines = (float) DB::table('loan_repayments')
            ->join('loan_applications', 'loan_applications.id', '=', 'loan_repayments.loan_id')
            ->where('loan_applications.member_id', $memberId)
            ->where('loan_repayments.status', 'accepted')
            ->whereDate('loan_repayments.created_at', '>=', $fromDate)
            ->whereDate('loan_repayments.created_at', '<=', $toDate)
            ->sum('loan_repayments.penalty_component');

        $rdFines = (float) DB::table('rd_installments')
            ->join('accounts', 'accounts.id', '=', 'rd_installments.account_id')
            ->where('accounts.member_id', $memberId)
            ->whereDate('rd_installments.due_on', '>=', $fromDate)
            ->whereDate('rd_installments.due_on', '<=', $toDate)
            ->sum('rd_installments.penalty_due');

        $fines = round($loanFines + $rdFines, 2);

        // 8. Closing Balance
        $closingBalance = round($openingBalance + $deposits + $interest - $withdrawals, 2);

        // Fetch Ledger Rows
        $ledgerRows = count($targetAccountIds) > 0
            ? DB::table('ledger_entries')
                ->join('accounts', 'accounts.id', '=', 'ledger_entries.account_id')
                ->leftJoin('receipts', 'receipts.transaction_reference', '=', 'ledger_entries.reference')
                ->whereIn('ledger_entries.account_id', $targetAccountIds)
                ->where('ledger_entries.status', 'posted')
                ->whereDate('ledger_entries.posted_at', '>=', $fromDate)
                ->whereDate('ledger_entries.posted_at', '<=', $toDate)
                ->select(
                    'ledger_entries.posted_at as date',
                    'ledger_entries.reference',
                    'receipts.receipt_no',
                    'accounts.account_no',
                    'ledger_entries.description',
                    DB::raw("COALESCE(receipts.payment_mode, 'transfer') as payment_mode"),
                    'ledger_entries.direction',
                    'ledger_entries.amount'
                )
                ->get()
            : collect();

        $loanRows = ($accountId === 'all')
            ? DB::table('loan_repayments')
                ->join('loan_applications', 'loan_applications.id', '=', 'loan_repayments.loan_id')
                ->leftJoin('receipts', 'receipts.transaction_reference', '=', 'loan_repayments.reference')
                ->where('loan_applications.member_id', $memberId)
                ->where('loan_repayments.status', 'accepted')
                ->whereDate('loan_repayments.created_at', '>=', $fromDate)
                ->whereDate('loan_repayments.created_at', '<=', $toDate)
                ->select(
                    'loan_repayments.created_at as date',
                    'loan_repayments.reference',
                    'receipts.receipt_no',
                    'loan_applications.application_no as account_no',
                    DB::raw("CONCAT('EMI Repayment (P: ₹', loan_repayments.principal_component, ', I: ₹', loan_repayments.interest_component, ')') as description"),
                    DB::raw("COALESCE(receipts.payment_mode, 'bank') as payment_mode"),
                    DB::raw("'credit' as direction"),
                    'loan_repayments.amount'
                )
                ->get()
            : collect();

        $combined = $ledgerRows->concat($loanRows)->sortBy('date');

        $entries = [];
        $runningBal = $openingBalance;
        foreach ($combined as $item) {
            $debit = $item->direction === 'debit' ? (float) $item->amount : 0.0;
            $credit = $item->direction === 'credit' ? (float) $item->amount : 0.0;
            $runningBal = round($runningBal + $credit - $debit, 2);
            $entries[] = [
                'date' => $item->date,
                'reference' => $item->reference,
                'receipt_no' => $item->receipt_no ?? null,
                'account_no' => $item->account_no,
                'description' => $item->description,
                'payment_mode' => strtoupper($item->payment_mode ?? 'CASH'),
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $runningBal,
            ];
        }

        return [
            'member' => $member,
            'accounts' => $accounts,
            'accountLabel' => $accountLabel,
            'statement' => [
                'opening_balance' => $openingBalance,
                'deposits' => $deposits,
                'withdrawals' => $withdrawals,
                'loans' => $loans,
                'emis' => $emis,
                'interest' => $interest,
                'fines' => $fines,
                'closing_balance' => $closingBalance,
                'entries' => $entries,
            ],
        ];
    }

    public function viewMemberProfile(int $id, Request $request)
    {
        $this->staff($request);
        $permissions = StaffPermissions::for($request->user());

        $member = DB::table('members')->join('users', 'users.id', '=', 'members.user_id')
            ->leftJoin('users as approver', 'approver.id', '=', 'members.approved_by')
            ->where('members.id', $id)
            ->select(
                'members.*',
                'users.name', 'users.phone', 'users.email', 'users.status as user_status', 'users.created_at as registered_at',
                'approver.name as approver_name'
            )
            ->first();
        abort_unless($member, 404, 'Member not found');

        $age = $member->date_of_birth ? Carbon::parse($member->date_of_birth)->age : null;

        $documents = DB::table('kyc_documents')
            ->leftJoin('users as reviewer', 'reviewer.id', '=', 'kyc_documents.reviewed_by')
            ->where('kyc_documents.member_id', $id)
            ->select('kyc_documents.*', 'reviewer.name as reviewer_name')
            ->latest('kyc_documents.id')
            ->get();

        $accounts = DB::table('accounts')->join('schemes', 'schemes.id', '=', 'accounts.scheme_id')
            ->where('accounts.member_id', $id)
            ->select(
                'accounts.*',
                'schemes.name as scheme_name',
                'schemes.type as scheme_type',
                'schemes.annual_rate as scheme_annual_rate',
                'schemes.term_months',
                'schemes.deposit_frequency',
                DB::raw('COALESCE(accounts.custom_interest_rate, schemes.annual_rate) as effective_rate')
            )
            ->latest('accounts.id')
            ->get();

        $savingsAccounts = $accounts->where('scheme_type', 'savings')->values();
        $fdAccounts = $accounts->where('scheme_type', 'fd')->values();
        $rdAccounts = $accounts->where('scheme_type', 'rd')->values();

        $accountIds = $accounts->pluck('id')->all();

        $rdInstallments = count($accountIds) > 0
            ? DB::table('rd_installments')->join('accounts', 'accounts.id', '=', 'rd_installments.account_id')
                ->whereIn('rd_installments.account_id', $accountIds)
                ->select('rd_installments.*', 'accounts.account_no')
                ->orderBy('rd_installments.due_on')
                ->get()
            : collect();

        $loans = DB::table('loan_applications')
            ->leftJoin('users as decider', 'decider.id', '=', 'loan_applications.decided_by')
            ->where('loan_applications.member_id', $id)
            ->select('loan_applications.*', 'decider.name as decider_name')
            ->latest('loan_applications.id')
            ->get();

        $loanIds = $loans->pluck('id')->all();

        $loanInstallments = count($loanIds) > 0
            ? DB::table('loan_installments')->join('loan_applications', 'loan_applications.id', '=', 'loan_installments.loan_id')
                ->whereIn('loan_installments.loan_id', $loanIds)
                ->select('loan_installments.*', 'loan_applications.application_no')
                ->orderBy('loan_installments.due_on')
                ->get()
            : collect();

        $loanRepayments = count($loanIds) > 0
            ? DB::table('loan_repayments')->join('loan_applications', 'loan_applications.id', '=', 'loan_repayments.loan_id')
                ->leftJoin('users as reviewer', 'reviewer.id', '=', 'loan_repayments.reviewed_by')
                ->whereIn('loan_repayments.loan_id', $loanIds)
                ->select('loan_repayments.*', 'loan_applications.application_no', 'reviewer.name as reviewer_name')
                ->latest('loan_repayments.id')
                ->get()
            : collect();

        $payments = DB::table('payments')->join('accounts', 'accounts.id', '=', 'payments.account_id')
            ->leftJoin('users as reviewer', 'reviewer.id', '=', 'payments.reviewed_by')
            ->where('accounts.member_id', $id)
            ->select('payments.*', 'accounts.account_no', 'reviewer.name as reviewer_name')
            ->latest('payments.id')
            ->get();

        $receipts = DB::table('receipts')
            ->leftJoin('users as issuer', 'issuer.id', '=', 'receipts.issued_by')
            ->where('receipts.member_id', $id)
            ->select('receipts.*', 'issuer.name as issuer_name')
            ->latest('receipts.id')
            ->get();

        $withdrawals = count($accountIds) > 0
            ? DB::table('ledger_entries')->join('accounts', 'accounts.id', '=', 'ledger_entries.account_id')
                ->leftJoin('users as creator', 'creator.id', '=', 'ledger_entries.created_by')
                ->where('accounts.member_id', $id)
                ->where('ledger_entries.direction', 'debit')
                ->select('ledger_entries.*', 'accounts.account_no', 'creator.name as creator_name')
                ->latest('ledger_entries.posted_at')
                ->latest('ledger_entries.id')
                ->get()
            : collect();

        $interestPostings = count($accountIds) > 0
            ? DB::table('interest_postings')->join('accounts', 'accounts.id', '=', 'interest_postings.account_id')
                ->where('accounts.member_id', $id)
                ->select('interest_postings.*', 'accounts.account_no')
                ->latest('interest_postings.period_end')
                ->get()
            : collect();

        $totalProfitEarned = (float) $interestPostings->sum('interest_amount');

        $loanPenalties = $loanInstallments->where('penalty_due', '>', 0)->values();
        $rdPenalties = $rdInstallments->where('penalty_due', '>', 0)->values();
        $totalPenaltiesDue = (float) $loanPenalties->whereIn('status', ['due', 'overdue', 'partial'])->sum('penalty_due')
            + (float) $rdPenalties->whereIn('status', ['due', 'overdue', 'partial'])->sum('penalty_due');
        $totalPenaltiesPaid = (float) $loanRepayments->sum('penalty_component');

        $transactions = count($accountIds) > 0
            ? DB::table('ledger_entries')->join('accounts', 'accounts.id', '=', 'ledger_entries.account_id')
                ->join('schemes', 'schemes.id', '=', 'accounts.scheme_id')
                ->leftJoin('users as creator', 'creator.id', '=', 'ledger_entries.created_by')
                ->where('accounts.member_id', $id)
                ->select(
                    'ledger_entries.*',
                    'accounts.account_no',
                    'schemes.name as scheme_name',
                    'schemes.type as scheme_type',
                    'creator.name as creator_name'
                )
                ->orderByDesc('ledger_entries.posted_at')
                ->orderByDesc('ledger_entries.id')
                ->get()
            : collect();

        $kpi = [
            'totalSavings' => (float) $savingsAccounts->where('status', 'active')->sum('balance'),
            'totalFd' => (float) $fdAccounts->where('status', 'active')->sum('balance'),
            'totalRd' => (float) $rdAccounts->where('status', 'active')->sum('balance'),
            'totalLoanOutstanding' => (float) $loans->whereIn('status', ['approved', 'disbursed'])->sum('outstanding_principal'),
            'totalProfitEarned' => $totalProfitEarned,
            'totalPenaltiesDue' => $totalPenaltiesDue,
            'totalPenaltiesPaid' => $totalPenaltiesPaid,
            'mcsScore' => $member->mcs_score ?? 500,
            'accountsCount' => $accounts->count(),
            'loansCount' => $loans->count(),
            'receiptsCount' => $receipts->count(),
            'transactionsCount' => $transactions->count(),
        ];

        return view('admin.member-profile', [
            'member' => $member,
            'age' => $age,
            'documents' => $documents,
            'accounts' => $accounts,
            'savingsAccounts' => $savingsAccounts,
            'fdAccounts' => $fdAccounts,
            'rdAccounts' => $rdAccounts,
            'rdInstallments' => $rdInstallments,
            'loans' => $loans,
            'loanInstallments' => $loanInstallments,
            'loanRepayments' => $loanRepayments,
            'payments' => $payments,
            'receipts' => $receipts,
            'withdrawals' => $withdrawals,
            'interestPostings' => $interestPostings,
            'loanPenalties' => $loanPenalties,
            'rdPenalties' => $rdPenalties,
            'transactions' => $transactions,
            'kpi' => $kpi,
            'permissions' => $permissions,
        ]);
    }

    public function viewMemberCard(int $id)
    {
        $member = DB::table('members')->join('users', 'users.id', '=', 'members.user_id')
            ->where('members.id', $id)
            ->select('members.*', 'users.name', 'users.phone', 'users.email')
            ->firstOrFail();

        $photo = DB::table('kyc_documents')->where('member_id', $id)->where('type', 'photo')->where('status', 'approved')->first();

        return view('admin.member-card', [
            'member' => $member,
            'photo' => $photo,
        ]);
    }

    public function viewMemberForm(int $id)
    {
        $member = DB::table('members')->join('users', 'users.id', '=', 'members.user_id')
            ->where('members.id', $id)
            ->select('members.*', 'users.name', 'users.phone', 'users.email')
            ->firstOrFail();

        $account = DB::table('accounts')
            ->leftJoin('schemes', 'schemes.id', '=', 'accounts.scheme_id')
            ->where('accounts.member_id', $id)
            ->select('accounts.*', 'schemes.name as scheme_name')
            ->orderByDesc('accounts.id')
            ->first();

        $photo = DB::table('kyc_documents')
            ->where('member_id', $id)
            ->where('type', 'photo')
            ->orderByRaw("CASE WHEN status = 'approved' THEN 1 WHEN status = 'pending' THEN 2 ELSE 3 END")
            ->orderByDesc('id')
            ->first();

        return view('admin.member-form', [
            'member' => $member,
            'account' => $account,
            'photo' => $photo,
        ]);
    }

    public function updateMember(Request $request, int $id)
    {
        $this->ensureMemberSchema();
        if (!StaffPermissions::allows($request->user(), 'member.approve') &&
            !StaffPermissions::allows($request->user(), 'account.create') &&
            !in_array($request->user()->role, ['super_admin','president','manager','secretary'], true)) {
            abort(403, 'Unauthorized to edit customer details');
        }

        $member = DB::table('members')->where('id', $id)->firstOrFail();
        $user = DB::table('users')->where('id', $member->user_id)->firstOrFail();

        $data = $request->validate([
            'member_no' => 'required|string|max:50|unique:members,member_no,' . $member->id,
            'name' => 'required|string|max:120',
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'email' => 'nullable|email|max:120|unique:users,email,' . $user->id,
            'status' => 'nullable|in:active,pending,suspended,blocked',
            'mpin' => 'nullable|digits:6',
            'created_at' => 'nullable|date',
            // Applicant Particulars
            'parent_name' => 'nullable|string|max:150',
            'mother_name' => 'nullable|string|max:150',
            'father_name' => 'nullable|string|max:150',
            'age' => 'nullable|integer|min:1|max:120',
            'date_of_birth' => 'nullable|date',
            'blood_group' => 'nullable|string|max:10',
            'qualification' => 'nullable|string|max:120',
            'marital_status' => 'nullable|string|max:30',
            // Contact & Addresses
            'address' => 'nullable|string|max:1000',
            'present_address' => 'nullable|string|max:1000',
            'pincode' => 'nullable|string|max:20',
            // Banking Particulars (Multiple Banks Support)
            'bank_name' => 'nullable|string|max:150',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:25',
            'bank_branch' => 'nullable|string|max:120',
            'bank2_name' => 'nullable|string|max:150',
            'bank2_account_no' => 'nullable|string|max:50',
            'bank2_ifsc' => 'nullable|string|max:25',
            'bank2_branch' => 'nullable|string|max:120',
            // Employment & Tax
            'occupation' => 'nullable|string|max:120',
            'office_address' => 'nullable|string|max:1000',
            'designation_salary' => 'nullable|string|max:150',
            'aadhaar' => 'nullable|string|max:25',
            'pan' => 'nullable|string|max:20',
            'relative_member_details' => 'nullable|string|max:255',
            // Nominee
            'nominee_name' => 'nullable|string|max:120',
            'nominee_parent' => 'nullable|string|max:150',
            'nominee_relation' => 'nullable|string|max:80',
            'nominee_occupation' => 'nullable|string|max:120',
            'nominee_age' => 'nullable|integer|min:1|max:120',
            'nominee_address' => 'nullable|string|max:1000',
            // Introducers
            'intro1_name' => 'nullable|string|max:150',
            'intro1_parent' => 'nullable|string|max:150',
            'intro1_account' => 'nullable|string|max:50',
            'intro1_lf' => 'nullable|string|max:50',
            'intro1_address' => 'nullable|string|max:1000',
            'intro2_name' => 'nullable|string|max:150',
            'intro2_parent' => 'nullable|string|max:150',
            'intro2_account' => 'nullable|string|max:50',
            'intro2_lf' => 'nullable|string|max:50',
            'intro2_address' => 'nullable|string|max:1000',
            // KYC & Score
            'kyc_status' => 'nullable|in:not_submitted,pending,approved,rejected',
            'mcs_score' => 'nullable|integer|min:300|max:900',
        ]);

        DB::transaction(function () use ($request, $member, $user, $data) {
            $userUpdates = [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'status' => $data['status'] ?? $user->status ?? 'active',
                'updated_at' => now(),
            ];
            if (!empty($data['mpin'])) {
                $userUpdates['mpin_hash'] = Hash::make($data['mpin']);
            }
            if (!empty($data['created_at'])) {
                $joiningDate = Carbon::parse($data['created_at']);
                $userUpdates['created_at'] = $joiningDate;
            }
            DB::table('users')->where('id', $user->id)->update($userUpdates);

            $memberUpdates = [
                'member_no' => $data['member_no'],
                'address' => $data['address'] ?? $member->address,
                'present_address' => $data['present_address'] ?? ($data['address'] ?? $member->address),
                'pincode' => $data['pincode'] ?? $member->pincode,
                'parent_name' => $data['parent_name'] ?? null,
                'mother_name' => $data['mother_name'] ?? null,
                'father_name' => $data['father_name'] ?? null,
                'age' => !empty($data['age']) ? (int)$data['age'] : null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'qualification' => $data['qualification'] ?? null,
                'marital_status' => $data['marital_status'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_no' => $data['bank_account_no'] ?? null,
                'bank_ifsc' => !empty($data['bank_ifsc']) ? strtoupper(trim($data['bank_ifsc'])) : null,
                'bank_branch' => $data['bank_branch'] ?? null,
                'bank2_name' => $data['bank2_name'] ?? null,
                'bank2_account_no' => $data['bank2_account_no'] ?? null,
                'bank2_ifsc' => !empty($data['bank2_ifsc']) ? strtoupper(trim($data['bank2_ifsc'])) : null,
                'bank2_branch' => $data['bank2_branch'] ?? null,
                'occupation' => $data['occupation'] ?? null,
                'office_address' => $data['office_address'] ?? null,
                'designation_salary' => $data['designation_salary'] ?? null,
                'relative_member_details' => $data['relative_member_details'] ?? null,
                'nominee_name' => $data['nominee_name'] ?? $member->nominee_name,
                'nominee_parent' => $data['nominee_parent'] ?? null,
                'nominee_relation' => $data['nominee_relation'] ?? null,
                'nominee_occupation' => $data['nominee_occupation'] ?? null,
                'nominee_age' => !empty($data['nominee_age']) ? (int)$data['nominee_age'] : null,
                'nominee_address' => $data['nominee_address'] ?? null,
                'intro1_name' => $data['intro1_name'] ?? null,
                'intro1_parent' => $data['intro1_parent'] ?? null,
                'intro1_account' => $data['intro1_account'] ?? null,
                'intro1_lf' => $data['intro1_lf'] ?? null,
                'intro1_address' => $data['intro1_address'] ?? null,
                'intro2_name' => $data['intro2_name'] ?? null,
                'intro2_parent' => $data['intro2_parent'] ?? null,
                'intro2_account' => $data['intro2_account'] ?? null,
                'intro2_lf' => $data['intro2_lf'] ?? null,
                'intro2_address' => $data['intro2_address'] ?? null,
                'kyc_status' => $data['kyc_status'] ?? $member->kyc_status ?? 'not_submitted',
                'updated_at' => now(),
            ];
            if (!empty($data['created_at'])) {
                $memberUpdates['created_at'] = Carbon::parse($data['created_at']);
            }
            if (isset($data['mcs_score'])) {
                $memberUpdates['mcs_score'] = (int) $data['mcs_score'];
            }
            if (isset($data['aadhaar'])) {
                $trimmedAadhaar = trim($data['aadhaar']);
                if ($trimmedAadhaar !== '') {
                    $cleanAadhaar = preg_replace('/\s+/', '', $trimmedAadhaar);
                    $memberUpdates['aadhaar_no'] = $cleanAadhaar;
                    $memberUpdates['aadhaar_last4'] = substr($cleanAadhaar, -4);
                } else {
                    $memberUpdates['aadhaar_no'] = null;
                    $memberUpdates['aadhaar_last4'] = null;
                }
            }
            if (isset($data['pan'])) {
                $trimmedPan = trim($data['pan']);
                if ($trimmedPan !== '') {
                    $cleanPan = strtoupper($trimmedPan);
                    $memberUpdates['pan_no'] = $cleanPan;
                    $memberUpdates['pan_last4'] = substr($cleanPan, -4);
                } else {
                    $memberUpdates['pan_no'] = null;
                    $memberUpdates['pan_last4'] = null;
                }
            }
            if ($data['kyc_status'] === 'approved' && $member->kyc_status !== 'approved') {
                $memberUpdates['approved_at'] = now();
                $memberUpdates['approved_by'] = $request->user()?->id ?: 1;
            }

            $existingCols = Schema::getColumnListing('members');
            DB::table('members')->where('id', $member->id)->update(array_intersect_key($memberUpdates, array_flip($existingCols)));

            $this->audit($request, 'member.updated', 'member', $member->id, null, [
                'member_no' => $data['member_no'],
                'name' => $data['name'],
                'phone' => $data['phone'],
                'status' => $data['status'],
                'kyc_status' => $data['kyc_status'],
            ]);
        });

        return back()->with('ok', 'Customer ' . $data['member_no'] . ' (' . $data['name'] . ') application form details updated successfully.');
    }

    public function deleteMember(Request $request, int $id)
    {
        $this->permit($request, 'member.approve');
        $member = DB::table('members')->where('id', $id)->firstOrFail();
        $user = DB::table('users')->where('id', $member->user_id)->firstOrFail();

        DB::transaction(function () use ($request, $member, $user) {
            $accountIds = DB::table('accounts')->where('member_id', $member->id)->pluck('id')->toArray();
            $loanIds = DB::table('loan_applications')->where('member_id', $member->id)->pluck('id')->toArray();

            // 1. Clean up loans and related repayments / installments
            if (!empty($loanIds)) {
                DB::table('loan_repayments')->whereIn('loan_id', $loanIds)->delete();
                DB::table('loan_installments')->whereIn('loan_id', $loanIds)->delete();
                DB::table('receipts')->whereIn('loan_id', $loanIds)->delete();
                DB::table('loan_applications')->whereIn('id', $loanIds)->delete();
            }

            // 2. Clean up any loan repayments submitted by this user
            DB::table('loan_repayments')->where('submitted_by', $user->id)->delete();

            // 3. Clean up accounts and all dependent financial records
            if (!empty($accountIds)) {
                DB::table('receipts')->whereIn('account_id', $accountIds)->delete();
                DB::table('rd_installments')->whereIn('account_id', $accountIds)->delete();
                DB::table('account_closures')->whereIn('account_id', $accountIds)->delete();
                DB::table('interest_postings')->whereIn('account_id', $accountIds)->delete();
                DB::table('ledger_entries')->whereIn('account_id', $accountIds)->delete();
                DB::table('payments')->whereIn('account_id', $accountIds)->delete();
                DB::table('accounts')->whereIn('id', $accountIds)->delete();
            }

            // 4. Clean up member-specific records
            DB::table('receipts')->where('member_id', $member->id)->delete();
            DB::table('kyc_documents')->where('member_id', $member->id)->delete();
            DB::table('member_notifications')->where('user_id', $user->id)->delete();
            DB::table('api_tokens')->where('user_id', $user->id)->delete();

            // 5. Delete member profile and user record
            DB::table('members')->where('id', $member->id)->delete();
            DB::table('users')->where('id', $user->id)->delete();

            $this->audit($request, 'member.deleted', 'member', $member->id, null, [
                'member_no' => $member->member_no,
                'name' => $user->name,
                'phone' => $user->phone,
            ]);
        });

        return back()->with('ok', 'Customer ' . $member->member_no . ' (' . $user->name . ') deleted successfully.');
    }

    public function downloadDatabaseBackup(Request $request)
    {
        $this->permit($request, 'backup.manage');
        $this->audit($request, 'backup.downloaded', 'database', null);

        $filename = 'mahamilon_backup_' . now()->format('Y-m-d_His') . '.sql';
        $tables = ['users', 'members', 'schemes', 'accounts', 'ledger_entries', 'loan_applications', 'loan_installments', 'loan_repayments', 'kyc_documents', 'payments', 'cash_ledger_entries', 'profit_loss_entries', 'account_closures', 'interest_postings', 'member_notifications', 'society_settings', 'bank_accounts', 'receipts', 'rd_installments', 'audit_logs'];

        $sql = "-- Mahamilon Society Database Backup\n-- Generated on: " . now()->toIso8601String() . "\n\n";

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) continue;
            $rows = DB::table($table)->get();
            $sql .= "-- Table: {$table}\n";
            foreach ($rows as $row) {
                $attrs = (array) $row;
                $keys = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($attrs)));
                $vals = implode(', ', array_map(function ($v) {
                    if ($v === null) return 'NULL';
                    return "'" . addslashes((string) $v) . "'";
                }, array_values($attrs)));
                $sql .= "INSERT INTO `{$table}` ({$keys}) VALUES ({$vals});\n";
            }
            $sql .= "\n";
        }

        return response($sql, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
