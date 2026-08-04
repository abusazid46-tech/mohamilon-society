import { Ionicons, MaterialCommunityIcons } from "@expo/vector-icons";
import { LinearGradient } from "expo-linear-gradient";
import * as Notifications from "expo-notifications";
import * as DocumentPicker from "expo-document-picker";
import React, { useEffect, useState } from "react";
import {
  Alert,
  Image,
  KeyboardAvoidingView,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
  ActivityIndicator,
  RefreshControl,
  Linking
} from "react-native";
import { SafeAreaProvider, SafeAreaView, useSafeAreaInsets } from "react-native-safe-area-context";
import { Scheme } from "./src/domain";
import { colors, shadow } from "./src/theme";
import { api, ApiError, Dashboard } from "./src/api";

Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldPlaySound: false,
    shouldSetBadge: true,
    shouldShowBanner: true,
    shouldShowList: true
  })
});

type Tab = "home" | "accounts" | "loan" | "kyc" | "profile";

type LoginForm = {
  phone: string;
  otp: string;
};

type AuthMode = "login" | "register";

type RegistrationForm = {
  fullName: string;
  phone: string;
  aadhaar: string;
  pan: string;
  address: string;
  nominee: string;
  occupation: string;
  mpin: string;
};

type AccountForm = {
  plan: string;
  deposit: string;
  shares: string;
};

type LoanForm = {
  amount: string;
  purpose: string;
  income: string;
  tenure: string;
  loanType: "personal" | "business" | "emergency";
  emiFrequency: "daily" | "weekly" | "monthly";
};

const emptyDashboard: Dashboard = {profile:{name:"",member_no:"",phone:"",email:null,address:"",nominee:"",occupation:null,kyc_status:"not_submitted",total_balance:"0",active_accounts:0,mcs_score:500,maximum_loan:0},accounts:[],payments:[],loans:[],installments:[],repayments:[],notifications:[],ledger:[]};

const tabs: { id: Tab; label: string; icon: keyof typeof Ionicons.glyphMap }[] = [
  { id: "home", label: "Home", icon: "home-outline" },
  { id: "accounts", label: "Accounts", icon: "wallet-outline" },
  { id: "loan", label: "Loan", icon: "document-text-outline" },
  { id: "kyc", label: "KYC", icon: "shield-checkmark-outline" },
  { id: "profile", label: "Profile", icon: "person-outline" }
];

export default function App() { return <SafeAreaProvider><AppContent /></SafeAreaProvider>; }

