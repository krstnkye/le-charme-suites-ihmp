# Le Charme Suites IHMP — Windows Local Demo Guide

This demonstration uses only a SQLite file stored on the laptop. It does not
need or contact Supabase.

## Demo staff accounts

The preparation screen asks for one password and applies it to these fictional
accounts:

| Department | Email | Login page |
| --- | --- | --- |
| Administrator | `admin@lecharmehotel.com` | `/admin` |
| Receptionist | `receptionist@lecharmehotel.com` | `/login` |
| Billing | `billing@lecharmehotel.com` | `/login` |
| Housekeeping | `housekeeping@lecharmehotel.com` | `/login` |

The password is not written to a source file. Record the chosen demonstration
password separately and give it only to the group members presenting the
system.

## Requirements on the presentation laptop

1. Windows 10 or 11.
2. PHP 8.4 or newer available from Command Prompt (`php -v`).
3. Required PHP extensions: `pdo_sqlite`, `mbstring`, `openssl`, and `fileinfo`.
4. Recommended PHP extension: `intl` (used by some Laravel diagnostic commands).
5. The complete project copy, including the `vendor` folder.

Composer and internet access are not required at presentation time when the
complete `vendor` folder is transferred.

## Prepare a fresh copy

1. Copy the complete repository folder to the laptop.
2. Double-click `prepare-local-demo.bat` in the repository's top folder.
3. Enter and confirm a demonstration password of at least 12 characters.
4. Wait for the message `Local demonstration preparation completed
   successfully.`

The script creates only these local, Git-ignored files:

- `le-charme-suites-ihmp\.env.local-demo`
- `le-charme-suites-ihmp\database\local-demo.sqlite`

It refuses to continue if the selected environment is not a local SQLite
database inside the application's `database` directory.

## Start the portal

1. Double-click `run-local-demo.bat`.
2. Keep the Command Prompt window open.
3. Open <http://127.0.0.1:8000> in Edge or Chrome.
4. Press `Ctrl+C`, then `Y`, in the Command Prompt window to stop the server.

For another device on the same private Wi-Fi, run:

```bat
run-local-demo.bat --lan
```

Use the laptop's IPv4 address shown by `ipconfig`, for example
`http://192.168.1.20:8000`. Allow PHP only on **Private networks** if Windows
Firewall asks. Debug mode remains off.

## Transfer the already prepared demonstration

The safest method for an urgent classroom presentation is to copy the complete
repository folder through a USB drive or a trusted local file transfer. Confirm
that all of these arrived on the other laptop:

- `prepare-local-demo.bat` and `run-local-demo.bat`
- the complete `le-charme-suites-ihmp\vendor` folder
- `le-charme-suites-ihmp\.env.local-demo`
- `le-charme-suites-ihmp\database\local-demo.sqlite`
- `le-charme-suites-ihmp\public\images` and `public\vendor`

Do not transfer `.git` if the receiving laptop only needs the demonstration.
Do not copy a Supabase `.env` file into this package.

## Reset the fictional demonstration

This deletes only the dedicated demo file. Stop the server first, then run the
following from PowerShell in the application folder:

```powershell
Remove-Item -LiteralPath .\database\local-demo.sqlite
```

Run `prepare-local-demo.bat` again and choose a new demonstration password.
Never use this reset command with any other database filename.

## Demonstration data and limitations

The local file contains 45 configured rooms and synthetic examples of a pending
website request, confirmed arrival, active stay, completed checkout, payment,
and housekeeping turnover. Every fictional guest record is marked as a demo.

The following features intentionally remain offline:

- the official Le Charme website and its real booking database;
- Supabase and any shared PostgreSQL database;
- real email delivery (mail is written to the local Laravel log);
- online payment gateways;
- inbound integration API access unless a key is configured later.

Normal portal pages, local public booking, role logins, room status, billing,
receipts, and housekeeping use the local SQLite database.

## Final laptop checklist

- Run `php -v` and confirm PHP 8.4+.
- Start with `run-local-demo.bat` while disconnected from the internet.
- Log in once with every department account.
- Check that the dashboard shows 45 rooms.
- Create or use a fictional reservation, check in, check out, and complete the
  housekeeping inspection flow.
- Restart the laptop and repeat the start/login check before presentation day.

The project is not considered ready on a different laptop until this checklist
has been performed on that actual laptop.
