# Official Website Booking Integration

## Purpose

This document defines how a completed booking from the official Le Charme Suites website can be sent to the Integrated Hotel Management Portal (IHMP).

The public hotel website and the IHMP are separate systems. A booking will appear in the IHMP only when the official website or its booking provider sends the booking data through an authorized server-to-server connection.

## Correct Business Flow

1. A guest searches for a room on the official Le Charme Suites website.
2. The guest selects a room, enters contact details, and completes the website's booking process.
3. After the official booking system accepts the booking, its server sends the approved details to the IHMP booking API.
4. The IHMP validates the API key, room type, dates, guest capacity, and duplicate reference.
5. The IHMP creates or updates the guest record and saves the booking as a pending reservation.
6. The reservation appears in the Front Desk Reservations page and the dashboard displays a new-booking notice.
7. Front Desk staff verifies and confirms the reservation.
8. On the guest's arrival date, Front Desk staff records the physical check-in. Only then does the booking appear as an active check-in and the room become occupied.

A website booking must not automatically be treated as a physical check-in. Reservation and check-in are separate hotel events.

## IHMP Receiving Endpoint

```http
POST https://YOUR-PUBLIC-IHMP-DOMAIN/api/v1/bookings
Authorization: Bearer YOUR_SHARED_INTEGRATION_KEY
Content-Type: application/json
Accept: application/json
```

The `X-IHMP-Key` header may be used instead of the Bearer token.

Example request:

```json
{
  "external_reference": "LECHARME-2026-000123",
  "first_name": "Maria",
  "last_name": "Santos",
  "email": "maria@example.com",
  "phone": "+639171234567",
  "room_type": "Deluxe King",
  "check_in_date": "2026-10-25",
  "check_out_date": "2026-10-27",
  "number_of_guests": 2,
  "adults": 2,
  "children": 0,
  "official_booking_status": "confirmed",
  "payment_status": "paid",
  "currency": "PHP",
  "gross_amount": 13750.00,
  "discount_amount": 8937.50,
  "total_amount": 4812.50,
  "rate_plan": "Regular Discount",
  "notes": "Booking received from the official Le Charme Suites website."
}
```

Successful requests return HTTP `201`. Repeated requests with the same official booking reference are handled without creating a duplicate reservation. The integration stores payment status and totals, but it never receives or stores card numbers.

Booking changes use:

```http
PUT /api/v1/bookings/{external_reference}
```

The request contains the complete updated booking details. Booking cancellations use:

```http
POST /api/v1/bookings/{external_reference}/cancel
Content-Type: application/json

{"reason":"Guest changed travel plans."}
```

## What Is Already Implemented

- Protected create, update, and cancellation APIs with shared-key authentication
- Request validation and duplicate prevention
- Guest record creation or matching
- Room type, capacity, date-conflict, and availability checks
- Pending reservation creation with source `hotel_website_api`
- Integration event history and transaction log
- Front Desk notification polling
- Front Desk confirmation and normal arrival/check-in workflow
- Automated tests covering API intake through check-in, billing, checkout, receipt, and housekeeping turnover

## What the Hotel Must Provide

Live integration requires all of the following:

1. Written permission to exchange guest and reservation data.
2. Contact with the developer or booking provider that operates the official website.
3. Permission to add a server-side webhook or API call after a booking is confirmed.
4. A public HTTPS address for the IHMP. `127.0.0.1` and `localhost` are accessible only on the development computer.
5. Agreement on booking references, room-type names, dates, guest details, status values, cancellations, amendments, and payment fields.
6. Secure storage and scheduled replacement of the shared integration key.
7. A test environment and test bookings before production access is enabled.

The IHMP accepts new bookings, updates, and cancellations. The hotel's developer must map its booking fields and trigger the correct operation after each official website event.

## Demonstration Versus Live Integration

The local `/website-booking` page is an academic simulator that sends data through the same IHMP booking service. It proves the receiving workflow without modifying the hotel's production website.

It must not be presented as a live connection to the official website. A live connection exists only after the hotel or its provider configures the official booking server to call the public IHMP endpoint.

## Acceptance Test for a Live Connection

1. Create a test booking on the official website using a reference supplied by the hotel.
2. Confirm that the official website receives a successful API response from the IHMP.
3. Confirm that exactly one reservation appears in IHMP with source `Official Website API`.
4. Confirm that Front Desk receives the new-booking notice within the configured polling interval.
5. Confirm the reservation and verify it enters the arrival list.
6. Record check-in on arrival and verify that the assigned room becomes occupied.
7. Change or cancel the test booking on the official website and verify the same change reaches IHMP.

## Defense Statement

The group implemented and tested the IHMP receiving API and the complete internal Front Desk workflow. The remaining production step is an authorized outbound webhook from Le Charme Suites' official booking system to a publicly hosted IHMP. Without access and written approval from the hotel or its booking provider, claiming a live production connection would be inaccurate and would risk unauthorized access to guest data.
