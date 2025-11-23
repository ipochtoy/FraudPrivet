@extends('layouts.app')

@section('title', 'Match Details')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-800">Match #{{ $match->id }}</h1>
        @if(!$match->verified)
        <form method="POST" action="{{ route('matches.verify', $match) }}">
            @csrf
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-sm font-medium">
                Verify Match
            </button>
        </form>
        @endif
    </div>

    <div class="p-6">
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <label class="text-sm text-gray-500">Match Type</label>
                <div class="font-medium">{{ ucfirst($match->match_type) }}</div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Confidence</label>
                <div class="font-medium {{ $match->confidence >= 0.9 ? 'text-green-600' : ($match->confidence >= 0.7 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ number_format($match->confidence * 100, 0) }}%
                </div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Status</label>
                <div>
                    @if($match->verified)
                        <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Verified</span>
                        <span class="text-xs text-gray-500 ml-2">by {{ $match->verified_by }} on {{ $match->verified_at->format('M d, Y') }}</span>
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded bg-yellow-100 text-yellow-800">Unverified</span>
                    @endif
                </div>
            </div>
            @if($match->amount_difference != 0)
            <div>
                <label class="text-sm text-gray-500">Amount Difference</label>
                <div class="font-medium text-red-600">${{ number_format(abs($match->amount_difference), 2) }}</div>
            </div>
            @endif
        </div>

        <!-- Transaction -->
        @if($match->transaction)
        <div class="mb-6 p-4 border rounded">
            <h3 class="text-lg font-medium mb-3">Transaction</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="text-sm text-gray-500">Amount</label>
                    <div class="font-bold">${{ number_format($match->transaction->amount, 2) }}</div>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Date</label>
                    <div>{{ $match->transaction->transaction_date->format('M d, Y') }}</div>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Vendor</label>
                    <div>{{ $match->transaction->vendor ?? '-' }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Order -->
        <div class="mb-6 p-4 border rounded">
            <h3 class="text-lg font-medium mb-3">Order</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="text-sm text-gray-500">Order ID</label>
                    <div class="font-bold">{{ $match->order->order_id }}</div>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Amount</label>
                    <div class="font-bold">${{ number_format($match->order->amount, 2) }}</div>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Platform</label>
                    <div>{{ $match->order->platform }}</div>
                </div>
            </div>
            @if($match->order->items->count())
            <div class="mt-4">
                <label class="text-sm text-gray-500">Items</label>
                <ul class="mt-1 text-sm">
                    @foreach($match->order->items as $item)
                    <li>{{ $item->item_name }} x{{ $item->quantity }} - ${{ number_format($item->price, 2) }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        @if($match->ai_explanation)
        <div class="mb-6 p-4 bg-blue-50 rounded">
            <h3 class="text-sm font-medium text-blue-800 mb-2">AI Explanation</h3>
            <div class="text-blue-700">{{ $match->ai_explanation }}</div>
        </div>
        @endif

        @if($match->notes)
        <div class="mb-6">
            <label class="text-sm text-gray-500">Notes</label>
            <div class="mt-1 p-3 bg-gray-50 rounded">{{ $match->notes }}</div>
        </div>
        @endif
    </div>

    <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
        <a href="{{ route('matches.index') }}" class="text-gray-600 hover:text-gray-800">Back to list</a>
        @if(!$match->verified)
        <form method="POST" action="{{ route('matches.reject', $match) }}" onsubmit="return confirm('Reject this match?')">
            @csrf
            <button type="submit" class="text-red-600 hover:text-red-800">Reject Match</button>
        </form>
        @endif
    </div>
</div>
@endsection