function AppContent() {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [authMode, setAuthMode] = useState<AuthMode>("login");
  const [activeTab, setActiveTab] = useState<Tab>("home");
  const [dashboard, setDashboard] = useState<Dashboard>(emptyDashboard);
  const [liveSchemes, setLiveSchemes] = useState<Scheme[]>([]);
  const [loading, setLoading] = useState(false);
  const [loginForm, setLoginForm] = useState<LoginForm>({
    phone: "",
    otp: ""
  });
  const [registrationForm, setRegistrationForm] = useState<RegistrationForm>({
    fullName: "",
    phone: "",
    aadhaar: "",
    pan: "",
    address: "",
    nominee: "",
    occupation: "",
    mpin: ""
  });
  const [accountForm, setAccountForm] = useState<AccountForm>({
    plan: "Daily RD Account",
    deposit: "",
    shares: "1"
  });
  const [loanForm, setLoanForm] = useState<LoanForm>({
    amount: "",
    purpose: "",
    income: "",
    tenure: "",
    loanType: "personal",
    emiFrequency: "monthly"
  });

  useEffect(() => {
    Notifications.requestPermissionsAsync().catch(() => undefined);
    api.hasSession().then(async (hasSession) => { if(hasSession){setIsLoggedIn(true); await refreshData();} }).catch(() => undefined);
  }, []);

  const refreshData = async () => {
    setLoading(true);
    try {
      const [data,serverSchemes]=await Promise.all([api.dashboard(),api.schemes()]);
      setDashboard(data);
      setLiveSchemes(serverSchemes.map((s:any)=>({
        id:String(s.id), title:s.name, type:s.type.toUpperCase()==="FD"?"FD":"RD",
        frequency:s.deposit_frequency?s.deposit_frequency[0].toUpperCase()+s.deposit_frequency.slice(1):"Monthly",
        amount:`Minimum ₹${Number(s.minimum_amount).toLocaleString("en-IN")}`,
        term:s.term_months?`${s.term_months} months`:"Flexible", rate:`${s.annual_rate}% p.a.`,
        status:"Available", maturity:s.description??"Calculated after approval"
      } as Scheme)));
    } catch(error) {
      if(error instanceof ApiError&&error.status===401){await api.logout();setIsLoggedIn(false);}
      else Alert.alert("Sync failed",error instanceof ApiError?error.message:"Unable to reach server");
    } finally { setLoading(false); }
  };

  const login = async () => {
    const hasPhone = loginForm.phone.trim().length >= 10;
    if (!hasPhone) {
      Alert.alert("Check login details", "Enter your registered mobile number.");
      return;
    }
    if (!loginForm.otp.trim()) { Alert.alert("MPIN required", "Enter your six-digit MPIN."); return; }
    try { await api.login(loginForm.phone.trim(), loginForm.otp.trim()); setIsLoggedIn(true); await refreshData(); }
    catch (error) { Alert.alert("Login failed", error instanceof ApiError ? error.message : "Unable to reach the society server."); }
  };

  const register = async () => {
    const requiredFields = [
      registrationForm.fullName,
      registrationForm.phone,
      registrationForm.aadhaar,
      registrationForm.address,
      registrationForm.nominee,
      registrationForm.mpin
    ];
    const hasMissing = requiredFields.some((value) => value.trim().length === 0);
    if (hasMissing || registrationForm.phone.trim().length < 10) {
      Alert.alert(
        "Complete registration",
        "Enter name, mobile, Aadhaar, address, nominee and MPIN."
      );
      return;
    }

    try { const result=await api.register({name:registrationForm.fullName.trim(),phone:registrationForm.phone.trim(),aadhaar:registrationForm.aadhaar.trim(),pan:registrationForm.pan.trim().toUpperCase()||undefined,address:registrationForm.address.trim(),nominee_name:registrationForm.nominee.trim(),occupation:registrationForm.occupation.trim()||undefined,mpin:registrationForm.mpin.trim()}); Alert.alert("Registration submitted",`${result.message}\nMember number: ${result.member_no}`); }
    catch(error){ Alert.alert("Registration failed",error instanceof ApiError?error.message:"Unable to reach the society server."); return; }
    setLoginForm((prev) => ({
      ...prev,
      phone: registrationForm.phone
    }));
    setRegistrationForm({
      fullName: "",
      phone: "",
      aadhaar: "",
      pan: "",
      address: "",
      nominee: "",
      occupation: "",
      mpin: ""
    });
    setAuthMode("login");
  };

  const sendLocalNotification = async () => {
    await Notifications.scheduleNotificationAsync({
      content: {
        title: "Mahamilon Society",
        body: "Your member notification is ready."
      },
      trigger: null
    });
    Alert.alert("Notification sent", "A local test notification was queued on this device.");
  };

  const submitAccount = async () => {
    const scheme=liveSchemes.find(item=>item.title===accountForm.plan)??liveSchemes[0]; if(!scheme||!Number(accountForm.deposit)){Alert.alert("Complete request","Choose a scheme and enter the opening deposit.");return;} try { await api.openAccount(Number(scheme.id),Number(accountForm.deposit),Math.max(1,Number(accountForm.shares)||1)); await refreshData(); Alert.alert("Request submitted", "After staff approval, your UPI QR will appear here for the opening payment."); } catch(error){Alert.alert("Request failed",error instanceof ApiError?error.message:"Server unavailable");return;}
    setAccountForm({ plan: "Daily RD Account", deposit: "", shares:"1" });
  };

  const submitLoan = async () => {
    try { await api.applyLoan({loan_type:loanForm.loanType,emi_frequency:loanForm.emiFrequency,amount:Number(loanForm.amount),purpose:loanForm.purpose,monthly_income:Number(loanForm.income),tenure_months:Number(loanForm.tenure)}); await refreshData(); Alert.alert("Loan application submitted",loanForm.loanType==="emergency"?"Emergency request is due for decision within three days.":"Your application is now under review."); } catch(error){Alert.alert("Application failed",error instanceof ApiError?error.message:"Server unavailable");return;}
    setLoanForm({ amount:"",purpose:"",income:"",tenure:"",loanType:"personal",emiFrequency:"monthly" });
  };

  const submitPayment = async (payment:any,utr:string) => {
    if(!utr.trim())return;
    try{await api.submitPayment(payment.id,utr.trim());await refreshData();Alert.alert("Submitted","Payment is awaiting staff verification.");}
    catch(error){Alert.alert("Failed",error instanceof ApiError?error.message:"Unable to submit payment");}
  };

  const respondToOffer=async(id:number,decision:"accept"|"decline")=>{
    try{await api.respondToLoanOffer(id,decision);await refreshData();Alert.alert("Offer updated",`Loan offer ${decision}ed.`);}
    catch(error){Alert.alert("Unable to update offer",error instanceof ApiError?error.message:"Server unavailable");}
  };

  const submitLoanRepayment=async(id:number,amount:number,utr:string)=>{
    try{await api.submitLoanRepayment(id,amount,utr);await refreshData();Alert.alert("Repayment submitted","Staff will verify the UTR before updating your EMI schedule.");}
    catch(error){Alert.alert("Repayment failed",error instanceof ApiError?error.message:"Server unavailable");}
  };

  const readNotification=async(id:number)=>{try{await api.markNotificationRead(id);await refreshData();}catch{}};

  const submitKyc = async (type:"aadhaar"|"pan"|"photo"|"address_proof") => { const result=await DocumentPicker.getDocumentAsync({type:["image/jpeg","image/png","application/pdf"],copyToCacheDirectory:true}); if(result.canceled)return; setLoading(true); try{await api.uploadKyc(type,result.assets[0]);await refreshData();Alert.alert("KYC uploaded","The document is awaiting staff review.");}catch(error){Alert.alert("Upload failed",error instanceof ApiError?error.message:"Unable to upload document");}finally{setLoading(false);} };

  if (!isLoggedIn) {
    return (
      <AuthScreen
        mode={authMode}
        setMode={setAuthMode}
        loginForm={loginForm}
        setLoginForm={setLoginForm}
        registrationForm={registrationForm}
        setRegistrationForm={setRegistrationForm}
        onLogin={login}
        onRegister={register}
      />
    );
  }

  return (
    <SafeAreaView style={styles.safeArea} edges={["top","left","right"]}>
      <KeyboardAvoidingView
        style={styles.keyboard}
        behavior={Platform.select({ ios: "padding", android: undefined })}
      >
        <View style={styles.appShell}>
            <ScrollView
            style={styles.content}
            contentContainerStyle={styles.contentInner}
              showsVerticalScrollIndicator={false}
              refreshControl={<RefreshControl refreshing={loading} onRefresh={refreshData} />}
            >
            <MemberHeader profile={dashboard.profile} onLogout={() => api.logout().finally(() => setIsLoggedIn(false))} />
            {activeTab === "home" && (
              <HomeScreen
                setActiveTab={setActiveTab}
                sendLocalNotification={sendLocalNotification}
                dashboard={dashboard}
                readNotification={readNotification}
              />
            )}
            {activeTab === "accounts" && (
              <AccountsScreen
                form={accountForm}
                setForm={setAccountForm}
                submitAccount={submitAccount}
                schemes={liveSchemes}
                accounts={dashboard.accounts}
                payments={dashboard.payments}
                submitPayment={submitPayment}
              />
            )}
            {activeTab === "loan" && (
              <LoanScreen form={loanForm} setForm={setLoanForm} submitLoan={submitLoan} loans={dashboard.loans} installments={dashboard.installments??[]} repayments={dashboard.repayments??[]} profile={dashboard.profile} respondToOffer={respondToOffer} submitRepayment={submitLoanRepayment} />
            )}
            {activeTab === "kyc" && (
              <KycScreen kycStatus={dashboard.profile.kyc_status} submitKyc={submitKyc} />
            )}
            {activeTab === "profile" && <ProfileScreen profile={dashboard.profile} />}
            {loading && dashboard.profile.name==="" && <ActivityIndicator size="large" color={colors.navy} />}
          </ScrollView>
          <BottomNav activeTab={activeTab} setActiveTab={setActiveTab} />
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

function AuthScreen({
  mode,
  setMode,
  loginForm,
  setLoginForm,
  registrationForm,
  setRegistrationForm,
  onLogin,
  onRegister
}: {
  mode: AuthMode;
  setMode: (mode: AuthMode) => void;
  loginForm: LoginForm;
  setLoginForm: React.Dispatch<React.SetStateAction<LoginForm>>;
  registrationForm: RegistrationForm;
  setRegistrationForm: React.Dispatch<React.SetStateAction<RegistrationForm>>;
  onLogin: () => void;
  onRegister: () => void;
}) {
  return (
    <SafeAreaView style={styles.loginSafe} edges={["top","bottom","left","right"]}>
      <KeyboardAvoidingView
        style={styles.keyboard}
        behavior={Platform.select({ ios: "padding", android: undefined })}
      >
        <ScrollView contentContainerStyle={styles.loginContent} keyboardShouldPersistTaps="handled">
          <LinearGradient colors={[colors.navyDark, colors.navy, colors.redDark]} style={styles.loginHero}>
            <Image source={require("./assets/mahamilon-logo.png")} style={styles.loginLogo} />
            <Text style={styles.loginTitle}>Mahamilon Society</Text>
            <Text style={styles.loginSubtitle}>Member banking access</Text>
          </LinearGradient>

          <View style={styles.authSwitch}>
            <Pressable
              style={[styles.authSwitchButton, mode === "login" && styles.authSwitchButtonActive]}
              onPress={() => setMode("login")}
            >
              <Ionicons
                name="log-in-outline"
                size={18}
                color={mode === "login" ? colors.white : colors.navy}
              />
              <Text style={[styles.authSwitchText, mode === "login" && styles.authSwitchTextActive]}>
                Login
              </Text>
            </Pressable>
            <Pressable
              style={[styles.authSwitchButton, mode === "register" && styles.authSwitchButtonActive]}
              onPress={() => setMode("register")}
            >
              <Ionicons
                name="person-add-outline"
                size={18}
                color={mode === "register" ? colors.white : colors.navy}
              />
              <Text
                style={[styles.authSwitchText, mode === "register" && styles.authSwitchTextActive]}
              >
                Register
              </Text>
            </Pressable>
          </View>

          {mode === "login" ? (
            <LoginPanel form={loginForm} setForm={setLoginForm} onLogin={onLogin} />
          ) : (
            <RegistrationPanel
              form={registrationForm}
              setForm={setRegistrationForm}
              onRegister={onRegister}
              onBackToLogin={() => setMode("login")}
            />
          )}

          <View style={styles.memberOnlyNotice}>
            <Ionicons name="business-outline" size={20} color={colors.navy} />
            <Text style={styles.noticeText}>
              Staff operations are handled in a separate secure web panel.
            </Text>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

function LoginPanel({
  form,
  setForm,
  onLogin
}: {
  form: LoginForm;
  setForm: React.Dispatch<React.SetStateAction<LoginForm>>;
  onLogin: () => void;
}) {
  return (
          <View style={styles.loginPanel}>
            <Text style={styles.panelTitleLarge}>Member Login</Text>
            <FormInput
              label="Registered mobile number"
              value={form.phone}
              keyboardType="phone-pad"
              onChangeText={(phone) => setForm((prev) => ({ ...prev, phone }))}
            />
            <FormInput
              label="OTP / MPIN"
              value={form.otp}
              keyboardType="numeric"
              secureTextEntry
              onChangeText={(otp) => setForm((prev) => ({ ...prev, otp }))}
            />
            <ActionButton label="Login" icon="log-in-outline" onPress={onLogin} />
          </View>
  );
}

function RegistrationPanel({
  form,
  setForm,
  onRegister,
  onBackToLogin
}: {
  form: RegistrationForm;
  setForm: React.Dispatch<React.SetStateAction<RegistrationForm>>;
  onRegister: () => void;
  onBackToLogin: () => void;
}) {
  return (
    <View style={styles.loginPanel}>
      <View style={styles.registrationTitleRow}>
        <View style={styles.registrationTitleCopy}>
          <Text style={styles.panelTitleLarge}>New Member Registration</Text>
          <Text style={styles.helperText}>Submit member details for manual KYC approval.</Text>
        </View>
        <Ionicons name="shield-checkmark-outline" size={28} color={colors.navy} />
      </View>

      <FormInput
        label="Full name"
        value={form.fullName}
        onChangeText={(fullName) => setForm((prev) => ({ ...prev, fullName }))}
        autoCapitalize="words"
      />
      <FormInput
        label="Mobile number"
        value={form.phone}
        keyboardType="phone-pad"
        onChangeText={(phone) => setForm((prev) => ({ ...prev, phone }))}
      />
      <FormInput
        label="Aadhaar / ID number"
        value={form.aadhaar}
        keyboardType="numeric"
        onChangeText={(aadhaar) => setForm((prev) => ({ ...prev, aadhaar }))}
      />
      <FormInput
        label="PAN / tax ID"
        value={form.pan}
        autoCapitalize="characters"
        onChangeText={(pan) => setForm((prev) => ({ ...prev, pan }))}
      />
      <FormInput
        label="Current address"
        value={form.address}
        onChangeText={(address) => setForm((prev) => ({ ...prev, address }))}
      />
      <FormInput
        label="Nominee name"
        value={form.nominee}
        onChangeText={(nominee) => setForm((prev) => ({ ...prev, nominee }))}
        autoCapitalize="words"
      />
      <FormInput
        label="Occupation"
        value={form.occupation}
        onChangeText={(occupation) => setForm((prev) => ({ ...prev, occupation }))}
      />
      <FormInput
        label="Create MPIN"
        value={form.mpin}
        keyboardType="numeric"
        secureTextEntry
        onChangeText={(mpin) => setForm((prev) => ({ ...prev, mpin }))}
      />

      <ActionButton label="Submit Registration" icon="person-add-outline" onPress={onRegister} />
      <Pressable style={styles.textButton} onPress={onBackToLogin}>
        <Text style={styles.textButtonLabel}>Already registered? Login</Text>
      </Pressable>
    </View>
  );
}

function MemberHeader({ profile, onLogout }: { profile: Dashboard["profile"]; onLogout: () => void }) {
  return (
    <LinearGradient colors={[colors.navyDark, colors.navy]} style={styles.header}>
      <View style={styles.headerTop}>
        <Image source={require("./assets/mahamilon-logo.png")} style={styles.headerLogo} />
        <View style={styles.headerCopy}>
          <Text style={styles.headerLabel}>Member account</Text>
          <Text style={styles.headerTitle}>Mahamilon Society</Text>
        </View>
        <Pressable style={styles.iconButton} onPress={onLogout}>
          <Ionicons name="log-out-outline" size={20} color={colors.white} />
        </Pressable>
      </View>
      <View style={styles.memberCard}>
        <View>
          <Text style={styles.memberName}>{profile.name}</Text>
          <Text style={styles.memberMeta}>{profile.member_no}</Text>
        </View>
        <StatusPill label={profile.kyc_status} />
      </View>
    </LinearGradient>
  );
}

function HomeScreen({
  setActiveTab,
  sendLocalNotification,
  dashboard
  ,readNotification
}: {
  setActiveTab: (tab: Tab) => void;
  sendLocalNotification: () => void;
  dashboard: Dashboard;
  readNotification: (id:number)=>void;
}) {
  return (
    <>
      <View style={styles.balancePanel}>
        <Text style={styles.balanceLabel}>Savings balance</Text>
        <Text style={styles.balanceValue}>₹{Number(dashboard.profile.total_balance).toLocaleString("en-IN",{minimumFractionDigits:2})}</Text>
        <Text style={styles.balanceHint}>Updated from the live society ledger</Text>
      </View>

      <View style={styles.metricGrid}>
        <MetricCard icon="trending-up-outline" label="Total balance" value={`₹${Number(dashboard.profile.total_balance).toLocaleString("en-IN")}`} />
        <MetricCard icon="albums-outline" label="Accounts" value={String(dashboard.profile.active_accounts)} />
        <MetricCard icon="shield-checkmark-outline" label="KYC" value={dashboard.profile.kyc_status.replaceAll("_"," ")} />
        <MetricCard icon="speedometer-outline" label="MCS score" value={String(dashboard.profile.mcs_score)} />
      </View>

      <View style={styles.quickActions}>
        <QuickAction icon="wallet-outline" label="Open RD/FD" onPress={() => setActiveTab("accounts")} />
        <QuickAction icon="document-text-outline" label="Apply Loan" onPress={() => setActiveTab("loan")} />
        <QuickAction icon="shield-outline" label="Update KYC" onPress={() => setActiveTab("kyc")} />
      </View>

      <Panel title="Recent Ledger" icon="receipt-outline">
        {dashboard.ledger.length===0?<Text style={styles.helperText}>No ledger entries yet.</Text>:dashboard.ledger.map((item:any) => (
          <InfoRow key={item.id} label={`${item.description} | ${new Date(item.created_at).toLocaleDateString()}`} value={`₹${item.amount} | ${item.status}`} />
        ))}
      </Panel>

      <NotificationList sendLocalNotification={sendLocalNotification} notifications={dashboard.notifications??[]} readNotification={readNotification} />
    </>
  );
}

function AccountsScreen({
  form,
  setForm,
  submitAccount,
  schemes,
  accounts,
  payments,
  submitPayment
}: {
  form: AccountForm;
  setForm: React.Dispatch<React.SetStateAction<AccountForm>>;
  submitAccount: () => void;
  schemes: Scheme[];
  accounts: any[];
  payments: any[];
  submitPayment: (payment:any,utr:string) => void;
}) {
  const [utrs,setUtrs]=useState<Record<number,string>>({});
  return (
    <>
      <Panel title="My Accounts" icon="wallet-outline">
        {accounts.length===0?<Text style={styles.helperText}>No account requests yet.</Text>:accounts.map((account:any)=>(
          <View key={account.id} style={styles.loanItem}>
            <View style={styles.loanTop}><Text style={styles.cardTitle}>{account.scheme_name}</Text><StatusPill label={account.onboarding_status?.replaceAll("_"," ")??account.status}/></View>
            <Text style={styles.cardMeta}>{account.account_no} · {account.share_count} share(s) · {account.effective_interest_rate}% p.a.</Text>
            <InfoRow label="Balance" value={`₹${Number(account.balance).toLocaleString("en-IN")}`}/>
            <InfoRow label="Monthly commitment" value={`₹${Number(account.monthly_commitment).toLocaleString("en-IN")}`}/>
          </View>
        ))}
      </Panel>

      {payments.filter((payment:any)=>["requested","rejected"].includes(payment.status)).map((payment:any)=>(
        <Panel key={payment.id} title="Opening Payment Required" icon="qr-code-outline">
          <Text style={styles.helperText}>Pay exactly ₹{Number(payment.amount).toLocaleString("en-IN")} for account {payment.account_no}. The account activates only after staff verifies your UTR.</Text>
          <Image source={{uri:`https://quickchart.io/qr?size=260&text=${encodeURIComponent(payment.upi_uri)}`}} style={styles.paymentQr} resizeMode="contain"/>
          <ActionButton label="Open UPI App" icon="phone-portrait-outline" onPress={()=>Linking.openURL(payment.upi_uri)}/>
          <FormInput label="UPI transaction reference (UTR)" value={utrs[payment.id]??""} onChangeText={(value)=>setUtrs(prev=>({...prev,[payment.id]:value}))} autoCapitalize="characters"/>
          <ActionButton label="Submit Payment for Verification" icon="checkmark-circle-outline" onPress={()=>submitPayment(payment,utrs[payment.id]??"")}/>
          {payment.status==="rejected"&&<Text style={[styles.helperText,{color:colors.red}]}>Previous submission was rejected. Check the UTR and submit again.</Text>}
        </Panel>
      ))}
      {payments.filter((payment:any)=>payment.status==="submitted").map((payment:any)=>(
        <Panel key={payment.id} title="Payment Under Review" icon="time-outline"><InfoRow label="Reference" value={payment.reference}/><InfoRow label="UTR" value={payment.utr}/><InfoRow label="Status" value="Awaiting staff verification" emphasis/></Panel>
      ))}

      <Panel title="RD Accounts" icon="repeat-outline">
        {schemes
          .filter((scheme) => scheme.type === "RD")
          .map((scheme) => (
            <SchemeCard key={scheme.id} scheme={scheme} />
          ))}
      </Panel>

      <Panel title="FD Account" icon="lock-closed-outline">
        {schemes
          .filter((scheme) => scheme.type === "FD")
          .map((scheme) => (
            <SchemeCard key={scheme.id} scheme={scheme} />
          ))}
      </Panel>

      <Panel title="Account Opening Request" icon="person-add-outline">
        <Text style={styles.fieldLabel}>Choose scheme</Text>
        {schemes.map((scheme)=><Pressable key={scheme.id} style={[styles.quickAction,form.plan===scheme.title&&{borderColor:colors.navy,backgroundColor:"#edf4ff"}]} onPress={()=>setForm(prev=>({...prev,plan:scheme.title}))}><Ionicons name={form.plan===scheme.title?"radio-button-on":"radio-button-off"} size={20} color={colors.navy}/><Text style={styles.quickActionText}>{scheme.title} · {scheme.amount}</Text></Pressable>)}
        <FormInput
          label="Opening deposit"
          value={form.deposit}
          keyboardType="numeric"
          onChangeText={(deposit) => setForm((prev) => ({ ...prev, deposit }))}
        />
        <FormInput label="Number of ₹500 monthly shares" value={form.shares} keyboardType="numeric" onChangeText={(shares)=>setForm(prev=>({...prev,shares}))}/>
        <ActionButton label="Submit Request" icon="cloud-upload-outline" onPress={submitAccount} />
      </Panel>
    </>
  );
}

function LoanScreen({
  form,
  setForm,
  submitLoan,
  loans,
  installments,
  repayments,
  profile,
  respondToOffer,
  submitRepayment
}: {
  form: LoanForm;
  setForm: React.Dispatch<React.SetStateAction<LoanForm>>;
  submitLoan: () => void;
  loans: any[];
  installments: any[];
  repayments: any[];
  profile: Dashboard["profile"];
  respondToOffer: (id:number,decision:"accept"|"decline")=>void;
  submitRepayment: (id:number,amount:number,utr:string)=>void;
}) {
  const [repaymentForms,setRepaymentForms]=useState<Record<number,{amount:string;utr:string}>>({});
  return (
    <>
      <Panel title="Mahamilon Credit Score" icon="speedometer-outline">
        <InfoRow label="Current MCS" value={`${profile.mcs_score} / 900`} emphasis/>
        <InfoRow label="Maximum loan (85% of savings)" value={`₹${Number(profile.maximum_loan).toLocaleString("en-IN")}`}/>
        <Text style={styles.helperText}>MCS reflects verified deposits, account history, completed loans, and rejected payment or loan events.</Text>
      </Panel>
      <Panel title="Loan Application" icon="document-text-outline">
        <Text style={styles.fieldLabel}>Loan type</Text>
        <View style={styles.choiceRow}>{(["personal","business","emergency"] as const).map(value=><Pressable key={value} style={[styles.choice,form.loanType===value&&styles.choiceActive]} onPress={()=>setForm(prev=>({...prev,loanType:value}))}><Text style={[styles.choiceText,form.loanType===value&&styles.choiceTextActive]}>{value==="emergency"?"Emergency health":value}</Text></Pressable>)}</View>
        <Text style={styles.fieldLabel}>EMI frequency</Text>
        <View style={styles.choiceRow}>{(["daily","weekly","monthly"] as const).map(value=><Pressable key={value} style={[styles.choice,form.emiFrequency===value&&styles.choiceActive]} onPress={()=>setForm(prev=>({...prev,emiFrequency:value}))}><Text style={[styles.choiceText,form.emiFrequency===value&&styles.choiceTextActive]}>{value}</Text></Pressable>)}</View>
        <FormInput
          label="Loan amount"
          value={form.amount}
          keyboardType="numeric"
          onChangeText={(amount) => setForm((prev) => ({ ...prev, amount }))}
        />
        <FormInput
          label="Purpose"
          value={form.purpose}
          onChangeText={(purpose) => setForm((prev) => ({ ...prev, purpose }))}
        />
        <FormInput
          label="Monthly income"
          value={form.income}
          keyboardType="numeric"
          onChangeText={(income) => setForm((prev) => ({ ...prev, income }))}
        />
        <FormInput
          label="Preferred tenure"
          value={form.tenure}
          onChangeText={(tenure) => setForm((prev) => ({ ...prev, tenure }))}
        />
        <ActionButton label="Submit Application" icon="send-outline" onPress={submitLoan} />
      </Panel>

      <Panel title="Loan Tracking" icon="analytics-outline">
        {loans.map((loan) => (
          <View key={loan.id} style={styles.loanItem}>
            <View style={styles.loanTop}>
              <Text style={styles.cardTitle}>{loan.purpose}</Text>
              <StatusPill label={loan.status} />
            </View>
            <Text style={styles.cardMeta}>{loan.loan_type} · {loan.emi_frequency} EMI · ₹{Number(loan.amount).toLocaleString("en-IN")}</Text>
            <InfoRow label="MCS at application" value={String(loan.mcs_snapshot??profile.mcs_score)} />
            {loan.annual_interest_rate&&<InfoRow label="Loan interest" value={`${loan.annual_interest_rate}% p.a.`}/>}
            {loan.outstanding_principal!==null&&loan.outstanding_principal!==undefined&&<InfoRow label="Outstanding principal" value={`₹${Number(loan.outstanding_principal).toLocaleString("en-IN")}`}/>}
            {Boolean(loan.is_offer)&&loan.status==="submitted"&&<View style={styles.choiceRow}><Pressable style={[styles.choice,styles.choiceActive]} onPress={()=>respondToOffer(loan.id,"accept")}><Text style={styles.choiceTextActive}>Accept offer</Text></Pressable><Pressable style={styles.choice} onPress={()=>respondToOffer(loan.id,"decline")}><Text style={styles.choiceText}>Decline</Text></Pressable></View>}
            {loan.loan_type==="emergency"&&["submitted","under_review"].includes(loan.status)&&<Text style={[styles.helperText,{color:colors.red}]}>Emergency decision due within 3 days of submission.</Text>}
            {["approved","disbursed"].includes(loan.status)&&<>
              {installments.filter(item=>item.loan_id===loan.id&&item.status!=="paid").slice(0,3).map(item=><InfoRow key={item.id} label={`EMI #${item.installment_no} · ${item.due_on} · ${item.status}`} value={`₹${(Number(item.principal_due)+Number(item.interest_due)+Number(item.penalty_due)-Number(item.paid_amount)).toFixed(2)}`}/>)}
              <FormInput label="Repayment amount" value={repaymentForms[loan.id]?.amount??""} keyboardType="numeric" onChangeText={(amount)=>setRepaymentForms(prev=>({...prev,[loan.id]:{amount,utr:prev[loan.id]?.utr??""}}))}/>
              <FormInput label="Payment UTR" value={repaymentForms[loan.id]?.utr??""} autoCapitalize="characters" onChangeText={(utr)=>setRepaymentForms(prev=>({...prev,[loan.id]:{amount:prev[loan.id]?.amount??"",utr}}))}/>
              <ActionButton label="Submit Repayment" icon="card-outline" onPress={()=>submitRepayment(loan.id,Number(repaymentForms[loan.id]?.amount),repaymentForms[loan.id]?.utr??"")}/>
            </>}
          </View>
        ))}
      </Panel>
      <Panel title="Repayment History" icon="checkmark-done-outline">
        {repayments.length===0?<Text style={styles.helperText}>No repayments submitted.</Text>:repayments.map(item=><InfoRow key={item.id} label={`${item.application_no} · ${item.utr}`} value={`₹${Number(item.amount).toLocaleString("en-IN")} · ${item.status}`}/>)}
      </Panel>
    </>
  );
}

function KycScreen({kycStatus,submitKyc}:{kycStatus:string;submitKyc:(type:"aadhaar"|"pan"|"photo"|"address_proof")=>void}) {
  return (
    <>
      <Panel title="KYC Status" icon="shield-checkmark-outline">
        <InfoRow label="Current status" value={kycStatus.replaceAll("_"," ")} emphasis />
        <InfoRow label="Verification method" value="Manual society office review" />
        <InfoRow label="Required documents" value="Aadhaar, PAN, photo, address proof" />
      </Panel>

      <Panel title="Upload KYC Documents" icon="id-card-outline">
        <Text style={styles.helperText}>PDF, JPG or PNG up to 5 MB. Documents are encrypted at rest and reviewed by authorized staff.</Text>
        <ActionButton label="Upload Aadhaar" icon="cloud-upload-outline" onPress={()=>submitKyc("aadhaar")} />
        <ActionButton label="Upload PAN" icon="cloud-upload-outline" onPress={()=>submitKyc("pan")} />
        <ActionButton label="Upload Photo" icon="camera-outline" onPress={()=>submitKyc("photo")} />
        <ActionButton label="Upload Address Proof" icon="document-outline" onPress={()=>submitKyc("address_proof")} />
      </Panel>
    </>
  );
}

function ProfileScreen({profile}:{profile:Dashboard["profile"]}) {
  return (
    <>
      <Panel title="Member Profile" icon="person-circle-outline">
        <View style={styles.profileHeader}>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>{profile.name.split(" ").map(v=>v[0]).slice(0,2).join("")}</Text>
          </View>
          <View style={styles.profileCopy}>
            <Text style={styles.profileName}>{profile.name}</Text>
            <Text style={styles.cardMeta}>{profile.member_no}</Text>
          </View>
        </View>
        <InfoRow label="Phone" value={profile.phone} />
        <InfoRow label="Email" value={profile.email??"Not provided"} />
        <InfoRow label="Address" value={profile.address} />
        <InfoRow label="Nominee" value={profile.nominee} />
        <InfoRow label="Occupation" value={profile.occupation??"Not provided"} />
        <InfoRow label="Mahamilon Credit Score" value={`${profile.mcs_score} / 900`} emphasis />
        <InfoRow label="Current maximum loan" value={`₹${Number(profile.maximum_loan).toLocaleString("en-IN")}`} />
      </Panel>

      <Panel title="Security" icon="lock-closed-outline">
        <InfoRow label="Login method" value="Member ID + registered mobile + OTP/MPIN" />
        <InfoRow label="Device alerts" value="Enabled" />
        <InfoRow label="Data sync" value="Connected to encrypted live ledger" emphasis />
      </Panel>
    </>
  );
}

function BottomNav({
  activeTab,
  setActiveTab
}: {
  activeTab: Tab;
  setActiveTab: (tab: Tab) => void;
}) {
  const insets=useSafeAreaInsets();
  return (
    <View style={[styles.bottomNav,{paddingBottom:Math.max(insets.bottom,10),minHeight:72+Math.max(insets.bottom,10)}]}>
      {tabs.map((tab) => {
        const active = activeTab === tab.id;
        return (
          <Pressable key={tab.id} style={styles.navButton} onPress={() => setActiveTab(tab.id)}>
            <View style={[styles.navIconWrap, active && styles.navIconWrapActive]}>
              <Ionicons name={tab.icon} size={20} color={active ? colors.white : colors.muted} />
            </View>
            <Text style={[styles.navLabel, active && styles.navLabelActive]} numberOfLines={1}>
              {tab.label}
            </Text>
          </Pressable>
        );
      })}
    </View>
  );
}

function Panel({
  title,
  icon,
  children
}: {
  title: string;
  icon: keyof typeof Ionicons.glyphMap;
  children: React.ReactNode;
}) {
  return (
    <View style={styles.panel}>
      <View style={styles.panelHeader}>
        <Ionicons name={icon} size={20} color={colors.navy} />
        <Text style={styles.panelTitle}>{title}</Text>
      </View>
      {children}
    </View>
  );
}

function MetricCard({
  icon,
  label,
  value
}: {
  icon: keyof typeof Ionicons.glyphMap;
  label: string;
  value: string;
}) {
  return (
    <View style={styles.metricCard}>
      <Ionicons name={icon} size={20} color={colors.red} />
      <Text style={styles.metricValue}>{value}</Text>
      <Text style={styles.metricLabel}>{label}</Text>
    </View>
  );
}

function QuickAction({
  icon,
  label,
  onPress
}: {
  icon: keyof typeof Ionicons.glyphMap;
  label: string;
  onPress: () => void;
}) {
  return (
    <Pressable style={styles.quickAction} onPress={onPress}>
      <Ionicons name={icon} size={21} color={colors.navy} />
      <Text style={styles.quickActionText}>{label}</Text>
    </Pressable>
  );
}

function SchemeCard({ scheme }: { scheme: Scheme }) {
  return (
    <View style={styles.schemeCard}>
      <View style={styles.schemeIcon}>
        <MaterialCommunityIcons
          name={scheme.type === "RD" ? "bank-transfer-in" : "safe"}
          size={23}
          color={colors.navy}
        />
      </View>
      <View style={styles.schemeBody}>
        <View style={styles.schemeTop}>
          <Text style={styles.cardTitle}>{scheme.title}</Text>
          <StatusPill label={scheme.status} />
        </View>
        <Text style={styles.cardMeta}>
          {scheme.frequency} | {scheme.term} | {scheme.rate}
        </Text>
        <Text style={styles.schemeAmount}>{scheme.amount}</Text>
        <Text style={styles.cardMeta}>Maturity: {scheme.maturity}</Text>
      </View>
    </View>
  );
}

function NotificationList({sendLocalNotification,notifications,readNotification}:{sendLocalNotification:()=>void;notifications:any[];readNotification:(id:number)=>void}) {
  return (
    <Panel title="Notifications" icon="notifications-outline">
      {notifications.length===0?<Text style={styles.helperText}>No society notifications yet.</Text>:notifications.map(item=>(
        <Pressable key={item.id} style={styles.notificationItem} onPress={()=>readNotification(item.id)}>
          <Ionicons name={item.read_at?"mail-open-outline":"mail-unread-outline"} size={20} color={item.read_at?colors.muted:colors.red}/>
          <View style={styles.notificationText}><Text style={styles.cardTitle}>{item.title}</Text><Text style={styles.cardMeta}>{item.message}</Text><Text style={styles.notificationTime}>{new Date(item.created_at).toLocaleString()}</Text></View>
        </Pressable>
      ))}
      <ActionButton label="Verify Device Alerts" icon="notifications-outline" onPress={sendLocalNotification} />
    </Panel>
  );
}

function FormInput({
  label,
  value,
  onChangeText,
  keyboardType = "default",
  secureTextEntry,
  autoCapitalize = "sentences"
}: {
  label: string;
  value: string;
  onChangeText: (text: string) => void;
  keyboardType?: "default" | "numeric" | "phone-pad";
  secureTextEntry?: boolean;
  autoCapitalize?: "none" | "sentences" | "words" | "characters";
}) {
  return (
    <View style={styles.field}>
      <Text style={styles.fieldLabel}>{label}</Text>
      <TextInput
        style={styles.input}
        placeholder={label}
        placeholderTextColor="#8f9aab"
        value={value}
        onChangeText={onChangeText}
        keyboardType={keyboardType}
        secureTextEntry={secureTextEntry}
        autoCapitalize={autoCapitalize}
      />
    </View>
  );
}

function ActionButton({
  label,
  icon,
  onPress
}: {
  label: string;
  icon: keyof typeof Ionicons.glyphMap;
  onPress: () => void;
}) {
  return (
    <Pressable style={styles.actionButton} onPress={onPress}>
      <Ionicons name={icon} size={19} color={colors.white} />
      <Text style={styles.actionButtonText}>{label}</Text>
    </Pressable>
  );
}

function InfoRow({ label, value, emphasis }: { label: string; value: string; emphasis?: boolean }) {
  return (
    <View style={styles.infoRow}>
      <Text style={styles.infoLabel}>{label}</Text>
      <Text style={[styles.infoValue, emphasis && styles.infoValueEmphasis]}>{value}</Text>
    </View>
  );
}

function StatusPill({ label }: { label: string }) {
  const positive = label === "Active" || label === "Approved";
  const warning = label.includes("Pending") || label.includes("Review") || label.includes("Matures");
  return (
    <View
      style={[
        styles.statusPill,
        positive && styles.statusPillPositive,
        warning && styles.statusPillWarning
      ]}
    >
      <Text
        style={[
          styles.statusText,
          positive && styles.statusTextPositive,
          warning && styles.statusTextWarning
        ]}
      >
        {label}
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: colors.navyDark
  },
  loginSafe: {
    flex: 1,
    backgroundColor: colors.panel
  },
  keyboard: {
    flex: 1
  },
  appShell: {
    flex: 1,
    backgroundColor: colors.panel
  },
  content: {
    flex: 1
  },
  contentInner: {
    paddingBottom: 128
  },
  loginContent: {
    flexGrow: 1,
    backgroundColor: colors.panel,
    paddingBottom: 24
  },
  loginHero: {
    alignItems: "center",
    paddingTop: 34,
    paddingHorizontal: 24,
    paddingBottom: 34,
    borderBottomLeftRadius: 26,
    borderBottomRightRadius: 26
  },
  loginLogo: {
    width: 118,
    height: 118,
    borderRadius: 59,
    backgroundColor: colors.white,
    borderWidth: 4,
    borderColor: colors.white
  },
  loginTitle: {
    color: colors.white,
    fontSize: 30,
    fontWeight: "900",
    marginTop: 14,
    textAlign: "center"
  },
  loginSubtitle: {
    color: "#dbe7ff",
    fontSize: 15,
    fontWeight: "800",
    marginTop: 6
  },
  authSwitch: {
    marginHorizontal: 16,
    marginTop: 16,
    padding: 4,
    borderRadius: 8,
    backgroundColor: colors.white,
    borderWidth: 1,
    borderColor: colors.line,
    flexDirection: "row"
  },
  authSwitchButton: {
    flex: 1,
    minHeight: 44,
    borderRadius: 8,
    alignItems: "center",
    justifyContent: "center",
    flexDirection: "row",
    gap: 7
  },
  authSwitchButtonActive: {
    backgroundColor: colors.navy
  },
  authSwitchText: {
    color: colors.navy,
    fontSize: 14,
    fontWeight: "900"
  },
  authSwitchTextActive: {
    color: colors.white
  },
  loginPanel: {
    marginHorizontal: 16,
    marginTop: 18,
    padding: 16,
    borderRadius: 8,
    backgroundColor: colors.white,
    ...shadow
  },
  panelTitleLarge: {
    color: colors.ink,
    fontSize: 22,
    fontWeight: "900",
    marginBottom: 12
  },
  registrationTitleRow: {
    flexDirection: "row",
    alignItems: "flex-start",
    gap: 10,
    marginBottom: 12
  },
  registrationTitleCopy: {
    flex: 1
  },
  helperText: {
    color: colors.muted,
    fontSize: 13,
    fontWeight: "700",
    lineHeight: 18
  },
  textButton: {
    minHeight: 44,
    alignItems: "center",
    justifyContent: "center",
    marginTop: 8
  },
  textButtonLabel: {
    color: colors.navy,
    fontSize: 14,
    fontWeight: "900"
  },
  memberOnlyNotice: {
    marginHorizontal: 16,
    marginTop: 14,
    padding: 14,
    borderRadius: 8,
    backgroundColor: "#edf4ff",
    borderWidth: 1,
    borderColor: "#cfe0fb",
    flexDirection: "row",
    alignItems: "center",
    gap: 10
  },
  noticeText: {
    flex: 1,
    color: colors.navy,
    fontSize: 13,
    fontWeight: "800",
    lineHeight: 18
  },
  header: {
    paddingHorizontal: 16,
    paddingTop: 18,
    paddingBottom: 16,
    borderBottomLeftRadius: 24,
    borderBottomRightRadius: 24
  },
  headerTop: {
    flexDirection: "row",
    alignItems: "center",
    gap: 12
  },
  headerLogo: {
    width: 58,
    height: 58,
    borderRadius: 29,
    backgroundColor: colors.white,
    borderWidth: 2,
    borderColor: colors.white
  },
  headerCopy: {
    flex: 1
  },
  headerLabel: {
    color: "#bfd3ff",
    fontSize: 12,
    fontWeight: "900",
    textTransform: "uppercase"
  },
  headerTitle: {
    color: colors.white,
    fontSize: 22,
    fontWeight: "900",
    marginTop: 2
  },
  iconButton: {
    width: 42,
    height: 42,
    borderRadius: 21,
    alignItems: "center",
    justifyContent: "center",
    backgroundColor: "rgba(255,255,255,0.14)"
  },
  memberCard: {
    marginTop: 16,
    backgroundColor: "rgba(255,255,255,0.12)",
    borderWidth: 1,
    borderColor: "rgba(255,255,255,0.22)",
    borderRadius: 8,
    padding: 14,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    gap: 10
  },
  memberName: {
    color: colors.white,
    fontSize: 18,
    fontWeight: "900"
  },
  memberMeta: {
    color: "#d9e6ff",
    fontSize: 13,
    fontWeight: "700",
    marginTop: 3
  },
  balancePanel: {
    marginHorizontal: 16,
    marginTop: 16,
    borderRadius: 8,
    padding: 18,
    backgroundColor: colors.white,
    ...shadow
  },
  balanceLabel: {
    color: colors.muted,
    fontSize: 13,
    fontWeight: "800"
  },
  balanceValue: {
    color: colors.ink,
    fontSize: 34,
    fontWeight: "900",
    marginTop: 4
  },
  balanceHint: {
    color: colors.red,
    fontSize: 14,
    fontWeight: "900",
    marginTop: 6
  },
  metricGrid: {
    flexDirection: "row",
    flexWrap: "wrap",
    gap: 10,
    marginHorizontal: 16,
    marginTop: 12
  },
  metricCard: {
    width: "48%",
    minHeight: 102,
    borderRadius: 8,
    padding: 14,
    backgroundColor: colors.white,
    borderWidth: 1,
    borderColor: colors.line
  },
  metricValue: {
    color: colors.ink,
    fontSize: 18,
    fontWeight: "900",
    marginTop: 10
  },
  metricLabel: {
    color: colors.muted,
    fontSize: 12,
    fontWeight: "800",
    marginTop: 4
  },
  quickActions: {
    flexDirection: "row",
    gap: 10,
    marginHorizontal: 16,
    marginTop: 12
  },
  quickAction: {
    flex: 1,
    minHeight: 74,
    borderRadius: 8,
    backgroundColor: "#edf4ff",
    borderWidth: 1,
    borderColor: "#d3e0f4",
    alignItems: "center",
    justifyContent: "center",
    paddingHorizontal: 8
  },
  quickActionText: {
    color: colors.navy,
    fontSize: 12,
    fontWeight: "900",
    textAlign: "center",
    marginTop: 6
  },
  panel: {
    marginHorizontal: 16,
    marginTop: 14,
    padding: 14,
    backgroundColor: colors.white,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: colors.line
  },
  panelHeader: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
    marginBottom: 12
  },
  panelTitle: {
    color: colors.ink,
    fontSize: 18,
    fontWeight: "900",
    flex: 1
  },
  schemeCard: {
    flexDirection: "row",
    gap: 12,
    paddingVertical: 12,
    borderTopWidth: 1,
    borderTopColor: colors.line
  },
  schemeIcon: {
    width: 44,
    height: 44,
    borderRadius: 8,
    backgroundColor: "#eef5ff",
    alignItems: "center",
    justifyContent: "center"
  },
  schemeBody: {
    flex: 1
  },
  schemeTop: {
    flexDirection: "row",
    alignItems: "flex-start",
    gap: 8
  },
  cardTitle: {
    flex: 1,
    color: colors.ink,
    fontSize: 15,
    fontWeight: "900",
    lineHeight: 20
  },
  cardMeta: {
    color: colors.muted,
    fontSize: 12,
    fontWeight: "700",
    lineHeight: 17,
    marginTop: 3
  },
  schemeAmount: {
    color: colors.red,
    fontSize: 16,
    fontWeight: "900",
    marginTop: 5
  },
  loanItem: {
    paddingVertical: 12,
    borderTopWidth: 1,
    borderTopColor: colors.line
  },
  paymentQr: {
    width: 260,
    height: 260,
    alignSelf: "center",
    marginVertical: 14,
    backgroundColor: colors.white
  },
  choiceRow: {
    flexDirection: "row",
    flexWrap: "wrap",
    gap: 8,
    marginBottom: 12
  },
  choice: {
    minHeight: 42,
    paddingHorizontal: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: colors.line,
    alignItems: "center",
    justifyContent: "center",
    backgroundColor: colors.white
  },
  choiceActive: {
    backgroundColor: colors.navy,
    borderColor: colors.navy
  },
  choiceText: {
    color: colors.navy,
    fontSize: 12,
    fontWeight: "900",
    textTransform: "capitalize"
  },
  choiceTextActive: {
    color: colors.white
  },
  loanTop: {
    flexDirection: "row",
    gap: 8,
    alignItems: "flex-start"
  },
  field: {
    marginBottom: 12
  },
  fieldLabel: {
    color: colors.ink,
    fontSize: 13,
    fontWeight: "900",
    marginBottom: 7
  },
  input: {
    minHeight: 48,
    borderWidth: 1,
    borderColor: colors.line,
    borderRadius: 8,
    backgroundColor: "#fbfdff",
    color: colors.ink,
    fontSize: 15,
    fontWeight: "700",
    paddingHorizontal: 12
  },
  actionButton: {
    minHeight: 50,
    borderRadius: 8,
    backgroundColor: colors.red,
    alignItems: "center",
    justifyContent: "center",
    flexDirection: "row",
    gap: 8,
    paddingHorizontal: 12,
    marginTop: 4
  },
  actionButtonText: {
    color: colors.white,
    fontSize: 15,
    fontWeight: "900"
  },
  infoRow: {
    paddingVertical: 11,
    borderTopWidth: 1,
    borderTopColor: colors.line,
    flexDirection: "row",
    alignItems: "center",
    gap: 12
  },
  infoLabel: {
    flex: 1,
    color: colors.muted,
    fontSize: 13,
    fontWeight: "800",
    lineHeight: 18
  },
  infoValue: {
    flex: 1.25,
    color: colors.ink,
    fontSize: 13,
    fontWeight: "900",
    textAlign: "right",
    lineHeight: 18
  },
  infoValueEmphasis: {
    color: colors.red
  },
  profileHeader: {
    flexDirection: "row",
    alignItems: "center",
    gap: 12,
    paddingBottom: 12
  },
  avatar: {
    width: 58,
    height: 58,
    borderRadius: 29,
    backgroundColor: colors.navy,
    alignItems: "center",
    justifyContent: "center"
  },
  avatarText: {
    color: colors.white,
    fontSize: 18,
    fontWeight: "900"
  },
  profileCopy: {
    flex: 1
  },
  profileName: {
    color: colors.ink,
    fontSize: 20,
    fontWeight: "900"
  },
  notificationItem: {
    flexDirection: "row",
    alignItems: "flex-start",
    gap: 10,
    paddingVertical: 12,
    borderTopWidth: 1,
    borderTopColor: colors.line
  },
  notificationText: {
    flex: 1
  },
  notificationTime: {
    color: colors.muted,
    fontSize: 11,
    fontWeight: "800",
    marginTop: 2
  },
  statusPill: {
    borderRadius: 8,
    paddingHorizontal: 9,
    paddingVertical: 5,
    backgroundColor: "#eef2f7",
    alignSelf: "flex-start"
  },
  statusPillPositive: {
    backgroundColor: "#e8f6ef"
  },
  statusPillWarning: {
    backgroundColor: "#fff5df"
  },
  statusText: {
    color: colors.muted,
    fontSize: 11,
    fontWeight: "900"
  },
  statusTextPositive: {
    color: colors.green
  },
  statusTextWarning: {
    color: "#9a6a00"
  },
  bottomNav: {
    position: "absolute",
    left: 0,
    right: 0,
    bottom: 0,
    minHeight: 82,
    paddingTop: 9,
    paddingHorizontal: 8,
    backgroundColor: colors.white,
    borderTopWidth: 1,
    borderTopColor: colors.line,
    flexDirection: "row",
    ...shadow
  },
  navButton: {
    flex: 1,
    alignItems: "center",
    justifyContent: "center"
  },
  navIconWrap: {
    width: 34,
    height: 30,
    borderRadius: 8,
    alignItems: "center",
    justifyContent: "center"
  },
  navIconWrapActive: {
    backgroundColor: colors.navy
  },
  navLabel: {
    color: colors.muted,
    fontSize: 10,
    fontWeight: "900",
    marginTop: 4
  },
  navLabelActive: {
    color: colors.navy
  }
});
