@extends('layouts.app')

@section('title', 'Transaction Details')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800">Transaction #{{ $transaction->id }}</h1>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <label class="text-sm text-gray-500">Date</label>
                <div class="font-medium">{{ $transaction->transaction_date->format('M d, Y') }}</div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Amount</label>
                <div class="text-xl font-bold">${{ number_format($transaction->amount, 2) }}</div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Vendor</label>
                <div class="font-medium">{{ $transaction->vendor ?? '-' }}</div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Source</label>
                <div class="font-medium">{{ $transaction->source }}</div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Status</label>
                <div>
                    @switch($transaction->match_status)
                        @case('matched')
                            <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Matched</span>
                            @break
                        @case('pending')
                            <span class="px-2 py-1 text-xs font-medium rounded bg-yellow-100 text-yellow-800">Pending</span>
                            @break
                        @default
                            <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">{{ $transaction->match_status }}</span>
                    @endswitch
                </div>
            </div>
            <div>
                <label class="text-sm text-gray-500">External ID</label>
                <div class="font-medium">{{ $transaction->external_id ?? '-' }}</div>
            </div>
        </div>

        @if($transaction->memo)
        <div class="mb-6">
            <label class="text-sm text-gray-500">Memo</label>
            <div class="mt-1 p-3 bg-gray-50 rounded">{{ $transaction->memo }}</div>
        </div>
        @endif

        @if($transaction->matches->count())
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-3">Matches</h3>
            <div class="space-y-2">
                @foreach($transaction->matches as $match)
                <div class="p-3 border rounded flex justify-between items-center">
                    <div>
                        <span class="font-medium">Order #{{ $match->order->order_id }}</span>
                        <span class="text-gray-500 ml-2">({{ $match->match_type }})</span>
                    </div>
                    <a href="{{ route('matches.show', $match) }}" class="text-blue-600 hover:text-blue-800 text-sm">View Match</a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($transaction->aiSuggestions->count())
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-3">AI Suggestions</h3>
            @foreach($transaction->aiSuggestions as $suggestion)
            <div class="p-4 bg-blue-50 rounded mb-2">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-sm text-blue-800">{{ $suggestion->explanation }}</div>
                        <div class="text-xs text-blue-600 mt-1">Confidence: {{ number_format($suggestion->confidence * 100, 0) }}%</div>
                    </div>
                    @if($suggestion->status === 'pending')
                    <div class="flex space-x-2">
                        <form method="POST" action="{{ route('suggestions.accept', $suggestion) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1 bg-green-100 text-green-700 rounded text-sm">Accept</button>
                        </form>
                        <form method="POST" action="{{ route('suggestions.reject', $suggestion) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1 bg-red-100 text-red-700 rounded text-sm">Reject</button>
                        </form>
                    </div>
                    @else
                    <span class="text-xs text-gray-500">{{ ucfirst($suggestion->status) }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
        <a href="{{ route('transactions.index') }}" class="text-gray-600 hover:text-gray-800">Back to list</a>
        <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm('Are you sure?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
        </form>
    </div>
</div>
@endsection
