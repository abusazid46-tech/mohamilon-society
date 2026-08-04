<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\{CreditScore, FinanceLedger, FinancialAutomation, LoanSchedule, MemberNotifier, StaffPermissions};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, Storage, Validator};
use Illuminate\Support\Str;

class AdminController extends Controller
{
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
            'onboarding_status' => 'payment_required', 'approved_by' => $request->user()->id,
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
                ->join('users','users.id','=','members.user_id')->select('loan_applications.*','users.name','members.member_no','members.mcs_score')
                ->latest('loan_applications.id')->limit(100)->get(),
            'repayments' => DB::table('loan_repayments')->join('loan_applications','loan_applications.id','=','loan_repayments.loan_id')
                ->join('members','members.id','=','loan_applications.member_id')->join('users','users.id','=','members.user_id')
                ->select('loan_repayments.*','loan_applications.application_no','users.name')
                ->latest('loan_repayments.id')->limit(100)->get(),
            'installments' => DB::table('loan_installments')->join('loan_applications','loan_applications.id','=','loan_installments.loan_id')
                ->join('members','members.id','=','loan_applications.member_id')->join('users','users.id','=','members.user_id')
                ->select('loan_installments.*','loan_applications.application_no','users.name')
                ->whereIn('loan_installments.status',['due','partial','overdue'])->orderBy('loan_installments.due_on')->limit(100)->get(),
            'cashEntries' => DB::table('cash_ledger_entries')->leftJoin('users','users.id','=','cash_ledger_entries.created_by')
                ->select('cash_ledger_entries.*','users.name as actor')->latest('cash_ledger_entries.id')->limit(100)->get(),
            'profitLossEntries' => DB::table('profit_loss_entries')->leftJoin('users','users.id','=','profit_loss_entries.created_by')
                ->select('profit_loss_entries.*','users.name as actor')->latest('profit_loss_entries.id')->limit(100)->get(),
            'closures' => DB::table('account_closures')->join('accounts','accounts.id','=','account_closures.account_id')
                ->join('members','members.id','=','accounts.member_id')->join('users','users.id','=','members.user_id')
                ->select('account_closures.*','accounts.account_no','accounts.balance','users.name')
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
            'settings' => DB::table('society_settings')->pluck('value', 'key'),
            'auditLogs' => DB::table('audit_logs')->leftJoin('users','users.id','=','audit_logs.actor_id')
                ->select('audit_logs.*','users.name as actor')->latest('audit_logs.id')->limit(30)->get(),
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
        $after = [
            'approved_at' => $data['decision'] === 'approve' ? now() : null,
            'approved_by' => $request->user()->id, 'updated_at' => now(),
        ];
        DB::transaction(function () use ($request, $id, $member, $after, $data) {
            DB::table('members')->where('id', $id)->update($after);
            DB::table('users')->where('id', $member->user_id)->update(['status' => $data['decision'] === 'approve' ? 'active' : 'pending']);
            $this->audit($request, 'member.'.$data['decision'], 'member', $id, $member, $after);
        });
        return back()->with('ok', 'Member decision saved');
    }

    public function createAccount(Request $request)
    {
        $this->permit($request, 'account.create');
        $data = $request->validate([
            'member_source' => 'required|in:existing,new',
            'member_id' => 'nullable|required_if:member_source,existing|exists:members,id',
            'applicant_name' => 'nullable|required_if:member_source,new|string|max:120',
            'applicant_phone' => ['nullable','required_if:member_source,new','regex:/^[6-9][0-9]{9}$/','unique:users,phone'],
            'applicant_email' => 'nullable|email|max:255|unique:users,email',
            'applicant_mpin' => 'nullable|required_if:member_source,new|digits:6',
            'applicant_address' => 'nullable|required_if:member_source,new|string|max:1000',
            'applicant_nominee' => 'nullable|required_if:member_source,new|string|max:120',
            'applicant_occupation' => 'nullable|string|max:120',
            'applicant_date_of_birth' => 'nullable|date|before:today',
            'applicant_aadhaar' => 'nullable|required_if:member_source,new|digits:12',
            'applicant_pan' => ['nullable','regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'scheme_id' => 'required|exists:schemes,id',
            'opening_deposit' => 'required|numeric|min:1', 'share_count' => 'nullable|integer|min:1|max:100',
            'custom_interest_rate' => 'nullable|numeric|min:0|max:30',
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
            if ($isNewApplicant) {
                $user = User::create([
                    'name' => $data['applicant_name'], 'email' => $data['applicant_email'] ?? null,
                    'phone' => $data['applicant_phone'], 'password' => Hash::make(Str::random(48)),
                    'mpin_hash' => Hash::make($data['applicant_mpin']), 'role' => 'member', 'status' => 'pending',
                ]);
                $memberNo = 'MS-'.now()->format('Y').'-'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
                $memberId = DB::table('members')->insertGetId([
                    'user_id' => $user->id, 'member_no' => $memberNo,
                    'address' => $data['applicant_address'], 'nominee_name' => $data['applicant_nominee'],
                    'occupation' => $data['applicant_occupation'] ?? null,
                    'date_of_birth' => $data['applicant_date_of_birth'] ?? null,
                    'aadhaar_last4' => substr($data['applicant_aadhaar'], -4),
                    'pan_last4' => isset($data['applicant_pan']) ? substr($data['applicant_pan'], -4) : null,
                    'kyc_status' => 'not_submitted', 'mcs_score' => 500,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->audit($request, 'member.created_by_staff', 'member', $memberId, null, [
                    'member_no' => $memberNo, 'name' => $data['applicant_name'],
                    'phone' => $data['applicant_phone'], 'status' => 'pending', 'kyc_status' => 'not_submitted',
                ]);
            }

            $id = DB::table('accounts')->insertGetId([
                'account_no' => $accountNo, 'member_id' => $memberId, 'scheme_id' => $data['scheme_id'],
                'share_count' => $shares, 'monthly_commitment' => in_array($scheme->code, ['SAVINGS','RD-MONTHLY'], true) ? 500 * $shares : 0,
                'principal' => $data['opening_deposit'], 'balance' => 0, 'custom_interest_rate' => $data['custom_interest_rate'] ?? null,
                'status' => 'pending', 'onboarding_status' => 'awaiting_review', 'created_at' => now(), 'updated_at' => now(),
            ]);
            $account = DB::table('accounts')->where('id', $id)->firstOrFail();
            if ($canApprove) {
                $this->requestOpeningPayment($request, $account);
            }
            $this->audit($request, 'account.created_by_staff', 'account', $id, null, [
                'account_no' => $accountNo, 'member_id' => $memberId, 'member_no' => $memberNo,
                'scheme_id' => $data['scheme_id'], 'opening_deposit' => $data['opening_deposit'],
                'share_count' => $shares, 'custom_interest_rate' => $data['custom_interest_rate'] ?? null,
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
        $account = DB::table('accounts')->where('id', $id)->firstOrFail();
        abort_unless($account->status === 'pending' && $account->onboarding_status === 'awaiting_review', 409, 'Account is not awaiting review.');
        if ($decision['decision'] === 'approve') {
            $member = DB::table('members')->join('users', 'users.id', '=', 'members.user_id')
                ->where('members.id', $account->member_id)
                ->select('members.kyc_status', 'users.status as user_status')->firstOrFail();
            abort_unless($member->user_status === 'active', 422, 'Approve the applicant as a member before approving this account.');
            abort_unless($member->kyc_status === 'approved', 422, 'Approve the member KYC before requesting the opening payment.');
            DB::transaction(fn () => $this->requestOpeningPayment($request, $account));
        } else {
            DB::table('accounts')->where('id', $id)->update(['status' => 'rejected', 'onboarding_status' => 'rejected', 'decision_note' => $decision['note'] ?? null, 'updated_at' => now()]);
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
        $data = $request->validate(['decision' => 'required|in:accepted,rejected', 'note' => 'nullable|string|max:1000']);
        $payment = DB::table('payments')->where('id', $id)->firstOrFail();
        abort_unless($payment->status === 'submitted', 409, 'Payment is not awaiting verification.');
        DB::transaction(function () use ($request, $payment, $data) {
            DB::table('payments')->where('id', $payment->id)->update([
                'status' => $data['decision'], 'review_note' => $data['note'] ?? null,
                'reviewed_by' => $request->user()->id, 'reviewed_at' => now(), 'updated_at' => now(),
            ]);
            if ($data['decision'] === 'accepted') {
                $termMonths = (int) (DB::table('accounts')->join('schemes','schemes.id','=','accounts.scheme_id')
                    ->where('accounts.id',$payment->account_id)->value('schemes.term_months')
                    ?: DB::table('society_settings')->where('key','default_account_term_months')->value('value')
                    ?: 12);
                $openedOn = today();
                DB::table('accounts')->where('id', $payment->account_id)->update([
                    'status' => 'active', 'onboarding_status' => 'active', 'balance' => $payment->amount,
                    'opened_on' => $openedOn, 'matures_on' => $openedOn->copy()->addMonthsNoOverflow($termMonths)->subDay(),
                    'updated_at' => now(),
                ]);
                DB::table('ledger_entries')->insert([
                    'reference' => (string) Str::uuid(), 'account_id' => $payment->account_id, 'direction' => 'credit',
                    'amount' => $payment->amount, 'description' => 'Verified opening payment '.$payment->reference,
                    'status' => 'posted', 'created_by' => $request->user()->id, 'posted_at' => now(),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $account = DB::table('accounts')->where('id',$payment->account_id)->first();
                FinanceLedger::cash('bank','credit','member_deposit',(float)$payment->amount,
                    'Verified opening payment '.$payment->reference,$request->user()->id,'payment',$payment->id);
                MemberNotifier::send(MemberNotifier::memberUserId($account->member_id),'payment',
                    'Account activated','Your opening payment was verified and account '.$account->account_no.' is active.',
                    'opening-payment-accepted-'.$payment->id,'/accounts');
            } else {
                DB::table('accounts')->where('id', $payment->account_id)->update(['onboarding_status' => 'payment_required', 'updated_at' => now()]);
                $account = DB::table('accounts')->where('id',$payment->account_id)->first();
                MemberNotifier::send(MemberNotifier::memberUserId($account->member_id),'payment',
                    'Payment needs correction','The opening payment for '.$account->account_no.' was rejected. Check the UTR and submit again.',
                    'opening-payment-rejected-'.$payment->id,'/accounts');
            }
        });
        $memberId = DB::table('accounts')->where('id', $payment->account_id)->value('member_id');
        CreditScore::recalculate($memberId);
        $this->audit($request, 'payment.'.$data['decision'], 'payment', $id, $payment, $data);
        return back()->with('ok', 'Payment decision saved');
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
        $data = $request->validate(['decision' => 'required|in:approved,rejected', 'sanctioned_amount' => 'nullable|numeric|min:1', 'annual_interest_rate'=>'nullable|numeric|min:0|max:36', 'note' => 'nullable|string|max:1000']);
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
        abort_unless(in_array($type,['members','accounts','loans','transactions','cash','profit-loss'],true),404);
        $filters = $request->validate(['from'=>'nullable|date','to'=>'nullable|date|after_or_equal:from']);
        $from = isset($filters['from']) ? Carbon::parse($filters['from'])->toDateString() : null;
        $to = isset($filters['to']) ? Carbon::parse($filters['to'])->toDateString() : null;
        [$headers,$rows] = match ($type) {
            'members' => [
                ['Member No','Name','Phone','Status','KYC','MCS','Accounts','Active Balance','Loans','Outstanding Loan'],
                DB::table('members')->join('users','users.id','=','members.user_id')
                    ->select('members.member_no','users.name','users.phone','users.status','members.kyc_status','members.mcs_score')
                    ->selectRaw('(SELECT COUNT(*) FROM accounts WHERE accounts.member_id=members.id) account_count')
                    ->selectRaw("(SELECT COALESCE(SUM(balance),0) FROM accounts WHERE accounts.member_id=members.id AND accounts.status='active') account_balance")
                    ->selectRaw('(SELECT COUNT(*) FROM loan_applications WHERE loan_applications.member_id=members.id) loan_count')
                    ->selectRaw("(SELECT COALESCE(SUM(outstanding_principal),0) FROM loan_applications WHERE loan_applications.member_id=members.id AND loan_applications.status IN ('approved','disbursed')) loan_outstanding")
                    ->orderBy('users.name')->get(),
            ],
            'accounts' => [
                ['Account','Member','Scheme','Status','Opened','Matures','Balance','Rate'],
                DB::table('accounts')->join('members','members.id','=','accounts.member_id')->join('users','users.id','=','members.user_id')
                    ->join('schemes','schemes.id','=','accounts.scheme_id')
                    ->select('accounts.account_no','users.name','schemes.name as scheme','accounts.status','accounts.opened_on','accounts.matures_on','accounts.balance')
                    ->selectRaw('COALESCE(accounts.custom_interest_rate,schemes.annual_rate) rate')->orderBy('accounts.id')->get(),
            ],
            'loans' => [
                ['Application','Member','Type','EMI','Requested','Sanctioned','Outstanding','Rate','Status','Created'],
                DB::table('loan_applications')->join('members','members.id','=','loan_applications.member_id')->join('users','users.id','=','members.user_id')
                    ->select('loan_applications.application_no','users.name','loan_applications.loan_type','loan_applications.emi_frequency',
                        'loan_applications.amount','loan_applications.sanctioned_amount','loan_applications.outstanding_principal',
                        'loan_applications.annual_interest_rate','loan_applications.status','loan_applications.created_at')->orderBy('loan_applications.id')->get(),
            ],
            'transactions' => [
                ['Reference','Account','Direction','Amount','Description','Status','Posted'],
                DB::table('ledger_entries')->join('accounts','accounts.id','=','ledger_entries.account_id')
                    ->when($from,fn($q)=>$q->whereDate('ledger_entries.posted_at','>=',$from))->when($to,fn($q)=>$q->whereDate('ledger_entries.posted_at','<=',$to))
                    ->select('ledger_entries.reference','accounts.account_no','ledger_entries.direction','ledger_entries.amount','ledger_entries.description','ledger_entries.status','ledger_entries.posted_at')->orderBy('ledger_entries.id')->get(),
            ],
            'cash' => [
                ['Reference','Location','Direction','Category','Amount','Note','Date'],
                DB::table('cash_ledger_entries')->when($from,fn($q)=>$q->whereDate('occurred_on','>=',$from))->when($to,fn($q)=>$q->whereDate('occurred_on','<=',$to))
                    ->select('reference','location','direction','category','amount','note','occurred_on')->orderBy('id')->get(),
            ],
            default => [
                ['Reference','Type','Category','Amount','Note','Date'],
                DB::table('profit_loss_entries')->when($from,fn($q)=>$q->whereDate('occurred_on','>=',$from))->when($to,fn($q)=>$q->whereDate('occurred_on','<=',$to))
                    ->select('reference','entry_type','category','amount','note','occurred_on')->orderBy('id')->get(),
            ],
        };
        $filename = 'mahamilon-'.$type.'-'.today()->format('Ymd').'.csv';
        return response()->streamDownload(function () use ($headers,$rows) {
            $out = fopen('php://output','w');
            fputcsv($out,$headers);
            foreach ($rows as $row) {
                $values = array_values((array)$row);
                $values = array_map(fn($value) => is_string($value) && preg_match('/^[=+\-@]/',$value) ? "'".$value : $value, $values);
                fputcsv($out,$values);
            }
            fclose($out);
        },$filename,['Content-Type'=>'text/csv; charset=UTF-8']);
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

    public function downloadKyc(Request $request, int $id)
    {
        $this->staff($request);
        $document = DB::table('kyc_documents')->where('id', $id)->firstOrFail();
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);
        $this->audit($request, 'kyc.downloaded', 'kyc_document', $id, null, ['type' => $document->type]);
        return Storage::disk($document->disk)->download($document->path, basename($document->path), ['Content-Type' => $document->mime]);
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
}
