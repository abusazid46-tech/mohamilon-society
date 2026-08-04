<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;
class ApiTokenAuth {
 public function handle(Request $request, Closure $next) {
  $plain=$request->bearerToken(); if(!$plain) return response()->json(['message'=>'Unauthenticated'],401);
  $token=DB::table('api_tokens')->where('token_hash',hash('sha256',$plain))->where('expires_at','>',now())->first();
  if(!$token) return response()->json(['message'=>'Session expired'],401);
  $user=\App\Models\User::find($token->user_id); if(!$user||$user->status!=='active') return response()->json(['message'=>'Account unavailable'],403);
  $request->setUserResolver(fn()=>$user); DB::table('api_tokens')->where('id',$token->id)->update(['last_used_at'=>now()]); return $next($request);
 }
}
