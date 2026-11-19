# Project Update Summary: Le Charme Suites IHMP

**State as of:** 12 November 2026 (final defense: 14 November 2026)
**Repository:** https://github.com/krstnkye/le-charme-suites-ihmp
**Defended build:** `feature/compliance-checklist` at `1b24789` (QA Batch 1 merged through PR #3)

## 1. Branches

| Branch | Commit | Relation to the defended build | Status |
|---|---|---|---|
| `feature/compliance-checklist` | `1b24789` | The defended build | Merged work. CI green on the Batch 1 head (per the remediation tracker; not re-checked here). |
| `demo/offline-sqlite-professor-checking` | `82a42fe` | `1b24789` plus one commit of offline demo scripts. No application views changed. | Not merged. Used for the demonstration and for the screenshots. 215 tests pass. |
| `fix/final-defense-ui` | `b66e7e6` | `82a42fe` plus one styling commit (12 Nov 2026): desktop sidebar spacing and a phone-friendly card layout for the front-desk reservation list. Two view files; no logic or database change. | Not merged. Not re-tested or re-screenshotted for the document. |
| `fix/qa-batch-2d-script-safety` | `137225d` | `1b24789` plus safer setup and update scripts (one commit) | Not merged. Not part of the defended build. |
| `main` | `24eaf70` | 11 commits behind. Lacks Batch 1, the policy pages and the Supabase option. | GitHub default branch, out of date. |
| `redesign/modern-portal-ui` | `6f367d5` | Based on `main`, new interface styling | Not merged. Not shown in the document. |
| `fix/portal-audit` and five other feature branches | various | Older work never merged (for example guest deposits, ID verification, front-desk task assignment) | Not part of the defended build. |

**Important for the demo:** do not use `update-and-run.bat` on the defended build. It pulls `main`, which does not have the Batch 1 fixes.

## 2. Features

### Implemented and tested (in `1b24789`)
- Staff registration with administrator approval; separate staff (`/login`) and administrator (`/admin`) sign-in pages; password reset pages (messages go to the log).
- Four roles (admin, receptionist, cashier shown as "Billing", housekeeping) with role middleware plus a database permission matrix (5 capabilities × 5 actions).
- Guest records, room inventory and room status grid; reservations with booking reference `LCS-YYMMDD-XXXXXX` and a printable registration card.
- Public booking page and the official-website booking simulator, both creating pending reservations that the front desk confirms.
- Check-in, server-calculated bill (`folio()`: rate × nights, minimum 1, plus recorded positive extras), checkout, bill preview and receipt. Payment status must match the amount paid.
- Checkout sets the room to cleaning and creates a high-priority turnover task due in 2 hours.
- Housekeeping task state machine (turnover and deep cleaning need inspection) and room-release rules.
- Admin-only editing of room number, type, rate and capacity.
- Integration center with CSV/JSON exports, SHA-256 checksums and a transfer history.
- JWT staff API (15-minute access token, 7-day refresh token) and an integration-key API for bookings and datasets. The key API returns 503 until `IHMP_API_KEY` is set, which is the default in the demo.
- Department messages, profile and interface preferences, legal pages and cookie notice, Windows setup and launcher scripts.

### Partial
- Billing: no extra-guest charges, taxes or deposits (M-03); receipts recalculate from the current rate instead of a saved snapshot (M-04); no posting of charges or payments after check-in, refunds or voids (M-05).
- Front-desk booking does not set the room to reserved, and room status, active stays and reservation dates can disagree (M-14).
- Activity records do not store which staff member acted (M-11, M-12).
- Two authorisation mechanisms (role gates and the permission matrix) can disagree.

### Simulated (on screen, not backed by real data or actions)
- Official website booking simulator: labelled as a simulation; not connected to the hotel website.
- Admin Settings Save, Backup and Poll buttons show success but do nothing (M-08).
- Housekeeping pages for Lost and Found, guest requests and notifications show sample content, with about 50 controls that do nothing (M-09).
- Audit-log page shows generated IP values (M-11).
- "Report Issue" overwrites the room description instead of creating a maintenance record (M-10).

### Not operational
- Live link to the official Le Charmé website or its booking provider.
- Payment gateway or card processing; official tax receipts.
- E-mail and SMS delivery.
- Supabase/PostgreSQL: supported by configuration only; the project is paused and was not accessed.
- Public hosting, HTTPS, backups, monitoring.

### Unmerged work
- Safer setup and update scripts (`fix/qa-batch-2d-script-safety`).
- Offline demo tooling (`demo/offline-sqlite-professor-checking`).
- Final UI spacing and mobile layout fix (`fix/final-defense-ui`).
- UI redesign (`redesign/modern-portal-ui`).
- Older feature branches including `fix/portal-audit`.

## 3. Testing

| Run | Result |
|---|---|
| `1b24789`, PHP 8.4.27, 7 Nov 2026 | 203 passed, 0 failed, 1,837 assertions, about 5.0 s |
| `82a42fe`, same run | 215 passed, 0 failed, 1,916 assertions, about 5.3 s |
| Laravel Pint on `1b24789` | Pass |
| Browser checks TC-18 to TC-20 (Playwright, sandbox copy) | Pass: checkout to receipt REC-2026-00002, refused housekeeping jump then correct release, 403 for housekeeping on `/guests` |
| Earlier baseline `63a810e`, 6 Nov 2026 | 155 passed |

Not done: user acceptance testing with hotel staff, usability survey, load or stress tests, penetration test.

## 4. QA status

An independent review of `63a810e` found 42 consolidated issues (2 Critical, 13 High, 16 Medium, 11 Low).
- **Fixed in Batch 1:** M-01, M-02, M-06, M-07, M-15 (in code; the team must still set `APP_DEBUG=false` on their PC), M-25.
- **Open:** M-03 to M-05, M-08 to M-14, M-16 to M-24, M-28 to M-42, and NF-1 (8 security advisories in 5 Symfony packages).

## 5. Limitations to state at the defense
- All data is fictional. The room list is a sample layout until the hotel confirms room numbers and counts.
- The system runs on a laptop or a private Wi-Fi network only.
- Several screens show sample content, and these are disclosed in the document (Section 4.6.2, Table 15).
- Security remediation is not complete.
