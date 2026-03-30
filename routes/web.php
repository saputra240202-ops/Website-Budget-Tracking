<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SavingController;

// Auth routes (dari Breeze)
require __DIR__ . '/auth.php';

// Protected routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class , 'index'])->name('dashboard');

    // Transactions
    Route::get('/transactions', [TransactionController::class , 'index'])->name('transactions.index');
    Route::post('/transactions', [TransactionController::class , 'store'])->name('transactions.store');
    Route::put('/transactions/{transaction}', [TransactionController::class , 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class , 'destroy'])->name('transactions.destroy');

    // Categories
    Route::get('/categories', [CategoryController::class , 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class , 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class , 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class , 'destroy'])->name('categories.destroy');

    // Reports
    Route::get('/reports', [ReportsController::class , 'index'])->name('reports.index');

    // Budget
    Route::get('/budget', [BudgetController::class , 'index'])->name('budget.index');
    Route::post('/budget', [BudgetController::class , 'store'])->name('budget.store');
    Route::put('/budget/{budget}', [BudgetController::class , 'update'])->name('budget.update');
    Route::delete('/budget/{budget}', [BudgetController::class , 'destroy'])->name('budget.destroy');

    // Savings
    Route::get('/savings', [SavingController::class , 'index'])->name('savings.index');
    Route::post('/savings', [SavingController::class , 'store'])->name('savings.store');
    Route::put('/savings/{saving}', [SavingController::class , 'update'])->name('savings.update');
    Route::delete('/savings/{saving}', [SavingController::class , 'destroy'])->name('savings.destroy');
    Route::post('/savings/{saving}/deposit', [SavingController::class , 'deposit'])->name('savings.deposit');
    Route::post('/savings/{saving}/withdraw', [SavingController::class , 'withdraw'])->name('savings.withdraw');
    Route::get('/savings/{saving}/deposits', [SavingController::class , 'deposits'])->name('savings.deposits');

    // Settings
    Route::get('/settings', [SettingsController::class , 'index'])->name('settings.index');
    Route::put('/settings/profile', [SettingsController::class , 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [SettingsController::class , 'updatePassword'])->name('settings.password');

});