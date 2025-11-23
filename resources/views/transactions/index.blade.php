@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-800">Transactions</h1>
        <a href="{{ route('transactions.needs-attention') }}" class="px-4 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm font-medium">
            Needs Attention
        </a>
    </div>

    <!-- Filters -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <form method="GET" class="flex flex-wrap gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                   class="px-3 py-2 border rounded text-sm">
            <select name="status" class="px-3 py-2 border rounded text-sm">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="matched" {{ request('status') == 'matched' ? 'selected' : '' }}>Matched</option>
                <option value="unmatched" {{ request('status') == 'unmatched' ? 'selected' : '' }}>Unmatched</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 border rounded text-sm">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 border rounded text-sm">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($transactions as $transaction)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction->transaction_date->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">${{ number_format($transaction->amount, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction->vendor ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $transaction->source }}</td>
                    <td class="px-6 py-4">
                        @switch($transaction->match_status)
                            @case('matched')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Matched</span>
                                @break
                            @case('pending')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-yellow-100 text-yellow-800">Pending</span>
                                @break
                            @case('unmatched')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-800">Unmatched</span>
                                @break
                            @default
                                <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">{{ $transaction->match_status }}</span>
                        @endswitch
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('transactions.show', $transaction) }}" class="text-blue-600 hover:text-blue-800">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No transactions found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
