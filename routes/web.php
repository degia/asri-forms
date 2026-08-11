<?php

use App\Http\Controllers\ExportPdfController;
use App\Http\Controllers\FormExportController;
use App\Http\Controllers\UserExportController;
use App\Http\Controllers\SiteExportController;
use App\Http\Controllers\AssetExportController;
use App\Http\Controllers\EmployeeExportController;
use App\Http\Controllers\StructureOrganizationExportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', fn () => redirect()->route('login'));

Route::post('logout', function () {
    Auth::guard('web')->logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/login');
})->middleware('auth')->name('logout');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', fn () => auth()->user()->hasAnyRole(['admin', 'manager_it'])
        ? redirect()->route('admin.dashboard')
        : redirect()->route('forms.search'))
        ->name('dashboard');

    Route::view('profile', 'profile')
        ->name('profile');

    Volt::route('pemeriksaan/create', 'pages.pemeriksaan.create')
        ->name('pemeriksaan.create');

    Volt::route('perawatan/create', 'pages.perawatan.create')
        ->name('perawatan.create');

    Volt::route('pemeriksaan/{id}/signature', 'pages.pemeriksaan.signature')
        ->name('pemeriksaan.signature');

    Volt::route('perawatan/{id}/signature', 'pages.perawatan.signature')
        ->name('perawatan.signature');

    Volt::route('approval/{type}/{id}', 'pages.approval.show')
        ->name('approval.show');

    Volt::route('assets', 'pages.assets.index')
        ->name('assets.index');

    Volt::route('assets/{id}/edit', 'pages.assets.edit')
        ->middleware('role:admin|teknisi')
        ->name('assets.edit');

    Volt::route('assets/{id}', 'pages.assets.show')
        ->name('assets.show');

    Volt::route('forms', 'pages.forms.search')
        ->name('forms.search');

    Route::get('pemeriksaan/{id}/export-pdf', [ExportPdfController::class, 'pemeriksaan'])
        ->name('pemeriksaan.export-pdf');

    Route::get('perawatan/{id}/export-pdf', [ExportPdfController::class, 'perawatan'])
        ->name('perawatan.export-pdf');

    // ── Admin Panel ──────────────────────────────────────────
    Route::middleware('role:admin|manager_it')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'))
            ->name('index');

        Route::view('dashboard', 'admin-dashboard')
            ->name('dashboard');

        // Sites
        Volt::route('sites', 'admin.pages.sites.index')
            ->middleware('role:admin')
            ->name('sites.index');

        Volt::route('sites/create', 'admin.pages.sites.create')
            ->middleware('role:admin')
            ->name('sites.create');

        Volt::route('sites/{idSite}/edit', 'admin.pages.sites.edit')
            ->middleware('role:admin')
            ->name('sites.edit');

        Volt::route('sites/import', 'admin.pages.sites.import')
            ->middleware('role:admin')
            ->name('sites.import');

        Route::get('sites/export/{format}', [SiteExportController::class, 'export'])
            ->middleware('role:admin')
            ->whereIn('format', ['pdf', 'xlsx', 'xls', 'csv', 'html'])
            ->name('sites.export');

        Route::get('sites/import/template', [SiteExportController::class, 'template'])
            ->middleware('role:admin')
            ->name('sites.import.template');

        // Assets
        Volt::route('assets', 'admin.pages.assets.index')
            ->name('assets.index');

        Volt::route('assets/create', 'admin.pages.assets.create')
            ->middleware('role:admin')
            ->name('assets.create');

        Volt::route('assets/{id}/edit', 'admin.pages.assets.edit')
            ->middleware('role:admin')
            ->name('assets.edit');

        Volt::route('assets/import', 'admin.pages.assets.import')
            ->middleware('role:admin')
            ->name('assets.import');

        Route::get('assets/export/{format}', [AssetExportController::class, 'export'])
            ->whereIn('format', ['pdf', 'xlsx', 'xls', 'csv', 'html'])
            ->name('assets.export');

        Route::get('assets/import/template', [AssetExportController::class, 'template'])
            ->middleware('role:admin')
            ->name('assets.import.template');

        // Users
        Volt::route('users', 'admin.pages.users.index')
            ->middleware('role:admin')
            ->name('users.index');

        Volt::route('users/create', 'admin.pages.users.create')
            ->middleware('role:admin')
            ->name('users.create');

        Volt::route('users/{userEmail}/edit', 'admin.pages.users.edit')
            ->middleware('role:admin')
            ->name('users.edit');

        Volt::route('users/import', 'admin.pages.users.import')
            ->middleware('role:admin')
            ->name('users.import');

        Route::get('users/export/{format}', [UserExportController::class, 'export'])
            ->middleware('role:admin')
            ->whereIn('format', ['pdf', 'xlsx', 'xls', 'csv', 'html'])
            ->name('users.export');

        Route::get('users/import/template', [UserExportController::class, 'template'])
            ->middleware('role:admin')
            ->name('users.import.template');

        // Employees
        Volt::route('employees', 'admin.pages.employees.index')
            ->name('employees.index');

        Volt::route('employees/create', 'admin.pages.employees.create')
            ->middleware('role:admin')
            ->name('employees.create');

        Volt::route('employees/{nik}/edit', 'admin.pages.employees.edit')
            ->middleware('role:admin')
            ->name('employees.edit');

        Volt::route('employees/import', 'admin.pages.employees.import')
            ->middleware('role:admin')
            ->name('employees.import');

        Route::get('employees/export/{format}', [EmployeeExportController::class, 'export'])
            ->whereIn('format', ['pdf', 'xlsx', 'xls', 'csv', 'html'])
            ->name('employees.export');

        Route::get('employees/import/template', [EmployeeExportController::class, 'template'])
            ->middleware('role:admin')
            ->name('employees.import.template');

        // Structure Organization
        Volt::route('structure-organization', 'admin.pages.structure-organization.index')
            ->middleware('role:admin')
            ->name('structure-organization.index');

        Volt::route('structure-organization/{type}/import', 'admin.pages.structure-organization.import')
            ->middleware('role:admin')
            ->whereIn('type', ['directorate', 'divisi', 'departement', 'sub_departement', 'position'])
            ->name('structure-organization.import');

        Route::get('structure-organization/{type}/export/{format}', [StructureOrganizationExportController::class, 'export'])
            ->middleware('role:admin')
            ->whereIn('type', ['directorate', 'divisi', 'departement', 'sub_departement', 'position'])
            ->whereIn('format', ['pdf', 'xlsx', 'xls', 'csv', 'html'])
            ->name('structure-organization.export');

        Route::get('structure-organization/{type}/import/template', [StructureOrganizationExportController::class, 'template'])
            ->middleware('role:admin')
            ->whereIn('type', ['directorate', 'divisi', 'departement', 'sub_departement', 'position'])
            ->name('structure-organization.import.template');

        // Form Pemeriksaan (PMR)
        Volt::route('pemeriksaan', 'admin.pages.pemeriksaan.index')
            ->name('pemeriksaan.index');

        Route::get('pemeriksaan/export/{format}', [FormExportController::class, 'exportPemeriksaan'])
            ->name('pemeriksaan.export')
            ->whereIn('format', ['pdf', 'xlsx', 'xls', 'html', 'csv']);

        // Form Perawatan (PWT)
        Volt::route('perawatan', 'admin.pages.perawatan.index')
            ->name('perawatan.index');

        Route::get('perawatan/export/{format}', [FormExportController::class, 'exportPerawatan'])
            ->name('perawatan.export')
            ->whereIn('format', ['pdf', 'xlsx', 'xls', 'html', 'csv']);

        // Form Pengembalian Asset (PNG)
        Volt::route('pengembalian', 'admin.pages.pengembalian.index')
            ->name('pengembalian.index');

        Volt::route('pengembalian/create', 'admin.pages.pengembalian.create')
            ->middleware('role:admin')
            ->name('pengembalian.create');

        // Backup
        Volt::route('backup', 'admin.pages.backup.index')
            ->middleware('role:admin')
            ->name('backup.index');

        Route::get('backup/download/{filename}', function (string $filename) {
            $path = storage_path('app/backups/' . basename($filename));
            if (!file_exists($path)) {
                abort(404);
            }
            return response()->download($path);
        })->middleware('role:admin')->name('backup.download');

        // Activity Log
        Volt::route('activity-log', 'admin.pages.activity-log.index')
            ->name('activity-log.index');

        // System Log
        Volt::route('system-log', 'admin.pages.system-log.index')
            ->middleware('role:admin')
            ->name('system-log.index');
    });

    // ── Legacy user routes → redirect to admin ───────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('users', fn () => redirect()->route('admin.users.index'))
            ->name('users.index');

        Route::get('users/create', fn () => redirect()->route('admin.users.create'))
            ->name('users.create');

        Route::get('users/{id}/edit', fn ($id) => redirect()->route('admin.users.edit', $id))
            ->name('users.edit');

        Route::get('users/import', fn () => redirect()->route('admin.users.import'))
            ->name('users.import');
    });
});

require __DIR__.'/auth.php';
