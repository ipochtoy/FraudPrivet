<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\Match;
use App\Models\AISuggestion;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();

        // Real stats from database
        $stats = [
            'new_transactions' => Transaction::whereDate('created_at', $today)->count(),
            'auto_matched' => Transaction::where('match_status', 'matched')
                ->whereDate('updated_at', $today)->count(),
            'new_orders' => Order::whereDate('created_at', $today)->count(),
            'needs_attention' => Transaction::needsAttention()->count(),
            'unmatched_amount' => Transaction::needsAttention()->sum('amount')
        ];

        // Get unmatched transactions with AI suggestions
        $unmatchedTransactions = Transaction::needsAttention()
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get();

        $unmatched = $unmatchedTransactions->map(function ($transaction) {
            $suggestion = AISuggestion::where('transaction_id', $transaction->id)
                ->where('status', 'pending')
                ->orderBy('confidence', 'desc')
                ->first();

            return (object)[
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'vendor' => $transaction->vendor ?? 'Unknown',
                'date' => $transaction->transaction_date->format('M d'),
                'ai_suggestion' => $suggestion ? $suggestion->explanation : null
            ];
        });

        // Weekly stats
        $weeklyStats = [
            'matched' => Match::whereBetween('created_at', [$weekStart, now()])->count(),
            'total_transactions' => Transaction::whereBetween('created_at', [$weekStart, now()])->count(),
        ];

        return view('dashboard', compact('stats', 'unmatched', 'weeklyStats'));
    }
}
