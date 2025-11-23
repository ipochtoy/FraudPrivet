<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReconciliationController;

// Stats
Route::get('/stats', [ReconciliationController::class, 'stats']);

// Transactions
Route::get('/transactions', [ReconciliationController::class, 'transactions']);
Route::post('/transactions/import', [ReconciliationController::class, 'importTransactions']);
Route::get('/transactions/{transaction}/matches', [ReconciliationController::class, 'findMatches']);
Route::get('/transactions/{transaction}/ai-suggest', [ReconciliationController::class, 'aiSuggest']);

// Orders
Route::get('/orders', [ReconciliationController::class, 'orders']);
Route::post('/orders/import', [ReconciliationController::class, 'importOrders']);

// Matching
Route::post('/matches', [ReconciliationController::class, 'createMatch']);
Route::post('/auto-match', [ReconciliationController::class, 'autoMatch']);
