<?php

use App\Http\Controllers\AdminAuditLogController;
use App\Http\Controllers\AdminGuestController;
use App\Http\Controllers\AdminIntegrationCenterController;
use App\Http\Controllers\AdminOperationsController;
use App\Http\Controllers\AdminSystemSettingsController;
use App\Http\Controllers\AdminUserRoleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\CheckOutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentMessageController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\HousekeepingTaskController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationConfirmationController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SystemManualController;
use App\Http\Controllers\UiPreferenceController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');
Route::post('/generate', [HomeController::class, 'generate'])->name('generate');
Route::get('/book', [PublicBookingController::class, 'create'])->name('booking.create');
Route::post('/book', [PublicBookingController::class, 'store'])->middleware('throttle:10,1')->name('booking.store');
Route::get('/book/confirmation/{reference}', [PublicBookingController::class, 'confirmation'])
    ->middleware('throttle:30,1')
    ->name('booking.confirmation');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:5,1')
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/photo', [ProfileController::class, 'photo'])->name('profile.photo');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/messages', [DepartmentMessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [DepartmentMessageController::class, 'store'])->name('messages.store');
    Route::post('/messages/{message}/read', [DepartmentMessageController::class, 'markRead'])->name('messages.read');
    Route::put('/ui-preferences', [UiPreferenceController::class, 'update'])->name('ui-preferences.update');
    Route::get('/system-manual', SystemManualController::class)->name('system-manual');

    Route::middleware('role:receptionist')->group(function () {
        Route::resource('guests', GuestController::class);
        Route::get('/reservations/{reservation}/registration-card', [ReservationController::class, 'registrationCard'])
            ->name('reservations.registration-card');
        Route::resource('reservations', ReservationController::class);
        Route::get('/check-ins', [CheckInController::class, 'index'])->name('check-ins.index');
        Route::get('/check-ins/create', [CheckInController::class, 'create'])->name('check-ins.create');
        Route::post('/check-ins', [CheckInController::class, 'store'])->name('check-ins.store');
        Route::post('/reservations/{reservation}/confirmation', ReservationConfirmationController::class)
            ->name('reservations.confirmation.store');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/audit-logs', [AdminAuditLogController::class, 'index'])->name('admin.audit-logs.index');
        Route::get('/admin/guests', [AdminGuestController::class, 'index'])->name('admin.guests.index');
        Route::get('/admin/users', [AdminUserRoleController::class, 'index'])->name('admin.users.index');
        Route::post('/admin/users', [AdminUserRoleController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [AdminUserRoleController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [AdminUserRoleController::class, 'destroy'])->name('admin.users.destroy');
        Route::get('/admin/roles', [AdminUserRoleController::class, 'roles'])->name('admin.roles.index');
        Route::get('/admin/permissions', [AdminUserRoleController::class, 'permissions'])->name('admin.permissions.index');
        Route::post('/admin/permissions', [AdminUserRoleController::class, 'savePermissions'])->name('admin.permissions.save');
        Route::post('/admin/permissions/reset', [AdminUserRoleController::class, 'resetPermissions'])->name('admin.permissions.reset');
        Route::get('/admin/operations/{module}', [AdminOperationsController::class, 'index'])->name('admin.operations.index');
        Route::get('/admin/settings', [AdminSystemSettingsController::class, 'index'])->name('admin.settings.index');
        Route::get('/admin/integration-center', [AdminIntegrationCenterController::class, 'index'])->name('admin.integration.index');
        Route::get('/admin/integration-center/export/{dataset}/{format}', [AdminIntegrationCenterController::class, 'export'])->name('admin.integration.export');
        Route::get('/admin/integration-center/transfer-report', [AdminIntegrationCenterController::class, 'transferReport'])->name('admin.integration.transfer-report');
        Route::resource('rooms', RoomController::class)->only(['create', 'store', 'destroy']);
    });

    Route::middleware('role:receptionist,housekeeping')->group(function () {
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::get('/rooms/{room}', [RoomController::class, 'show'])->whereNumber('room')->name('rooms.show');
        Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->whereNumber('room')->name('rooms.edit');
        Route::match(['put', 'patch'], '/rooms/{room}', [RoomController::class, 'update'])->whereNumber('room')->name('rooms.update');
    });

    Route::middleware('role:housekeeping')->group(function () {
        Route::get('/housekeeping/{page}', HousekeepingController::class)->name('housekeeping.page');
    });

    Route::middleware('role:receptionist,housekeeping')->group(function () {
        Route::post('/housekeeping-tasks', [HousekeepingTaskController::class, 'store'])->name('housekeeping-tasks.store');
        Route::patch('/housekeeping-tasks/{housekeepingTask}', [HousekeepingTaskController::class, 'update'])
            ->name('housekeeping-tasks.update');
    });

    Route::middleware('role:receptionist,cashier')->group(function () {
        Route::get('/check-outs', [CheckOutController::class, 'index'])->name('check-outs.index');
        Route::get('/check-outs/history', [CheckOutController::class, 'history'])->name('check-outs.history');
        Route::get('/check-outs/create', [CheckOutController::class, 'create'])->name('check-outs.create');
        Route::get('/check-outs/{checkOut}/receipt', [CheckOutController::class, 'receipt'])->name('check-outs.receipt');
        Route::post('/check-outs', [CheckOutController::class, 'store'])->name('check-outs.store');
    });

    Route::middleware('role:cashier')->group(function () {
        Route::get('/reports', ReportController::class)->name('reports.index');
    });
});
