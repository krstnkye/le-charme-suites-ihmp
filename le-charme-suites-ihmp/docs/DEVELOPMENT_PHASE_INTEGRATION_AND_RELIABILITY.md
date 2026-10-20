# Development Phase: Integration, Reliability, and Data Exchange

## Purpose

This document maps the professor's discussion points to verifiable parts of the
Le Charme Suites Integrated Hotel Management Portal (IHMP). It describes the
implemented development-phase behavior; it does not claim production approval
from Le Charme Suites, SBMA, or a payment provider.

## Requirements Traceability

| Discussion point | Implemented response | Where to demonstrate |
| --- | --- | --- |
| UI settings | Each authenticated user can save display density, text size, and desktop sidebar preference. Preferences are stored in the `users.ui_preferences` database field. | Use the tune button displayed near the upper-right side of every department workspace. |
| Embedded flipbook | A seven-page local system flipbook explains architecture, security, intranet use, shared data, messaging, API transfer, and reliability procedure. | Open **System Flipbook** from UI Settings or view it inside **Admin > Integration Center**. |
| Show integration location | The Integration Center lists database health, shared record counts, memory use, network mode, API state, exports, and saved transfer events. | Sign in as Administrator and open **Integration Center**. |
| Login and logout security | Work-domain email validation, hashed passwords, regenerated sessions, CSRF protection, role middleware, logout invalidation, and access history are implemented. | Use Login/Logout, then open **Admin > Audit Logs > User Access**. |
| Internet / intranet | Daily hotel operations are intranet-first. Internet is optional. One host serves the application to department devices through a private LAN. | Run `run-shared-lan-server.bat`; open the host IPv4 address from another device. |
| Internet capability problems | Frontend assets are local. The application does not require a CDN, Node server, or cloud database during hotel operation. | Disconnect internet while keeping the LAN and host active; pages continue to load. |
| Lesser memory and components | The project uses PHP, Laravel, SQLite, and locally stored static assets. API output is capped to prevent large transfers over slow links. | View **Integration Center > Lightweight Runtime**. |
| Network reliability / slow connection | Central hosting avoids database copies on each department PC. Local files reduce bandwidth, and API limits default to 200 records per request. | View `IHMP_MAX_EXPORT_RECORDS` in `.env.example` and the Integration Center. |
| Autonomous operation | The system remains usable on the private LAN without public internet. It is not autonomous if the host computer or LAN is turned off; that operational dependency must be stated honestly. | Demonstrate the shared LAN procedure and `/up` health endpoint. |
| File transfer between platforms | Admins can export Guests, Rooms, Reservations, and Transactions as CSV or JSON. Each export is recorded with record count and SHA-256 checksum. | **Admin > Integration Center > Platform File Transfer**. |
| File transfer report | A CSV transfer report contains time, actor, direction, channel, dataset, format, record count, status, checksum, and endpoint. | Select **Download Transfer Report** in the Integration Center. |
| Databases | SQLite is the academic/local deployment database. Migrations reproduce its structure. All departments use the one database on the host. | **Integration Center > Shared Database Health** and `database/migrations`. |
| Procedures | Setup, update, shared-LAN startup, backup, API configuration, transfer verification, and recovery procedures are documented. | `README.md`, `GROUPMATES_SETUP.md`, and this document. |
| Messaging | Staff can send normal, important, or urgent messages to one department or all departments. | Open **Department Messages** in a department sidebar. |
| Shared departmental data | Reservations affect Front Desk and room availability; room status affects Housekeeping and Owner monitoring; transactions affect Billing and reports. | Create a record in the authorized department, then inspect it from another authorized dashboard. |
| History saved | Login/logout, failed access, room status, transactions, message reads, API calls, and file exports have persistent database history. | **Audit Logs**, **Message History**, and **Integration Center**. |
| API | A versioned API provides read-only status and approved datasets plus a protected official-website booking intake route. It is disabled until an API key is configured. | `/api/v1/status`, `/api/v1/datasets/{dataset}`, and `POST /api/v1/bookings` with a Bearer key. |
| Development phase | The project is an academic development-phase prototype. Hotel validation, penetration testing, production infrastructure, privacy approval, and payment-provider certification remain future work. | State this limitation during the defense and in the final manuscript. |
| Bookbind / hardbind | Hardbinding is a submission procedure, not a software feature. Freeze the approved manuscript, export to PDF, verify pagination, print the required copies, and hardbind only after adviser approval. | Final documentation checklist. |

