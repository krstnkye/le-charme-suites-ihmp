<?php

namespace Tests\Feature;

use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\Guest;
use App\Models\HousekeepingTask;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Services\LocalDemoDatabaseGuard;
use App\Services\LocalDemoDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class LocalDemoPreparationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=']);
    }

    public function test_guard_accepts_sqlite_memory_only_in_the_test_environment(): void
    {
        app(LocalDemoDatabaseGuard::class)->assertSafe();

        $this->assertTrue(true);
    }

    public function test_guard_accepts_the_named_local_demo_environment_with_a_database_file(): void
    {
        $originalDatabase = config('database.connections.sqlite.database');

        try {
            $this->app->detectEnvironment(fn (): string => 'local-demo');
            config(['database.connections.sqlite.database' => database_path('local-demo.sqlite')]);

            app(LocalDemoDatabaseGuard::class)->assertSafe();
            $this->assertTrue(true);
        } finally {
            $this->app->detectEnvironment(fn (): string => 'testing');
            config(['database.connections.sqlite.database' => $originalDatabase]);
        }
    }

    public function test_guard_resolves_the_relative_database_path_used_by_the_windows_template(): void
    {
        $originalDatabase = config('database.connections.sqlite.database');

        try {
            $this->app->detectEnvironment(fn (): string => 'local-demo');
            config(['database.connections.sqlite.database' => 'database/local-demo.sqlite']);

            app(LocalDemoDatabaseGuard::class)->assertSafe();
            $this->assertTrue(true);
        } finally {
            $this->app->detectEnvironment(fn (): string => 'testing');
            config(['database.connections.sqlite.database' => $originalDatabase]);
        }
    }

    public function test_guard_refuses_a_non_sqlite_connection_before_demo_data_is_written(): void
    {
        $original = config('database.default');

        try {
            config([
                'database.default' => 'pgsql',
                'database.connections.pgsql.database' => 'shared-hotel-data',
            ]);

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('dedicated local SQLite database');

            app(LocalDemoDatabaseGuard::class)->assertSafe();
        } finally {
            config(['database.default' => $original]);
        }
    }

    public function test_guard_refuses_a_database_url_even_when_sqlite_is_selected(): void
    {
        $originalUrl = config('database.connections.sqlite.url');
        $originalDatabase = config('database.connections.sqlite.database');

        try {
            $this->app->detectEnvironment(fn (): string => 'local-demo');
            config([
                'database.connections.sqlite.url' => 'pgsql://shared.example.invalid/hotel',
                'database.connections.sqlite.database' => database_path('local-demo.sqlite'),
            ]);

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('database URL');

            app(LocalDemoDatabaseGuard::class)->assertSafe();
        } finally {
            $this->app->detectEnvironment(fn (): string => 'testing');
            config([
                'database.connections.sqlite.url' => $originalUrl,
                'database.connections.sqlite.database' => $originalDatabase,
            ]);
        }
    }

    public function test_guard_refuses_any_sqlite_file_other_than_the_dedicated_demo_database(): void
    {
        $originalDatabase = config('database.connections.sqlite.database');

        try {
            $this->app->detectEnvironment(fn (): string => 'local-demo');
            config(['database.connections.sqlite.database' => database_path('database.sqlite')]);

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('local-demo.sqlite');

            app(LocalDemoDatabaseGuard::class)->assertSafe();
        } finally {
            $this->app->detectEnvironment(fn (): string => 'testing');
            config(['database.connections.sqlite.database' => $originalDatabase]);
        }
    }

    public function test_service_creates_an_idempotent_and_consistent_fictional_demo(): void
    {
        $password = 'Local-Demo-Only-2026!';
        $service = app(LocalDemoDataService::class);

        $first = $service->prepare($password);
        $second = $service->prepare($password);

        $this->assertSame($first, $second);
        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('rooms', 45);
        $this->assertDatabaseCount('guests', 4);
        $this->assertDatabaseCount('reservations', 4);
        $this->assertDatabaseCount('check_ins', 2);
        $this->assertDatabaseCount('check_outs', 1);
        $this->assertDatabaseCount('housekeeping_tasks', 1);

        foreach ([
            'admin@lecharmehotel.com' => 'admin',
            'receptionist@lecharmehotel.com' => 'receptionist',
            'billing@lecharmehotel.com' => 'cashier',
            'housekeeping@lecharmehotel.com' => 'housekeeping',
        ] as $email => $role) {
            $user = User::where('email', $email)->firstOrFail();
            $this->assertSame($role, $user->role);
            $this->assertNotNull($user->approved_at);
            $this->assertTrue(Hash::check($password, $user->password));
        }

        $this->assertSame(45, Room::count());
        $this->assertSame(1, Room::where('status', 'reserved')->count());
        $this->assertSame(1, Room::where('status', 'occupied')->count());
        $this->assertSame(1, Room::where('status', 'cleaning')->count());
        $this->assertSame(42, Room::where('status', 'available')->count());

        $this->assertSame('pending', Reservation::where('booking_reference', 'DEMO-PENDING-001')->value('status'));
        $this->assertSame('reserved', Reservation::where('booking_reference', 'DEMO-ARRIVAL-001')->value('status'));
        $this->assertSame('checked_in', Reservation::where('booking_reference', 'DEMO-STAY-001')->value('status'));
        $this->assertSame('completed', Reservation::where('booking_reference', 'DEMO-CHECKOUT-001')->value('status'));

        $this->assertSame('active', CheckIn::whereHas('reservation', fn ($query) => $query->where('booking_reference', 'DEMO-STAY-001'))->value('status'));
        $this->assertSame('completed', CheckIn::whereHas('reservation', fn ($query) => $query->where('booking_reference', 'DEMO-CHECKOUT-001'))->value('status'));
        $this->assertSame('paid', CheckOut::sole()->payment_status);
        $this->assertSame('queued', HousekeepingTask::sole()->status);
        $this->assertSame('housekeeping@lecharmehotel.com', HousekeepingTask::sole()->assignee->email);
        $this->assertTrue(Guest::get()->every(fn (Guest $guest): bool => str_contains((string) $guest->notes, 'FICTIONAL DEMO')));
    }

    public function test_service_rejects_a_weak_demo_password_without_writing_records(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('at least 12 characters');

        app(LocalDemoDataService::class)->prepare('short');
    }

    public function test_all_demo_accounts_can_authenticate_through_the_staff_login(): void
    {
        $password = 'Local-Demo-Only-2026!';
        app(LocalDemoDataService::class)->prepare($password);

        $adminResponse = $this->post('/admin', [
            'email' => 'admin@lecharmehotel.com',
            'password' => $password,
        ]);
        $adminResponse->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs(User::where('email', 'admin@lecharmehotel.com')->firstOrFail());
        auth()->logout();

        foreach ([
            ['receptionist', 'receptionist@lecharmehotel.com'],
            ['cashier', 'billing@lecharmehotel.com'],
            ['housekeeping', 'housekeeping@lecharmehotel.com'],
        ] as [$role, $email]) {
            $response = $this->post('/login', [
                'role' => $role,
                'email' => $email,
                'password' => $password,
            ]);

            $response->assertRedirect('/dashboard');
            $this->assertAuthenticatedAs(User::where('email', $email)->firstOrFail());
            auth()->logout();
        }
    }
}
