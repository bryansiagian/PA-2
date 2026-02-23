<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\DrugController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\CmsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rute API untuk Sistem Manajemen Logistik E-Pharma.
| Menggunakan Laravel Sanctum untuk Autentikasi.
|
*/

// --- 1. RUTE PUBLIK (Bisa diakses tanpa login) ---
Route::get('/public/landing-page', [CmsController::class, 'getLandingPageData']);
Route::get('/public/drugs', [DrugController::class, 'index']);
Route::get('/public/files', function() {
    return \App\Models\GeneralFile::where('active', 1)->latest()->get();
});


// --- 2. RUTE TERPROTEKSI (Wajib Login) ---
Route::middleware('auth:sanctum')->group(function () {

    // A. MANAJEMEN USER & VERIFIKASI (Admin & Operator - Revisi Dosen #3)
    Route::middleware(['permission:manage users'])->group(function () {
        Route::get('/users/pending', [AdminController::class, 'getPendingUsers']);
        Route::post('/users/{id}/approve', [AdminController::class, 'approveUser']);
        Route::post('/users/{id}/reject', [AdminController::class, 'rejectUser']);
    });

    // B. KHUSUS ADMIN (Full System Control & CMS)
    Route::middleware(['role:admin'])->group(function () {
        // Master Users
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/users/{id}', [AdminController::class, 'showUser']);
        Route::post('/users', [AdminController::class, 'storeUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser']);
        Route::get('/roles', [AdminController::class, 'getRoles']);

        // Logs & Analytics
        Route::get('/admin/logs', [AdminController::class, 'getLogs']);
        Route::get('/admin/analytics', [AdminController::class, 'getAnalytics']);

        // CMS Content Management
        Route::put('/cms/profile', [CmsController::class, 'updateProfile']);
        Route::put('/cms/contact', [CmsController::class, 'updateContact']);
        Route::get('/cms/org', [CmsController::class, 'indexOrg']);
        Route::post('/cms/org', [CmsController::class, 'storeOrg']);

        // CMS Posts (Berita & Kegiatan)
        Route::get('/cms/posts', [CmsController::class, 'indexPosts']);
        Route::post('/cms/posts', [CmsController::class, 'storePost']);
        Route::get('/cms/posts/{id}', [CmsController::class, 'showPost']);
        Route::put('/cms/posts/{id}', [CmsController::class, 'updatePost']);
        Route::delete('/cms/posts/{id}', [CmsController::class, 'deletePost']);

        // CMS Post Categories
        Route::get('/cms/post-categories', [CmsController::class, 'indexPostCategories']);
        Route::post('/cms/post-categories', [CmsController::class, 'storePostCategory']);
        Route::put('/cms/post-categories/{id}', [CmsController::class, 'updatePostCategory']);
        Route::delete('/cms/post-categories/{id}', [CmsController::class, 'deletePostCategory']);
        Route::get('/cms/galleries', [CmsController::class, 'indexGalleries']);
        Route::post('/cms/galleries', [CmsController::class, 'storeGallery']);
        Route::delete('/cms/galleries/{id}', [CmsController::class, 'deleteGallery']);

        // CMS Contacts
        Route::get('/cms/contacts', [CmsController::class, 'indexContacts']);
        Route::post('/cms/contacts', [CmsController::class, 'storeContact']);
        Route::put('/cms/contacts/{id}', [CmsController::class, 'updateContact']);
        Route::delete('/cms/contacts/{id}', [CmsController::class, 'deleteContact']);

        // CMS General File
        Route::get('/cms/general-files', [CmsController::class, 'indexGeneralFiles']);
        Route::post('/cms/general-files', [CmsController::class, 'storeGeneralFile']);
        Route::delete('/cms/general-files/{id}', [CmsController::class, 'deleteGeneralFile']);
    });

    // C. MANAJEMEN INVENTARIS (Admin & Operator)
    Route::middleware(['permission:manage inventory'])->group(function () {
        // Drugs CRUD
        Route::post('/drugs', [DrugController::class, 'store']);
        Route::put('/drugs/{id}', [DrugController::class, 'update']);
        Route::delete('/drugs/{id}', [DrugController::class, 'destroy']);
        Route::post('/drugs/stock-in', [DrugController::class, 'updateStock']);

        // Medicine Categories CRUD
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // Transactional Approval
        Route::post('/requests/{id}/approve', [RequestController::class, 'approve']);
        Route::post('/requests/{id}/reject', [RequestController::class, 'reject']);
        Route::post('/deliveries/ready/{id}', [DeliveryController::class, 'makeReady']);
    });

    // D. KHUSUS CUSTOMER (Cart & Orders)
    Route::middleware(['role:customer'])->group(function () {
        // Shopping Cart
        Route::get('/cart', [CartApiController::class, 'index']);
        Route::post('/cart', [CartApiController::class, 'store']);
        Route::put('/cart/{id}', [CartApiController::class, 'update']);
        Route::delete('/cart/{id}', [CartApiController::class, 'destroy']);
        Route::delete('/cart-clear', [CartApiController::class, 'clear']);

        // Request Process
        Route::post('/requests', [RequestController::class, 'store']);
        Route::post('/requests/{id}/cancel', [RequestController::class, 'cancel']);
    });

    // E. KHUSUS COURIER (Logistics)
    Route::middleware(['role:courier'])->group(function () {
        Route::get('/courier/stats', [DeliveryController::class, 'getCourierStats']);
        Route::get('/courier/history', [DeliveryController::class, 'getCourierHistory']);
        Route::get('/deliveries/available', [DeliveryController::class, 'getAvailableDeliveries']);
        Route::get('/deliveries/active', [DeliveryController::class, 'getActiveDeliveries']);
        Route::post('/deliveries/claim/{id}', [DeliveryController::class, 'claim']);
        Route::post('/deliveries/start/{id}', [DeliveryController::class, 'startShipping']);
        Route::post('/deliveries/complete/{id}', [DeliveryController::class, 'complete']);
    });

    // F. AKSES UMUM TERAUTENTIKASI (Common Routes)
    Route::get('/drugs', [DrugController::class, 'index']);
    Route::get('/drugs/{id}', [DrugController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/requests', [RequestController::class, 'index']);
    Route::get('/requests/{id}', [RequestController::class, 'show']);
    Route::get('/deliveries/{id}/tracking', [DeliveryController::class, 'getTracking']);
    Route::get('/post-categories', [CmsController::class, 'indexPostCategories']); // Dropdown akses
});