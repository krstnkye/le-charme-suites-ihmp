<?php

namespace App\Http\Controllers;

use App\Models\AuthActivityLog;
use App\Models\JwtToken;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        $auditEvents = $authEvents
            ->take(50)
            ->values();

        $sessionTable = (string) config('session.table', 'sessions');
        $activeSessions = config('session.driver') === 'database' && Schema::hasTable($sessionTable)
            ? User::whereIn('id', DB::table($sessionTable)->whereNotNull('user_id')->pluck('user_id'))->get()
            : collect();

        return view('admin.audit-logs.index', [
            'auditEvents' => $auditEvents,
            'activeSessions' => $activeSessions,
            'totalAuditEvents' => AuthActivityLog::count(),
            'privilegedActions' => AuthActivityLog::where('event_type', 'like', 'USER_%')
                ->orWhere('event_type', 'like', 'ROLE_%')
                ->count(),
            'registeredAccounts' => AuthActivityLog::where('event_type', 'ACCOUNT_REGISTERED')->count(),
            'successfulLogins' => AuthActivityLog::where('event_type', 'LOGIN_SUCCESS')->where('successful', true)->count(),
            'blockedAttempts' => AuthActivityLog::where('event_type', 'LOGIN_FAILED')->where('successful', false)->count(),
            'ledgerRoot' => hash('sha256', $auditEvents->map(fn (array $event): string => implode('|', [
                optional($event['timestamp'])->toDateTimeString(),
                $event['actor'],
                $event['event'],
                $event['target'],
            ]))->implode(';')),
        ]);
    }

    public function terminateRemoteSessions(Request $request, AuditService $audit): RedirectResponse
    {
        $sessionTable = (string) config('session.table', 'sessions');

        if (config('session.driver') !== 'database' || ! Schema::hasTable($sessionTable)) {
            return back()->withErrors([
                'sessions' => 'Remote session termination requires the database session driver.',
            ]);
        }

        $terminated = DB::table($sessionTable)
            ->where('id', '!=', $request->session()->getId())
            ->delete();
        $revokedTokens = JwtToken::whereNull('revoked_at')->update(['revoked_at' => now()]);

        $audit->record($request, 'REMOTE_SESSIONS_TERMINATED', null, [], [
            'web_sessions_terminated' => $terminated,
            'jwt_tokens_revoked' => $revokedTokens,
        ]);

        return back()->with('status', "Terminated {$terminated} remote web sessions and revoked {$revokedTokens} API tokens.");
    }
}
