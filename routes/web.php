<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\GarageController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/service', [ServiceController::class, 'index'])->name('service.index');
Route::get('/garage', [GarageController::class, 'index'])->name('garage.index');
Route::get('/garage/{car}', [GarageController::class, 'show'])->name('garage.show');
Route::get('/testimoni', [TestimonialController::class, 'index'])->name('testimonial.index');
Route::get('/faq', [FAQController::class, 'index'])->name('faq.index');

// Authentication Routes (via Laravel Breeze)
Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');
});

// Protected Routes
Route::middleware('auth')->group(function () {
    // Booking Routes
    Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/my-bookings', [BookingController::class, 'userBookings'])->name('booking.user');
    Route::get('/booking/{booking}', [BookingController::class, 'show'])->name('booking.show');
    Route::post('/booking/{booking}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');

    // Voucher Routes
    Route::get('/claim-voucher', [VoucherController::class, 'claim'])->name('voucher.claim');
    Route::post('/voucher/{voucher}/claim', [VoucherController::class, 'store'])->name('voucher.store');
    Route::get('/my-vouchers', [VoucherController::class, 'myVouchers'])->name('voucher.my');

    // Profile Routes
    Route::view('/profile', 'profile.show')->name('profile.show');
    Route::post('/profile', 'ProfileController@update')->name('profile.update');

    // Garage Owner Routes
    Route::middleware('role:garage_owner')->group(function () {
        Route::get('/garage/dashboard', [GarageController::class, 'dashboard'])->name('garage.dashboard');
        Route::get('/garage/create', [GarageController::class, 'create'])->name('garage.create');
        Route::post('/garage', [GarageController::class, 'store'])->name('garage.store');
        Route::get('/garage/{car}/edit', [GarageController::class, 'edit'])->name('garage.edit');
        Route::put('/garage/{car}', [GarageController::class, 'update'])->name('garage.update');
        Route::delete('/garage/{car}', [GarageController::class, 'destroy'])->name('garage.destroy');
    });

    // Admin Routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', 'AdminController@dashboard')->name('admin.dashboard');
        Route::get('/admin/services', 'AdminController@services')->name('admin.services');
        Route::post('/admin/services', 'AdminController@storeService')->name('admin.service.store');
        Route::get('/admin/vouchers', 'AdminController@vouchers')->name('admin.vouchers');
        Route::post('/admin/vouchers', 'AdminController@storeVoucher')->name('admin.voucher.store');
        Route::get('/admin/testimonials', 'AdminController@testimonials')->name('admin.testimonials');
        Route::post('/admin/testimonials/{testimonial}/approve', 'AdminController@approveTestimonial')->name('admin.testimonial.approve');
    });
});

// Logout Route
Route::post('/logout', 'AuthController@logout')->name('logout')->middleware('auth');

require __DIR__.'/auth.php';
