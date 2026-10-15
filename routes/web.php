<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/reservar', [BookingController::class, 'create'])->name('booking.create');
    Route::get('/reservar/disponibilidad', [BookingController::class, 'availability'])->name('booking.availability');
    Route::post('/reservar', [BookingController::class, 'store'])->name('booking.store');

    Route::get('/mis-citas', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::patch('/mis-citas/{appointment}/cancelar', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');
    Route::get('/agenda', Admin\AgendaController::class)->name('agenda');

    Route::get('/citas', [Admin\AppointmentController::class, 'index'])->name('appointments.index');
    Route::patch('/citas/{appointment}/estado', [Admin\AppointmentController::class, 'updateStatus'])->name('appointments.status');

    Route::resource('servicios', Admin\ServiceController::class)
        ->except('show')
        ->parameters(['servicios' => 'service'])
        ->names('services');

    Route::resource('estilistas', Admin\StylistController::class)
        ->except('show')
        ->parameters(['estilistas' => 'stylist'])
        ->names('stylists');
});

require __DIR__.'/auth.php';
