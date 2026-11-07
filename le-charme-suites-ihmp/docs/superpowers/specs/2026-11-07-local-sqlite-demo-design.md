# Local SQLite Demonstration Design

## Goal

Provide a Windows-ready IHMP demonstration that runs without Supabase or any
shared database. The demo must use a dedicated local SQLite file, fictional
hotel records, and four role-specific staff accounts whose password is entered
locally during setup.

## Safety boundaries

- Never read, copy, or contact Supabase credentials.
- Refuse preparation unless the application environment is local/testing and
  the active database is SQLite.
- Refuse ambiguous, in-memory, or out-of-project database paths for the Windows
  demonstration setup.
- Do not store a demo password in source control, seeders, templates, or logs.
- Keep the generated environment file and SQLite database ignored by Git.

## Data set

- 45 fictional room inventory records based on the portal's configured five
  room types.
- Approved demo accounts for Administrator, Receptionist, Billing, and
  Housekeeping.
- Synthetic reservations representing a pending booking, a confirmed arrival,
  an active stay, and a completed checkout awaiting housekeeping.
- Matching room, check-in, checkout, billing, and housekeeping statuses.

## Windows workflow

`prepare-local-demo.bat` creates `.env.local-demo`, creates the dedicated
`database/local-demo.sqlite` file, runs migrations only against that file, and
invokes an interactive Artisan command for the password and fictional data.
`run-local-demo.bat` revalidates the local-only configuration before starting
Laravel on the laptop.

## Non-goals

- No live official-website, payment, email, or Supabase integration.
- No changes to the paused Supabase project.
- No production deployment or claim that another laptop is ready before it is
  tested on that laptop.
