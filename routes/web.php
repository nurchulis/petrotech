<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VdiController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\VmMonitorController;
use App\Http\Controllers\Admin\StorageController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\RBAC\UserController;
use App\Http\Controllers\RBAC\RoleController;

// ─── Public Auth routes (Breeze) ─────────────────────────────────────────────
require __DIR__ . '/auth.php';

// ─── Authenticated routes ─────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class , 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class , 'index']);

    // ── VDI Access (all authenticated users) ──────────────────────────────
    Route::prefix('vdi')->name('vdi.')->group(function () {
            Route::get('/', [VdiController::class , 'index'])->name('index');
            Route::get('/{vm}/rdp', [VdiController::class , 'rdp'])->name('rdp');
            Route::get('/{vm}', [VdiController::class , 'show'])->name('show');
            Route::post('/{vm}/connect', [VdiController::class , 'connect'])->name('connect');
            Route::post('/sessions/{session}/terminate', [VdiController::class , 'terminate'])->name('terminate');
        }
        );

        // ── Ticketing (all authenticated users) ───────────────────────────────
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [TicketController::class , 'index'])->name('index');
            Route::get('/create', [TicketController::class , 'create'])->name('create');
            Route::post('/', [TicketController::class , 'store'])->name('store');
            Route::get('/{ticket}', [TicketController::class , 'show'])->name('show');
            Route::post('/{ticket}/assign', [TicketController::class , 'assign'])->name('assign');
            Route::post('/{ticket}/status', [TicketController::class , 'updateStatus'])->name('status');
            Route::post('/{ticket}/comments', [TicketController::class , 'comment'])->name('comment');
        }
        );

        // ── Admin-only routes ─────────────────────────────────────────────────
        Route::middleware('role:admin|super_admin')->prefix('admin')->name('admin.')->group(function () {

            // License Management
            Route::resource('licenses', LicenseController::class);

            Route::post('/licenses/{license}/toggle', [LicenseController::class , 'toggle'])->name('licenses.toggle');

            // VM Monitoring
            Route::prefix('vm-monitoring')->name('vm-monitoring.')->group(function () {
                    Route::get('/', [VmMonitorController::class , 'index'])->name('index');
                    Route::get('/{vm}', [VmMonitorController::class , 'show'])->name('show');
                }
                );

                // Storage Monitoring
                Route::prefix('storage')->name('storage.')->group(function () {
                    Route::get('/', [StorageController::class , 'index'])->name('index');
                    Route::get('/{storage}', [StorageController::class , 'show'])->name('show');
                }
                );

                // Analytics & Reports
                Route::get('/analytics', [AnalyticsController::class , 'index'])->name('analytics.index');
                Route::resource('users', UserController::class)->except(['show']);
            }
            );

            // ── RBAC: Role Management (super_admin only) ─────────────────────────
            Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
            Route::resource('roles', RoleController::class)->except(['show']);
        }
        );

        // ── Settings (all authenticated users) ───────────────────────────────
        Route::get('/settings', [SettingsController::class , 'index'])->name('settings.index');
        Route::patch('/settings/profile', [SettingsController::class , 'updateProfile'])->name('settings.profile');
        Route::put('/settings/password', [SettingsController::class , 'updatePassword'])->name('settings.password');
    });