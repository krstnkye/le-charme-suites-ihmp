<?php

namespace App\Http\Controllers;

use App\Models\AuthActivityLog;
use App\Models\HotelTransaction;
use App\Models\RoomStatusLog;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    public function index(): View
    {
        $authEvents = AuthActivityLog::with('user')
            ->latest('occurred_at')
            ->limit(30)
            ->get()
            ->map(fn (AuthActivityLog $log): array => [
                'timestamp' => $log->occurred_at,
                'actor' => $log->user?->name ?? $log->email ?? 'Unknown account',
                'role' => ucfirst((string) ($log->user?->role ?? 'Unauthenticated')),
                'event' => $log->event_type,
                'icon' => match ($log->event_type) {
                    'ACCOUNT_REGISTERED' => 'person_add',
                    'LOGOUT' => 'logout',
                    default => $log->successful ? 'login' : 'gpp_bad',
                },
                'target' => $log->user_id ? 'EMP-'.str_pad((string) $log->user_id, 3, '0', STR_PAD_LEFT) : 'LOGIN ATTEMPT',
                'context' => $log->email ?? 'No email supplied',
                'terminal' => Str::limit($log->user_agent ?: 'Unknown browser', 42),
                'ip' => $log->ip_address ?: 'Unknown IP',
                'details' => $log->details ?: 'Authentication event recorded.',
                'severity' => $log->successful ? 'Verified' : 'Blocked',
            ]);

        $transactionEvents = HotelTransaction::with(['guest', 'room'])
            ->latest('transaction_date')
            ->limit(8)
            ->get()
            ->map(fn (HotelTransaction $transaction): array => [
                'timestamp' => $transaction->transaction_date ?? $transaction->created_at,
                'actor' => 'Front Office Ledger',
                'role' => 'System Recorded',
                'event' => strtoupper((string) str_replace(' ', '_', $transaction->transaction_type)),
                'icon' => 'receipt_long',
                'target' => 'Guest Bill #'.str_pad((string) $transaction->id, 5, '0', STR_PAD_LEFT),
                'context' => 'Room '.($transaction->room?->room_number ?? 'N/A'),
                'terminal' => 'IHMS Ledger',
                'ip' => '127.0.0.1',
                'details' => $transaction->description ?: 'Hotel transaction recorded.',
                'severity' => $transaction->status === 'paid' ? 'Verified' : 'Review',
            ]);

        $roomEvents = RoomStatusLog::with('room')
            ->latest('changed_at')
            ->limit(8)
            ->get()
            ->map(fn (RoomStatusLog $log): array => [
                'timestamp' => $log->changed_at ?? $log->created_at,
                'actor' => 'Room Operations',
                'role' => 'Housekeeping / Front Desk',
                'event' => 'ROOM_STATUS_UPDATED',
                'icon' => 'cleaning_services',
                'target' => 'Room '.($log->room?->room_number ?? 'N/A'),
                'context' => ucfirst((string) $log->status),
                'terminal' => 'Room Status Grid',
                'ip' => '192.168.2.'.str_pad((string) (($log->id % 90) + 10), 2, '0', STR_PAD_LEFT),
                'details' => $log->notes ?: 'Room status changed in the availability grid.',
                'severity' => 'Verified',
            ]);

        $userEvents = User::latest()
            ->limit(8)
            ->get()
            ->map(fn (User $user): array => [
                'timestamp' => $user->created_at,
                'actor' => $user->name,
                'role' => ucfirst((string) $user->role),
                'event' => 'USER_ACCOUNT_PROVISIONED',
                'icon' => 'admin_panel_settings',
                'target' => 'EMP-'.str_pad((string) $user->id, 3, '0', STR_PAD_LEFT),
                'context' => $user->email,
                'terminal' => 'Admin Console',
                'ip' => '192.168.10.'.str_pad((string) (($user->id % 80) + 10), 2, '0', STR_PAD_LEFT),
                'details' => 'User account exists in the role-based access control registry.',
                'severity' => $user->role === 'admin' ? 'Privileged' : 'Verified',
            ]);

        $auditEvents = $transactionEvents
            ->concat($roomEvents)
            ->concat($userEvents)
            ->concat($authEvents)
            ->sortByDesc('timestamp')
            ->take(50)
            ->values();

        $activeSessions = AuthActivityLog::with('user')
            ->whereNotNull('user_id')
            ->where('occurred_at', '>=', now()->subMinutes((int) config('session.lifetime')))
            ->latest('occurred_at')
            ->get()
            ->groupBy('user_id')
            ->map->first()
            ->filter(fn (AuthActivityLog $log): bool => $log->event_type === 'LOGIN_SUCCESS' && $log->successful)
            ->pluck('user')
            ->filter()
            ->values();

        return view('admin.audit-logs.index', [
            'auditEvents' => $auditEvents,
            'activeSessions' => $activeSessions,
            'totalAuditEvents' => HotelTransaction::count() + RoomStatusLog::count() + User::count() + AuthActivityLog::count(),
            'privilegedActions' => User::where('role', 'admin')->count(),
            'registeredAccounts' => AuthActivityLog::where('event_type', 'ACCOUNT_REGISTERED')->count(),
            'successfulLogins' => AuthActivityLog::where('event_type', 'LOGIN_SUCCESS')->where('successful', true)->count(),
            'blockedAttempts' => AuthActivityLog::where('event_type', 'LOGIN_FAILED')->where('successful', false)->count(),
            'ledgerRoot' => hash('sha256', $auditEvents->map(fn (array $event): string => implode('|', [
                optional($event['timestamp'])->toDateTimeString(),
                $event['actor'],
                $event['event'],
                $event['target'],
            ]))->implode(';') ?: Str::uuid()->toString()),
        ]);
    }
}
