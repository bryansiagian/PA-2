<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\DrugController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\CmsController;

// ROUTE PUBLIK (Tanpa Login)
Route::get('/public/landing-page', [CmsController::class, 'getLandingPageData']);
Route::get('/public/drugs', [DrugController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {

    // --- MANAJEMEN USER (ADMIN & OPERATOR) ---
    // Sesuai revisi dosen: Operator sekarang bisa verifikasi user
    Route::middleware(['permission:manage users'])->group(function () {
        Route::get('/users/pending', [AdminController::class, 'getPendingUsers']);
        Route::post('/users/{id}/approve', [AdminController::class, 'approveUser']);
        Route::post('/users/{id}/reject', [AdminController::class, 'rejectUser']);
    });

    // Khusus Admin (Full CRUD User)
    Route::middleware(['role:admin'])->group(function () {

        Route::get('/cms/posts', [CmsController::class, 'indexPosts']);
        Route::post('/cms/posts', [CmsController::class, 'storePost']);
        Route::delete('/cms/posts/{id}', [CmsController::class, 'deletePost']);
        Route::put('/cms/profile', [CmsController::class, 'updateProfile']);
        Route::get('/cms/org', [CmsController::class, 'indexOrg']);
        Route::post('/cms/org', [CmsController::class, 'storeOrg']);

        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/users/{id}', [AdminController::class, 'showUser']);
        Route::post('/users', [AdminController::class, 'storeUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser']);
        Route::get('/admin/logs', [AdminController::class, 'getLogs']);
        Route::get('/roles', [AdminController::class, 'getRoles']);
        Route::get('/admin/analytics', [AdminController::class, 'getAnalytics']);
    });

    // --- MANAJEMEN OBAT & STOK (ADMIN & OPERATOR) ---
    Route::middleware(['permission:manage inventory'])->group(function () {
        Route::post('/drugs', [DrugController::class, 'store']);
        Route::put('/drugs/{id}', [DrugController::class, 'update']);
        Route::delete('/drugs/{id}', [DrugController::class, 'destroy']);
        Route::post('/drugs/stock-in', [DrugController::class, 'updateStock']);

        // Manajemen Kategori
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    });

    // Akses Publik Terotentikasi (Obat & Kategori)
    Route::get('/drugs', [DrugController::class, 'index']);
    Route::get('/drugs/{id}', [DrugController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    // --- KERANJANG (KHUSUS CUSTOMER) ---
    Route::middleware(['role:customer'])->group(function () {
        Route::get('/cart', [CartApiController::class, 'index']);
        Route::post('/cart', [CartApiController::class, 'store']);
        Route::put('/cart/{id}', [CartApiController::class, 'update']);
        Route::delete('/cart/{id}', [CartApiController::class, 'destroy']);
        Route::delete('/cart-clear', [CartApiController::class, 'clear']);
    });

    // --- MANAJEMEN REQUEST (CUSTOMER & OPERATOR) ---
    Route::get('/requests', [RequestController::class, 'index']);
    Route::get('/requests/{id}', [RequestController::class, 'show']);
    Route::post('/requests', [RequestController::class, 'store'])->middleware('role:customer');
    Route::post('/requests/{id}/cancel', [RequestController::class, 'cancel'])->middleware('role:customer');

    // Approval & Siapkan Pengiriman (Operator/Admin)
    Route::middleware(['permission:manage inventory'])->group(function () {
        Route::post('/requests/{id}/approve', [RequestController::class, 'approve']);
        Route::post('/requests/{id}/reject', [RequestController::class, 'reject']);
        Route::post('/deliveries/ready/{id}', [DeliveryController::class, 'makeReady']);
    });

    // --- MANAJEMEN PENGIRIMAN (COURIER) ---
    Route::middleware(['role:courier'])->group(function () {
        Route::get('/courier/stats', [DeliveryController::class, 'getCourierStats']);
        Route::get('/courier/history', [DeliveryController::class, 'getCourierHistory']);
        Route::get('/deliveries/available', [DeliveryController::class, 'getAvailableDeliveries']);
        Route::get('/deliveries/active', [DeliveryController::class, 'getActiveDeliveries']);
        Route::post('/deliveries/claim/{id}', [DeliveryController::class, 'claim']);
        Route::post('/deliveries/start/{id}', [DeliveryController::class, 'startShipping']);
        Route::post('/deliveries/complete/{id}', [DeliveryController::class, 'complete']);
    });

    // Umum (Bisa diakses untuk monitoring)
    Route::get('/deliveries/{id}/tracking', [DeliveryController::class, 'getTracking']);
});