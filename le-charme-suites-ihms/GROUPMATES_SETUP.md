# Le Charme Suites IHMS - Group Setup and Troubleshooting

## Important Folder Layout

After cloning GitHub, the repository contains this structure:

```text
le-charme-suites-ihms repository
|-- setup-and-run.bat
|-- update-and-run.bat
`-- le-charme-suites-ihms
    |-- artisan
    |-- app
    |-- database
    |-- resources
    `-- routes
```

The inner `le-charme-suites-ihms` folder is the Laravel application. Open the
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

Recommended VS Code extensions:

- PHP Intelephense
- Laravel Blade Snippets
- SQLite Viewer
- GitHub Pull Requests and Issues

Extensions improve editing but are not required to run the application.

## First-Time Setup

1. Clone `https://github.com/krstnkye/le-charme-suites-ihms.git`.
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

Run:

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
