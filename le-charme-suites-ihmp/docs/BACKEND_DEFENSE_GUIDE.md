# IHMP Backend Defense Guide

## Simple System Explanation

The IHMP is a Laravel application that uses one shared database for Front Desk, Housekeeping, Billing, and Administration. The backend receives requests, checks whether the user or connected system is allowed to perform the action, validates the information, saves related records together, and returns the result to the page or API caller.

The portal uses SQLite during local development and demonstration. SQLite is stored on the host computer in `database/database.sqlite`. For actual multi-computer hotel deployment, the same Laravel structure can use PostgreSQL or MySQL by changing the database configuration and running the migrations.

## Main Backend Parts

| Part | Purpose |
| --- | --- |
| Routes | Connect a URL and request method to the correct controller. |
| Middleware | Checks login, staff permission, JWT, or the official website integration key. |
| Controllers | Validate requests and coordinate the required operation. |
| Services | Contain important multi-step business rules such as receiving, changing, and cancelling an online booking. |
| Models | Represent database records and their relationships. |
| Migrations | Create and update the database structure in a repeatable way. |
| Transactions | Make related database changes succeed or fail together. |
| Tests | Automatically verify expected results and prevent old functions from breaking. |

## Official Website Booking Flow

1. The official website completes a booking.
2. Its server sends a protected `POST /api/v1/bookings` request to IHMP.
3. Middleware compares the private integration key without exposing it in the browser.
4. The controller validates the official booking number, guest, room type, dates, guest count, status, and payment totals.
5. The booking service checks room capacity and conflicting dates inside a database transaction.
6. IHMP creates or matches the guest, assigns an available encoded room, and creates a pending reservation.
7. It saves a hotel transaction and integration history entry for accountability.
8. The Front Desk notification check finds the pending reservation.
9. Front Desk verifies and confirms it, then records check-in only when the guest arrives.

Changes use `PUT /api/v1/bookings/{external_reference}`. Cancellations use `POST /api/v1/bookings/{external_reference}/cancel`. A repeated official booking number does not create another reservation.

## Database Records Used

- `guests`: guest name and contact details
- `rooms`: encoded room, room type, capacity, rate, and operational status
- `reservations`: official and IHMP booking numbers, dates, room assignment, source, guest counts, booking status, and payment summary
- `hotel_transactions`: business history such as booking received, updated, confirmed, cancelled, checked in, and checked out
- `integration_events`: technical history of API and file-transfer events
- `check_ins` and `check_outs`: physical arrival and departure records
- `housekeeping_tasks`: room work created after checkout or manually assigned

## Security Controls

- Role-based access control limits staff functions by department and permission.
- Website pages use Laravel session authentication and logout protection.
- Staff API routes use short-lived JWT access tokens and refresh-token replacement.
- Official website integration routes use a separate private API key.
- Request throttling limits repeated login and booking API attempts.
- Laravel validation rejects missing, invalid, or unsafe input.
- Parameterized database queries protect against SQL injection.
- Passwords are hashed and are never stored as readable text.
- Database transactions prevent incomplete multi-table operations.
- Integration events and hotel transactions provide saved history.
- The booking API stores payment totals and status only, never card numbers.

## Live Integration Limitation

The IHMP backend is ready to receive the official website events, but the live connection cannot be activated from the student system alone. Le Charme Suites or its booking provider must give written permission and configure its server to call the publicly hosted HTTPS IHMP API. A local `127.0.0.1` address cannot receive calls from the hotel's internet server.

The `/website-booking` page is a demonstration sender. It proves the IHMP receiving process but is not the production Le Charme Suites website.

## Suggested Demonstration

1. Show that the Reservations page has no booking with the chosen official reference.
2. Send a sample official booking request through the protected API or simulator.
3. Open Reservations and show the new pending record, source, official booking number, dates, guest, room, payment status, and total.
4. Repeat the same request and show that no duplicate was created.
5. Update the booking and show the changed dates or total.
6. Confirm it as Front Desk and show that it enters the arrival list.
7. Record check-in and show that the room becomes occupied.
8. Explain that cancellation is accepted before arrival but rejected after check-in.
9. Show Integration Center or transaction history to prove that each event was saved.

## Recommended Presentation Script

“Our Integrated Hotel Management Portal uses one shared database for all departments. The backend is built with Laravel. Requests pass through security checks, controllers validate the information, service classes apply hotel business rules, and models save the result. Related records are protected by database transactions so partial updates are not left behind.

For official website integration, IHMP provides protected create, update, and cancellation endpoints. The official website sends a unique booking reference, guest details, room type, dates, guest count, and payment summary. IHMP prevents duplicate references and overlapping room assignments, saves the reservation as pending, and notifies Front Desk. Front Desk confirmation and physical check-in remain separate controls.

The receiving backend and complete internal workflow are implemented and tested. Production activation requires written permission, public HTTPS hosting, and a webhook configured by the hotel's website developer or booking provider. We do not claim unauthorized access to their private booking database.”

## Questions and Answers

**Why does an online booking go to Reservations instead of Check-ins?**  
A reservation records an intended future stay. Check-in confirms that the guest physically arrived, staff verified the booking, and the room can become occupied.

**How do you stop duplicate bookings?**  
The official booking number is unique. Retrying the same request returns the same IHMP reservation instead of inserting another one.

**How do you prevent double booking?**  
The backend checks for active reservations whose dates overlap the requested dates. The check and insert run inside one database transaction.

**What happens when a booking changes?**  
The website sends the complete updated booking to the update endpoint. IHMP checks capacity and availability again before saving it.

**What happens when it is cancelled?**  
The cancellation endpoint marks an upcoming booking cancelled and saves the reason and history. It refuses website cancellation after the guest has checked in or checked out.

**Do you save credit-card information?**  
No. IHMP stores only the payment status, currency, original amount, discount, and final amount. Payment credentials stay with the authorized payment provider.

**Is the official website connected now?**  
No. The receiving API is ready and tested, but production connection requires the hotel's authorization and a change made by its website developer or provider.

**Why use an API instead of reading the official database directly?**  
An API shares only approved fields, validates every request, creates an audit record, and avoids giving another system direct database access.

**Can this work in real time?**  
Yes. After a successful official booking, the hotel server can send the request immediately. IHMP saves it immediately, and the current Front Desk screen checks for new booking notices approximately every ten seconds.

**What happens without internet?**  
Internal IHMP work can continue on the local network, but new official website events cannot arrive until the internet connection returns. The official system must retry failed deliveries using the same booking reference.

**Why is SQLite used?**  
It is free, simple, and appropriate for local development and defense demonstrations. PostgreSQL or MySQL is recommended for a larger production deployment with many simultaneous users.
