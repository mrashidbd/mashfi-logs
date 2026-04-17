<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        $role = Auth::user()->role;
        if ($role === 'admin') {
            return redirect()->route('admin.welcome');
        }
        if ($role === 'surveyor') {
            return redirect()->route('surveyor.welcome');
        }
        if ($role === 'buyer') {
            return redirect()->route('buyer.welcome');
        }
    }

    return Inertia::render('Auth/Login', [
        'canResetPassword' => Route::has('password.request'),
        'status' => session('status'),
    ]);
});

Route::get('/dashboard', function () {
    $role = Auth::user()->role;
    if ($role === 'admin') {
        return redirect()->route('admin.welcome');
    }
    if ($role === 'surveyor') {
        return redirect()->route('surveyor.welcome');
    }
    if ($role === 'buyer') {
        return redirect()->route('buyer.welcome');
    }

    return Inertia::render('Dashboard'); // Fallback
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // User Management
    Route::resource('/admin/users', \App\Http\Controllers\UserController::class);

    // Admin Welcome
    Route::get('/admin/welcome', [\App\Http\Controllers\AdminController::class, 'welcome'])->name('admin.welcome');

    // Admin Vessel Management
    Route::get('/admin/vessels', [\App\Http\Controllers\VesselController::class, 'index'])->name('vessels.index');
    Route::post('/admin/vessels', [\App\Http\Controllers\VesselController::class, 'store'])->name('vessels.store');
    Route::get('/admin/vessels/{vessel}', [\App\Http\Controllers\VesselController::class, 'show'])->name('vessels.show');
    Route::put('/admin/vessels/{vessel}', [\App\Http\Controllers\VesselController::class, 'update'])->name('vessels.update');
    Route::delete('/admin/vessels/{vessel}', [\App\Http\Controllers\VesselController::class, 'destroy'])->name('vessels.destroy');
    Route::get('/admin/vessels/{vessel}/pdf', [\App\Http\Controllers\AdminController::class, 'downloadPdf'])->name('admin.vessel.pdf');

    // Log Management
    Route::post('/admin/vessels/{vessel}/logs', [\App\Http\Controllers\VesselController::class, 'storeLog'])->name('vessels.logs.store');
    Route::put('/admin/logs/{log}', [\App\Http\Controllers\VesselController::class, 'updateLog'])->name('logs.update');
    Route::delete('/admin/logs/{log}', [\App\Http\Controllers\VesselController::class, 'destroyLog'])->name('logs.destroy');

    // Admin Survey Data
    Route::get('/admin/survey-data', [\App\Http\Controllers\AdminController::class, 'surveyData'])->name('admin.survey-data');
    Route::get('/admin/survey-data/pdf', [\App\Http\Controllers\AdminController::class, 'surveyDataPdf'])->name('admin.survey-data.pdf');

    // Admin Reports
    Route::get('/admin/daily-report', [\App\Http\Controllers\AdminController::class, 'dailyReport'])->name('admin.daily-report');

    // Surveyor Routes
    Route::get('/surveyor/welcome', [\App\Http\Controllers\SurveyorController::class, 'welcome'])->name('surveyor.welcome');
    Route::get('/surveyor/search', [\App\Http\Controllers\SurveyorController::class, 'search'])->name('surveyor.search');
    Route::get('/surveyor/search/query', [\App\Http\Controllers\SurveyorController::class, 'searchQuery'])->name('surveyor.search.query');
    Route::get('/surveyor/dashboard', [\App\Http\Controllers\SurveyorController::class, 'dashboard'])->name('surveyor.dashboard');
    Route::get('/surveyor/log/{log}', [\App\Http\Controllers\SurveyorController::class, 'show'])->name('surveyor.show');
    Route::post('/surveyor/inspection/{log}', [\App\Http\Controllers\SurveyorController::class, 'store'])->name('inspections.store');

    // Buyer Routes
    Route::get('/buyer/welcome', [\App\Http\Controllers\BuyerController::class, 'welcome'])->name('buyer.welcome');
    Route::get('/buyer/dashboard', [\App\Http\Controllers\BuyerController::class, 'dashboard'])->name('buyer.dashboard');
    Route::get('/buyer/inventory', [\App\Http\Controllers\BuyerController::class, 'index'])->name('buyer.index');
    Route::get('/buyer/shipment/{vessel}', [\App\Http\Controllers\BuyerController::class, 'show'])->name('buyer.show');
    Route::get('/buyer/shipment/{vessel}/pdf', [\App\Http\Controllers\BuyerController::class, 'downloadPdf'])->name('buyer.pdf');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
