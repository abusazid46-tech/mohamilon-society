import { Ionicons, MaterialCommunityIcons } from "@expo/vector-icons";
import { LinearGradient } from "expo-linear-gradient";
import * as Notifications from "expo-notifications";
import React, { useEffect, useState } from "react";
import {
  Alert,
  Image,
  KeyboardAvoidingView,
  Platform,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View
} from "react-native";
import { ledger, loans, memberProfile, notifications, Scheme, schemes } from "./src/domain";
import { colors, shadow } from "./src/theme";

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
  memberId: string;
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
  openingScheme: string;
  openingDeposit: string;
  mpin: string;
};

type AccountForm = {
  name: string;
  phone: string;
  nominee: string;
  plan: string;
  deposit: string;
};

type LoanForm = {
  amount: string;
  purpose: string;
  income: string;
  tenure: string;
};

type KycForm = {
  aadhaar: string;
  pan: string;
  address: string;
};

const tabs: { id: Tab; label: string; icon: keyof typeof Ionicons.glyphMap }[] = [
  { id: "home", label: "Home", icon: "home-outline" },
  { id: "accounts", label: "Accounts", icon: "wallet-outline" },
  { id: "loan", label: "Loan", icon: "document-text-outline" },
  { id: "kyc", label: "KYC", icon: "shield-checkmark-outline" },
  { id: "profile", label: "Profile", icon: "person-outline" }
];

