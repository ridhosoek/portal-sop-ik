<?php

use App\Http\Controllers\Admin\AuditTrailController;
use App\Http\Controllers\Admin\BrokenLinkReportController as AdminBrokenLinkReportController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\OrganizationStructureController as AdminOrganizationStructureController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentCatalogController;
use App\Http\Controllers\OrganizationStructureController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/documents', [DocumentCatalogController::class, 'index'])->name('documents.index');
    Route::get('/documents/{document}', [DocumentCatalogController::class, 'show'])->name('documents.show');
    Route::get('/documents/{document}/open', [DocumentCatalogController::class, 'open'])->name('documents.open');
    Route::post('/documents/{document}/broken-link-reports', [DocumentCatalogController::class, 'report'])->name('documents.broken-link-reports.store');
    Route::get('/organization-structure', [OrganizationStructureController::class, 'index'])->name('organization-structure.index');
    Route::get('/organization-structures/{organizationStructure}/file', [OrganizationStructureController::class, 'file'])->name('organization-structures.file');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:document-admin,super-admin,auditor')
        ->group(function (): void {
            Route::get('/', AdminDashboardController::class)->name('dashboard');
            Route::resource('documents', AdminDocumentController::class);
            Route::post('/documents/{document}/publish', [AdminDocumentController::class, 'publish'])->name('documents.publish');
            Route::post('/documents/{document}/archive', [AdminDocumentController::class, 'archive'])->name('documents.archive');
            Route::post('/documents/{document}/versions', [AdminDocumentController::class, 'createDraftVersion'])->name('documents.versions.store');
            Route::resource('organization-structures', AdminOrganizationStructureController::class)
                ->parameters(['organization-structures' => 'organizationStructure'])
                ->except(['destroy']);
            Route::post('/organization-structures/{organizationStructure}/archive', [AdminOrganizationStructureController::class, 'archive'])->name('organization-structures.archive');

            Route::get('/master-data', [MasterDataController::class, 'index'])->name('master-data.index');
            Route::post('/master-data/{type}', [MasterDataController::class, 'store'])->name('master-data.store');
            Route::patch('/master-data/{type}/{id}/toggle', [MasterDataController::class, 'toggle'])->name('master-data.toggle');

            Route::get('/broken-links', [AdminBrokenLinkReportController::class, 'index'])->name('broken-links.index');
            Route::patch('/broken-links/{report}/resolve', [AdminBrokenLinkReportController::class, 'resolve'])->name('broken-links.resolve');

            Route::get('/audit-trail', AuditTrailController::class)->name('audit.index');
            Route::get('/users', [UserRoleController::class, 'index'])->middleware('role:document-admin,super-admin')->name('users.index');
            Route::get('/users/create', [UserRoleController::class, 'create'])->middleware('role:document-admin,super-admin')->name('users.create');
            Route::post('/users', [UserRoleController::class, 'store'])->middleware('role:document-admin,super-admin')->name('users.store');
            Route::patch('/users/{user}', [UserRoleController::class, 'update'])->middleware('role:document-admin,super-admin')->name('users.update');
            Route::patch('/users/{user}/password', [UserRoleController::class, 'resetPassword'])->middleware('role:document-admin,super-admin')->name('users.password.update');

            Route::get('/settings', [SettingsController::class, 'index'])->middleware('role:super-admin')->name('settings.index');
            Route::patch('/settings', [SettingsController::class, 'update'])->middleware('role:super-admin')->name('settings.update');
        });
});
