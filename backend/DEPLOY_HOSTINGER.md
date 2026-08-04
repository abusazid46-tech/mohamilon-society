# Hostinger deployment

1. Create a MySQL database/user in hPanel and import no demo data.
2. Point the domain or subdomain document root to `backend/public` (never the repository root).
3. Upload the backend, run `composer install --no-dev --optimize-autoloader`, and copy `.env.example` to `.env`.
4. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, database credentials, a unique admin email/password, mail settings, and run `php artisan key:generate`.
5. Run `php artisan migrate --force`, `php artisan db:seed --force`, `php artisan storage:link`, `php artisan optimize`, and configure a cron job for `php artisan schedule:run` every minute.
6. Force HTTPS, PHP 8.3+, secure cookies, daily encrypted database/file backups, and rotate the seeded password immediately.
7. Set the mobile app's `EXPO_PUBLIC_API_URL=https://api.your-domain.example/api/v1` and rebuild the APK/AAB.

KYC files belong on the private Laravel disk and must never be publicly addressable. Grant document access only through an authorized controller with an audit event.
