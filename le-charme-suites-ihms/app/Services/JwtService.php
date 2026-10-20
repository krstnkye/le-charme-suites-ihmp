<?php

namespace App\Services;

use App\Models\JwtToken;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Str;
use JsonException;

class JwtService
{
    public function issuePair(User $user): array
    {
        $access = $this->issue($user, 'access', (int) config('jwt.access_ttl', 15));
        $refresh = $this->issue($user, 'refresh', (int) config('jwt.refresh_ttl', 10080));

        return [
            'token_type' => 'Bearer',
            'access_token' => $access['token'],
            'expires_in' => $access['expires_in'],
            'refresh_token' => $refresh['token'],
            'refresh_expires_in' => $refresh['expires_in'],
        ];
    }

    public function authenticate(string $token, string $expectedType = 'access'): JwtToken
    {
        try {
            $parts = explode('.', $token);

            if (count($parts) !== 3) {
                throw new AuthenticationException('Invalid token.');
            }

            [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
            $header = json_decode($this->decodeSegment($encodedHeader), true, 512, JSON_THROW_ON_ERROR);
            $payload = json_decode($this->decodeSegment($encodedPayload), true, 512, JSON_THROW_ON_ERROR);

            if (($header['alg'] ?? null) !== 'HS256' || ($header['typ'] ?? null) !== 'JWT') {
                throw new AuthenticationException('Invalid token.');
            }

            $expectedSignature = $this->base64UrlEncode(hash_hmac(
                'sha256',
                $encodedHeader.'.'.$encodedPayload,
                $this->secret(),
                true,
            ));

            if (! hash_equals($expectedSignature, $encodedSignature)) {
                throw new AuthenticationException('Invalid token.');
            }

            $now = now()->timestamp;

            if (($payload['iss'] ?? null) !== config('jwt.issuer')
                || ($payload['aud'] ?? null) !== config('jwt.audience')
                || ($payload['type'] ?? null) !== $expectedType
                || ! isset($payload['sub'], $payload['jti'], $payload['exp'], $payload['iat'])
                || (int) $payload['exp'] <= $now
                || (int) ($payload['nbf'] ?? $payload['iat']) > $now + 30) {
                throw new AuthenticationException('Invalid or expired token.');
            }

            $record = JwtToken::with('user')
                ->whereKey((string) $payload['jti'])
                ->where('user_id', (int) $payload['sub'])
                ->where('type', $expectedType)
                ->whereNull('revoked_at')
                ->where('expires_at', '>', now())
                ->first();

            if (! $record?->user) {
                throw new AuthenticationException('Invalid or revoked token.');
            }

            return $record;
        } catch (AuthenticationException $exception) {
            throw $exception;
        } catch (JsonException) {
            throw new AuthenticationException('Invalid token.');
        }
    }

    public function revoke(JwtToken $token): void
    {
        $token->update(['revoked_at' => now()]);
    }

    public function revokeEncoded(string $token, string $expectedType): void
    {
        $this->revoke($this->authenticate($token, $expectedType));
    }

    private function issue(User $user, string $type, int $ttlMinutes): array
    {
        $issuedAt = now();
        $expiresAt = $issuedAt->copy()->addMinutes($ttlMinutes);
        $id = (string) Str::uuid();

        JwtToken::create([
            'id' => $id,
            'user_id' => $user->id,
            'type' => $type,
            'expires_at' => $expiresAt,
        ]);

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'iss' => config('jwt.issuer'),
            'aud' => config('jwt.audience'),
            'sub' => (string) $user->id,
            'jti' => $id,
            'iat' => $issuedAt->timestamp,
            'nbf' => $issuedAt->timestamp,
            'exp' => $expiresAt->timestamp,
            'type' => $type,
            'role' => $user->role,
        ];

        $encodedHeader = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = $this->base64UrlEncode(hash_hmac(
            'sha256',
            $encodedHeader.'.'.$encodedPayload,
            $this->secret(),
            true,
        ));

        return [
            'token' => $encodedHeader.'.'.$encodedPayload.'.'.$signature,
            'expires_in' => $ttlMinutes * 60,
        ];
    }

    private function secret(): string
    {
        $secret = (string) config('jwt.secret');

        if (str_starts_with($secret, 'base64:')) {
            $secret = (string) base64_decode(substr($secret, 7), true);
        }

        if (strlen($secret) < 32) {
            throw new \RuntimeException('JWT_SECRET must contain at least 32 bytes.');
        }

        return $secret;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function decodeSegment(string $value): string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        if ($decoded === false) {
            throw new AuthenticationException('Invalid token.');
        }

        return $decoded;
    }
}
