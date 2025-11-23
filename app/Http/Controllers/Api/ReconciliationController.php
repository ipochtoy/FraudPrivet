<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\Match;
use App\Services\MatchingEngine;
use App\Services\AIService;

class ReconciliationController extends Controller
{
    /**
     * Get pending transactions
     */
    public function transactions(Request $request)
    {
        $query = Transaction::query();

        if ($request->filled('status')) {
            $query->where('match_status', $request->status);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->paginate($request->input('per_page', 25));

        return response()->json($transactions);
    }

    /**
     * Get pending orders
     */
    public function orders(Request $request)
    {
        $query = Order::with('items');

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('status')) {
            $query->where('match_status', $request->status);
        }

        $orders = $query->orderBy('order_date', 'desc')
            ->paginate($request->input('per_page', 25));

        return response()->json($orders);
    }

    /**
     * Find match candidates for a transaction
     */
    public function findMatches(Transaction $transaction)
    {
        $engine = new MatchingEngine();
        $candidates = $engine->findMatches($transaction);

        return response()->json([
            'transaction_id' => $transaction->id,
            'candidates' => $candidates,
        ]);
    }

    /**
     * Create a match
     */
    public function createMatch(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'order_id' => 'required|exists:orders,id',
            'match_type' => 'required|in:direct,split,discount,fuzzy,manual,ai_suggested',
            'confidence' => 'nullable|numeric|min:0|max:1',
            'notes' => 'nullable|string',
        ]);

        $transaction = Transaction::findOrFail($validated['transaction_id']);
        $order = Order::findOrFail($validated['order_id']);

        $match = Match::create([
            'match_type' => $validated['match_type'],
            'confidence' => $validated['confidence'] ?? 1.0,
            'order_id' => $order->id,
            'transaction_id' => $transaction->id,
            'amount_difference' => abs($transaction->amount - $order->amount),
            'notes' => $validated['notes'] ?? null,
        ]);

        $transaction->update(['match_status' => 'matched']);
        $order->update(['match_status' => 'matched']);

        return response()->json([
            'success' => true,
            'match' => $match->load('transaction', 'order'),
        ]);
    }

    /**
     * Run auto-matching
     */
    public function autoMatch(Request $request)
    {
        $minConfidence = (float) $request->input('min_confidence', 0.95);

        $engine = new MatchingEngine();
        $results = $engine->autoMatch($minConfidence);

        return response()->json($results);
    }

    /**
     * Get AI suggestion for transaction
     */
    public function aiSuggest(Transaction $transaction)
    {
        $engine = new MatchingEngine();
        $candidates = $engine->findMatches($transaction);

        if (empty($candidates)) {
            return response()->json([
                'suggestion' => null,
                'message' => 'No candidates found',
            ]);
        }

        $orders = Order::whereIn('id', array_column($candidates, 'order_id'))->get();

        $aiService = new AIService();
        $suggestion = $aiService->suggestMatches($transaction, $orders->all());

        return response()->json([
            'suggestion' => $suggestion,
            'candidates' => $candidates,
        ]);
    }

    /**
     * Import transactions
     */
    public function importTransactions(Request $request)
    {
        $validated = $request->validate([
            'transactions' => 'required|array',
            'transactions.*.transaction_date' => 'required|date',
            'transactions.*.amount' => 'required|numeric',
            'transactions.*.vendor' => 'nullable|string',
        ]);

        $imported = 0;
        foreach ($validated['transactions'] as $data) {
            Transaction::create(array_merge($data, [
                'source' => $request->input('source', 'api'),
                'match_status' => 'pending',
            ]));
            $imported++;
        }

        return response()->json([
            'success' => true,
            'imported' => $imported,
        ]);
    }

    /**
     * Import orders
     */
    public function importOrders(Request $request)
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*.order_id' => 'required|string',
            'orders.*.platform' => 'required|string',
            'orders.*.order_date' => 'required|date',
            'orders.*.amount' => 'required|numeric',
        ]);

        $imported = 0;
        foreach ($validated['orders'] as $data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $order = Order::create(array_merge($data, [
                'match_status' => 'pending',
            ]));

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            $imported++;
        }

        return response()->json([
            'success' => true,
            'imported' => $imported,
        ]);
    }

    /**
     * Get dashboard stats
     */
    public function stats()
    {
        return response()->json([
            'transactions' => [
                'total' => Transaction::count(),
                'pending' => Transaction::pending()->count(),
                'matched' => Transaction::matched()->count(),
                'unmatched' => Transaction::unmatched()->count(),
            ],
            'orders' => [
                'total' => Order::count(),
                'pending' => Order::pending()->count(),
                'matched' => Order::matched()->count(),
            ],
            'matches' => [
                'total' => Match::count(),
                'verified' => Match::verified()->count(),
                'unverified' => Match::unverified()->count(),
            ],
        ]);
    }
}
