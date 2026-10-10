import * as SecureStore from "expo-secure-store";
import { Platform } from "react-native";
const BASE_URL = process.env.EXPO_PUBLIC_API_URL ?? "https://blue-albatross-762783.hostingersite.com/api/v1";
const TOKEN_KEY = "mahamilon_session";
const sessionStore = {
  get: async () => Platform.OS === "web" ? globalThis.localStorage?.getItem(TOKEN_KEY) ?? null : SecureStore.getItemAsync(TOKEN_KEY),
  set: async (value:string) => { if(Platform.OS === "web") globalThis.localStorage?.setItem(TOKEN_KEY,value); else await SecureStore.setItemAsync(TOKEN_KEY,value); },
  remove: async () => { if(Platform.OS === "web") globalThis.localStorage?.removeItem(TOKEN_KEY); else await SecureStore.deleteItemAsync(TOKEN_KEY); }
};
export class ApiError extends Error { constructor(message:string, public status:number, public fields?:Record<string,string[]>){super(message);} }
export type Dashboard={
  profile:{
    name:string;
    member_no:string;
    phone:string;
    email:string|null;
    address:string;
    nominee:string;
    occupation:string|null;
    kyc_status:string;
    kyc_rejection_note?:string|null;
    total_balance:string;
    savings_balance?:string;
    fd_balance?:string;
    rd_balance?:string;
    active_accounts:number;
    mcs_score:number;
    maximum_loan:number;
  };
  accounts:any[];
  payments:any[];
  receipts?:any[];
  loans:any[];
  installments:any[];
  repayments:any[];
  notifications:any[];
  rd_installments?:any[];
  profit_earned?:number;
  penalties_due?:number;
  documents?:any[];
  withdrawals?:any[];
  ledger:any[];
};
async function request<T>(path:string, init:RequestInit={}){
  const token=await sessionStore.get(); const bodyIsForm=init.body instanceof FormData;
  const response=await fetch(`${BASE_URL}${path}`,{...init,headers:{Accept:"application/json",...(bodyIsForm?{}:{"Content-Type":"application/json"}),...(token?{Authorization:`Bearer ${token}`}:{ }),...init.headers}});
  const data=response.status===204?null:await response.json().catch(()=>({message:"Invalid server response"}));
  if(!response.ok) throw new ApiError(data?.message??"Request failed",response.status,data?.errors); return data as T;
}
export const api={
  login:async(phone:string,mpin:string)=>{const data=await request<{token:string}>("/auth/login",{method:"POST",body:JSON.stringify({phone,mpin,device_name:Platform.OS==="web"?"Mahamilon local web":"Mahamilon mobile app"})}); await sessionStore.set(data.token); return data;},
  register:(payload:unknown)=>request<{message:string;member_no:string}>("/auth/register",{method:"POST",body:JSON.stringify(payload)}),
  dashboard:()=>request<Dashboard>("/dashboard"), schemes:()=>request<any[]>("/schemes"),
  getReceipts:()=>request<{data:any[];total:number}>("/receipts"),
  openAccount:(scheme_id:number,opening_deposit:number,share_count:number)=>request("/accounts",{method:"POST",body:JSON.stringify({scheme_id,opening_deposit,share_count})}),
 submitPayment:async(id:number,utr:string,receipt?:{uri:string;name:string;mimeType?:string})=>{const form=new FormData();form.append("utr",utr);if(receipt){if(Platform.OS==="web"){const blob=await fetch(receipt.uri).then(response=>response.blob());form.append("receipt",blob,receipt.name);}else form.append("receipt",{uri:receipt.uri,name:receipt.name,type:receipt.mimeType??"application/octet-stream"} as any);}return request(`/payments/${id}/submit`,{method:"POST",body:form});},
 applyLoan:(payload:unknown)=>request("/loans",{method:"POST",body:JSON.stringify(payload)}),
 respondToLoanOffer:(id:number,decision:"accept"|"decline")=>request(`/loans/${id}/offer-response`,{method:"POST",body:JSON.stringify({decision})}),
 submitLoanRepayment:(id:number,amount:number,utr:string)=>request(`/loans/${id}/repayments`,{method:"POST",body:JSON.stringify({amount,utr})}),
 markNotificationRead:(id:number)=>request(`/notifications/${id}/read`,{method:"POST"}),
 uploadKyc:async(type:string,asset:{uri:string;name:string;mimeType?:string})=>{const form=new FormData(); form.append("type",type); if(Platform.OS==="web"){const blob=await fetch(asset.uri).then(response=>response.blob()); form.append("document",blob,asset.name);}else form.append("document",{uri:asset.uri,name:asset.name,type:asset.mimeType??"application/octet-stream"} as any); return request<{message:string}>("/kyc",{method:"POST",body:form});},
 logout:async()=>{try{await request("/auth/logout",{method:"POST"});}finally{await sessionStore.remove();}},
 hasSession:async()=>Boolean(await sessionStore.get())
};
