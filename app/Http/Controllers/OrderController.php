<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items');

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('status')) {
            $query->where('match_status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                  ->orWhere('client_email', 'like', "%{$search}%")
                  ->orWhere('store', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('order_date', 'desc')
            ->paginate(25);

        $platforms = Order::distinct()->pluck('platform');

        return view('orders.index', compact('orders', 'platforms'));
    }

    public function show(Order $order)
    {
        $order->load('items', 'matches.transaction', 'aiSuggestions');

        return view('orders.show', compact('order'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|string|max:100',
            'platform' => 'required|string|max:50',
            'store' => 'nullable|string|max:100',
            'order_date' => 'required|date',
            'amount' => 'required|numeric',
            'original_amount' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'client_id' => 'nullable|string|max:100',
            'client_email' => 'nullable|email|max:255',
            'items' => 'nullable|array',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
        ]);

        $order = Order::create($validated);

        if ($request->has('items')) {
            foreach ($request->items as $item) {
                $order->items()->create($item);
            }
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order created successfully');
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'store' => 'nullable|string|max:100',
            'amount' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'match_status' => 'nullable|in:pending,matched,partial,unmatched',
        ]);

        $order->update($validated);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order updated successfully');
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', 'Order deleted successfully');
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.order_id' => 'required|string',
            'orders.*.platform' => 'required|string',
            'orders.*.order_date' => 'required|date',
            'orders.*.amount' => 'required|numeric',
        ]);

        $imported = 0;
        foreach ($request->orders as $data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $order = Order::create($data);

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            $imported++;
        }

        return response()->json([
            'message' => "Successfully imported {$imported} orders",
            'count' => $imported
        ]);
    }

    public function unmatched()
    {
        $orders = Order::where('match_status', 'pending')
            ->with('items')
            ->orderBy('order_date', 'desc')
            ->paginate(25);

        return view('orders.unmatched', compact('orders'));
    }
}
