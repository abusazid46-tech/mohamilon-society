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
  Linking,
  Modal
} from "react-native";
import { SafeAreaProvider, SafeAreaView, useSafeAreaInsets } from "react-native-safe-area-context";
import { Scheme } from "./src/domain";
import { colors, shadow } from "./src/theme";
import { api, ApiError, Dashboard, MemberAccount, AccountPassbookData, RdInstallment } from "./src/api";

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
    deposit: "500",
    shares: "1"
  });
  const [passbookAccount, setPassbookAccount] = useState<MemberAccount | null>(null);
  const [passbookData, setPassbookData] = useState<AccountPassbookData | null>(null);
  const [passbookLoading, setPassbookLoading] = useState(false);
  const [rdScheduleAccount, setRdScheduleAccount] = useState<MemberAccount | null>(null);

  const openPassbook = async (account: MemberAccount) => {
    setPassbookAccount(account);
    setPassbookData(null);
    setPassbookLoading(true);
    try {
      const data = await api.getAccountPassbook(account.id);
      setPassbookData(data);
    } catch (error) {
      Alert.alert("Passbook error", error instanceof ApiError ? error.message : "Failed to load live passbook.");
    } finally {
      setPassbookLoading(false);
    }
  };

  const openRdSchedule = (account: MemberAccount) => {
    setRdScheduleAccount(account);
  };
  const [loanForm, setLoanForm] = useState<LoanForm>({
    amount: "",
    purpose: "",
    income: "",
    tenure: "",
    loanType: "personal",
    emiFrequency: "monthly"
  });
  const [loginErrors, setLoginErrors] = useState<Record<string, string>>({});
  const [regErrors, setRegErrors] = useState<Record<string, string>>({});
  const [authErrorMessage, setAuthErrorMessage] = useState<string | null>(null);

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
    setAuthErrorMessage(null);
    const errs: Record<string, string> = {};
    const cleanPhone = loginForm.phone.trim();
    if (!cleanPhone) {
      errs.phone = "Mobile number is required";
    } else if (!/^[6-9][0-9]{9}$/.test(cleanPhone)) {
      errs.phone = "Enter valid 10-digit mobile number (e.g. 9876543210)";
    }
    if (!loginForm.otp.trim()) {
      errs.otp = "6-digit MPIN is required";
    } else if (loginForm.otp.trim().length !== 6) {
      errs.otp = "MPIN must be exactly 6 digits";
    }

    if (Object.keys(errs).length > 0) {
      setLoginErrors(errs);
      return;
    }
    setLoginErrors({});

    try {
      await api.login(cleanPhone, loginForm.otp.trim());
      setIsLoggedIn(true);
      await refreshData();
    } catch (error) {
      const msg = error instanceof ApiError ? error.message : "Unable to reach the society server.";
      setAuthErrorMessage(msg);
      Alert.alert("Login failed", msg);
    }
  };

  const register = async () => {
    setAuthErrorMessage(null);
    const errs: Record<string, string> = {};
    const cleanName = registrationForm.fullName.trim();
    const cleanPhone = registrationForm.phone.trim();
    const cleanAadhaar = registrationForm.aadhaar.trim();
    const cleanPan = registrationForm.pan.trim().toUpperCase();
    const cleanAddress = registrationForm.address.trim();
    const cleanNominee = registrationForm.nominee.trim();
    const cleanMpin = registrationForm.mpin.trim();

    if (!cleanName) errs.fullName = "Full name is required";
    else if (cleanName.length < 3) errs.fullName = "Full name must be at least 3 characters";

    if (!cleanPhone) errs.phone = "Mobile number is required";
    else if (!/^[6-9][0-9]{9}$/.test(cleanPhone)) errs.phone = "Enter valid 10-digit mobile (starts with 6-9)";

    if (!cleanAadhaar) errs.aadhaar = "12-digit Aadhaar number is required";
    else if (!/^[0-9]{12}$/.test(cleanAadhaar)) errs.aadhaar = "Aadhaar must be exactly 12 numeric digits";

    if (cleanPan && !/^[A-Z]{5}[0-9]{4}[A-Z]$/.test(cleanPan)) {
      errs.pan = "PAN must match standard format (e.g. ABCDE1234F)";
    }

    if (!cleanAddress) errs.address = "Permanent residential address is required";
    else if (cleanAddress.length < 5) errs.address = "Please enter complete street/village address";

    if (!cleanNominee) errs.nominee = "Nominee full name is required";
    else if (cleanNominee.length < 3) errs.nominee = "Nominee name must be at least 3 characters";

    if (!cleanMpin) errs.mpin = "6-digit security MPIN is required";
    else if (!/^[0-9]{6}$/.test(cleanMpin)) errs.mpin = "MPIN must be exactly 6 numeric digits";

    if (Object.keys(errs).length > 0) {
      setRegErrors(errs);
      const firstField = Object.values(errs)[0];
      Alert.alert("Please complete the form", firstField);
      return;
    }
    setRegErrors({});

    try {
      const result = await api.register({
        name: cleanName,
        phone: cleanPhone,
        aadhaar: cleanAadhaar,
        pan: cleanPan || undefined,
        address: cleanAddress,
        nominee_name: cleanNominee,
        occupation: registrationForm.occupation.trim() || undefined,
        mpin: cleanMpin
      });
      Alert.alert("Registration submitted", `${result.message}\nMember number: ${result.member_no}`);
      setLoginForm((prev) => ({
        ...prev,
        phone: cleanPhone
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
    } catch (error) {
      if (error instanceof ApiError && error.fields) {
        const fieldErrors: Record<string, string> = {};
        if (error.fields.phone) fieldErrors.phone = error.fields.phone[0];
        if (error.fields.aadhaar) fieldErrors.aadhaar = error.fields.aadhaar[0];
        if (error.fields.pan) fieldErrors.pan = error.fields.pan[0];
        if (error.fields.name) fieldErrors.fullName = error.fields.name[0];
        if (error.fields.mpin) fieldErrors.mpin = error.fields.mpin[0];
        if (error.fields.address) fieldErrors.address = error.fields.address[0];
        if (error.fields.nominee_name) fieldErrors.nominee = error.fields.nominee_name[0];
        setRegErrors(fieldErrors);
      }
      const msg = error instanceof ApiError ? error.message : "Unable to reach the society server.";
      setAuthErrorMessage(msg);
      Alert.alert("Registration failed", msg);
    }
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
    const scheme = liveSchemes.find(item => item.title === accountForm.plan) ?? liveSchemes[0];
    const shares = Math.max(1, Number(accountForm.shares) || 1);
    const calculatedDeposit = shares * 500;
    const depositAmount = Number(accountForm.deposit) || calculatedDeposit;
    if (!scheme) {
      Alert.alert("Complete request", "Choose an account scheme.");
      return;
    }
    if (depositAmount < calculatedDeposit) {
      Alert.alert("Deposit too low", `Minimum opening deposit for ${shares} share(s) is ₹${calculatedDeposit.toLocaleString("en-IN")}.`);
      return;
    }
    try {
      await api.openAccount(Number(scheme.id), depositAmount, shares);
      await refreshData();
      Alert.alert("Request submitted", "After staff approval, your UPI QR will appear here for the opening payment.");
    } catch (error) {
      Alert.alert("Request failed", error instanceof ApiError ? error.message : "Server unavailable");
      return;
    }
    setAccountForm({ plan: "Daily RD Account", deposit: "500", shares: "1" });
  };

  const submitLoan = async () => {
    if (!loanForm.amount || Number(loanForm.amount) <= 0) {
      Alert.alert("Incomplete request", "Please enter requested loan amount.");
      return;
    }
    if (!loanForm.purpose.trim()) {
      Alert.alert("Incomplete request", "Please enter loan purpose.");
      return;
    }
    if (!loanForm.income || Number(loanForm.income) <= 0) {
      Alert.alert("Incomplete request", "Please enter monthly income.");
      return;
    }
    if (!loanForm.tenure || Number(loanForm.tenure) <= 0) {
      Alert.alert("Incomplete request", "Please enter preferred tenure in months.");
      return;
    }
    try {
      await api.applyLoan({
        loan_type: loanForm.loanType,
        emi_frequency: loanForm.emiFrequency,
        amount: Number(loanForm.amount),
        purpose: loanForm.purpose,
        monthly_income: Number(loanForm.income),
        tenure_months: Number(loanForm.tenure)
      });
      await refreshData();
      Alert.alert("Loan application submitted", loanForm.loanType === "emergency" ? "Emergency request is due for decision within three days." : "Your application is now under review.");
    } catch (error) {
      Alert.alert("Application failed", error instanceof ApiError ? error.message : "Server unavailable");
      return;
    }
    setLoanForm({ amount: "", purpose: "", income: "", tenure: "", loanType: "personal", emiFrequency: "monthly" });
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
        setMode={(m) => {
          setAuthMode(m);
          setAuthErrorMessage(null);
        }}
        loginForm={loginForm}
        setLoginForm={setLoginForm}
        registrationForm={registrationForm}
        setRegistrationForm={setRegistrationForm}
        onLogin={login}
        onRegister={register}
        loginErrors={loginErrors}
        setLoginErrors={setLoginErrors}
        regErrors={regErrors}
        setRegErrors={setRegErrors}
        authErrorMessage={authErrorMessage}
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
                openPassbook={openPassbook}
                openRdSchedule={openRdSchedule}
              />
            )}
            {activeTab === "loan" && (
              <LoanScreen form={loanForm} setForm={setLoanForm} submitLoan={submitLoan} loans={dashboard.loans} installments={dashboard.installments??[]} repayments={dashboard.repayments??[]} profile={dashboard.profile} respondToOffer={respondToOffer} submitRepayment={submitLoanRepayment} />
            )}
            {activeTab === "kyc" && (
              <KycScreen kycStatus={dashboard.profile.kyc_status} rejectionNote={dashboard.profile.kyc_rejection_note} submitKyc={submitKyc} />
            )}
            {activeTab === "profile" && <ProfileScreen profile={dashboard.profile} dashboard={dashboard} />}
            {loading && dashboard.profile.name==="" && <ActivityIndicator size="large" color={colors.navy} />}
          </ScrollView>
          <BottomNav activeTab={activeTab} setActiveTab={setActiveTab} />
          <PassbookModal
            account={passbookAccount}
            data={passbookData}
            loading={passbookLoading}
            onClose={() => setPassbookAccount(null)}
          />
          <RdScheduleModal
            account={rdScheduleAccount}
            installments={dashboard.rd_installments ?? []}
            onClose={() => setRdScheduleAccount(null)}
          />
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
  onRegister,
  loginErrors,
  setLoginErrors,
  regErrors,
  setRegErrors,
  authErrorMessage
}: {
  mode: AuthMode;
  setMode: (mode: AuthMode) => void;
  loginForm: LoginForm;
  setLoginForm: React.Dispatch<React.SetStateAction<LoginForm>>;
  registrationForm: RegistrationForm;
  setRegistrationForm: React.Dispatch<React.SetStateAction<RegistrationForm>>;
  onLogin: () => void;
  onRegister: () => void;
  loginErrors: Record<string, string>;
  setLoginErrors: React.Dispatch<React.SetStateAction<Record<string, string>>>;
  regErrors: Record<string, string>;
  setRegErrors: React.Dispatch<React.SetStateAction<Record<string, string>>>;
  authErrorMessage: string | null;
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
            <LoginPanel
              form={loginForm}
              setForm={setLoginForm}
              onLogin={onLogin}
              errors={loginErrors}
              setErrors={setLoginErrors}
              generalError={authErrorMessage}
            />
          ) : (
            <RegistrationPanel
              form={registrationForm}
              setForm={setRegistrationForm}
              onRegister={onRegister}
              onBackToLogin={() => setMode("login")}
              errors={regErrors}
              setErrors={setRegErrors}
              generalError={authErrorMessage}
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
  onLogin,
  errors,
  setErrors,
  generalError
}: {
  form: LoginForm;
  setForm: React.Dispatch<React.SetStateAction<LoginForm>>;
  onLogin: () => void;
  errors: Record<string, string>;
  setErrors: React.Dispatch<React.SetStateAction<Record<string, string>>>;
  generalError?: string | null;
}) {
  return (
    <View style={styles.loginPanel}>
      <Text style={styles.panelTitleLarge}>Member Login</Text>
      {generalError ? (
        <View style={styles.errorBanner}>
          <Ionicons name="alert-circle" size={18} color={colors.red} />
          <Text style={styles.errorBannerText}>{generalError}</Text>
        </View>
      ) : null}
      <FormInput
        label="Registered mobile number"
        value={form.phone}
        keyboardType="phone-pad"
        error={errors.phone}
        onChangeText={(phone) => {
          setForm((prev) => ({ ...prev, phone }));
          if (errors.phone) setErrors((prev) => ({ ...prev, phone: "" }));
        }}
      />
      <FormInput
        label="OTP / MPIN"
        value={form.otp}
        keyboardType="numeric"
        secureTextEntry
        error={errors.otp}
        onChangeText={(otp) => {
          setForm((prev) => ({ ...prev, otp }));
          if (errors.otp) setErrors((prev) => ({ ...prev, otp: "" }));
        }}
      />
      <ActionButton label="Login" icon="log-in-outline" onPress={onLogin} />
    </View>
  );
}

