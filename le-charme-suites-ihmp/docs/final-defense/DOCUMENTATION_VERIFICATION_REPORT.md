# Documentation Verification Report

**Document:** `FINAL_IHMP_DOCUMENTATION.docx` / `.pdf`
**Prepared:** 7 November 2026

This report lists the evidence the document relies on, what could not be verified, and what the team must confirm before submission.

## 1. Evidence used

| Evidence | What it supports |
|---|---|
| Repository `krstnkye/le-charme-suites-ihmp`, defended build `1b24789` and demo build `82a42fe` (read-only clone) | Code facts: versions, 28 controllers, 14 models, 5 services, 4 custom middleware, 25 migrations, 14 application tables, routes, business rules, code excerpts |
| `composer.json`, `composer.lock`, `package.json` | Laravel 13.34.0, PHP ^8.4, Tailwind 3.4.17, PHPUnit 12 |
| `config/rbac.php`, `role_permissions` seeder | Permission matrix and default role permissions (Table 24) |
| Test run on 7 Nov 2026, PHP 8.4.27 (`evidence/tests/`) | 203 tests on `1b24789`, 215 on `82a42fe`, Pint pass (Tables 14, 16, 25, 26) |
| Browser session on a throw-away SQLite copy (`evidence/screenshots/`) | Figures 10–44 and test cases TC-18 to TC-20 |
| Diagrams drawn from the code (`evidence/diagrams/`) | Figures 1–9 and 33 |
| QA reports and remediation tracker in `/mnt/project-files/qa-reports/` | Defect list (Table 15), CI result on the Batch 1 head, earlier 155-test baseline, browser crawl |
| Git history and branch list | Branch table (Table 18), commit counts and dates |
| Earlier team documents (Project Documentation Updated, Defense Edition, Proposal, Interview Guide) | Project background and scope wording only, after correction against the code |

**Not accessed, on purpose:** Supabase (paused), any `.env` file with real credentials, the production or shared databases. No application code, database, migration or seeder in the repository was changed, and no branch was merged.

## 2. Corrections to earlier documents

| Earlier claim | Corrected to | Basis |
|---|---|---|
| 95 tests (Updated version) and 138 tests (Defense Edition) | 203 tests on the defended build, 215 on the demo build | Re-run on 7 Nov 2026 |
| Repository URL ending in `-ihms` | `https://github.com/krstnkye/le-charme-suites-ihmp` | Git remote |
| Fixed demo logins and passwords printed in the document | Removed. The demo script creates a random password that is shown only on the demo laptop. | `demo:prepare` behaviour; credential rule |
| A front-desk booking sets the room to reserved | It does not. Room status, stays and reservation dates can disagree (M-14). | Code and QA review |
| Checkout tax, deposit and extra charges | Removed from the screen in Batch 1. The bill is rate × nights plus recorded extras. | `CheckOutController::folio()` |
| MySQL as the database | SQLite by default. PostgreSQL/Supabase is optional and paused. Early mention of MySQL is noted as corrected. | `config/database.php`, `.env.example` |

## 3. Information that could not be verified or was not found

- **Interviews, surveys and UAT.** The Interview Guide exists, but no completed interviews, survey results or user acceptance tests were found. The document says so and does not report any findings.
- **Hotel policies and room list.** No confirmed room numbers, room counts per type, extra-guest, tax or deposit policy. The room list is described as a sample layout.
- **Client permission.** No written agreement or API access from Le Charmé Suites or its booking provider.
- **CI result.** GitHub Actions results could not be read from this session. The green result on the Batch 1 head is taken from the team's remediation tracker.
- **Windows launcher.** The scripts were read, and the tests ran on Linux. The Windows `.bat` launcher was not run on a Windows laptop.
- **Photos, CVs, slides.** None were found.

## 4. Items the team must confirm or supply

| # | Item | Where it appears |
|---|---|---|
| 1 | Members' middle names (template format is Last, First, Middle) | Title page |
| 2 | Course section | Project Information Sheet |
| 3 | Semester (the academic year is given as 2026–2027) | Project Information Sheet |
| 4 | Whether the instructor accepts APA 7th edition | References |
| 5 | One-page CV for each member | Appendix J |
| 6 | Dated development photos, if required | Appendix I |
| 7 | Signed institutional AI declaration form, and that the list of AI tools is complete | Appendix K |
| 8 | Presentation slides (marked "No" on the submission checklist) | Submission Checklist |
| 9 | The hotel's actual room layout and billing policies, or keep the "sample" wording | Sections 1.5, 4.2, 5.2 |
| 10 | Run the presentation-laptop checklist on the actual Windows laptop, with `APP_DEBUG=false` | Section 4.7.3, Appendix B |
| 11 | Which branch is the release branch (the team must not run `update-and-run.bat`, which pulls `main`) | Sections 4.7.2, 4.7.3 |
| 12 | Fill in the "Instructor Check" column only if the instructor asks the team to | Submission Checklist |

## 5. Final checks done on the file

- No leftover template placeholders (`[Enter]`, `[WRITE HERE]`, `[page/link]`) or unresolved figure or table references.
- No passwords, API keys, app keys or real tokens. The token values shown in the API examples are shortened and labelled illustrative. The demo password does not appear in the document or the evidence files.
- Page numbers in the table of contents match the PDF. The document was built twice, and the page numbers matched both times.
- Every figure and table is numbered and referred to in the text.
- The words "deployed", "integrated" and "live" are used only with their stated limits. The document does not claim production deployment, website integration, a payment gateway, Supabase use or completed security remediation.
