# Le Charme Suites IHMP - Group Setup and Troubleshooting

## Important Folder Layout

After cloning GitHub, the repository contains this structure:

```text
le-charme-suites-ihmp repository
|-- setup-and-run.bat
|-- update-and-run.bat
`-- le-charme-suites-ihmp
    |-- artisan
    |-- app
    |-- database
    |-- resources
    `-- routes
```

The inner `le-charme-suites-ihmp` folder is the Laravel application. Open the
outer repository folder in VS Code, but run `php artisan ...` commands from the
inner folder.

## Required Software

1. Git for Windows
2. PHP 8.3 or newer
3. Composer 2
4. Visual Studio Code

XAMPP is optional and is not required by this project. The default database is
SQLite. If XAMPP is installed, its PHP version must still be 8.3 or newer and
the SQLite extensions must be enabled.

## Working Without Internet

The application database, Tailwind interface, fonts, icons, and hotel logo are
stored locally. After the first setup is complete, an internet connection is
not required for day-to-day use on the host computer.

To let several laptops use one shared database without internet:

1. Connect all laptops to the same private Wi-Fi router or local network. The
   router does not need an active internet connection.
2. On one laptop, open a terminal in the inner application folder and run
   `php artisan serve --host=0.0.0.0 --port=8000`.
3. On the host laptop, run `ipconfig` and find its **IPv4 Address**, such as
   `192.168.1.25`.
4. Allow PHP through Windows Defender Firewall on **Private networks** if
   Windows asks.
5. On every other laptop, open `http://HOST-IP:8000`, replacing `HOST-IP` with
   the address from step 3, for example `http://192.168.1.25:8000`.
6. Keep the host laptop, Laravel terminal, and local Wi-Fi router running while
   the group is using the system.

All users then read and write the host laptop's single SQLite database. Running
separate cloned copies creates separate databases and does not synchronize
records automatically.

This is the recommended intranet setup for a demonstration or small deployment.
It uses one host as the application and database server, so department laptops
share the same guests, rooms, reservations, transactions, housekeeping records,
messages, and history. Internet is optional; the private LAN connection to the
host is required while other laptops are using it.

## New Reliability and Integration Features

After pulling the latest version, always run `php artisan migrate --seed` so the
message, preference, and integration-history tables are created.

1. Open **Department Messages** from any department sidebar to send a persistent
   internal message. Opening a message saves a per-user read acknowledgement.
2. Sign in as Administrator and open **Integration Center** to inspect shared
   database health, PHP memory usage, operating mode, and transfer history.
3. Use the export buttons for approved CSV or JSON transfer files. Each export
   is recorded with date, actor, record count, format, status, and SHA-256
   checksum so the receiving copy can be verified.
4. Open **System Flipbook** from the top-right interface settings menu or the
   Integration Center. The manual is embedded locally and works offline.
5. Use the interface settings button to save comfortable/compact density,
   standard/large text, and the default sidebar state for the signed-in account.

### Optional API Exchange

The application does not require an API for normal hotel operation. To enable
the read-only integration API, add a long private key to the host computer's
`.env` file:

```env
IHMP_API_KEY=replace-this-with-a-long-random-private-key
IHMP_MAX_EXPORT_RECORDS=200
```

Restart the server after changing `.env`. Approved clients can call
`/api/v1/status` or `/api/v1/datasets/rooms` with either an
`Authorization: Bearer YOUR_KEY` header or an `X-IHMP-Key` header. Never commit
the real key to GitHub. Leave `IHMP_API_KEY` blank when no external connection is
needed; the endpoints then return a disabled response.

## Viewing Signup and Login History

1. Sign in with `admin@lecharmehotel.com`.
2. In the administrator sidebar, find **Control**.
3. Open **Audit Logs**.
4. Set the module filter to **User Access**.
5. Use the level filter for **Verified** or **Blocked**, or search by employee
   name, work email, IP address, or event type.

The page stores `ACCOUNT_REGISTERED`, `LOGIN_SUCCESS`, `LOGIN_FAILED`, and
`LOGOUT` events. Each record includes its timestamp, account, IP address, and
browser terminal. The history is stored in SQLite and remains available
offline.

Recommended VS Code extensions:

- PHP Intelephense
- Laravel Blade Snippets
- SQLite Viewer
- GitHub Pull Requests and Issues

Extensions improve editing but are not required to run the application.

## First-Time Setup

1. Clone `https://github.com/krstnkye/le-charme-suites-ihmp.git`.
2. Open the cloned outer folder in VS Code.
3. Run `setup-and-run.bat` from the outer folder.
4. Wait for Composer and database setup to finish.
5. Open <http://127.0.0.1:8000>.

The initial Composer install may take several minutes on Windows. Do not close
the terminal while it says `Generating optimized autoload files`.

## Updating an Existing Copy

Before starting new work:

1. Close the running Laravel terminal.
2. Make sure personal code changes are committed or copied somewhere safe.
3. Run `update-and-run.bat` from the outer repository folder.

This pulls `main`, installs changed dependencies, runs migrations and seeders,
clears stale route/view/config caches, and restarts the server.

## If Pages Appear Missing

Open a terminal in the inner application folder and run:

```bat
git status
git pull --ff-only origin main
composer install
php artisan migrate --seed
php artisan optimize:clear
php artisan route:list
php artisan serve --host=127.0.0.1 --port=8000
```

Then sign in with the correct department account. Pages are intentionally
filtered by role, so a housekeeping user will not see administrator or billing
pages.

## Common Problems

### `php` is not recognized

Install PHP 8.3 or newer and add the PHP directory to the Windows `PATH`, then
restart VS Code.

### `composer` is not recognized

Install Composer for Windows and restart VS Code.

### `vendor/autoload.php` is missing

Run `composer install` inside the inner application folder and allow it to
finish completely.

### Database or table errors

If the error says `database/database.sqlite does not exist`, close the Laravel
server and run `setup-and-run.bat` from the outer repository folder. The setup
script creates the local SQLite file before applying the database migrations.

To repair it manually, open PowerShell in the inner `le-charme-suites-ihmp`
application folder and run:

```powershell
if (!(Test-Path database\database.sqlite)) {
    New-Item -ItemType File -Path database\database.sqlite
}
php artisan migrate --seed
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000
```

For other database or table errors, run:

```bat
php artisan migrate --seed
```

For a disposable local database that contains no important work, use:

```bat
php artisan migrate:fresh --seed
```

The second command deletes local records, so do not use it when local data must
be preserved.

### Old page is still displayed

Run:

```bat
php artisan optimize:clear
```

Then refresh the browser with `Ctrl+F5`.

### Port 8000 is already in use

Run the server on another port:

```bat
php artisan serve --host=127.0.0.1 --port=8001
```

Then open <http://127.0.0.1:8001>.

## Before Pushing Group Changes

Run:

```bat
php artisan test
git status
git add YOUR_CHANGED_FILES
git commit -m "Describe the completed update"
git push origin main
```

Never commit `.env`, `vendor`, or `database/database.sqlite`.