function RegistrationPanel({
  form,
  setForm,
  onRegister,
  onBackToLogin,
  errors,
  setErrors,
  generalError
}: {
  form: RegistrationForm;
  setForm: React.Dispatch<React.SetStateAction<RegistrationForm>>;
  onRegister: () => void;
  onBackToLogin: () => void;
  errors: Record<string, string>;
  setErrors: React.Dispatch<React.SetStateAction<Record<string, string>>>;
  generalError?: string | null;
}) {
  const clearError = (field: string) => {
    if (errors[field]) {
      setErrors((prev) => {
        const next = { ...prev };
        delete next[field];
        return next;
      });
    }
  };

  return (
    <View style={styles.loginPanel}>
      <View style={styles.registrationTitleRow}>
        <View style={styles.registrationTitleCopy}>
          <Text style={styles.panelTitleLarge}>New Member Registration</Text>
          <Text style={styles.helperText}>Submit member details for manual KYC approval.</Text>
        </View>
        <Ionicons name="shield-checkmark-outline" size={28} color={colors.navy} />
      </View>

      {generalError ? (
        <View style={styles.errorBanner}>
          <Ionicons name="alert-circle" size={18} color={colors.red} />
          <Text style={styles.errorBannerText}>{generalError}</Text>
        </View>
      ) : null}

      <FormInput
        label="Full name"
        value={form.fullName}
        error={errors.fullName}
        onChangeText={(fullName) => {
          setForm((prev) => ({ ...prev, fullName }));
          clearError("fullName");
        }}
        autoCapitalize="words"
      />
      <FormInput
        label="Mobile number"
        value={form.phone}
        keyboardType="phone-pad"
        error={errors.phone}
        onChangeText={(phone) => {
          setForm((prev) => ({ ...prev, phone }));
          clearError("phone");
        }}
      />
      <FormInput
        label="Aadhaar / ID number"
        value={form.aadhaar}
        keyboardType="numeric"
        error={errors.aadhaar}
        onChangeText={(aadhaar) => {
          setForm((prev) => ({ ...prev, aadhaar }));
          clearError("aadhaar");
        }}
      />
      <FormInput
        label="PAN / tax ID (Optional)"
        value={form.pan}
        autoCapitalize="characters"
        error={errors.pan}
        onChangeText={(pan) => {
          setForm((prev) => ({ ...prev, pan }));
          clearError("pan");
        }}
      />
      <FormInput
        label="Current address"
        value={form.address}
        error={errors.address}
        onChangeText={(address) => {
          setForm((prev) => ({ ...prev, address }));
          clearError("address");
        }}
      />
      <FormInput
        label="Nominee name"
        value={form.nominee}
        error={errors.nominee}
        onChangeText={(nominee) => {
          setForm((prev) => ({ ...prev, nominee }));
          clearError("nominee");
        }}
        autoCapitalize="words"
      />
      <FormInput
        label="Occupation (Optional)"
        value={form.occupation}
        error={errors.occupation}
        onChangeText={(occupation) => {
          setForm((prev) => ({ ...prev, occupation }));
          clearError("occupation");
        }}
      />
      <FormInput
        label="Create MPIN (6 digits)"
        value={form.mpin}
        keyboardType="numeric"
        secureTextEntry
        error={errors.mpin}
        onChangeText={(mpin) => {
          setForm((prev) => ({ ...prev, mpin }));
          clearError("mpin");
        }}
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
  dashboard,
  readNotification
}: {
  setActiveTab: (tab: Tab) => void;
  sendLocalNotification: () => void;
  dashboard: Dashboard;
  readNotification: (id: number) => void;
}) {
  const [selectedReceipt, setSelectedReceipt] = useState<any | null>(null);

  return (
    <>
      {dashboard.profile.kyc_status === "rejected" && (
        <View style={styles.kycBanner}>
          <Ionicons name="alert-circle" size={24} color={colors.red} />
          <View style={styles.kycBannerCopy}>
            <Text style={styles.kycBannerTitle}>KYC Verification Rejected</Text>
            <Text style={styles.kycBannerText}>
              {dashboard.profile.kyc_rejection_note || "Document was rejected during office review. Please re-upload a clear copy."}
            </Text>
            <Pressable style={styles.kycReuploadBtn} onPress={() => setActiveTab("kyc")}>
              <Text style={styles.kycReuploadBtnText}>Re-Upload Documents</Text>
            </Pressable>
          </View>
        </View>
      )}

      {Number(dashboard.profile.rd_due_count ?? 0) > 0 && (
        <View style={[styles.kycBanner, { backgroundColor: "#fff5f5", borderColor: "#fca5a5" }]}>
          <Ionicons name="alert-circle" size={24} color={colors.red} />
          <View style={styles.kycBannerCopy}>
            <Text style={[styles.kycBannerTitle, { color: colors.red }]}>RD Installment Due Alert</Text>
            <Text style={styles.kycBannerText}>
              You have {dashboard.profile.rd_due_count} pending installment{(dashboard.profile.rd_due_count ?? 0) > 1 ? "s" : ""} totaling ₹{Number(dashboard.profile.rd_due_amount ?? 0).toLocaleString("en-IN")}. Please clear dues to keep your account active and avoid fines.
            </Text>
            <Pressable style={[styles.kycReuploadBtn, { backgroundColor: colors.red }]} onPress={() => setActiveTab("accounts")}>
              <Text style={styles.kycReuploadBtnText}>View Accounts & Dues</Text>
            </Pressable>
          </View>
        </View>
      )}

      <View style={styles.balancePanel}>
        <Text style={styles.balanceLabel}>Total Society Balance</Text>
        <Text style={styles.balanceValue}>
          ₹{Number(dashboard.profile.total_balance).toLocaleString("en-IN", { minimumFractionDigits: 2 })}
        </Text>
        <Text style={styles.balanceHint}>Updated from live society ledger</Text>
      </View>

      <View style={styles.schemeRow}>
        <View style={styles.schemeMiniCard}>
          <Ionicons name="wallet-outline" size={18} color={colors.navy} />
          <Text style={styles.schemeMiniLabel}>Savings</Text>
          <Text style={styles.schemeMiniVal}>
            ₹{Number(dashboard.profile.savings_balance ?? dashboard.profile.total_balance).toLocaleString("en-IN", { minimumFractionDigits: 0 })}
          </Text>
        </View>
        <View style={styles.schemeMiniCard}>
          <Ionicons name="lock-closed-outline" size={18} color="#087443" />
          <Text style={styles.schemeMiniLabel}>Fixed (FD)</Text>
          <Text style={[styles.schemeMiniVal, { color: "#087443" }]}>
            ₹{Number(dashboard.profile.fd_balance ?? 0).toLocaleString("en-IN", { minimumFractionDigits: 0 })}
          </Text>
        </View>
        <View style={styles.schemeMiniCard}>
          <Ionicons name="repeat-outline" size={18} color={colors.red} />
          <Text style={styles.schemeMiniLabel}>Recurring (RD)</Text>
          <Text style={[styles.schemeMiniVal, { color: colors.red }]}>
            ₹{Number(dashboard.profile.rd_balance ?? 0).toLocaleString("en-IN", { minimumFractionDigits: 0 })}
          </Text>
        </View>
      </View>

      <View style={styles.metricGrid}>
        <MetricCard icon="albums-outline" label="Active accounts" value={String(dashboard.profile.active_accounts)} />
        <MetricCard icon="shield-checkmark-outline" label="KYC status" value={dashboard.profile.kyc_status.replaceAll("_", " ")} />
        <MetricCard icon="speedometer-outline" label="MCS score" value={`${dashboard.profile.mcs_score} / 900`} />
        <MetricCard icon="cash-outline" label="Max loan (85%)" value={`₹${Number(dashboard.profile.maximum_loan).toLocaleString("en-IN")}`} />
      </View>

      <View style={styles.quickActions}>
        <QuickAction icon="wallet-outline" label="Open RD/FD" onPress={() => setActiveTab("accounts")} />
        <QuickAction icon="document-text-outline" label="Apply Loan" onPress={() => setActiveTab("loan")} />
        <QuickAction icon="shield-outline" label="Update KYC" onPress={() => setActiveTab("kyc")} />
      </View>

      <Panel title="Official Payment Receipts" icon="receipt-outline">
        {!dashboard.receipts || dashboard.receipts.length === 0 ? (
          <Text style={styles.helperText}>No payment receipts issued yet.</Text>
        ) : (
          dashboard.receipts.slice(0, 5).map((r: any) => (
            <Pressable key={r.id} style={styles.receiptItem} onPress={() => setSelectedReceipt(r)}>
              <View style={{ flex: 1 }}>
                <Text style={styles.receiptNo}>{r.receipt_no}</Text>
                <Text style={styles.receiptCategory}>
                  {String(r.category).replaceAll("_", " ").toUpperCase()} · {String(r.payment_mode).toUpperCase()}
                </Text>
                <Text style={styles.receiptDate}>{new Date(r.created_at).toLocaleDateString()}</Text>
              </View>
              <View style={{ alignItems: "flex-end" }}>
                <Text style={styles.receiptAmount}>
                  ₹{Number(r.amount).toLocaleString("en-IN", { minimumFractionDigits: 2 })}
                </Text>
                <View style={styles.receiptViewBadge}>
                  <Text style={styles.receiptViewText}>VIEW</Text>
                </View>
              </View>
            </Pressable>
          ))
        )}
      </Panel>

      <Modal visible={selectedReceipt !== null} transparent animationType="slide" onRequestClose={() => setSelectedReceipt(null)}>
        <View style={styles.modalOverlay}>
          <View style={styles.receiptModalCard}>
            <View style={styles.receiptModalHeader}>
              <Ionicons name="document-text-outline" size={24} color={colors.navy} />
              <Text style={styles.receiptModalTitle}>Payment Receipt</Text>
              <Pressable onPress={() => setSelectedReceipt(null)} style={{ padding: 4 }}>
                <Ionicons name="close" size={24} color={colors.ink} />
              </Pressable>
            </View>
            <View style={styles.receiptModalDivider} />
            {selectedReceipt && (
              <>
                <InfoRow label="Receipt No" value={selectedReceipt.receipt_no} emphasis />
                <InfoRow label="Member" value={`${dashboard.profile.name} (${dashboard.profile.member_no})`} />
                <InfoRow label="Category" value={String(selectedReceipt.category).replaceAll("_", " ").toUpperCase()} />
                <InfoRow label="Amount Paid" value={`₹${Number(selectedReceipt.amount).toLocaleString("en-IN", { minimumFractionDigits: 2 })}`} emphasis />
                <InfoRow label="Payment Mode" value={String(selectedReceipt.payment_mode).toUpperCase()} />
                <InfoRow label="Date & Time" value={new Date(selectedReceipt.created_at).toLocaleString()} />
                {selectedReceipt.notes ? <InfoRow label="Reference / Notes" value={selectedReceipt.notes} /> : null}
                <Text style={styles.receiptModalFooter}>Official Mahamilon Society Digital Receipt</Text>
              </>
            )}
            <ActionButton label="Close Receipt" icon="checkmark-outline" onPress={() => setSelectedReceipt(null)} />
          </View>
        </View>
      </Modal>

      <Panel title="Recent Ledger" icon="list-outline">
        {dashboard.ledger.length === 0 ? (
          <Text style={styles.helperText}>No ledger entries yet.</Text>
        ) : (
          dashboard.ledger.map((item: any) => {
            const isCr = item.direction === "credit";
            return (
              <View key={item.id} style={styles.passbookTxRow}>
                <View style={{ flex: 1, paddingRight: 8 }}>
                  <Text style={styles.passbookTxDesc}>{item.description}</Text>
                  <Text style={styles.passbookTxDate}>
                    {item.account_no} · {new Date(item.posted_at || item.created_at).toLocaleDateString()}
                  </Text>
                </View>
                <View style={{ alignItems: "flex-end" }}>
                  <Text style={[styles.passbookTxAmount, isCr ? { color: "#087443" } : { color: colors.red }]}>
                    {isCr ? "+Cr" : "-Dr"} ₹{Number(item.amount).toLocaleString("en-IN", { minimumFractionDigits: 2 })}
                  </Text>
                  {item.running_balance !== undefined && (
                    <Text style={styles.passbookTxBal}>
                      Bal: ₹{Number(item.running_balance).toLocaleString("en-IN", { minimumFractionDigits: 2 })}
                    </Text>
                  )}
                </View>
              </View>
            );
          })
        )}
      </Panel>

      <NotificationList sendLocalNotification={sendLocalNotification} notifications={dashboard.notifications ?? []} readNotification={readNotification} />
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
  submitPayment,
  openPassbook,
  openRdSchedule
}: {
  form: AccountForm;
  setForm: React.Dispatch<React.SetStateAction<AccountForm>>;
  submitAccount: () => void;
  schemes: Scheme[];
  accounts: any[];
  payments: any[];
  submitPayment: (payment: any, utr: string) => void;
  openPassbook: (account: any) => void;
  openRdSchedule: (account: any) => void;
}) {
  const [utrs, setUtrs] = useState<Record<number, string>>({});
  return (
    <>
      <Panel title="My Accounts" icon="wallet-outline">
        {accounts.length === 0 ? (
          <Text style={styles.helperText}>No account requests yet.</Text>
        ) : (
          accounts.map((account: any) => {
            const isFd = account.scheme_type === "fd";
            const isRd = account.scheme_type === "rd";
            return (
              <View key={account.id} style={styles.loanItem}>
                <View style={styles.loanTop}>
                  <View style={{ flex: 1, paddingRight: 8 }}>
                    <Text style={styles.cardTitle}>{account.scheme_name}</Text>
                    {isFd ? (
                      <Text style={styles.cardMeta}>
                        Certificate: {account.certificate_no || "Pending"} · Acc: {account.account_no}
                      </Text>
                    ) : (
                      <Text style={styles.cardMeta}>
                        {account.account_no} · {account.share_count} share(s) · {account.effective_interest_rate}% p.a.
                      </Text>
                    )}
                  </View>
                  <StatusPill label={account.onboarding_status?.replaceAll("_", " ") ?? account.status} />
                </View>

                {isFd ? (
                  <>
                    <InfoRow label="Principal Deposited" value={`₹${Number(account.principal || account.balance).toLocaleString("en-IN")}`} emphasis />
                    <InfoRow label="Target Maturity" value={`₹${Number(account.maturity_amount || 0).toLocaleString("en-IN")}`} emphasis />
                    <InfoRow label="Interest Rate" value={`${account.effective_interest_rate}% p.a. Simple Interest`} />
                    <InfoRow label="Tenure" value={account.term_months ? `${account.term_months} Months` : "Flexible"} />
                    <InfoRow label="Matures On" value={account.matures_on ? new Date(account.matures_on).toLocaleDateString() : "Active"} />
                    <InfoRow label="Current Balance" value={`₹${Number(account.balance).toLocaleString("en-IN")}`} />
                    <View style={styles.accountActionRow}>
                      <Pressable style={styles.accountActionBtn} onPress={() => openPassbook(account)}>
                        <Ionicons name="book-outline" size={16} color={colors.navy} />
                        <Text style={styles.accountActionBtnText}>View Live Passbook</Text>
                      </Pressable>
                    </View>
                  </>
                ) : isRd ? (
                  <>
                    <InfoRow label="RD Current Balance" value={`₹${Number(account.balance).toLocaleString("en-IN")}`} emphasis />
                    <InfoRow
                      label="Installment Commitment"
                      value={`₹${Number(account.monthly_commitment).toLocaleString("en-IN")} / ${account.deposit_frequency || "month"}`}
                    />
                    {Number(account.total_due) > 0 ? (
                      <View style={{ marginVertical: 4 }}>
                        <InfoRow
                          label="Pending Installments Due"
                          value={`₹${Number(account.total_due).toLocaleString("en-IN")} (${account.due_count} pending)`}
                          emphasis
                        />
                        <View style={styles.dueBadge}>
                          <Text style={styles.dueBadgeText}>⚠️ Payment Due: {account.due_count} installment(s)</Text>
                        </View>
                      </View>
                    ) : (
                      <View style={styles.clearBadge}>
                        <Text style={styles.clearBadgeText}>✓ Installments Up to Date</Text>
                      </View>
                    )}
                    {Number(account.maturity_amount) > 0 && (
                      <InfoRow label="Target Maturity" value={`₹${Number(account.maturity_amount).toLocaleString("en-IN")}`} />
                    )}
                    <View style={styles.accountActionRow}>
                      <Pressable style={styles.accountActionBtn} onPress={() => openRdSchedule(account)}>
                        <Ionicons name="calendar-outline" size={16} color={colors.navy} />
                        <Text style={styles.accountActionBtnText}>Installment Schedule</Text>
                      </Pressable>
                      <Pressable style={styles.accountActionBtn} onPress={() => openPassbook(account)}>
                        <Ionicons name="book-outline" size={16} color={colors.navy} />
                        <Text style={styles.accountActionBtnText}>View Passbook</Text>
                      </Pressable>
                    </View>
                  </>
                ) : (
                  <>
                    <InfoRow label="Current Balance" value={`₹${Number(account.balance).toLocaleString("en-IN")}`} emphasis />
                    <InfoRow label="Share Capital (₹500/share)" value={`₹${(Number(account.share_count) * 500).toLocaleString("en-IN")}`} />
                    <View style={styles.accountActionRow}>
                      <Pressable style={styles.accountActionBtn} onPress={() => openPassbook(account)}>
                        <Ionicons name="book-outline" size={16} color={colors.navy} />
                        <Text style={styles.accountActionBtnText}>View Live Passbook</Text>
                      </Pressable>
                    </View>
                  </>
                )}
              </View>
            );
          })
        )}
      </Panel>

      {payments.filter((payment: any) => ["requested", "rejected"].includes(payment.status)).map((payment: any) => (
        <Panel key={payment.id} title="Opening Payment Required" icon="qr-code-outline">
          <Text style={styles.helperText}>
            Pay exactly ₹{Number(payment.amount).toLocaleString("en-IN")} for account {payment.account_no}. The account activates only after staff verifies your UTR.
          </Text>
          <Image source={{ uri: `https://quickchart.io/qr?size=260&text=${encodeURIComponent(payment.upi_uri)}` }} style={styles.paymentQr} resizeMode="contain" />
          <ActionButton label="Open UPI App" icon="phone-portrait-outline" onPress={() => Linking.openURL(payment.upi_uri)} />
          <FormInput label="UPI transaction reference (UTR)" value={utrs[payment.id] ?? ""} onChangeText={(value) => setUtrs(prev => ({ ...prev, [payment.id]: value }))} autoCapitalize="characters" />
          <ActionButton label="Submit Payment for Verification" icon="checkmark-circle-outline" onPress={() => submitPayment(payment, utrs[payment.id] ?? "")} />
          {payment.status === "rejected" && (
            <Text style={[styles.helperText, { color: colors.red }]}>Previous submission was rejected. Check the UTR and submit again.</Text>
          )}
        </Panel>
      ))}

      {payments.filter((payment: any) => payment.status === "submitted").map((payment: any) => (
        <Panel key={payment.id} title="Payment Under Review" icon="time-outline">
          <InfoRow label="Reference" value={payment.reference} />
          <InfoRow label="UTR" value={payment.utr} />
          <InfoRow label="Status" value="Awaiting staff verification" emphasis />
        </Panel>
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
        {schemes.map((scheme) => {
          const selected = form.plan === scheme.title;
          return (
            <Pressable
              key={scheme.id}
              style={[styles.quickAction, selected && { borderColor: colors.navy, backgroundColor: "#edf4ff" }]}
              onPress={() => {
                const sharesNum = Math.max(1, parseInt(form.shares, 10) || 1);
                setForm((prev) => ({
                  ...prev,
                  plan: scheme.title,
                  deposit: String(sharesNum * 500)
                }));
              }}
            >
              <Ionicons name={selected ? "radio-button-on" : "radio-button-off"} size={20} color={colors.navy} />
              <Text style={styles.quickActionText}>{scheme.title} · {scheme.amount}</Text>
            </Pressable>
          );
        })}
        <FormInput
          label="Number of ₹500 monthly shares"
          value={form.shares}
          keyboardType="numeric"
          onChangeText={(shares) => {
            const num = Math.max(1, parseInt(shares, 10) || 1);
            setForm((prev) => ({
              ...prev,
              shares,
              deposit: String(num * 500)
            }));
          }}
        />
        <Text style={styles.sharesCalculationHint}>
          ₹500 per share: {form.shares || 1} share(s) = ₹{((Math.max(1, parseInt(form.shares, 10) || 1)) * 500).toLocaleString("en-IN")} opening deposit
        </Text>
        <FormInput
          label="Opening deposit (₹)"
          value={form.deposit}
          keyboardType="numeric"
          error={Number(form.deposit) < (Math.max(1, parseInt(form.shares, 10) || 1) * 500) ? `Minimum deposit for ${form.shares || 1} share(s) is ₹${(Math.max(1, parseInt(form.shares, 10) || 1) * 500).toLocaleString("en-IN")}` : undefined}
          onChangeText={(deposit) => setForm((prev) => ({ ...prev, deposit }))}
        />
        <ActionButton label="Submit Request" icon="cloud-upload-outline" onPress={submitAccount} />
      </Panel>
    </>
  );
}

function PassbookModal({
  account,
  data,
  loading,
  onClose
}: {
  account: MemberAccount | null;
  data: AccountPassbookData | null;
  loading: boolean;
  onClose: () => void;
}) {
  if (!account) return null;
  return (
    <Modal visible={account !== null} transparent animationType="slide" onRequestClose={onClose}>
      <View style={styles.modalOverlay}>
        <View style={[styles.receiptModalCard, { maxHeight: "88%", paddingBottom: 16 }]}>
          <View style={styles.receiptModalHeader}>
            <Ionicons name="book-outline" size={24} color={colors.navy} />
            <Text style={styles.receiptModalTitle}>Account Passbook</Text>
            <Pressable onPress={onClose} style={{ padding: 4 }}>
              <Ionicons name="close" size={24} color={colors.ink} />
            </Pressable>
          </View>
          <View style={styles.receiptModalDivider} />
          <Text style={styles.passbookAccHeader}>{account.scheme_name} · {account.account_no}</Text>
          {account.certificate_no && (
            <Text style={styles.passbookCertNo}>Certificate No: {account.certificate_no}</Text>
          )}

          {loading ? (
            <View style={{ paddingVertical: 40, alignItems: "center" }}>
              <ActivityIndicator size="large" color={colors.navy} />
              <Text style={[styles.helperText, { marginTop: 10 }]}>Loading live society ledger...</Text>
            </View>
          ) : data ? (
            <>
              <View style={styles.passbookSummaryGrid}>
                <View style={styles.passbookSummaryCol}>
                  <Text style={styles.passbookSummaryLabel}>Opening</Text>
                  <Text style={styles.passbookSummaryVal}>₹{Number(data.summary.opening_deposit).toLocaleString("en-IN")}</Text>
                </View>
                <View style={styles.passbookSummaryCol}>
                  <Text style={styles.passbookSummaryLabel}>Total Credits</Text>
                  <Text style={[styles.passbookSummaryVal, { color: "#087443" }]}>+₹{Number(data.summary.total_credits).toLocaleString("en-IN")}</Text>
                </View>
                <View style={styles.passbookSummaryCol}>
                  <Text style={styles.passbookSummaryLabel}>Total Debits</Text>
                  <Text style={[styles.passbookSummaryVal, { color: colors.red }]}>-₹{Number(data.summary.total_debits).toLocaleString("en-IN")}</Text>
                </View>
                <View style={styles.passbookSummaryCol}>
                  <Text style={styles.passbookSummaryLabel}>Current Balance</Text>
                  <Text style={[styles.passbookSummaryVal, { color: colors.navy, fontWeight: "900" }]}>₹{Number(data.summary.current_balance).toLocaleString("en-IN")}</Text>
                </View>
              </View>

              <Text style={[styles.fieldLabel, { marginTop: 12, marginBottom: 6 }]}>Real-Time Transactions ({data.transactions.length})</Text>
              <ScrollView style={{ maxHeight: 280 }} showsVerticalScrollIndicator={true}>
                {data.transactions.length === 0 ? (
                  <Text style={styles.helperText}>No transactions posted yet.</Text>
                ) : (
                  data.transactions.map((tx) => {
                    const isCr = tx.direction === "credit";
                    return (
                      <View key={tx.id} style={styles.passbookTxRow}>
                        <View style={{ flex: 1, paddingRight: 8 }}>
                          <Text style={styles.passbookTxDesc}>{tx.description}</Text>
                          <Text style={styles.passbookTxDate}>
                            {new Date(tx.posted_at || tx.created_at).toLocaleDateString()} · {tx.reference.slice(0, 8)}...
                          </Text>
                        </View>
                        <View style={{ alignItems: "flex-end" }}>
                          <Text style={[styles.passbookTxAmount, isCr ? { color: "#087443" } : { color: colors.red }]}>
                            {isCr ? "+Cr" : "-Dr"} ₹{Number(tx.amount).toLocaleString("en-IN", { minimumFractionDigits: 2 })}
                          </Text>
                          <Text style={styles.passbookTxBal}>
                            Bal: ₹{Number(tx.running_balance).toLocaleString("en-IN", { minimumFractionDigits: 2 })}
                          </Text>
                        </View>
                      </View>
                    );
                  })
                )}
              </ScrollView>
            </>
          ) : (
            <Text style={styles.helperText}>No passbook data available.</Text>
          )}

          <View style={{ marginTop: 14 }}>
            <ActionButton label="Close Passbook" icon="checkmark-outline" onPress={onClose} />
          </View>
        </View>
      </View>
    </Modal>
  );
}

function RdScheduleModal({
  account,
  installments,
  onClose
}: {
  account: MemberAccount | null;
  installments: RdInstallment[];
  onClose: () => void;
}) {
  if (!account) return null;
  const filtered = installments.filter((i) => i.account_id === account.id || i.account_no === account.account_no);
  const paidCount = filtered.filter((i) => i.status === "paid").length;
  const dueCount = filtered.filter((i) => ["due", "overdue", "partial"].includes(i.status)).length;
  const totalDueAmount = filtered
    .filter((i) => ["due", "overdue", "partial"].includes(i.status))
    .reduce((sum, i) => sum + (Number(i.amount_due) + Number(i.penalty_due) - Number(i.paid_amount)), 0);

  return (
    <Modal visible={account !== null} transparent animationType="slide" onRequestClose={onClose}>
      <View style={styles.modalOverlay}>
        <View style={[styles.receiptModalCard, { maxHeight: "88%", paddingBottom: 16 }]}>
          <View style={styles.receiptModalHeader}>
            <Ionicons name="calendar-outline" size={24} color={colors.navy} />
            <Text style={styles.receiptModalTitle}>RD Installment Schedule</Text>
            <Pressable onPress={onClose} style={{ padding: 4 }}>
              <Ionicons name="close" size={24} color={colors.ink} />
            </Pressable>
          </View>
          <View style={styles.receiptModalDivider} />
          <Text style={styles.passbookAccHeader}>{account.scheme_name} · {account.account_no}</Text>
          <Text style={styles.passbookCertNo}>
            Commitment: ₹{Number(account.monthly_commitment).toLocaleString("en-IN")} / {account.deposit_frequency || "month"}
          </Text>

          <View style={styles.passbookSummaryGrid}>
            <View style={styles.passbookSummaryCol}>
              <Text style={styles.passbookSummaryLabel}>Total</Text>
              <Text style={styles.passbookSummaryVal}>{filtered.length}</Text>
            </View>
            <View style={styles.passbookSummaryCol}>
              <Text style={styles.passbookSummaryLabel}>Paid</Text>
              <Text style={[styles.passbookSummaryVal, { color: "#087443" }]}>{paidCount}</Text>
            </View>
            <View style={styles.passbookSummaryCol}>
              <Text style={styles.passbookSummaryLabel}>Pending Dues</Text>
              <Text style={[styles.passbookSummaryVal, { color: dueCount > 0 ? colors.red : "#087443" }]}>
                {dueCount}
              </Text>
            </View>
            <View style={styles.passbookSummaryCol}>
              <Text style={styles.passbookSummaryLabel}>Due Amount</Text>
              <Text style={[styles.passbookSummaryVal, { color: dueCount > 0 ? colors.red : colors.ink, fontWeight: "900" }]}>
                ₹{totalDueAmount.toLocaleString("en-IN")}
              </Text>
            </View>
          </View>

          <Text style={[styles.fieldLabel, { marginTop: 12, marginBottom: 6 }]}>Installments List ({filtered.length})</Text>
          <ScrollView style={{ maxHeight: 280 }} showsVerticalScrollIndicator={true}>
            {filtered.length === 0 ? (
              <Text style={styles.helperText}>No installment schedule generated yet.</Text>
            ) : (
              filtered.map((item) => {
                const isPaid = item.status === "paid";
                return (
                  <View key={item.id} style={styles.passbookTxRow}>
                    <View style={{ flex: 1, paddingRight: 8 }}>
                      <Text style={styles.passbookTxDesc}>Installment #{item.installment_no}</Text>
                      <Text style={styles.passbookTxDate}>Due on: {new Date(item.due_on).toLocaleDateString()}</Text>
                      {Number(item.penalty_due) > 0 && (
                        <Text style={styles.installmentFine}>Fine/Penalty: ₹{Number(item.penalty_due).toLocaleString("en-IN")}</Text>
                      )}
                    </View>
                    <View style={{ alignItems: "flex-end" }}>
                      <Text style={styles.passbookTxAmount}>
                        ₹{Number(item.amount_due).toLocaleString("en-IN")}
                      </Text>
                      <StatusPill label={item.status} />
                      {isPaid && item.paid_at && (
                        <Text style={[styles.passbookTxDate, { color: "#087443", marginTop: 2 }]}>
                          Paid {new Date(item.paid_at).toLocaleDateString()}
                        </Text>
                      )}
                    </View>
                  </View>
                );
              })
            )}
          </ScrollView>

          <View style={{ marginTop: 14 }}>
            <ActionButton label="Close Schedule" icon="checkmark-outline" onPress={onClose} />
          </View>
        </View>
      </View>
    </Modal>
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

function KycScreen({
  kycStatus,
  rejectionNote,
  submitKyc
}: {
  kycStatus: string;
  rejectionNote?: string | null;
  submitKyc: (type: "aadhaar" | "pan" | "photo" | "address_proof") => void;
}) {
  return (
    <>
      {kycStatus === "rejected" && (
        <View style={styles.kycBanner}>
          <Ionicons name="alert-circle" size={24} color={colors.red} />
          <View style={styles.kycBannerCopy}>
            <Text style={styles.kycBannerTitle}>KYC Verification Rejected</Text>
            <Text style={styles.kycBannerText}>
              {rejectionNote || "Document was rejected during office verification. Please re-upload clear photos or documents."}
            </Text>
          </View>
        </View>
      )}

      <Panel title="KYC Status" icon="shield-checkmark-outline">
        <InfoRow label="Current status" value={kycStatus.replaceAll("_", " ")} emphasis />
        <InfoRow label="Verification method" value="Manual society office review" />
        <InfoRow label="Required documents" value="Aadhaar, PAN, photo, address proof" />
      </Panel>

      <Panel title="Upload KYC Documents" icon="id-card-outline">
        <Text style={styles.helperText}>PDF, JPG or PNG up to 5 MB. Documents are encrypted at rest and reviewed by authorized staff.</Text>
        <ActionButton label="Upload Aadhaar" icon="cloud-upload-outline" onPress={() => submitKyc("aadhaar")} />
        <ActionButton label="Upload PAN" icon="cloud-upload-outline" onPress={() => submitKyc("pan")} />
        <ActionButton label="Upload Photo" icon="camera-outline" onPress={() => submitKyc("photo")} />
        <ActionButton label="Upload Address Proof" icon="document-outline" onPress={() => submitKyc("address_proof")} />
      </Panel>
    </>
  );
}

function ProfileScreen({ profile, dashboard }: { profile: Dashboard["profile"]; dashboard: Dashboard }) {
  const activeLoans = dashboard.loans.filter(l => ["approved", "disbursed"].includes(l.status));
  const outstandingLoan = activeLoans.reduce((sum, l) => sum + Number(l.outstanding_principal ?? 0), 0);

  return (
    <>
      <Panel title="Digital Membership Card" icon="id-card-outline">
        <LinearGradient colors={[colors.navyDark, colors.navy, "#071c3e"]} style={styles.idCard}>
          <View style={styles.idCardHeader}>
            <Image source={require("./assets/mahamilon-logo.png")} style={styles.idCardLogo} />
            <View style={{ flex: 1, marginLeft: 10 }}>
              <Text style={styles.idCardSociety}>MAHAMILON SOCIETY</Text>
              <Text style={styles.idCardSub}>Member Identification</Text>
            </View>
          </View>
          <View style={styles.idCardBody}>
            <View style={styles.idCardInfo}>
              <Text style={styles.idCardMemberName}>{profile.name}</Text>
              <Text style={styles.idCardMemberNo}>{profile.member_no}</Text>
              <Text style={styles.idCardDetail}>Phone: {profile.phone}</Text>
              <Text style={styles.idCardDetail}>Nominee: {profile.nominee}</Text>
              <Text style={styles.idCardStatus}>KYC: {profile.kyc_status.replaceAll("_", " ").toUpperCase()}</Text>
            </View>
            <Image
              source={{ uri: `https://quickchart.io/qr?size=220&text=${encodeURIComponent(`Mahamilon Member: ${profile.member_no} - ${profile.name}`)}` }}
              style={styles.idCardQr}
            />
          </View>
        </LinearGradient>
      </Panel>

      <Panel title="Member Profile & Nominee" icon="person-circle-outline">
        <View style={styles.profileHeader}>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>{profile.name.split(" ").map(v => v[0]).slice(0, 2).join("")}</Text>
          </View>
          <View style={styles.profileCopy}>
            <Text style={styles.profileName}>{profile.name}</Text>
            <Text style={styles.cardMeta}>{profile.member_no}</Text>
          </View>
        </View>
        <InfoRow label="Mobile Phone" value={profile.phone} />
        <InfoRow label="Email Address" value={profile.email ?? "Not provided"} />
        <InfoRow label="Residential Address" value={profile.address} />
        <InfoRow label="Nominee Name" value={profile.nominee} emphasis />
        <InfoRow label="Occupation" value={profile.occupation ?? "Not provided"} />
        <InfoRow label="KYC Verification" value={profile.kyc_status.replaceAll("_", " ").toUpperCase()} />
        <InfoRow label="Mahamilon Credit Score" value={`${profile.mcs_score} / 900`} emphasis />
        <InfoRow label="Current Max Loan Limit" value={`₹${Number(profile.maximum_loan).toLocaleString("en-IN")}`} />
      </Panel>

      <Panel title="Deposit Accounts (Savings, FD, RD)" icon="wallet-outline">
        <InfoRow label="Savings Balance" value={`₹${Number(profile.savings_balance ?? profile.total_balance).toLocaleString("en-IN", { minimumFractionDigits: 2 })}`} emphasis />
        <InfoRow label="Fixed Deposits (FD)" value={`₹${Number(profile.fd_balance ?? 0).toLocaleString("en-IN", { minimumFractionDigits: 2 })}`} />
        <InfoRow label="Recurring Deposits (RD)" value={`₹${Number(profile.rd_balance ?? 0).toLocaleString("en-IN", { minimumFractionDigits: 2 })}`} />
        <InfoRow label="Total Society Balance" value={`₹${Number(profile.total_balance).toLocaleString("en-IN", { minimumFractionDigits: 2 })}`} emphasis />
        <InfoRow label="Active Accounts" value={String(profile.active_accounts)} />
      </Panel>

      <Panel title="Loans & Financial Standing" icon="cash-outline">
        <InfoRow label="Active Loans" value={String(activeLoans.length)} />
        <InfoRow label="Outstanding Principal" value={`₹${outstandingLoan.toLocaleString("en-IN", { minimumFractionDigits: 2 })}`} emphasis />
        <InfoRow label="Profit / Interest Earned" value={`₹${Number(dashboard.profit_earned ?? 0).toLocaleString("en-IN", { minimumFractionDigits: 2 })}`} emphasis />
        <InfoRow label="Penalties / Fines Due" value={`₹${Number(dashboard.penalties_due ?? 0).toLocaleString("en-IN", { minimumFractionDigits: 2 })}`} />
        <InfoRow label="Total Receipts Issued" value={String(dashboard.receipts?.length ?? 0)} />
      </Panel>

      <Panel title="Security & Authentication" icon="lock-closed-outline">
        <InfoRow label="Login Method" value="Member ID + registered mobile + OTP/MPIN" />
        <InfoRow label="Device Alerts" value="Active & synchronized" />
        <InfoRow label="Data Sync" value="Connected to encrypted live society ledger" emphasis />
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
  autoCapitalize = "sentences",
  error,
  helperText
}: {
  label: string;
  value: string;
  onChangeText: (text: string) => void;
  keyboardType?: "default" | "numeric" | "phone-pad";
  secureTextEntry?: boolean;
  autoCapitalize?: "none" | "sentences" | "words" | "characters";
  error?: string;
  helperText?: string;
}) {
  const hasError = Boolean(error && error.trim().length > 0);
  return (
    <View style={styles.field}>
      <Text style={styles.fieldLabel}>{label}</Text>
      <TextInput
        style={[
          styles.input,
          hasError && styles.inputError
        ]}
        placeholder={label}
        placeholderTextColor="#8f9aab"
        value={value}
        onChangeText={onChangeText}
        keyboardType={keyboardType}
        secureTextEntry={secureTextEntry}
        autoCapitalize={autoCapitalize}
      />
      {hasError ? (
        <View style={styles.errorRow}>
          <Ionicons name="alert-circle" size={13} color={colors.red} />
          <Text style={styles.fieldErrorText}>{error}</Text>
        </View>
      ) : helperText ? (
        <Text style={styles.fieldHelperText}>{helperText}</Text>
      ) : null}
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
  const norm = (label || "").toLowerCase();
  const positive = norm === "active" || norm === "approved" || norm === "paid";
  const danger = norm === "overdue" || norm === "rejected";
  const warning = norm === "due" || norm === "partial" || norm === "scheduled" || norm.includes("pending") || norm.includes("review") || norm.includes("matures");
  return (
    <View
      style={[
        styles.statusPill,
        positive && styles.statusPillPositive,
        warning && styles.statusPillWarning,
        danger && styles.statusPillDanger
      ]}
    >
      <Text
        style={[
          styles.statusText,
          positive && styles.statusTextPositive,
          warning && styles.statusTextWarning,
          danger && styles.statusTextDanger
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
  inputError: {
    borderColor: colors.red,
    borderWidth: 1.5,
    backgroundColor: "#fff8f8"
  },
  errorRow: {
    flexDirection: "row",
    alignItems: "center",
    gap: 4,
    marginTop: 4
  },
  fieldErrorText: {
    color: colors.red,
    fontSize: 12,
    fontWeight: "700"
  },
  fieldHelperText: {
    color: "#64748b",
    fontSize: 11,
    marginTop: 3
  },
  errorBanner: {
    backgroundColor: "#fef2f2",
    borderWidth: 1,
    borderColor: "#fecaca",
    borderRadius: 8,
    padding: 12,
    marginBottom: 14,
    flexDirection: "row",
    alignItems: "center",
    gap: 8
  },
  errorBannerText: {
    color: colors.red,
    fontSize: 13,
    fontWeight: "700",
    flex: 1
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
  statusPillDanger: {
    backgroundColor: "#fff1f0"
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
  statusTextDanger: {
    color: colors.red
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
  },
  kycBanner: {
    marginHorizontal: 16,
    marginTop: 14,
    padding: 14,
    borderRadius: 8,
    backgroundColor: "#fff0f0",
    borderWidth: 1,
    borderColor: "#ffd4d4",
    flexDirection: "row",
    alignItems: "flex-start",
    gap: 10
  },
  kycBannerCopy: {
    flex: 1
  },
  kycBannerTitle: {
    color: colors.red,
    fontSize: 14,
    fontWeight: "900",
    marginBottom: 2
  },
  kycBannerText: {
    color: "#681316",
    fontSize: 12,
    fontWeight: "700",
    lineHeight: 17
  },
  kycReuploadBtn: {
    marginTop: 8,
    alignSelf: "flex-start",
    paddingHorizontal: 12,
    paddingVertical: 6,
    backgroundColor: colors.red,
    borderRadius: 6
  },
  kycReuploadBtnText: {
    color: colors.white,
    fontSize: 12,
    fontWeight: "900"
  },
  schemeRow: {
    flexDirection: "row",
    gap: 8,
    marginHorizontal: 16,
    marginTop: 10
  },
  schemeMiniCard: {
    flex: 1,
    padding: 12,
    borderRadius: 8,
    backgroundColor: colors.white,
    borderWidth: 1,
    borderColor: colors.line,
    alignItems: "center"
  },
  schemeMiniLabel: {
    color: colors.muted,
    fontSize: 11,
    fontWeight: "800",
    marginTop: 4
  },
  schemeMiniVal: {
    color: colors.navy,
    fontSize: 14,
    fontWeight: "900",
    marginTop: 2
  },
  receiptItem: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: colors.line
  },
  receiptNo: {
    color: colors.ink,
    fontSize: 13,
    fontWeight: "900"
  },
  receiptCategory: {
    color: colors.muted,
    fontSize: 11,
    fontWeight: "800",
    marginTop: 2
  },
  receiptDate: {
    color: colors.muted,
    fontSize: 11,
    fontWeight: "700",
    marginTop: 1
  },
  receiptAmount: {
    color: colors.green,
    fontSize: 14,
    fontWeight: "900"
  },
  receiptViewBadge: {
    marginTop: 4,
    paddingHorizontal: 7,
    paddingVertical: 3,
    borderRadius: 4,
    backgroundColor: "#edf4ff"
  },
  receiptViewText: {
    color: colors.navy,
    fontSize: 10,
    fontWeight: "900"
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: "rgba(0,0,0,0.55)",
    justifyContent: "center",
    padding: 20
  },
  receiptModalCard: {
    backgroundColor: colors.white,
    borderRadius: 12,
    padding: 20,
    ...shadow
  },
  receiptModalHeader: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between"
  },
  receiptModalTitle: {
    flex: 1,
    marginLeft: 10,
    color: colors.navy,
    fontSize: 18,
    fontWeight: "900"
  },
  receiptModalDivider: {
    height: 1,
    backgroundColor: colors.line,
    marginVertical: 14
  },
  receiptModalFooter: {
    color: colors.muted,
    fontSize: 11,
    fontWeight: "800",
    textAlign: "center",
    marginVertical: 14,
    fontStyle: "italic"
  },
  idCard: {
    borderRadius: 12,
    padding: 16,
    ...shadow
  },
  idCardHeader: {
    flexDirection: "row",
    alignItems: "center",
    borderBottomWidth: 1,
    borderBottomColor: "rgba(255,255,255,0.18)",
    paddingBottom: 10,
    marginBottom: 12
  },
  idCardLogo: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: colors.white
  },
  idCardSociety: {
    color: colors.white,
    fontSize: 15,
    fontWeight: "900",
    letterSpacing: 0.5
  },
  idCardSub: {
    color: "#a9c4f5",
    fontSize: 11,
    fontWeight: "700"
  },
  idCardBody: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between"
  },
  idCardInfo: {
    flex: 1,
    paddingRight: 12
  },
  idCardMemberName: {
    color: colors.white,
    fontSize: 16,
    fontWeight: "900"
  },
  idCardMemberNo: {
    color: "#e2ecff",
    fontSize: 12,
    fontWeight: "800",
    marginTop: 2
  },
  idCardDetail: {
    color: "#bfd3f8",
    fontSize: 11,
    fontWeight: "700",
    marginTop: 2
  },
  idCardStatus: {
    color: "#4ade80",
    fontSize: 11,
    fontWeight: "900",
    marginTop: 4
  },
  idCardQr: {
    width: 86,
    height: 86,
    borderRadius: 8,
    backgroundColor: colors.white,
    padding: 4
  },
  sharesCalculationHint: {
    fontSize: 12,
    color: colors.navy,
    fontWeight: "700",
    marginTop: -6,
    marginBottom: 12,
    marginLeft: 2
  },
  accountActionRow: {
    flexDirection: "row",
    gap: 10,
    marginTop: 10
  },
  accountActionBtn: {
    flex: 1,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 6,
    paddingVertical: 9,
    paddingHorizontal: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: colors.navy,
    backgroundColor: "#edf4ff"
  },
  accountActionBtnText: {
    fontSize: 12,
    fontWeight: "800",
    color: colors.navy
  },
  dueBadge: {
    backgroundColor: "#fff1f0",
    borderColor: "#ffa39e",
    borderWidth: 1,
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: 6,
    alignSelf: "flex-start",
    marginTop: 4
  },
  dueBadgeText: {
    color: colors.red,
    fontSize: 12,
    fontWeight: "800"
  },
  clearBadge: {
    backgroundColor: "#f6ffed",
    borderColor: "#b7eb8f",
    borderWidth: 1,
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: 6,
    alignSelf: "flex-start",
    marginTop: 4
  },
  clearBadgeText: {
    color: "#389e0d",
    fontSize: 12,
    fontWeight: "800"
  },
  passbookAccHeader: {
    fontSize: 15,
    fontWeight: "900",
    color: colors.navy,
    marginBottom: 2
  },
  passbookCertNo: {
    fontSize: 12,
    color: colors.muted,
    fontWeight: "700",
    marginBottom: 10
  },
  passbookSummaryGrid: {
    flexDirection: "row",
    backgroundColor: "#f8fafc",
    borderRadius: 8,
    padding: 10,
    borderWidth: 1,
    borderColor: colors.line,
    marginBottom: 6
  },
  passbookSummaryCol: {
    flex: 1,
    alignItems: "center"
  },
  passbookSummaryLabel: {
    fontSize: 10,
    color: colors.muted,
    fontWeight: "700",
    marginBottom: 2
  },
  passbookSummaryVal: {
    fontSize: 12,
    fontWeight: "800",
    color: colors.ink
  },
  passbookTxRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: colors.line
  },
  passbookTxDesc: {
    fontSize: 13,
    fontWeight: "700",
    color: colors.ink
  },
  passbookTxDate: {
    fontSize: 11,
    color: colors.muted,
    marginTop: 2
  },
  passbookTxAmount: {
    fontSize: 13,
    fontWeight: "800",
    color: colors.ink
  },
  passbookTxBal: {
    fontSize: 11,
    color: colors.muted,
    marginTop: 2
  },
  installmentFine: {
    fontSize: 11,
    color: colors.red,
    fontWeight: "700",
    marginTop: 2
  }
});
