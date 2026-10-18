# Le Charme Suites Integrated Hotel Management System

Le Charme Suites IHMS is a Laravel application with role-specific workspaces for
administrators, receptionists, billing staff, housekeeping staff, and owners.
It uses a local SQLite database by default, so XAMPP and MySQL are not required.

## Requirements

- PHP 8.3 or newer with `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, and `fileinfo`
- Composer 2
- Git
- Visual Studio Code or another code editor

There is no Node.js build step. The interface loads its frontend libraries
directly from the views.

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
| Administrator | `admin@example.com` |
| Receptionist | `receptionist@lecharme.test` |
| Billing | `cashier@lecharme.test` |
| Housekeeping | `housekeeping@lecharme.test` |
| Owner / Management | `owner@lecharme.test` |

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
