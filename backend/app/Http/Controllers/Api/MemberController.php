<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CreditScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash};
use Illuminate\Support\Str;

class MemberController extends Controller
{
    private function member(Request $request): object
    {
        return DB::table('members')->where('user_id', $request->user()->id)->firstOrFail();
    }

    private function audit(Request $request, string $action, string $type, ?int $id, array $after = []): void
    {
        DB::table('audit_logs')->insert([
            'actor_id' => $request->user()?->id, 'action' => $action, 'entity_type' => $type,
            'entity_id' => $id, 'after' => json_encode($after), 'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500), 'created_at' => now(),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120', 'phone' => 'required|regex:/^[6-9][0-9]{9}$/|unique:users,phone',
            'email' => 'nullable|email|unique:users,email', 'mpin' => 'required|digits:6',
            'address' => 'required|string|max:1000', 'nominee_name' => 'required|string|max:120',
            'occupation' => 'nullable|string|max:120', 'aadhaar' => 'required|digits:12',
            'pan' => 'nullable|regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/',
        ]);
        return DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name' => $data['name'], 'email' => $data['email'] ?? null, 'phone' => $data['phone'],
                'password' => Hash::make(Str::random(48)), 'mpin_hash' => Hash::make($data['mpin']),
                'role' => 'member', 'status' => 'pending',
            ]);
            $memberNo = 'MS-'.now()->format('Y').'-'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
            $id = DB::table('members')->insertGetId([
                'user_id' => $user->id, 'member_no' => $memberNo, 'address' => $data['address'],
                'nominee_name' => $data['nominee_name'], 'occupation' => $data['occupation'] ?? null,
                'aadhaar_last4' => substr($data['aadhaar'], -4),
                'pan_last4' => isset($data['pan']) ? substr($data['pan'], -4) : null,
                'kyc_status' => 'pending', 'mcs_score' => 500, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->audit($request, 'member.registered', 'member', $id);
            return response()->json(['message' => 'Registration received for staff approval', 'member_no' => $memberNo], 201);
        });
    }

    public function login(Request $request)
    {
        $data = $request->validate(['phone' => 'required|string', 'mpin' => 'required|string', 'device_name' => 'required|string|max:100']);
        $user = User::where('phone', $data['phone'])->first();
        if (! $user || ! Hash::check($data['mpin'], $user->mpin_hash)) {
            return response()->json(['message' => 'Invalid phone or MPIN'], 422);
        }
        if ($user->status !== 'active') {
            return response()->json(['message' => 'Membership is not active', 'status' => $user->status], 403);
        }
        $plain = Str::random(80);
        DB::table('api_tokens')->insert([
            'user_id' => $user->id, 'token_hash' => hash('sha256', $plain),
            'device_name' => $data['device_name'], 'expires_at' => now()->addDays(30),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $user->update(['last_login_at' => now()]);
        return ['token' => $plain, 'token_type' => 'Bearer', 'expires_at' => now()->addDays(30)->toIso8601String()];
    }

    public function logout(Request $request)
    {
        DB::table('api_tokens')->where('token_hash', hash('sha256', (string) $request->bearerToken()))->delete();
        return response()->noContent();
    }

    public function me(Request $request)
    {
        return ['user' => $request->user()->only(['id','name','phone','email','status']), 'member' => $this->member($request)];
    }

    public function schemes()
    {
        return DB::table('schemes')->where('active', true)->orderBy('type')->get();
    }

    public function dashboard(Request $request)
    {
        $member = $this->member($request);
        $score = CreditScore::recalculate($member->id);
        $accounts = DB::table('accounts')->join('schemes', 'schemes.id', '=', 'accounts.scheme_id')
            ->where('member_id', $member->id)
            ->select('accounts.*', 'schemes.name as scheme_name', 'schemes.type as scheme_type',
                'schemes.deposit_frequency', DB::raw('COALESCE(accounts.custom_interest_rate, schemes.annual_rate) as effective_interest_rate'))
            ->get();
        $payments = DB::table('payments')->join('accounts', 'accounts.id', '=', 'payments.account_id')
            ->where('accounts.member_id', $member->id)
            ->select('payments.*', 'accounts.account_no')->latest('payments.id')->get();
        $totalSavings = (float) $accounts->where('status', 'active')->sum('balance');
        return [
            'profile' => [
                'name' => $request->user()->name, 'member_no' => $member->member_no, 'phone' => $request->user()->phone,
                'email' => $request->user()->email, 'address' => $member->address, 'nominee' => $member->nominee_name,
                'occupation' => $member->occupation, 'kyc_status' => $member->kyc_status,
                'total_balance' => (string) $totalSavings, 'active_accounts' => $accounts->where('status', 'active')->count(),
                'mcs_score' => $score, 'maximum_loan' => round($totalSavings * .85, 2),
            ],
            'accounts' => $accounts, 'payments' => $payments,
            'loans' => DB::table('loan_applications')->where('member_id', $member->id)->latest()->get(),
            'installments' => DB::table('loan_installments')->join('loan_applications','loan_applications.id','=','loan_installments.loan_id')
                ->where('loan_applications.member_id',$member->id)
                ->select('loan_installments.*','loan_applications.application_no')->orderBy('due_on')->limit(200)->get(),
            'repayments' => DB::table('loan_repayments')->join('loan_applications','loan_applications.id','=','loan_repayments.loan_id')
                ->where('loan_applications.member_id',$member->id)
                ->select('loan_repayments.*','loan_applications.application_no')->latest('loan_repayments.id')->get(),
            'notifications' => DB::table('member_notifications')->where('user_id',$request->user()->id)
                ->latest('id')->limit(50)->get(),
            'ledger' => DB::table('ledger_entries')->join('accounts', 'accounts.id', '=', 'ledger_entries.account_id')
                ->where('accounts.member_id', $member->id)->select('ledger_entries.*', 'accounts.account_no')->latest('ledger_entries.id')->limit(50)->get(),
        ];
    }

    public function openAccount(Request $request)
    {
        $data = $request->validate([
            'scheme_id' => 'required|exists:schemes,id', 'opening_deposit' => 'required|numeric|min:1',
            'share_count' => 'nullable|integer|min:1|max:100',
        ]);
        $member = $this->member($request);
        $scheme = DB::table('schemes')->where('id', $data['scheme_id'])->where('active', true)->firstOrFail();
        $shares = (int) ($data['share_count'] ?? 1);
        $minimum = $scheme->code === 'SAVINGS' || $scheme->code === 'RD-MONTHLY' ? 500 * $shares : (float) $scheme->minimum_amount;
        if ((float) $data['opening_deposit'] < $minimum) {
            return response()->json(['message' => "Minimum amount is ₹".number_format($minimum, 2)], 422);
        }
        $id = DB::table('accounts')->insertGetId([
            'account_no' => 'AC'.now()->format('ymd').str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'member_id' => $member->id, 'scheme_id' => $scheme->id, 'share_count' => $shares,
            'monthly_commitment' => in_array($scheme->code, ['SAVINGS','RD-MONTHLY'], true) ? 500 * $shares : 0,
            'principal' => $data['opening_deposit'], 'balance' => 0, 'status' => 'pending',
            'onboarding_status' => 'awaiting_review', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->audit($request, 'account.requested', 'account', $id, $data);
        return response()->json(['message' => 'Account request submitted for approval', 'id' => $id], 201);
    }

    public function submitPayment(Request $request, int $id)
    {
        $data = $request->validate([
            'utr' => 'required|string|min:6|max:80',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        $member = $this->member($request);
        $payment = DB::table('payments')->join('accounts', 'accounts.id', '=', 'payments.account_id')
            ->where('payments.id', $id)->where('accounts.member_id', $member->id)
            ->select('payments.*')->firstOrFail();
        abort_unless(in_array($payment->status, ['requested','rejected'], true), 409, 'Payment is not awaiting submission.');
        $receiptPath = $payment->receipt_path;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('payments/'.$member->id, 'private');
        }
        DB::transaction(function () use ($request, $data, $payment, $receiptPath) {
            DB::table('payments')->where('id', $payment->id)->update([
                'utr' => $data['utr'], 'receipt_disk' => $receiptPath ? 'private' : null, 'receipt_path' => $receiptPath,
                'status' => 'submitted', 'submitted_by' => $request->user()->id, 'submitted_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('accounts')->where('id', $payment->account_id)->update(['onboarding_status' => 'payment_submitted', 'updated_at' => now()]);
        });
        $this->audit($request, 'payment.submitted', 'payment', $payment->id, ['utr' => $data['utr']]);
        return ['message' => 'Payment submitted for verification'];
    }

    public function applyLoan(Request $request)
    {
        $data = $request->validate([
            'loan_type' => 'required|in:personal,business,emergency',
            'emi_frequency' => 'required|in:daily,weekly,monthly',
            'amount' => 'required|numeric|min:1000|max:5000000', 'monthly_income' => 'required|numeric|min:1',
            'tenure_months' => 'required|integer|min:1|max:120', 'purpose' => 'required|string|max:1000',
        ]);
        $member = $this->member($request);
        if ($member->kyc_status !== 'approved') {
            return response()->json(['message' => 'Approved KYC is required'], 422);
        }
        $totalSavings = (float) DB::table('accounts')->where('member_id', $member->id)->where('status', 'active')->sum('balance');
        $limit = round($totalSavings * .85, 2);
        if ((float) $data['amount'] > $limit) {
            return response()->json(['message' => 'Requested amount exceeds 85% savings limit of ₹'.number_format($limit, 2)], 422);
        }
        $score = CreditScore::recalculate($member->id);
        $id = DB::table('loan_applications')->insertGetId($data + [
            'application_no' => 'LN'.now()->format('ymd').str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'member_id' => $member->id, 'mcs_snapshot' => $score, 'status' => 'submitted',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->audit($request, 'loan.submitted', 'loan_application', $id, $data);
        return response()->json([
            'message' => $data['loan_type'] === 'emergency'
                ? 'Emergency loan submitted. Decision is due within 3 days.'
                : 'Loan application submitted',
            'id' => $id, 'maximum_eligible' => $limit,
        ], 201);
    }

    public function respondToLoanOffer(Request $request, int $id)
    {
        $data = $request->validate(['decision' => 'required|in:accept,decline']);
        $member = $this->member($request);
        $loan = DB::table('loan_applications')->where('id', $id)->where('member_id', $member->id)
            ->where('is_offer', true)->where('status', 'submitted')->firstOrFail();
        abort_if($loan->offer_expires_at && now()->isAfter($loan->offer_expires_at), 409, 'This offer has expired.');
        DB::table('loan_applications')->where('id', $id)->update([
            'status' => $data['decision'] === 'accept' ? 'under_review' : 'rejected',
            'decision_note' => $data['decision'] === 'decline' ? 'Declined by member' : 'Offer accepted by member',
            'updated_at' => now(),
        ]);
        $this->audit($request, 'loan.offer_'.$data['decision'], 'loan_application', $id);
        return ['message' => 'Loan offer '.$data['decision'].'ed'];
    }

    public function submitLoanRepayment(Request $request, int $id)
    {
        $data = $request->validate(['amount'=>'required|numeric|min:1','utr'=>'required|string|min:6|max:80']);
        $member = $this->member($request);
        $loan = DB::table('loan_applications')->where('id',$id)->where('member_id',$member->id)
            ->whereIn('status',['approved','disbursed'])->firstOrFail();
        $openDue = (float) DB::table('loan_installments')->where('loan_id',$loan->id)
            ->selectRaw('COALESCE(SUM(principal_due + interest_due + penalty_due - paid_amount),0) due')->value('due');
        abort_if($openDue <= 0, 409, 'This loan has no outstanding installments.');
        abort_if((float)$data['amount'] > $openDue + .01, 422, 'Repayment exceeds the outstanding schedule amount.');
        abort_if(DB::table('loan_repayments')->where('utr',$data['utr'])->whereIn('status',['submitted','accepted'])->exists(),422,'This UTR is already in use.');
        $repaymentId = DB::table('loan_repayments')->insertGetId([
            'reference'=>'LR'.now()->format('ymd').str_pad((string)random_int(1,999999),6,'0',STR_PAD_LEFT),
            'loan_id'=>$loan->id,'amount'=>$data['amount'],'utr'=>$data['utr'],'status'=>'submitted',
            'submitted_by'=>$request->user()->id,'submitted_at'=>now(),'created_at'=>now(),'updated_at'=>now(),
        ]);
        $this->audit($request,'loan.repayment_submitted','loan_repayment',$repaymentId,$data);
        return response()->json(['message'=>'Repayment submitted for staff verification','id'=>$repaymentId],201);
    }

    public function markNotificationRead(Request $request, int $id)
    {
        DB::table('member_notifications')->where('id',$id)->where('user_id',$request->user()->id)
            ->update(['read_at'=>now(),'updated_at'=>now()]);
        return response()->noContent();
    }

    public function submitKyc(Request $request)
    {
        $data = $request->validate(['type' => 'required|in:aadhaar,pan,photo,address_proof', 'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120']);
        $member = $this->member($request);
        $file = $request->file('document');
        $path = $file->store('kyc/'.$member->id, 'private');
        $id = DB::table('kyc_documents')->insertGetId([
            'member_id' => $member->id, 'type' => $data['type'], 'disk' => 'private', 'path' => $path,
            'sha256' => hash_file('sha256', $file->getRealPath()), 'mime' => $file->getMimeType(),
            'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('members')->where('id', $member->id)->update(['kyc_status' => 'pending', 'updated_at' => now()]);
        $this->audit($request, 'kyc.uploaded', 'kyc_document', $id);
        return response()->json(['message' => 'Document uploaded for review', 'id' => $id], 201);
    }
}
