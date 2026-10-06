# Le Charme Suites IHMS - Group Setup

This is a Laravel project. Open the `vibe-app` folder in Visual Studio Code.

## Requirements

- PHP 8.2 or newer
- Composer
- SQLite PHP extension enabled
- Visual Studio Code

## First-Time Setup

Run these commands in the VS Code terminal from inside the `vibe-app` folder:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

Then open:

```text
http://127.0.0.1:8000
```

## If The Database File Is Missing

Create the SQLite database file, then migrate:

```bash
type nul > database\database.sqlite
php artisan migrate --seed
```

## Sharing On The Same Wi-Fi Network

If one person wants to host it temporarily for nearby groupmates on the same Wi-Fi, run:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Then the others can open:

```text
http://YOUR-COMPUTER-IP:8000
```

Replace `YOUR-COMPUTER-IP` with the host computer's local IP address.

## Notes

- Do not share the `.env` file publicly.
- The `vendor` folder is not included in the share package. It is recreated by running `composer install`.
- Use GitHub or Google Drive if you want everyone to edit and submit changes.
