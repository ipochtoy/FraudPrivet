<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MatchingRuleController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Transactions
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
Route::get('/transactions/needs-attention', [TransactionController::class, 'needsAttention'])->name('transactions.needs-attention');
Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
Route::post('/transactions/bulk-import', [TransactionController::class, 'bulkImport'])->name('transactions.bulk-import');

// Orders
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/unmatched', [OrderController::class, 'unmatched'])->name('orders.unmatched');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
Route::post('/orders/bulk-import', [OrderController::class, 'bulkImport'])->name('orders.bulk-import');

// Matches
Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
Route::get('/matches/unverified', [MatchController::class, 'unverified'])->name('matches.unverified');
Route::post('/matches', [MatchController::class, 'store'])->name('matches.store');
Route::get('/matches/{match}', [MatchController::class, 'show'])->name('matches.show');
Route::post('/matches/{match}/verify', [MatchController::class, 'verify'])->name('matches.verify');
Route::post('/matches/{match}/reject', [MatchController::class, 'reject'])->name('matches.reject');

// AI Suggestions
Route::post('/suggestions/{suggestion}/accept', [MatchController::class, 'acceptSuggestion'])->name('suggestions.accept');
Route::post('/suggestions/{suggestion}/reject', [MatchController::class, 'rejectSuggestion'])->name('suggestions.reject');

// Import
Route::get('/import', [ImportController::class, 'index'])->name('import.index');
Route::post('/import/transactions', [ImportController::class, 'importTransactions'])->name('import.transactions');
Route::post('/import/orders', [ImportController::class, 'importOrders'])->name('import.orders');
Route::post('/import/auto-match', [ImportController::class, 'runAutoMatch'])->name('import.auto-match');

// Matching Rules
Route::get('/rules', [MatchingRuleController::class, 'index'])->name('rules.index');
Route::get('/rules/create', [MatchingRuleController::class, 'create'])->name('rules.create');
Route::post('/rules', [MatchingRuleController::class, 'store'])->name('rules.store');
Route::get('/rules/{rule}', [MatchingRuleController::class, 'show'])->name('rules.show');
Route::get('/rules/{rule}/edit', [MatchingRuleController::class, 'edit'])->name('rules.edit');
Route::put('/rules/{rule}', [MatchingRuleController::class, 'update'])->name('rules.update');
Route::delete('/rules/{rule}', [MatchingRuleController::class, 'destroy'])->name('rules.destroy');
Route::post('/rules/{rule}/toggle', [MatchingRuleController::class, 'toggle'])->name('rules.toggle');
