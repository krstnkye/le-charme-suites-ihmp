@php
    $receiptNumber = 'REC-' . ($checkOut->checked_out_at?->format('Y') ?? now()->format('Y')) . '-' . str_pad($checkOut->id, 5, '0', STR_PAD_LEFT);
    $bookingReference = $reservation?->booking_reference ?: 'Not recorded';
    $statusLabels = ['paid' => 'PAID', 'partial' => 'PARTIAL PAYMENT', 'unpaid' => 'UNPAID'];
    $statusLabel = $statusLabels[$checkOut->payment_status] ?? strtoupper($checkOut->payment_status);
    $statusClass = $checkOut->payment_status === 'paid' ? 'paid' : ($checkOut->payment_status === 'partial' ? 'partial' : 'unpaid');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $receiptNumber }} - Guest Checkout Receipt</title>
    @include('partials.favicon')
    <style>
        :root { --navy:#01162d; --gold:#8a682d; --paper:#fff; --warm:#f6f3ed; --line:#d9d5cd; --muted:#5d6978; --green:#087a55; --red:#a71919; }
        * { box-sizing:border-box; }
        html { background:#ebe8e2; }
        body { margin:0; color:var(--navy); background:#ebe8e2; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:1.45; }
        button, a { font:inherit; }
        .toolbar { position:sticky; top:0; z-index:10; display:flex; justify-content:space-between; align-items:center; gap:16px; padding:12px 20px; background:var(--navy); color:#fff; box-shadow:0 3px 14px rgba(1,22,45,.2); }
        .toolbar-group { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
        .toolbar-title { font-weight:700; }
        .button { display:inline-flex; align-items:center; justify-content:center; min-height:38px; padding:8px 14px; border:1px solid rgba(255,255,255,.45); border-radius:6px; color:#fff; background:transparent; text-decoration:none; font-weight:700; cursor:pointer; transition:background .18s ease, transform .18s ease; }
        .button:hover { background:rgba(255,255,255,.12); transform:translateY(-1px); }
        .button.primary { border-color:#e6c185; color:var(--navy); background:#ffdeaa; }
        .sheet { width:210mm; min-height:297mm; margin:18px auto; padding:14mm 15mm 12mm; background:var(--paper); box-shadow:0 6px 28px rgba(1,22,45,.14); }
        .receipt-header { display:grid; grid-template-columns:1.2fr .8fr; gap:18px; align-items:start; padding-bottom:14px; border-bottom:3px solid var(--navy); }
        .brand { display:flex; gap:15px; align-items:center; }
        .brand img { width:154px; height:68px; object-fit:contain; background:#fff; }
        .hotel-name { margin:0; font-family:Georgia, 'Times New Roman', serif; font-size:20px; }
        .hotel-details { margin-top:4px; color:var(--muted); font-size:10px; line-height:1.55; }
        .receipt-heading { text-align:right; }
        .eyebrow { color:var(--gold); font-size:10px; font-weight:800; letter-spacing:.16em; text-transform:uppercase; }
        h1 { margin:4px 0 7px; font-family:Georgia, 'Times New Roman', serif; font-size:25px; line-height:1.15; }
        .receipt-ref { font-family:'Courier New', monospace; font-size:13px; font-weight:700; }
        .status { display:inline-block; margin-top:8px; padding:5px 12px; border:1px solid currentColor; border-radius:999px; font-size:10px; font-weight:800; letter-spacing:.08em; }
        .status.paid { color:var(--green); background:#e9fbf4; }
        .status.partial { color:#7b5718; background:#fff5dd; }
        .status.unpaid { color:var(--red); background:#fff0f0; }
        .section { margin-top:16px; }
        .section-title { display:flex; align-items:center; justify-content:space-between; gap:12px; margin:0 0 8px; padding-bottom:6px; border-bottom:1px solid var(--line); font-family:Georgia, 'Times New Roman', serif; font-size:15px; }
        .section-note { color:var(--muted); font-family:Arial, Helvetica, sans-serif; font-size:9px; font-weight:400; }
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
        .info-card { padding:11px 12px; border:1px solid var(--line); border-radius:6px; background:#fff; }
        .detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:5px 18px; }
        .detail { min-width:0; padding:5px 0; border-bottom:1px dotted #ddd8ce; }
        .label { display:block; margin-bottom:2px; color:var(--muted); font-size:9px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; }
        .value { display:block; overflow-wrap:anywhere; font-size:11px; font-weight:700; }
        table { width:100%; border-collapse:collapse; }
        th { padding:8px 7px; color:#fff; background:var(--navy); font-size:9px; letter-spacing:.04em; text-align:left; text-transform:uppercase; }
        td { padding:8px 7px; border-bottom:1px solid var(--line); vertical-align:top; }
        th.amount, td.amount { text-align:right; white-space:nowrap; }
        .description { font-weight:700; }
        .subdescription { margin-top:2px; color:var(--muted); font-size:9px; }
        .summary-wrap { display:grid; grid-template-columns:1fr 270px; gap:18px; margin-top:14px; align-items:start; }
        .notes { min-height:112px; padding:11px 12px; border:1px solid var(--line); border-radius:6px; background:var(--warm); }
        .notes p { margin:5px 0 0; color:#3e4b5b; white-space:pre-wrap; }
        .summary { border:1px solid var(--line); border-radius:6px; overflow:hidden; }
        .summary-row { display:flex; justify-content:space-between; gap:16px; padding:8px 11px; border-bottom:1px solid var(--line); }
        .summary-row:last-child { border:0; }
        .summary-row strong:last-child { white-space:nowrap; }
        .summary-row.total { color:#fff; background:var(--navy); font-size:13px; }
        .summary-row.balance { color:var(--red); background:#fff4f4; }
        .summary-row.balance.zero { color:var(--green); background:#edf9f4; }
        .payment-box { display:grid; grid-template-columns:repeat(3, 1fr); gap:9px; margin-top:14px; }
        .payment-item { padding:9px 10px; border:1px solid var(--line); border-radius:6px; background:var(--warm); }
        .signatures { display:grid; grid-template-columns:1fr 1fr; gap:48px; margin-top:34px; }
        .signature { padding-top:26px; border-bottom:1px solid var(--navy); text-align:center; }
        .signature-label { margin-top:5px; color:var(--muted); font-size:9px; text-align:center; }
        .footer { margin-top:18px; padding-top:9px; border-top:1px solid var(--line); color:var(--muted); font-size:9px; text-align:center; }
        .footer strong { color:var(--navy); }
        @page { size: A4 portrait; margin: 0; }
        @media (max-width: 820px) {
            .toolbar { align-items:flex-start; flex-direction:column; }
            .sheet { width:100%; min-height:auto; margin:0; padding:22px 16px; }
            .receipt-header, .info-grid, .summary-wrap { grid-template-columns:1fr; }
            .receipt-heading { text-align:left; }
            .payment-box { grid-template-columns:1fr; }
            .brand { align-items:flex-start; flex-direction:column; }
        }
        @media print {
            html, body { background:#fff; font-size:10px; }
            .no-print { display:none!important; }
            .sheet { width:210mm; min-height:297mm; margin:0; padding:8mm 10mm 6mm; box-shadow:none; print-color-adjust:exact; -webkit-print-color-adjust:exact; }
            .receipt-header { grid-template-columns:1.2fr .8fr; gap:12px; padding-bottom:9px; }
            .brand { align-items:center; flex-direction:row; gap:10px; }
            .brand img { width:126px; height:56px; }
            .hotel-name { font-size:18px; }
            .receipt-heading { text-align:right; }
            h1 { font-size:22px; }
            .section { margin-top:10px; }
            .section-title { margin-bottom:5px; padding-bottom:4px; }
            .info-grid { grid-template-columns:1fr 1fr; gap:8px; }
            .info-card { padding:7px 8px; }
            .detail { padding:3px 0; }
            th, td { padding:5px 6px; }
            .summary-wrap { grid-template-columns:1fr 250px; gap:12px; margin-top:9px; }
            .notes { min-height:0; padding:8px; }
            .summary-row { padding:6px 8px; }
            .payment-box { grid-template-columns:repeat(3, 1fr); gap:6px; margin-top:8px; }
            .payment-item { padding:6px 7px; }
            .signatures { grid-template-columns:1fr 1fr; margin-top:20px; }
            .signature { padding-top:18px; }
            .footer { margin-top:9px; padding-top:6px; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <div>
            <div class="toolbar-title">Guest Checkout Receipt</div>
            <div>{{ $receiptNumber }}</div>
        </div>
        <div class="toolbar-group">
            <a class="button" href="{{ route('check-outs.history') }}">Back to Payments</a>
            <button class="button primary" type="button" onclick="window.print()">Print Receipt</button>
        </div>
    </div>

    <main class="sheet">
        <header class="receipt-header">
            <div class="brand">
                <img src="/images/le-charme-logo-white.png" alt="Le Charme Suites logo">
                <div>
                    <h2 class="hotel-name">Le Charme Suites Subic</h2>
                    <div class="hotel-details">
                        Le Charme Bldg., 1131 Palm Street<br>
                        Subic Bay Freeport Zone, Philippines 2200<br>
                        +63 (047) 250-3333<br>
                        frontoffice@lecharmehotels.com
                    </div>
                </div>
            </div>
            <div class="receipt-heading">
                <div class="eyebrow">Integrated Hotel Management Portal</div>
                <h1>GUEST CHECKOUT RECEIPT</h1>
                <div class="receipt-ref">{{ $receiptNumber }}</div>
                <div class="status {{ $statusClass }}">{{ $statusLabel }}</div>
            </div>
        </header>

        <section class="section info-grid">
            <div class="info-card">
                <h2 class="section-title">Guest Information</h2>
                <div class="detail-grid">
                    <div class="detail"><span class="label">Guest name</span><span class="value">{{ $checkOut->guest?->full_name ?? 'Not recorded' }}</span></div>
                    <div class="detail"><span class="label">Guest number</span><span class="value">GST-{{ str_pad($checkOut->guest?->id ?? 0, 5, '0', STR_PAD_LEFT) }}</span></div>
                    <div class="detail"><span class="label">Email</span><span class="value">{{ $checkOut->guest?->email ?: 'Not recorded' }}</span></div>
                    <div class="detail"><span class="label">Mobile number</span><span class="value">{{ $checkOut->guest?->phone ?: 'Not recorded' }}</span></div>
                    <div class="detail" style="grid-column:1/-1"><span class="label">Address</span><span class="value">{{ $checkOut->guest?->address ?: 'Not recorded' }}</span></div>
                </div>
            </div>
            <div class="info-card">
                <h2 class="section-title">Stay Information</h2>
                <div class="detail-grid">
                    <div class="detail"><span class="label">Booking reference</span><span class="value">{{ $bookingReference }}</span></div>
                    <div class="detail"><span class="label">Room</span><span class="value">Room {{ $checkOut->room?->room_number ?? 'Not recorded' }}</span></div>
                    <div class="detail"><span class="label">Room type</span><span class="value">{{ $checkOut->room?->room_type ?: 'Not recorded' }}</span></div>
                    <div class="detail"><span class="label">Number of guests</span><span class="value">{{ $reservation?->number_of_guests ?? 'Not recorded' }}</span></div>
                    <div class="detail"><span class="label">Check-in date</span><span class="value">{{ $reservation?->check_in_date?->format('M d, Y') ?? $checkOut->checkIn?->checked_in_at?->format('M d, Y') ?? 'Not recorded' }}</span></div>
                    <div class="detail"><span class="label">Check-out date</span><span class="value">{{ $checkOut->checked_out_at?->format('M d, Y') ?? 'Not recorded' }}</span></div>
                    <div class="detail"><span class="label">Length of stay</span><span class="value">{{ $nights }} {{ $nights === 1 ? 'night' : 'nights' }}</span></div>
                    <div class="detail"><span class="label">Room rate</span><span class="value">PHP {{ number_format((float) ($checkOut->room?->rate ?? 0), 2) }} / night</span></div>
                </div>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title">Charges and Payment <span class="section-note">Amounts come from the saved room and checkout records.</span></h2>
            <table>
                <thead>
                    <tr><th>Date</th><th>Description</th><th>Reference</th><th class="amount">Charge</th><th class="amount">Payment</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $reservation?->check_in_date?->format('M d, Y') ?? 'Not recorded' }}</td>
                        <td><div class="description">Room accommodation</div><div class="subdescription">Room {{ $checkOut->room?->room_number ?? 'Not recorded' }} at PHP {{ number_format((float) ($checkOut->room?->rate ?? 0), 2) }} for {{ $nights }} {{ $nights === 1 ? 'night' : 'nights' }}</div></td>
                        <td>{{ $bookingReference }}</td>
                        <td class="amount">PHP {{ number_format($roomCharges, 2) }}</td>
                        <td class="amount">-</td>
                    </tr>
                    @foreach ($additionalCharges as $charge)
                        <tr>
                            <td>{{ $charge->transaction_date?->format('M d, Y') ?? 'Not recorded' }}</td>
                            <td><div class="description">{{ $charge->description ?: 'Additional hotel charge' }}</div><div class="subdescription">{{ ucfirst(str_replace('_', ' ', $charge->transaction_type)) }}</div></td>
                            <td>TRX-{{ str_pad($charge->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="amount">PHP {{ number_format((float) $charge->amount, 2) }}</td>
                            <td class="amount">-</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td>{{ $checkOut->checked_out_at?->format('M d, Y') ?? 'Not recorded' }}</td>
                        <td><div class="description">Payment received at checkout</div><div class="subdescription">Payment method: Not recorded</div></td>
                        <td>{{ $receiptNumber }}</td>
                        <td class="amount">-</td>
                        <td class="amount">PHP {{ number_format($amountPaid, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="summary-wrap">
            <div class="notes">
                <span class="label">Checkout notes</span>
                <p>{{ $checkOut->notes ?: 'No checkout notes were recorded.' }}</p>
                <div class="payment-box">
                    <div class="payment-item"><span class="label">Payment status</span><span class="value">{{ $statusLabel }}</span></div>
                    <div class="payment-item"><span class="label">Payment method</span><span class="value">Not recorded</span></div>
                    <div class="payment-item"><span class="label">Checkout time</span><span class="value">{{ $checkOut->checked_out_at?->format('h:i A') ?? 'Not recorded' }} PHT</span></div>
                </div>
            </div>
            <div class="summary">
                <div class="summary-row"><span>Room charges</span><strong>PHP {{ number_format($roomCharges, 2) }}</strong></div>
                <div class="summary-row"><span>Additional recorded charges</span><strong>PHP {{ number_format($additionalTotal, 2) }}</strong></div>
                <div class="summary-row total"><span>Total recorded charges</span><strong>PHP {{ number_format($totalCharges, 2) }}</strong></div>
                <div class="summary-row"><span>Payment received</span><strong>- PHP {{ number_format($amountPaid, 2) }}</strong></div>
                <div class="summary-row balance {{ $balance <= 0 ? 'zero' : '' }}"><span>Remaining balance</span><strong>PHP {{ number_format($balance, 2) }}</strong></div>
            </div>
        </section>

        <section class="signatures">
            <div><div class="signature"></div><div class="signature-label">Guest signature over printed name</div></div>
            <div><div class="signature"></div><div class="signature-label">Hotel staff signature over printed name</div></div>
        </section>

        <footer class="footer">
            <strong>Thank you for staying at Le Charme Suites Subic.</strong><br>
            This guest receipt was generated from the checkout information saved in the IHMP shared database.<br>
            Printed {{ now(config('app.timezone'))->format('M d, Y h:i A') }} PHT | PAGE 1 OF 1
        </footer>
    </main>

    @if (request()->boolean('print'))
        <script>window.addEventListener('load', () => window.print());</script>
    @endif
</body>
</html>
