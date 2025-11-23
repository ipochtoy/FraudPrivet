@extends('layouts.app')

@section('title', 'Matches')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-800">Matches</h1>
        <a href="{{ route('matches.unverified') }}" class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200 text-sm font-medium">
            Unverified Matches
        </a>
    </div>

    <!-- Filters -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <form method="GET" class="flex flex-wrap gap-4">
            <select name="type" class="px-3 py-2 border rounded text-sm">
                <option value="">All Types</option>
                <option value="direct" {{ request('type') == 'direct' ? 'selected' : '' }}>Direct</option>
                <option value="split" {{ request('type') == 'split' ? 'selected' : '' }}>Split</option>
                <option value="discount" {{ request('type') == 'discount' ? 'selected' : '' }}>Discount</option>
                <option value="fuzzy" {{ request('type') == 'fuzzy' ? 'selected' : '' }}>Fuzzy</option>
                <option value="manual" {{ request('type') == 'manual' ? 'selected' : '' }}>Manual</option>
                <option value="ai_suggested" {{ request('type') == 'ai_suggested' ? 'selected' : '' }}>AI Suggested</option>
            </select>
            <select name="verified" class="px-3 py-2 border rounded text-sm">
                <option value="">All</option>
                <option value="true" {{ request('verified') == 'true' ? 'selected' : '' }}>Verified</option>
                <option value="false" {{ request('verified') == 'false' ? 'selected' : '' }}>Unverified</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Confidence</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($matches as $match)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">#{{ $match->id }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs rounded bg-gray-100">{{ $match->match_type }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        @if($match->transaction)
                            ${{ number_format($match->transaction->amount, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $match->order->order_id ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <span class="{{ $match->confidence >= 0.9 ? 'text-green-600' : ($match->confidence >= 0.7 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ number_format($match->confidence * 100, 0) }}%
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($match->verified)
                            <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Verified</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded bg-yellow-100 text-yellow-800">Unverified</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('matches.show', $match) }}" class="text-blue-600 hover:text-blue-800">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No matches found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $matches->links() }}
    </div>
</div>
@endsection
