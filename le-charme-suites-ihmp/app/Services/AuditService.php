<?php

namespace App\Services;

use App\Models\AuthActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditService
{
    /** @param array<string, mixed> $before @param array<string, mixed> $after */
    public function record(
        Request $request,
        string $eventType,
        ?User $target = null,
        array $before = [],
        array $after = [],
    ): void {
        $actor = $request->user();

        AuthActivityLog::create([
            'user_id' => $actor?->id,
            'email' => $actor?->email,
            'event_type' => Str::limit($eventType, 40, ''),
            'successful' => true,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000),
            'details' => json_encode([
                'actor_id' => $actor?->id,
                'target_user_id' => $target?->id,
                'target_email' => $target?->email,
                'before' => $before,
                'after' => $after,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'occurred_at' => now(),
        ]);
    }
}
