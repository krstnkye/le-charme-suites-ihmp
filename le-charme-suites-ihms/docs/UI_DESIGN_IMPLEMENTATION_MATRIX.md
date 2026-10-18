# Le Charmé Suites IHMS UI Design Implementation Matrix

This checklist maps the supplied interface references to the running Laravel application. A page is marked **Integrated** only when its design is rendered by its real route and uses live application data where available.

## Shared Experience

| Area | Implementation | Status |
| --- | --- | --- |
| Hotel branding, logo, and favicon | Shared brand partials and favicon include | Integrated |
| Navy, gold, cream visual system | Department layouts and operational pages | Integrated |
| Live Philippine date and time | Department headers and shared application layout | Integrated |
| Responsive hide/show sidebar | Universal sidebar controls on department sidebars | Integrated |
| Sidebar scroll position | Universal sidebar persistence script | Integrated |
| Back navigation | Shared page back control with safe dashboard fallback | Integrated |
| Offline visual assets | Local Tailwind, fonts, and Material Symbols bundle | Integrated |

## Authentication

| Page | Route | Status |
| --- | --- | --- |
| Staff login | `/login` | Integrated |
| Staff registration | `/register` | Integrated |
| Work email validation | Login and registration actions | Integrated |

## Front Desk / Receptionist

| Page | Route | Status |
| --- | --- | --- |
| Operations dashboard | `/dashboard` | Integrated |
| Guest directory | `/guests` | Integrated |
| Register guest | `/guests/create` | Integrated |
| Guest profile and stay history | `/guests/{guest}` | Integrated |
| Edit guest profile | `/guests/{guest}/edit` | Integrated |
| Reservation management | `/reservations` | Integrated |
| Create reservation | `/reservations/create` | Integrated |
| Reservation detail | `/reservations/{reservation}` | Integrated |
| Edit reservation | `/reservations/{reservation}/edit` | Integrated |
| Check-ins | `/check-ins` | Integrated |
| Check-outs | `/check-outs` | Integrated |
| Room status | `/rooms` | Integrated |
| Room detail | `/rooms/{room}` | Integrated |
| Update room | `/rooms/{room}/edit` | Integrated |
| Transactions | Front Desk transaction route | Integrated |
| Messages | `/messages` | Integrated |
| Profile | `/profile` | Integrated |

## Housekeeping

| Page group | Pages | Status |
| --- | --- | --- |
| Dashboard and rooms | Dashboard, Room Status, My Assigned Rooms, Room Inspection, Room Detail | Integrated |
| Tasks | Housekeeping Tasks, Cleaning Tasks, Priority Rooms, Completed Tasks | Integrated |
| Service | Service Requests, Guest Requests, Special Cleaning | Integrated |
| Maintenance | Maintenance Requests, Report Issue, Reported Issues | Integrated |
| Records and supplies | Lost & Found, Linen & Supplies, Linen Records, Housekeeping Supplies | Integrated |
| Oversight | Reports, Notifications, My Profile | Integrated |

## Billing

| Page | Route or section | Status |
| --- | --- | --- |
| Billing dashboard | Billing-role `/dashboard` | Integrated |
| Billing records | Billing workspace | Integrated |
| Transactions | `/check-outs` | Integrated |
| Transaction history | `/check-outs/history` | Integrated |
| Reports | `/reports` | Integrated |
| Profile | `/profile` | Integrated |

## Owner / Management

| Page group | Pages | Status |
| --- | --- | --- |
| Executive overview | Dashboard, Guests, Rooms | Integrated |
| Room oversight | Room Inventory, Room Availability, Room Status | Integrated |
| Guest journey | Reservations, Check-in / Check-out, Check-in Records, Check-out Records | Integrated |
| Financial oversight | Billing & Transactions, Billing Records, Transactions | Integrated |
| Housekeeping oversight | Housekeeping, Room Status, Housekeeping Requests | Integrated |
| Reports and records | Reports & Records, Operational Reports, Transaction Reports, Records | Integrated |
| Profile | Owner My Profile | Integrated |

## Verification

`tests/Feature/UiDesignPlacementTest.php` checks the distinctive design heading and correct department sidebar on the supplied housekeeping, billing, owner, and Front Desk operational pages. The remainder of the feature suite verifies authorization and functional workflows.
