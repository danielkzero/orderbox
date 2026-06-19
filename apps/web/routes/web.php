<?php

use App\Http\Controllers\AdminModuleController;
use App\Http\Controllers\ApiClientController;
use App\Http\Controllers\CatalogCrudController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrderActionController;
use App\Http\Controllers\ProductPriceTableController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/shared/orders/{order}/pdf', [OrderActionController::class, 'sharedPdf'])
    ->middleware(['signed', 'throttle:60,1'])
    ->name('orders.pdf.shared');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'active.session', 'company.context', 'verified'])
    ->name('dashboard');
Route::get('/dashboard/export', [DashboardController::class, 'export'])
    ->middleware(['auth', 'active.session', 'company.context', 'verified', 'throttle:export'])
    ->name('dashboard.export');

Route::middleware(['auth', 'active.session', 'company.context'])->group(function () {
    Route::get('/locations/states', [LocationController::class, 'states'])->middleware('throttle:60,1')->name('locations.states');
    Route::get('/locations/states/{state}/municipalities', [LocationController::class, 'municipalities'])->middleware('throttle:60,1')->name('locations.municipalities');
    Route::get('/locations/zip-codes/{zipCode}', [LocationController::class, 'zipCode'])->middleware('throttle:60,1')->name('locations.zip-codes');
    Route::get('/customers', [AdminModuleController::class, 'customers'])->name('customers.index');
    Route::get('/products', [AdminModuleController::class, 'products'])->name('products.index');
    Route::post('/products/price-tables', [ProductPriceTableController::class, 'store'])
        ->middleware('throttle:sensitive-write')
        ->name('products.price-tables.store');
    Route::patch('/products/price-tables/{priceTable}', [ProductPriceTableController::class, 'update'])
        ->middleware('throttle:sensitive-write')
        ->name('products.price-tables.update');
    Route::get('/representatives', [AdminModuleController::class, 'representatives'])->name('representatives.index');
    Route::get('/orders', [AdminModuleController::class, 'orders'])->name('orders.index');
    Route::get('/payment-methods', [AdminModuleController::class, 'paymentMethods'])->name('payment-methods.index');
    Route::get('/payment-terms', [AdminModuleController::class, 'paymentTerms'])->name('payment-terms.index');
    Route::get('/categories', [AdminModuleController::class, 'categories'])->name('categories.index');
    Route::get('/brands', [AdminModuleController::class, 'brands'])->name('brands.index');
    Route::get('/units', [AdminModuleController::class, 'units'])->name('units.index');
    Route::get('/regions', [AdminModuleController::class, 'regions'])->name('regions.index');
    Route::get('/audit-logs', [AdminModuleController::class, 'auditLogs'])->name('audit-logs.index');
    Route::get('/manual', [DocumentationController::class, 'manual'])->name('manual.index');
    Route::get('/api-guide', [DocumentationController::class, 'apiGuide'])->name('api-guide.index');
    Route::get('/crud/{resource}/create', [CatalogCrudController::class, 'create'])->name('crud.create');
    Route::post('/crud/{resource}', [CatalogCrudController::class, 'store'])->name('crud.store');
    Route::get('/crud/{resource}/{id}/edit', [CatalogCrudController::class, 'edit'])->name('crud.edit');
    Route::put('/crud/{resource}/{id}', [CatalogCrudController::class, 'update'])->name('crud.update');
    Route::post('/crud/{resource}/{id}/deactivate', [CatalogCrudController::class, 'deactivate'])->name('crud.deactivate');
    Route::post('/orders/{order}/send', [CatalogCrudController::class, 'sendOrder'])->middleware('throttle:sensitive-write')->name('orders.send');
    Route::post('/orders/{order}/cancel', [CatalogCrudController::class, 'cancelOrder'])->middleware('throttle:sensitive-write')->name('orders.cancel');
    Route::get('/orders/{order}/view', [OrderActionController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/pdf', [OrderActionController::class, 'pdf'])->name('orders.pdf');
    Route::post('/orders/{order}/email', [OrderActionController::class, 'email'])->middleware('throttle:sensitive-write')->name('orders.email');
    Route::post('/orders/{order}/whatsapp', [OrderActionController::class, 'whatsapp'])->middleware('throttle:sensitive-write')->name('orders.whatsapp');
    Route::get('/orders/{order}/history', [OrderActionController::class, 'history'])->name('orders.history');
    Route::post('/orders/{order}/duplicate', [OrderActionController::class, 'duplicate'])->middleware('throttle:sensitive-write')->name('orders.duplicate');
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
