# Local SQLite Demonstration Implementation Plan

1. Add tests proving unsafe database configurations are refused before writes.
2. Add tests for password-driven, idempotent fictional demo data creation.
3. Remove static demo credentials from the general database seeder.
4. Add a guarded Artisan command for local demo preparation.
5. Add Windows preparation/start scripts and script regression checks.
6. Build a disposable local SQLite database and execute the real scripts with
   Windows `cmd.exe`.
7. Run the full PHP suite, Pint, Composer validation, and browser workflow smoke
   tests against only the disposable local database.
8. Document transfer/start commands, demo accounts, offline limitations, and
   the requirement to test the actual presentation laptop.
