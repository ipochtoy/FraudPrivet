<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Match;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\AISuggestion;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $query = Match::with(['transaction', 'order']);

        if ($request->filled('type')) {
            $query->where('match_type', $request->type);
        }

        if ($request->filled('verified')) {
            $query->where('verified', $request->verified === 'true');
        }

        $matches = $query->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('matches.index', compact('matches'));
    }

    public function show(Match $match)
    {
        $match->load('transaction', 'order.items');

        return view('matches.show', compact('match'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'match_type' => 'required|in:direct,split,discount,fuzzy,manual,ai_suggested',
            'order_id' => 'required|exists:orders,id',
            'transaction_id' => 'nullable|exists:transactions,id',
            'transaction_ids' => 'nullable|array',
            'transaction_ids.*' => 'exists:transactions,id',
            'confidence' => 'nullable|numeric|min:0|max:1',
            'amount_difference' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'discount_type' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $match = Match::create($validated);

        // Update transaction status
        if ($match->transaction_id) {
            Transaction::where('id', $match->transaction_id)
                ->update(['match_status' => 'matched']);
        }

        // Update split transaction statuses
        if (!empty($match->transaction_ids)) {
            Transaction::whereIn('id', $match->transaction_ids)
                ->update(['match_status' => 'matched']);
        }

        // Update order status
        Order::where('id', $match->order_id)
            ->update(['match_status' => 'matched']);

        return redirect()->route('matches.show', $match)
            ->with('success', 'Match created successfully');
    }

    public function verify(Request $request, Match $match)
    {
        $match->update([
            'verified' => true,
            'verified_by' => $request->user()->name ?? 'system',
            'verified_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Match verified successfully');
    }

    public function reject(Request $request, Match $match)
    {
        // Revert transaction status
        if ($match->transaction_id) {
            Transaction::where('id', $match->transaction_id)
                ->update(['match_status' => 'pending']);
        }

        if (!empty($match->transaction_ids)) {
            Transaction::whereIn('id', $match->transaction_ids)
                ->update(['match_status' => 'pending']);
        }

        // Revert order status
        Order::where('id', $match->order_id)
            ->update(['match_status' => 'pending']);

        $match->delete();

        return redirect()->route('matches.index')
            ->with('success', 'Match rejected and removed');
    }

    public function acceptSuggestion(Request $request, AISuggestion $suggestion)
    {
        $suggestion->accept($request->user()->name ?? 'system', $request->notes);

        // Create match from suggestion
        $matchData = $suggestion->suggestion;
        $matchData['ai_suggested'] = true;
        $matchData['ai_explanation'] = $suggestion->explanation;

        $match = Match::create($matchData);

        // Update statuses
        if (isset($matchData['transaction_id'])) {
            Transaction::where('id', $matchData['transaction_id'])
                ->update(['match_status' => 'matched']);
        }

        if (isset($matchData['order_id'])) {
            Order::where('id', $matchData['order_id'])
                ->update(['match_status' => 'matched']);
        }

        $suggestion->update(['feedback_applied' => true]);

        return redirect()->route('matches.show', $match)
            ->with('success', 'AI suggestion accepted and match created');
    }

    public function rejectSuggestion(Request $request, AISuggestion $suggestion)
    {
        $suggestion->reject($request->user()->name ?? 'system', $request->notes);

        return redirect()->back()
            ->with('success', 'AI suggestion rejected');
    }

    public function unverified()
    {
        $matches = Match::unverified()
            ->with(['transaction', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('matches.unverified', compact('matches'));
    }
}
