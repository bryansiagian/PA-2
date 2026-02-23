<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\CmsController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Rute Root: Langsung cek autentikasi
Route::get('/', function () {
    return view('welcome');
});

// Guest Routes (Login & Register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout

Route::middleware(['auth'])->group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // VERIFIKASI USER (Admin & Operator - Revisi Dosen)
    Route::middleware(['permission:manage users'])->group(function() {
        Route::get('/admin/users/pending', function () { return view('admin.pending_users'); });
        Route::get('/reports', function() { return view('admin.reports'); })->name('reports');
    });

    // MANAJEMEN AKUN (Hanya Admin)
    Route::middleware(['role:admin'])->group(function() {
        Route::get('/admin/cms/post-categories', function() { return view('admin.cms.post_categories'); });
        Route::get('/admin/cms/profile', function() { return view('admin.cms.profile'); });
        Route::get('/admin/cms/posts', function() { return view('admin.cms.posts'); });
        Route::get('/admin/cms/org', function() { return view('admin.cms.org'); });
        Route::get('/admin/cms/gallery', function() { return view('admin.cms.gallery'); });
        Route::get('/admin/users', function() { return view('admin.users'); });
        Route::get('/admin/logs', function() { return view('admin.logs'); });
        Route::get('/admin/cms/contacts', function() { return view('admin.cms.contacts'); });
        Route::get('/admin/cms/files', function() { return view('admin.cms.general_files'); });
    });

    // INVENTORY (Operator & Admin)
    Route::middleware(['permission:manage inventory'])->group(function() {
        Route::get('/operator/drugs', function() { return view('operator.drugs'); });
        Route::get('/operator/categories', function() { return view('operator.categories'); });
        Route::get('/operator/requests', function() { return view('operator.requests'); });
        Route::get('/operator/tracking/{id}', function ($id) { return view('operator.tracking', ['id' => $id]); })->name('operator.tracking');
    });

    Route::middleware(['role:customer'])->group(function() {
        Route::get('/customer/requests', function() { return view('customer.requests'); });
        Route::get('/customer/history', function () { return view('customer.history'); })->name('customer.history');
        Route::get('/customer/cart', function() { return view('customer.cart'); })->name('customer.cart');
        Route::get('/customer/request-new', function() { return view('customer.manual_request'); })->name('customer.manual_request');
        Route::get('/customer/tracking/{id}', function ($id) { return view('customer.tracking', ['id' => $id]); })->name('customer.tracking');
    });

    Route::middleware(['role:courier'])->group(function() {
        Route::get('/courier/tasks', function() { return view('courier.tasks'); });
        Route::get('/courier/available', function () { return view('courier.available'); })->name('courier.available');
        Route::get('/courier/active', function () { return view('courier.active'); })->name('courier.active');
        Route::get('/courier/history', function () { return view('courier.history'); })->name('courier.history');
    });
});

