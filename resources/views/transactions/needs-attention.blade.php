@extends('layouts.app')

@section('title', 'Transactions Needing Attention')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
        <h1 class="text-xl font-bold text-red-800">Transactions Needing Attention</h1>
    </div>

    <div class="divide-y divide-gray-200">
        @forelse($transactions as $transaction)
        <div class="p-6">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <span class="text-xl font-bold text-gray-900">${{ number_format($transaction->amount, 2) }}</span>
                    <span class="ml-2 px-2 py-1 bg-gray-100 rounded text-sm font-medium text-gray-600">{{ $transaction->vendor ?? 'Unknown' }}</span>
                    <span class="ml-2 text-gray-500 text-sm">{{ $transaction->transaction_date->format('M d') }}</span>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('transactions.show', $transaction) }}" class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-sm font-medium">View</a>
                </div>
            </div>

            @if($transaction->aiSuggestions->count())
            <div class="flex items-center bg-blue-50 p-3 rounded mt-3">
                <div class="flex-1">
                    <div class="text-sm font-bold text-blue-900">AI Suggestion:</div>
                    <div class="text-blue-800">{{ $transaction->aiSuggestions->first()->explanation }}</div>
                </div>
                <div class="flex space-x-2 ml-4">
                    <form method="POST" action="{{ route('suggestions.accept', $transaction->aiSuggestions->first()) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 text-sm">Accept</button>
                    </form>
                    <form method="POST" action="{{ route('suggestions.reject', $transaction->aiSuggestions->first()) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">Reject</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="p-6 text-center text-gray-500">
            No transactions need attention
        </div>
        @endforelse
    </div>

    <div class="px-6 py-4 border-t border-gray-200">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
