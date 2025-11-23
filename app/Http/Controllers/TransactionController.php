<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\AISuggestion;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query();

        if ($request->filled('status')) {
            $query->where('match_status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vendor', 'like', "%{$search}%")
                  ->orWhere('memo', 'like', "%{$search}%")
                  ->orWhere('external_id', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->paginate(25);

        return view('transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load('matches.order', 'aiSuggestions');

        return view('transactions.show', compact('transaction'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'external_id' => 'nullable|string|max:100',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric',
            'vendor' => 'nullable|string|max:100',
            'vendor_raw' => 'nullable|string|max:255',
            'memo' => 'nullable|string',
            'category' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:50',
            'card_last4' => 'nullable|string|max:4',
        ]);

        $transaction = Transaction::create($validated);

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaction created successfully');
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'vendor' => 'nullable|string|max:100',
            'memo' => 'nullable|string',
            'category' => 'nullable|string|max:50',
            'match_status' => 'nullable|in:pending,matched,partial,unmatched',
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaction updated successfully');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully');
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'transactions' => 'required|array',
            'transactions.*.transaction_date' => 'required|date',
            'transactions.*.amount' => 'required|numeric',
        ]);

        $imported = 0;
        foreach ($request->transactions as $data) {
            Transaction::create($data);
            $imported++;
        }

        return response()->json([
            'message' => "Successfully imported {$imported} transactions",
            'count' => $imported
        ]);
    }

    public function needsAttention()
    {
        $transactions = Transaction::needsAttention()
            ->with(['aiSuggestions' => function ($query) {
                $query->where('status', 'pending')->orderBy('confidence', 'desc');
            }])
            ->orderBy('transaction_date', 'desc')
            ->paginate(25);

        return view('transactions.needs-attention', compact('transactions'));
    }
}
