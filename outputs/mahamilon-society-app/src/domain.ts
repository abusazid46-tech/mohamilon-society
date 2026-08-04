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