## Shared Database Procedure

1. Choose one laptop or desktop as the IHMP host.
2. Run `setup-and-run.bat` once to install dependencies, create SQLite, and run migrations.
3. For shared operation, run `run-shared-lan-server.bat`.
4. Connect department devices to the same private router or Wi-Fi network.
5. Open `http://HOST-IP:8000` from department devices.
6. Use separate role accounts. Do not copy `database.sqlite` to every laptop for daily operations.
7. Back up `database/database.sqlite` only while writes are stopped or through an approved backup procedure.

If each group member runs a separate clone, each clone has a separate database.
Those records do not synchronize automatically. Shared operation requires all
users to connect to the same running host.

## API Procedure

1. Generate a long random API key for the approved receiving platform.
2. Set `IHMP_API_KEY` in the host `.env` file. Never commit the key to GitHub.
3. Run `php artisan optimize:clear` after changing `.env`.
4. Send `Authorization: Bearer YOUR_KEY` or `X-IHMP-Key: YOUR_KEY`.
5. Read status from `/api/v1/status`.
6. Read an approved dataset from `/api/v1/datasets/rooms`, `guests`, `reservations`, or `transactions`.
7. An authorized booking provider may send a new reservation to `POST /api/v1/bookings`.
8. Verify the transfer in **Admin > Integration Center**.
9. Rotate the key if it is exposed or if the receiving platform changes.

Status and dataset routes are read-only. The booking route is the only approved
write operation and applies validation, duplicate prevention, availability
checks, and integration history. Live use requires hotel approval and a
server-side webhook from the official booking website. See
`OFFICIAL_WEBSITE_BOOKING_INTEGRATION.md`.

## Reliability and Honest Limitations

- Public internet failure does not stop local LAN operation.
- Host failure, power failure, router failure, or database corruption can stop
  service; offline operation does not remove these dependencies.
- SQLite is suitable for the current academic and small local deployment. A
  production hotel rollout should evaluate PostgreSQL or MySQL, automated
  backups, TLS, redundant networking, centralized monitoring, and a UPS.
- Browser `navigator.onLine` only reports device connectivity. The `/up`
  endpoint and Integration Center are the authoritative application checks.
- No real payment gateway, government service, SMS provider, or official hotel
  production system is connected unless a written agreement and credentials
  are provided.

## Defense Demonstration Order

1. Log in using each role and show authorization boundaries.
2. Send a Front Desk message to Housekeeping and acknowledge it from the
   Housekeeping account.
3. Change a room status and show the updated shared view in another department.
4. Open the Integration Center and explain database, memory, network, and API
   status.
5. Export Rooms as CSV, then show its transfer-history row and checksum.
6. Open the embedded flipbook and summarize the architecture.
7. Disconnect public internet while keeping the LAN active and reload a local
   page.
8. Show Audit Logs and explain saved login/logout history.

## Hardbound Documentation Checklist

1. Obtain adviser approval for the final manuscript content.
2. Insert screenshots of the exact routes listed in the traceability table.
3. Include UML, ERD, data dictionary, testing results, and this development
   phase chapter.
4. Apply the school's required paper size, margins, font, spacing, front matter,
   and signature pages.
5. Export to PDF and inspect every page before printing.
6. Print and hardbind only the approved final version.
