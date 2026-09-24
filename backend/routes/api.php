<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MemberController;
Route::prefix('v1')->group(function () {
 Route::get('health', fn()=>['status'=>'ok','service'=>'mahamilon-api','time'=>now()->toIso8601String()]);
 Route::post('auth/register',[MemberController::class,'register'])->middleware('throttle:5,1');
 Route::post('auth/login',[MemberController::class,'login'])->middleware('throttle:10,1');
 Route::middleware('api.token')->group(function(){
  Route::post('auth/logout',[MemberController::class,'logout']); Route::get('me',[MemberController::class,'me']);
  Route::get('dashboard',[MemberController::class,'dashboard']); Route::get('schemes',[MemberController::class,'schemes']);
  Route::get('receipts',[MemberController::class,'receipts']);
  Route::post('accounts',[MemberController::class,'openAccount'])->middleware('throttle:10,1');
  Route::post('payments/{id}/submit',[MemberController::class,'submitPayment'])->middleware('throttle:10,1');
  Route::post('loans',[MemberController::class,'applyLoan'])->middleware('throttle:5,1');
  Route::post('loans/{id}/offer-response',[MemberController::class,'respondToLoanOffer'])->middleware('throttle:10,1');
  Route::post('loans/{id}/repayments',[MemberController::class,'submitLoanRepayment'])->middleware('throttle:10,1');
  Route::post('notifications/{id}/read',[MemberController::class,'markNotificationRead'])->middleware('throttle:30,1');
  Route::post('kyc',[MemberController::class,'submitKyc'])->middleware('throttle:5,1');
 });
});
