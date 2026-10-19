# Le Charme Suites Integrated Hotel Management Portal

Le Charme Suites IHMP is a Laravel application with role-specific workspaces for
administrators, receptionists, billing staff, housekeeping staff, and owners.
It uses a local SQLite database by default, so XAMPP and MySQL are not required.

## Requirements

- PHP 8.3 or newer with `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, and `fileinfo`
- Composer 2
- Git
- Visual Studio Code or another code editor

There is no Node.js build step. Tailwind, fonts, and interface icons are stored
inside `public/vendor`, so the running system does not need an internet
connection to render its interface.

## First-Time Setup

From the repository's outer folder, run:

```bat
setup-and-run.bat
```

For a manual setup, open a terminal inside `le-charme-suites-ihms` and run:

```bat
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000
```

Open <http://127.0.0.1:8000>.

## Demo Accounts

All seeded accounts use the password `password`.

| Department | Email |
| --- | --- |
| Administrator | `admin@lecharmehotel.com` |
| Receptionist | `receptionist@lecharmehotel.com` |
| Billing | `billing@lecharmehotel.com` |
| Housekeeping | `housekeeping@lecharmehotel.com` |
| Owner / Management | `owner@lecharmehotel.com` |

Each account intentionally sees only the pages authorized for its role. For
example, owner pages are view-only and housekeeping accounts cannot open guest
management or billing pages.

## Getting the Latest Pages

Close any running server, then run `update-and-run.bat` from the outer repository
folder. The script performs the equivalent of:

```bat
git pull --ff-only origin main
composer install
php artisan migrate --seed
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000
```

## Verification

Run the automated tests from the application folder:

```bat
php artisan test
```

The complete group setup and troubleshooting guide is in
[`GROUPMATES_SETUP.md`](GROUPMATES_SETUP.md).

## Account Access History

Sign in as an administrator, open **Audit Logs** under **Control**, then choose
**User Access**. The ledger records account registrations, successful logins,
failed login attempts, and logouts with date, time, email, IP address, and
browser information. Login history begins after the access-log migration is
installed; older accounts still appear as account-provisioning records.

## Integration, Messaging, and Offline Operation

- **Department Messages** is available from every department sidebar. Messages,
  timestamps, and read acknowledgements are stored in the shared database.
- Administrators can open **Integration Center** to check database/runtime
  health, export approved datasets, inspect checksums, and download the transfer
  history report.
- **System Flipbook** opens the locally embedded operating manual. It remains
  available without internet after setup.
- The system remains autonomous: hotel operations use the local shared database
  and do not depend on a third-party cloud service or API.
- Optional read-only integration endpoints are under `/api/v1`. Set a private
  `IHMP_API_KEY` in `.env` to enable them. When the value is blank, external API
  access is safely disabled.

The implementation and presentation traceability report is in
[`docs/DEVELOPMENT_PHASE_INTEGRATION_AND_RELIABILITY.md`](docs/DEVELOPMENT_PHASE_INTEGRATION_AND_RELIABILITY.md).
