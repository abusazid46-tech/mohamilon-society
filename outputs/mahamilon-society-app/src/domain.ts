export type SchemeFrequency = "Daily" | "Monthly" | "Yearly" | "Fixed";

export type Scheme = {
  id: string;
  title: string;
  type: "RD" | "FD";
  frequency: SchemeFrequency;
  amount: string;
  term: string;
  rate: string;
  status: "Active" | "Available" | "Matures Soon";
  maturity: string;
};

export type Loan = {
  id: string;
  purpose: string;
  amount: string;
  status: "Draft" | "Under Review" | "Approved";
  officer: string;
  nextStep: string;
};

export type LedgerItem = {
  id: string;
  title: string;
  amount: string;
  date: string;
  status: "Paid" | "Due" | "Processing";
};

export type NotificationItem = {
  id: string;
  title: string;
  body: string;
  time: string;
  unread?: boolean;
};

export const memberProfile = {
  name: "Rahul Das",
  initials: "RD",
  memberId: "MS-2026-0148",
  phone: "+91 98765 43210",
  email: "rahul.das@example.com",
  address: "Barpeta Road, Assam",
  nominee: "Mitali Das",
  occupation: "Small business owner",
  kycStatus: "Pending manual approval",
  savingsBalance: "Rs. 18,450",
  totalDeposit: "Rs. 1,36,800",
  activeAccounts: "4",
  nextCollection: "Today, 6:30 PM"
};

export const schemes: Scheme[] = [
  {
    id: "rd-daily",
    title: "Daily RD Account",
    type: "RD",
    frequency: "Daily",
    amount: "Rs. 100/day",
    term: "12 months",
    rate: "7.2% p.a.",
    status: "Active",
    maturity: "09 Jun 2027"
  },
  {
    id: "rd-monthly",
    title: "Monthly RD Account",
    type: "RD",
    frequency: "Monthly",
    amount: "Rs. 2,000/month",
    term: "24 months",
    rate: "7.8% p.a.",
    status: "Available",
    maturity: "24 months from opening"
  },
  {
    id: "rd-yearly",
    title: "Yearly RD Account",
    type: "RD",
    frequency: "Yearly",
    amount: "Rs. 15,000/year",
    term: "3 years",
    rate: "8.1% p.a.",
    status: "Available",
    maturity: "3 years from opening"
  },
  {
    id: "fd-classic",
    title: "Secure FD Account",
    type: "FD",
    frequency: "Fixed",
    amount: "Rs. 50,000",
    term: "18 months",
    rate: "8.4% p.a.",
    status: "Matures Soon",
    maturity: "18 Jul 2026"
  }
];

export const loans: Loan[] = [
  {
    id: "loan-business",
    purpose: "Small business working capital",
    amount: "Rs. 80,000",
    status: "Under Review",
    officer: "Manager desk",
    nextStep: "Income proof verification"
  },
  {
    id: "loan-education",
    purpose: "Education support loan",
    amount: "Rs. 45,000",
    status: "Draft",
    officer: "Member action needed",
    nextStep: "Upload fee estimate"
  }
];

export const ledger: LedgerItem[] = [
  {
    id: "txn-1",
    title: "Daily RD deposit",
    amount: "Rs. 100",
    date: "Today",
    status: "Due"
  },
  {
    id: "txn-2",
    title: "Savings deposit",
    amount: "Rs. 2,500",
    date: "20 Jun 2026",
    status: "Paid"
  },
  {
    id: "txn-3",
    title: "Loan application fee",
    amount: "Rs. 150",
    date: "18 Jun 2026",
    status: "Processing"
  }
];

export const notifications: NotificationItem[] = [
  {
    id: "n1",
    title: "Daily RD collection due",
    body: "Agent visit scheduled for your Daily RD deposit.",
    time: "Today",
    unread: true
  },
  {
    id: "n2",
    title: "KYC under manual review",
    body: "Your submitted KYC documents are awaiting approval.",
    time: "Yesterday"
  },
  {
    id: "n3",
    title: "FD maturity reminder",
    body: "Your Secure FD matures soon. Choose renewal or withdrawal.",
    time: "2 days ago"
  }
];
