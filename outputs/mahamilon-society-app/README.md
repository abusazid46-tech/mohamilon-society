# Mahamilon Society Expo App

React Native + Expo Android app starter for a microfinance society workflow.

## Included

- Member-only registration and login experience for account opening, RD, FD, loan application, KYC status, profile and notifications.
- New member registration form with personal details, KYC IDs, nominee, opening scheme, opening deposit and MPIN.
- Mandatory bottom navigation for Home, Accounts, Loan, KYC and Profile.
- Production-style member dashboard with savings balance, deposits, ledger and alerts.
- KYC submission status for manual verification by the society office.
- Push notification demo using `expo-notifications`.
- Mahamilon Society logo applied as app icon, splash image and in-app brand asset.
- EAS build profile configured for Android APK.

## Separate staff panel

Super admin, president, manager, secretary and agent access should be built as a separate secure web/admin panel, not inside the member APK. That panel should handle KYC approval, collection verification, staff permissions, scheme setup, loan approvals, audit logs and reports.

## Run locally

```powershell
npm.cmd install
npm.cmd run start
```

Then press `a` in the Expo terminal to open on an Android emulator, or scan the QR code with Expo Go.

## Build APK

```powershell
npm.cmd install
npx eas login
npx eas init
npm.cmd run apk
```

The `preview` build profile in `eas.json` creates an installable Android APK. For Play Store upload, use:

```powershell
npx eas build --platform android --profile production
```

## Backend needed for production

The app currently uses local demo data so the screens and workflow can be reviewed quickly. For a live microfinance app, connect these modules to a secure backend:

- OTP/MPIN member login.
- Member account ledger for RD, FD, savings and loans.
- KYC document storage with manual approval in the separate staff panel.
- Collection receipts and agent GPS/device audit in the separate staff panel.
- Loan application scoring, approval and repayment ledger.
- Push notification service from the backend.
- Regulatory reports, audit logs, backup and data export.

Sensitive financial data should not be stored only on the phone.
