<?php

use App\Http\Controllers\AdminModuleController;
use App\Http\Controllers\ApiClientController;
use App\Http\Controllers\CatalogCrudController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'active.session', 'company.context', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'active.session', 'company.context'])->group(function () {
    Route::get('/customers', [AdminModuleController::class, 'customers'])->name('customers.index');
    Route::get('/products', [AdminModuleController::class, 'products'])->name('products.index');
    Route::get('/price-tables', [AdminModuleController::class, 'priceTables'])->name('price-tables.index');
    Route::get('/representatives', [AdminModuleController::class, 'representatives'])->name('representatives.index');
    Route::get('/orders', [AdminModuleController::class, 'orders'])->name('orders.index');
    Route::get('/categories', [AdminModuleController::class, 'categories'])->name('categories.index');
    Route::get('/brands', [AdminModuleController::class, 'brands'])->name('brands.index');
    Route::get('/units', [AdminModuleController::class, 'units'])->name('units.index');
    Route::get('/regions', [AdminModuleController::class, 'regions'])->name('regions.index');
    Route::get('/audit-logs', [AdminModuleController::class, 'auditLogs'])->name('audit-logs.index');
    Route::get('/manual', [DocumentationController::class, 'manual'])->name('manual.index');
    Route::get('/api-guide', [DocumentationController::class, 'apiGuide'])->name('api-guide.index');
    Route::post('/products/price-tables', [CatalogCrudController::class, 'storeProductPriceTable'])->name('products.price-tables.store');
    Route::patch('/products/price-tables/{priceTable}', [CatalogCrudController::class, 'updateProductPriceTable'])->name('products.price-tables.update');
    Route::get('/crud/{resource}/create', [CatalogCrudController::class, 'create'])->name('crud.create');
    Route::post('/crud/{resource}', [CatalogCrudController::class, 'store'])->name('crud.store');
    Route::get('/crud/{resource}/{id}/edit', [CatalogCrudController::class, 'edit'])->name('crud.edit');
    Route::put('/crud/{resource}/{id}', [CatalogCrudController::class, 'update'])->name('crud.update');
    Route::post('/crud/{resource}/{id}/deactivate', [CatalogCrudController::class, 'deactivate'])->name('crud.deactivate');
    Route::get('/api-clients', [ApiClientController::class, 'index'])->name('api-clients.index');
    Route::post('/api-clients', [ApiClientController::class, 'store'])->name('api-clients.store');
    Route::post('/api-clients/{apiClient}/regenerate', [ApiClientController::class, 'regenerate'])->name('api-clients.regenerate');
    Route::post('/api-clients/{apiClient}/deactivate', [ApiClientController::class, 'deactivate'])->name('api-clients.deactivate');
    Route::resource('/users', UserManagementController::class)->except(['show', 'destroy']);
    Route::post('/users/{user}/deactivate', [UserManagementController::class, 'deactivate'])->name('users.deactivate');
    Route::get('/security', [SecurityController::class, 'index'])->name('security.index');
    Route::post('/security/2fa', [SecurityController::class, 'enable'])->name('security.2fa.enable');
    Route::delete('/security/2fa', [SecurityController::class, 'disable'])->name('security.2fa.disable');
    Route::delete('/security/sessions/{authenticationSession}', [SecurityController::class, 'revoke'])->name('security.sessions.revoke');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
