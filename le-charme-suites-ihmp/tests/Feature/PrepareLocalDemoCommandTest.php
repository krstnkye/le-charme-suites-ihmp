<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrepareLocalDemoCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_prompts_for_a_password_without_storing_it_in_arguments(): void
    {
        $this->artisan('demo:prepare')
            ->expectsQuestion('Enter one password for all four fictional demo accounts', 'Local-Demo-Only-2026!')
            ->expectsQuestion('Enter the same password again', 'Local-Demo-Only-2026!')
            ->expectsOutputToContain('Local SQLite demonstration data is ready.')
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('rooms', 45);
    }

    public function test_command_refuses_mismatched_passwords(): void
    {
        $this->artisan('demo:prepare')
            ->expectsQuestion('Enter one password for all four fictional demo accounts', 'Local-Demo-Only-2026!')
            ->expectsQuestion('Enter the same password again', 'Different-Demo-Password!')
            ->expectsOutputToContain('Passwords did not match')
            ->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_check_only_mode_performs_no_database_writes(): void
    {
        $this->artisan('demo:prepare', ['--check-only' => true])
            ->expectsOutputToContain('Safe local SQLite demonstration settings confirmed.')
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('rooms', 0);
    }
}
