<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\HotelTransaction;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOperationsController extends Controller
{
    public function index(Request $request, string $module): View
    {
        abort_unless(in_array($module, ['reservations', 'check-ins', 'check-outs', 'housekeeping', 'transactions', 'room-inventory', 'room-types', 'room-rates', 'room-status', 'reports'], true), 404);

        $data = match ($module) {
            'reservations' => $this->reservations($request),
            'check-ins' => $this->checkIns(),
            'check-outs' => $this->checkOuts(),
            'housekeeping' => $this->housekeeping(),
            'transactions' => $this->transactions(),
            'room-inventory' => $this->roomInventory(),
            'room-types' => $this->roomTypes(),
            'room-rates' => $this->roomRates(),
            'room-status' => $this->roomStatus(),
            'reports' => $this->reports(),
        };

        return view('admin.operations.index', [
            'module' => $module,
            ...$data,
        ]);
    }

    private function reservations(Request $request): array
    {
        $search = (string) $request->string('search');
        $records = Reservation::with(['guest', 'room'])
            ->when($search !== '', fn ($query) => $query->whereHas('guest', fn ($guest) => $guest->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")))
            ->latest()->limit(25)->get();

        return [
            'title' => 'Reservation Administration', 'subtitle' => 'Review, modify, cancel, and audit all property reservations.', 'icon' => 'calendar_month',
            'stats' => [['label' => 'Total reservations', 'value' => Reservation::count()], ['label' => 'Reserved', 'value' => Reservation::where('status', 'reserved')->count()], ['label' => 'Checked in', 'value' => Reservation::where('status', 'checked_in')->count()], ['label' => 'Cancelled', 'value' => Reservation::where('status', 'cancelled')->count()]],
            'records' => $records->map(fn ($record) => [$record->id, $record->guest?->full_name ?? 'Guest record unavailable', $record->room?->room_number ?? 'Unassigned', $record->check_in_date?->format('M d, Y').' - '.$record->check_out_date?->format('M d, Y'), ucfirst(str_replace('_', ' ', $record->status))]),
            'headers' => ['Reservation', 'Guest', 'Room', 'Dates', 'Status'],
        ];
    }

    private function checkIns(): array
    {
        $records = CheckIn::with(['guest', 'room', 'reservation'])->latest('checked_in_at')->limit(25)->get();

        return [
            'title' => 'Check-in Administration', 'subtitle' => 'Supervise arrivals, active stays, and front-office check-in records.', 'icon' => 'login',
            'stats' => [['label' => 'Arrival list', 'value' => Reservation::where('status', 'reserved')->count()], ['label' => 'Active stays', 'value' => CheckIn::where('status', 'active')->count()], ['label' => 'Completed today', 'value' => CheckIn::whereDate('checked_in_at', today())->count()], ['label' => 'Available rooms', 'value' => Room::where('status', 'available')->count()]],
            'records' => $records->map(fn ($record) => [$record->id, $record->guest?->full_name ?? 'Unknown guest', $record->room?->room_number ?? 'Unassigned', optional($record->checked_in_at)->format('M d, Y h:i A'), ucfirst($record->status)]),
            'headers' => ['Check-in', 'Guest', 'Room', 'Checked in', 'Status'],
        ];
    }

    private function checkOuts(): array
    {
        $records = CheckOut::with(['guest', 'room'])->latest('checked_out_at')->limit(25)->get();

        return [
            'title' => 'Check-out Administration', 'subtitle' => 'Review departures, payment status, room cleaning, and receipt records.', 'icon' => 'logout',
            'stats' => [['label' => 'Pending departures', 'value' => CheckIn::where('status', 'active')->count()], ['label' => 'Completed today', 'value' => CheckOut::whereDate('checked_out_at', today())->count()], ['label' => 'Paid today', 'value' => number_format((float) CheckOut::whereDate('checked_out_at', today())->sum('amount_paid'), 2)], ['label' => 'Rooms in cleaning', 'value' => Room::where('status', 'cleaning')->count()]],
            'records' => $records->map(fn ($record) => [$record->id, $record->guest?->full_name ?? 'Unknown guest', $record->room?->room_number ?? 'Unassigned', optional($record->checked_out_at)->format('M d, Y h:i A'), ucfirst($record->payment_status)]),
            'headers' => ['Check-out', 'Guest', 'Room', 'Checked out', 'Payment'],
        ];
    }

    private function housekeeping(): array
    {
        $records = Room::orderBy('room_number')->limit(50)->get();

        return [
            'title' => 'Housekeeping & Room Status Administration', 'subtitle' => 'Monitor rooms that are ready, cleaning lists, and room status across the hotel.', 'icon' => 'cleaning_services',
            'stats' => [['label' => 'Total rooms', 'value' => Room::count()], ['label' => 'Available', 'value' => Room::where('status', 'available')->count()], ['label' => 'Cleaning', 'value' => Room::where('status', 'cleaning')->count()], ['label' => 'Maintenance', 'value' => Room::where('status', 'maintenance')->count()]],
            'records' => $records->map(fn ($record) => [$record->room_number, $record->room_type, $record->capacity.' guests', 'PHP '.number_format((float) $record->rate, 2), ucfirst($record->status)]),
            'headers' => ['Room', 'Type', 'Capacity', 'Rate', 'Status'],
        ];
    }

    private function transactions(): array
    {
        $records = HotelTransaction::with(['guest', 'room'])->latest('transaction_date')->limit(25)->get();

        return [
            'title' => 'Transactions & Billing Administration', 'subtitle' => 'Review hotel charges, payments, guest bills, and billing activity across all departments.', 'icon' => 'receipt_long',
            'stats' => [['label' => 'Recorded transactions', 'value' => HotelTransaction::count()], ['label' => 'Collected amount', 'value' => 'PHP '.number_format((float) HotelTransaction::sum('amount'), 2)], ['label' => 'Reservation entries', 'value' => HotelTransaction::where('transaction_type', 'reservation')->count()], ['label' => 'Check-out entries', 'value' => HotelTransaction::where('transaction_type', 'check_out')->count()]],
            'records' => $records->map(fn ($record) => [$record->id, $record->guest?->full_name ?? 'Unknown guest', ucfirst(str_replace('_', ' ', $record->transaction_type)), 'PHP '.number_format((float) $record->amount, 2), optional($record->transaction_date)->format('M d, Y h:i A')]),
            'headers' => ['Transaction', 'Guest', 'Type', 'Amount', 'Date'],
        ];
    }

    private function roomInventory(): array
    {
        $records = Room::orderBy('room_number')->limit(50)->get();

        return [
            'title' => 'Room Inventory Administration', 'subtitle' => 'Review added rooms, room types, capacity, rates, and booking status from the administrator page.', 'icon' => 'inventory_2',
            'stats' => [['label' => 'Total rooms', 'value' => Room::count()], ['label' => 'Standard rooms', 'value' => Room::where('room_type', 'like', 'Standard%')->count()], ['label' => 'Available rooms', 'value' => Room::where('status', 'available')->count()], ['label' => 'Average rate', 'value' => 'PHP '.number_format((float) Room::avg('rate'), 2)]],
            'records' => $records->map(fn ($record) => [$record->room_number, $record->room_type, $record->capacity.' guests', 'PHP '.number_format((float) $record->rate, 2), ucfirst($record->status)]),
            'headers' => ['Room', 'Type', 'Capacity', 'Rate', 'Status'],
        ];
    }

    private function roomTypes(): array
    {
        $availability = app(RoomAvailabilityService::class)->summary();
        $rooms = Room::query()
            ->selectRaw('room_type, COUNT(*) as room_count, MIN(capacity) as min_capacity, MAX(capacity) as max_capacity, AVG(rate) as average_rate')
            ->groupBy('room_type')
            ->orderBy('room_type')
            ->get();

        return [
            'title' => 'Room Types Administration',
            'subtitle' => 'Review each room type, guest capacity, assigned rooms, and booking availability.',
            'icon' => 'hotel',
            'roomAvailability' => $availability,
            'stats' => [
                ['label' => 'Room type groups', 'value' => $rooms->count()],
                ['label' => 'Total hotel rooms', 'value' => $availability['property_total']],
                ['label' => 'Available now', 'value' => $availability['available']],
                ['label' => 'Highest capacity', 'value' => (Room::max('capacity') ?? 0).' guests'],
            ],
            'records' => $rooms->map(fn ($record) => [
                $record->room_type,
                $record->room_count.' rooms',
                ((int) $record->min_capacity === (int) $record->max_capacity ? $record->max_capacity : $record->min_capacity.'-'.$record->max_capacity).' guests',
                'PHP '.number_format((float) $record->average_rate, 2),
                ($availability['by_type'][$record->room_type]['available'] ?? 0).' available',
            ]),
            'headers' => ['Room Type', 'Inventory', 'Capacity Range', 'Average Rate', 'Availability'],
        ];
    }

    private function roomRates(): array
    {
        $rooms = Room::query()
            ->selectRaw('room_type, COUNT(*) as room_count, MIN(rate) as minimum_rate, MAX(rate) as maximum_rate, AVG(rate) as average_rate')
            ->groupBy('room_type')
            ->orderBy('room_type')
            ->get();

        return [
            'title' => 'Room Rates Administration',
            'subtitle' => 'Inspect published room pricing, rate bands, and category-level averages before applying future rate controls.',
            'icon' => 'sell',
            'stats' => [
                ['label' => 'Rate categories', 'value' => $rooms->count()],
                ['label' => 'Lowest rate', 'value' => 'PHP '.number_format((float) (Room::min('rate') ?? 0), 2)],
                ['label' => 'Highest rate', 'value' => 'PHP '.number_format((float) (Room::max('rate') ?? 0), 2)],
                ['label' => 'Average rate', 'value' => 'PHP '.number_format((float) Room::avg('rate'), 2)],
            ],
            'records' => $rooms->map(fn ($record) => [
                $record->room_type,
                $record->room_count.' rooms',
                'PHP '.number_format((float) $record->minimum_rate, 2),
                'PHP '.number_format((float) $record->maximum_rate, 2),
                'PHP '.number_format((float) $record->average_rate, 2),
            ]),
            'headers' => ['Room Type', 'Inventory', 'Minimum Rate', 'Maximum Rate', 'Average Rate'],
        ];
    }

    private function roomStatus(): array
    {
        $records = Room::orderBy('status')->orderBy('room_number')->get();
        $availability = app(RoomAvailabilityService::class)->summary();

        return [
            'title' => 'Room Status Administration', 'subtitle' => 'Monitor available, occupied, reserved, maintenance, and cleaned rooms from the admin portal.', 'icon' => 'bed',
            'roomAvailability' => $availability,
            'stats' => [['label' => 'Available', 'value' => $availability['available']], ['label' => 'Reserved', 'value' => $availability['reserved']], ['label' => 'Occupied', 'value' => $availability['occupied']], ['label' => 'Cleaning / Maintenance', 'value' => $availability['out_of_service']]],
            'records' => $records->map(fn ($record) => [$record->room_number, $record->room_type, ucfirst($record->status), $record->capacity.' guests', 'PHP '.number_format((float) $record->rate, 2)]),
            'headers' => ['Room', 'Type', 'Status', 'Capacity', 'Rate'],
        ];
    }

    private function reports(): array
    {
        $records = HotelTransaction::with(['guest', 'room'])->latest('transaction_date')->limit(25)->get();

        return [
            'title' => 'Reports Administration', 'subtitle' => 'Review room, reservation, and financial summaries from the administrator command sidebar.', 'icon' => 'monitoring',
            'stats' => [['label' => 'Rooms tracked', 'value' => Room::count()], ['label' => 'Reservations tracked', 'value' => Reservation::count()], ['label' => 'Transaction records', 'value' => HotelTransaction::count()], ['label' => 'Transaction total', 'value' => 'PHP '.number_format((float) HotelTransaction::sum('amount'), 2)]],
            'records' => $records->map(fn ($record) => [optional($record->transaction_date)->format('M d, Y h:i A'), ucfirst(str_replace('_', ' ', $record->transaction_type)), $record->guest?->full_name ?? 'N/A', $record->room?->room_number ?? 'N/A', 'PHP '.number_format((float) $record->amount, 2)]),
            'headers' => ['Date', 'Type', 'Guest', 'Room', 'Amount'],
        ];
    }
}