export default function App() {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [authMode, setAuthMode] = useState<AuthMode>("login");
  const [activeTab, setActiveTab] = useState<Tab>("home");
  const [loginForm, setLoginForm] = useState<LoginForm>({
    memberId: "MS-2026-0148",
    phone: "9876543210",
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
    openingScheme: "Daily RD Account",
    openingDeposit: "",
    mpin: ""
  });
  const [accountForm, setAccountForm] = useState<AccountForm>({
    name: "",
    phone: "",
    nominee: "",
    plan: "Daily RD Account",
    deposit: ""
  });
  const [loanForm, setLoanForm] = useState<LoanForm>({
    amount: "",
    purpose: "",
    income: "",
    tenure: ""
  });
  const [kycForm, setKycForm] = useState<KycForm>({
    aadhaar: "",
    pan: "",
    address: ""
  });

  useEffect(() => {
    Notifications.requestPermissionsAsync().catch(() => undefined);
  }, []);

  const login = () => {
    const hasMemberId = loginForm.memberId.trim().length >= 6;
    const hasPhone = loginForm.phone.trim().length >= 10;
    if (!hasMemberId || !hasPhone) {
      Alert.alert("Check login details", "Enter member ID and registered mobile number.");
      return;
    }
    setIsLoggedIn(true);
  };

  const register = () => {
    const requiredFields = [
      registrationForm.fullName,
      registrationForm.phone,
      registrationForm.aadhaar,
      registrationForm.address,
      registrationForm.nominee,
      registrationForm.openingDeposit,
      registrationForm.mpin
    ];
    const hasMissing = requiredFields.some((value) => value.trim().length === 0);
    if (hasMissing || registrationForm.phone.trim().length < 10) {
      Alert.alert(
        "Complete registration",
        "Enter name, mobile, Aadhaar, address, nominee, opening deposit and MPIN."
      );
      return;
    }

    Alert.alert(
      "Registration submitted",
      "Your member registration and KYC details were submitted for manual approval."
    );
    setLoginForm((prev) => ({
      ...prev,
      memberId: "Pending approval",
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
      openingScheme: "Daily RD Account",
      openingDeposit: "",
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

  const submitAccount = () => {
    Alert.alert("Request submitted", "Your account opening request was sent for society review.");
    setAccountForm({ name: "", phone: "", nominee: "", plan: "Daily RD Account", deposit: "" });
  };

  const submitLoan = () => {
    Alert.alert("Loan application submitted", "Your application is now under review.");
    setLoanForm({ amount: "", purpose: "", income: "", tenure: "" });
  };

  const submitKyc = () => {
    Alert.alert("KYC submitted", "Documents are marked for manual verification.");
    setKycForm({ aadhaar: "", pan: "", address: "" });
  };

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
    <SafeAreaView style={styles.safeArea}>
      <KeyboardAvoidingView
        style={styles.keyboard}
        behavior={Platform.select({ ios: "padding", android: undefined })}
      >
        <View style={styles.appShell}>
          <ScrollView
            style={styles.content}
            contentContainerStyle={styles.contentInner}
            showsVerticalScrollIndicator={false}
          >
            <MemberHeader onLogout={() => setIsLoggedIn(false)} />
            {activeTab === "home" && (
              <HomeScreen
                setActiveTab={setActiveTab}
                sendLocalNotification={sendLocalNotification}
              />
            )}
            {activeTab === "accounts" && (
              <AccountsScreen
                form={accountForm}
                setForm={setAccountForm}
                submitAccount={submitAccount}
              />
            )}
            {activeTab === "loan" && (
              <LoanScreen form={loanForm} setForm={setLoanForm} submitLoan={submitLoan} />
            )}
            {activeTab === "kyc" && (
              <KycScreen form={kycForm} setForm={setKycForm} submitKyc={submitKyc} />
            )}
            {activeTab === "profile" && <ProfileScreen />}
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
    <SafeAreaView style={styles.loginSafe}>
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
              label="Member ID"
              value={form.memberId}
              onChangeText={(memberId) => setForm((prev) => ({ ...prev, memberId }))}
              autoCapitalize="characters"
            />
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
        label="Opening scheme"
        value={form.openingScheme}
        onChangeText={(openingScheme) => setForm((prev) => ({ ...prev, openingScheme }))}
      />
      <FormInput
        label="Opening deposit"
        value={form.openingDeposit}
        keyboardType="numeric"
        onChangeText={(openingDeposit) => setForm((prev) => ({ ...prev, openingDeposit }))}
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

function MemberHeader({ onLogout }: { onLogout: () => void }) {
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
          <Text style={styles.memberName}>{memberProfile.name}</Text>
          <Text style={styles.memberMeta}>{memberProfile.memberId}</Text>
        </View>
        <StatusPill label={memberProfile.kycStatus} />
      </View>
    </LinearGradient>
  );
}

function HomeScreen({
  setActiveTab,
  sendLocalNotification
}: {
  setActiveTab: (tab: Tab) => void;
  sendLocalNotification: () => void;
}) {
  return (
    <>
      <View style={styles.balancePanel}>
        <Text style={styles.balanceLabel}>Savings balance</Text>
        <Text style={styles.balanceValue}>{memberProfile.savingsBalance}</Text>
        <Text style={styles.balanceHint}>Next collection: {memberProfile.nextCollection}</Text>
      </View>

      <View style={styles.metricGrid}>
        <MetricCard icon="trending-up-outline" label="Total deposit" value={memberProfile.totalDeposit} />
        <MetricCard icon="albums-outline" label="Accounts" value={memberProfile.activeAccounts} />
        <MetricCard icon="shield-checkmark-outline" label="KYC" value="In Review" />
        <MetricCard icon="cash-outline" label="Loan" value="Under Review" />
      </View>

      <View style={styles.quickActions}>
        <QuickAction icon="wallet-outline" label="Open RD/FD" onPress={() => setActiveTab("accounts")} />
        <QuickAction icon="document-text-outline" label="Apply Loan" onPress={() => setActiveTab("loan")} />
        <QuickAction icon="shield-outline" label="Update KYC" onPress={() => setActiveTab("kyc")} />
      </View>

      <Panel title="Recent Ledger" icon="receipt-outline">
        {ledger.map((item) => (
          <InfoRow key={item.id} label={`${item.title} | ${item.date}`} value={`${item.amount} | ${item.status}`} />
        ))}
      </Panel>

      <NotificationList sendLocalNotification={sendLocalNotification} />
    </>
  );
}

function AccountsScreen({
  form,
  setForm,
  submitAccount
}: {
  form: AccountForm;
  setForm: React.Dispatch<React.SetStateAction<AccountForm>>;
  submitAccount: () => void;
}) {
  return (
    <>
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
        <FormInput
          label="Applicant full name"
          value={form.name}
          onChangeText={(name) => setForm((prev) => ({ ...prev, name }))}
        />
        <FormInput
          label="Mobile number"
          value={form.phone}
          keyboardType="phone-pad"
          onChangeText={(phone) => setForm((prev) => ({ ...prev, phone }))}
        />
        <FormInput
          label="Nominee name"
          value={form.nominee}
          onChangeText={(nominee) => setForm((prev) => ({ ...prev, nominee }))}
        />
        <FormInput
          label="Scheme"
          value={form.plan}
          onChangeText={(plan) => setForm((prev) => ({ ...prev, plan }))}
        />
        <FormInput
          label="Opening deposit"
          value={form.deposit}
          keyboardType="numeric"
          onChangeText={(deposit) => setForm((prev) => ({ ...prev, deposit }))}
        />
        <ActionButton label="Submit Request" icon="cloud-upload-outline" onPress={submitAccount} />
      </Panel>
    </>
  );
}

function LoanScreen({
  form,
  setForm,
  submitLoan
}: {
  form: LoanForm;
  setForm: React.Dispatch<React.SetStateAction<LoanForm>>;
  submitLoan: () => void;
}) {
  return (
    <>
      <Panel title="Loan Application" icon="document-text-outline">
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
            <Text style={styles.cardMeta}>{loan.amount}</Text>
            <InfoRow label={loan.officer} value={loan.nextStep} />
          </View>
        ))}
      </Panel>
    </>
  );
}

function KycScreen({
  form,
  setForm,
  submitKyc
}: {
  form: KycForm;
  setForm: React.Dispatch<React.SetStateAction<KycForm>>;
  submitKyc: () => void;
}) {
  return (
    <>
      <Panel title="KYC Status" icon="shield-checkmark-outline">
        <InfoRow label="Current status" value={memberProfile.kycStatus} emphasis />
        <InfoRow label="Verification method" value="Manual society office review" />
        <InfoRow label="Required documents" value="Aadhaar, PAN, photo, address proof" />
      </Panel>

      <Panel title="Update KYC Details" icon="id-card-outline">
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
        <ActionButton label="Submit KYC" icon="cloud-upload-outline" onPress={submitKyc} />
      </Panel>
    </>
  );
}

function ProfileScreen() {
  return (
    <>
      <Panel title="Member Profile" icon="person-circle-outline">
        <View style={styles.profileHeader}>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>{memberProfile.initials}</Text>
          </View>
          <View style={styles.profileCopy}>
            <Text style={styles.profileName}>{memberProfile.name}</Text>
            <Text style={styles.cardMeta}>{memberProfile.memberId}</Text>
          </View>
        </View>
        <InfoRow label="Phone" value={memberProfile.phone} />
        <InfoRow label="Email" value={memberProfile.email} />
        <InfoRow label="Address" value={memberProfile.address} />
        <InfoRow label="Nominee" value={memberProfile.nominee} />
        <InfoRow label="Occupation" value={memberProfile.occupation} />
      </Panel>

      <Panel title="Security" icon="lock-closed-outline">
        <InfoRow label="Login method" value="Member ID + registered mobile + OTP/MPIN" />
        <InfoRow label="Device alerts" value="Enabled" />
        <InfoRow label="Data sync" value="Online ledger required for live launch" emphasis />
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
  return (
    <View style={styles.bottomNav}>
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

function NotificationList({ sendLocalNotification }: { sendLocalNotification: () => void }) {
  return (
    <Panel title="Notifications" icon="notifications-outline">
      {notifications.map((item) => (
        <View key={item.id} style={styles.notificationItem}>
          <Ionicons
            name={item.unread ? "notifications" : "notifications-outline"}
            size={22}
            color={item.unread ? colors.red : colors.navy}
          />
          <View style={styles.notificationText}>
            <Text style={styles.cardTitle}>{item.title}</Text>
            <Text style={styles.cardMeta}>{item.body}</Text>
          </View>
          <Text style={styles.notificationTime}>{item.time}</Text>
        </View>
      ))}
      <ActionButton label="Test Alert" icon="send-outline" onPress={sendLocalNotification} />
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
    paddingBottom: 104
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
    paddingBottom: Platform.OS === "ios" ? 20 : 10,
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
