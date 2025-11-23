@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800">Order #{{ $order->order_id }}</h1>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <label class="text-sm text-gray-500">Platform</label>
                <div class="font-medium">{{ $order->platform }}</div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Date</label>
                <div class="font-medium">{{ $order->order_date->format('M d, Y') }}</div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Amount</label>
                <div class="text-xl font-bold">${{ number_format($order->amount, 2) }}</div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Discount</label>
                <div class="font-medium {{ $order->discount > 0 ? 'text-red-600' : '' }}">
                    @if($order->discount > 0)
                        -${{ number_format($order->discount, 2) }}
                    @else
                        None
                    @endif
                </div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Store</label>
                <div class="font-medium">{{ $order->store ?? '-' }}</div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Status</label>
                <div>
                    @switch($order->match_status)
                        @case('matched')
                            <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Matched</span>
                            @break
                        @case('pending')
                            <span class="px-2 py-1 text-xs font-medium rounded bg-yellow-100 text-yellow-800">Pending</span>
                            @break
                        @default
                            <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">{{ $order->match_status }}</span>
                    @endswitch
                </div>
            </div>
            @if($order->client_email)
            <div>
                <label class="text-sm text-gray-500">Client Email</label>
                <div class="font-medium">{{ $order->client_email }}</div>
            </div>
            @endif
        </div>

        @if($order->items->count())
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-3">Order Items</h3>
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Item</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Qty</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Price</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($order->items as $item)
                    <tr>
                        <td class="px-4 py-2 text-sm">{{ $item->item_name }}</td>
                        <td class="px-4 py-2 text-sm">{{ $item->quantity }}</td>
                        <td class="px-4 py-2 text-sm">${{ number_format($item->price, 2) }}</td>
                        <td class="px-4 py-2 text-sm font-medium">${{ number_format($item->getTotal(), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($order->matches->count())
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-3">Matches</h3>
            <div class="space-y-2">
                @foreach($order->matches as $match)
                <div class="p-3 border rounded flex justify-between items-center">
                    <div>
                        <span class="font-medium">Transaction #{{ $match->transaction_id }}</span>
                        <span class="text-gray-500 ml-2">({{ $match->match_type }})</span>
                        @if($match->verified)
                            <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded">Verified</span>
                        @endif
                    </div>
                    <a href="{{ route('matches.show', $match) }}" class="text-blue-600 hover:text-blue-800 text-sm">View Match</a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
        <a href="{{ route('orders.index') }}" class="text-gray-600 hover:text-gray-800">Back to list</a>
        <form method="POST" action="{{ route('orders.destroy', $order) }}" onsubmit="return confirm('Are you sure?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
        </form>
    </div>
</div>
@endsection
