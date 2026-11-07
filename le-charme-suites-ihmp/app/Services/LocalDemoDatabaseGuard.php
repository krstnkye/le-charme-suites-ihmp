<?php

namespace App\Services;

use RuntimeException;

class LocalDemoDatabaseGuard
{
    public function assertSafe(): void
    {
        if (! app()->environment(['local-demo', 'testing'])) {
            throw new RuntimeException('Local demo preparation is allowed only in the local demo or testing environment.');
        }

        if (config('database.default') !== 'sqlite') {
            throw new RuntimeException('Local demo preparation requires a dedicated local SQLite database.');
        }

        if (filled(config('database.connections.sqlite.url'))) {
            throw new RuntimeException('The local demo database URL must be empty so it cannot redirect to a shared database.');
        }

        $database = (string) config('database.connections.sqlite.database');

        if ($database === ':memory:') {
            if (! app()->environment('testing')) {
                throw new RuntimeException('The Windows local demo requires a persistent SQLite database file.');
            }

            return;
        }

        if ($database === '') {
            throw new RuntimeException('The local demo SQLite database path is missing.');
        }

        $databasePath = $this->normalizePath($database);
        $expectedPath = $this->normalizePath(database_path('local-demo.sqlite'));

        if ($databasePath !== $expectedPath) {
            throw new RuntimeException('The local demo database must be exactly database/local-demo.sqlite.');
        }
    }

    private function normalizePath(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        $isAbsolute = preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1
            || str_starts_with($path, DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR)
            || str_starts_with($path, '/');

        if (! $isAbsolute) {
            $path = base_path($path);
        }

        $directory = realpath(dirname($path)) ?: dirname($path);

        return rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.basename($path);
    }
}
