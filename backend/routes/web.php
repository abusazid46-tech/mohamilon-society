<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
Route::redirect('/','/admin');
Route::get('/admin/login',[AdminController::class,'loginForm'])->name('login');
Route::post('/admin/login',[AdminController::class,'login'])->middleware('throttle:5,1');
Route::middleware('auth')->prefix('admin')->group(function(){
 Route::get('/',[AdminController::class,'dashboard']); Route::post('/logout',[AdminController::class,'logout']); Route::post('/password',[AdminController::class,'changePassword']);
 Route::post('/staff',[AdminController::class,'createStaff']); Route::post('/staff/{id}',[AdminController::class,'updateStaff']);
 Route::post('/members/{id}/decision',[AdminController::class,'memberDecision']); Route::post('/loans/{id}/decision',[AdminController::class,'loanDecision']); Route::post('/accounts/{id}/decision',[AdminController::class,'accountDecision']);
 Route::post('/accounts',[AdminController::class,'createAccount']); Route::post('/accounts/{id}/interest',[AdminController::class,'updateInterest']);
 Route::post('/payments/{id}/decision',[AdminController::class,'paymentDecision']); Route::post('/transactions',[AdminController::class,'postTransaction']);
 Route::post('/loans/offers',[AdminController::class,'createLoanOffer']);
 Route::post('/loan-repayments/{id}/decision',[AdminController::class,'repaymentDecision']);
 Route::post('/loan-installments/{id}/fine',[AdminController::class,'applyInstallmentFine']);
 Route::post('/finance/run',[AdminController::class,'runFinancialAutomation']);
 Route::post('/finance/cash',[AdminController::class,'cashEntry']);
 Route::post('/finance/profit-loss',[AdminController::class,'profitLossEntry']);
 Route::post('/finance/settings',[AdminController::class,'updateFinanceSettings']);
 Route::post('/accounts/{id}/maturity',[AdminController::class,'updateAccountMaturity']);
 Route::post('/accounts/{id}/closure',[AdminController::class,'accountClosure']);
 Route::get('/reports/{type}.csv',[AdminController::class,'exportReport']);
 Route::post('/kyc',[AdminController::class,'uploadKyc']); Route::post('/settings',[AdminController::class,'updateSettings']);
 Route::get('/kyc/{id}/download',[AdminController::class,'downloadKyc']); Route::post('/kyc/{id}/decision',[AdminController::class,'kycDecision']);
 Route::get('/members/{id}/kyc-folder',[AdminController::class,'downloadMemberKycFolder']);
 Route::post('/members/{id}/kyc-decision',[AdminController::class,'memberKycDecision']);
});
