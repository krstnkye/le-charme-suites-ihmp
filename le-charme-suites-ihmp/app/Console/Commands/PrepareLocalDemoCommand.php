<?php

namespace App\Console\Commands;

use App\Services\LocalDemoDatabaseGuard;
use App\Services\LocalDemoDataService;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

class PrepareLocalDemoCommand extends Command
{
    protected $signature = 'demo:prepare {--check-only : Validate local SQLite settings without changing data}';

    protected $description = 'Create fictional records for the isolated local SQLite demonstration';

    public function handle(LocalDemoDataService $demo): int
    {
        if ($this->option('check-only')) {
            try {
                app(LocalDemoDatabaseGuard::class)->assertSafe();
            } catch (RuntimeException $exception) {
                $this->components->error($exception->getMessage());

                return self::FAILURE;
            }

            $this->components->info('Safe local SQLite demonstration settings confirmed.');

            return self::SUCCESS;
        }

        $this->components->warn('This command is only for the isolated classroom demonstration database.');

        $password = (string) $this->secret('Enter one password for all four fictional demo accounts');
        $confirmation = (string) $this->secret('Enter the same password again');

        if ($password !== $confirmation) {
            $this->components->error('Passwords did not match. No demo records were created.');

            return self::FAILURE;
        }

        try {
            $counts = $demo->prepare($password);
        } catch (RuntimeException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        } catch (Throwable $exception) {
            report($exception);
            $this->components->error('Demo preparation failed. No shared database was used. Check storage/logs/laravel.log.');

            return self::FAILURE;
        }

        $this->components->info('Local SQLite demonstration data is ready.');
        $this->table(['Record type', 'Count'], collect($counts)->map(
            fn (int $count, string $type): array => [str_replace('_', ' ', ucfirst($type)), $count]
        )->values()->all());
        $this->newLine();
        $this->line('Accounts: admin, receptionist, billing, and housekeeping @lecharmehotel.com');
        $this->line('The password was used only to create local password hashes and was not saved in a file.');

        return self::SUCCESS;
    }
}
