@extends('layouts.app')

@section('title', 'Unverified Matches')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 bg-yellow-50">
        <h1 class="text-xl font-bold text-yellow-800">Unverified Matches</h1>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Confidence</th>
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
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $match->order->order_id ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="{{ $match->confidence >= 0.9 ? 'text-green-600' : ($match->confidence >= 0.7 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ number_format($match->confidence * 100, 0) }}%
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm space-x-2">
                        <form method="POST" action="{{ route('matches.verify', $match) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-green-600 hover:text-green-800">Verify</button>
                        </form>
                        <a href="{{ route('matches.show', $match) }}" class="text-blue-600 hover:text-blue-800">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No unverified matches</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-gray-200">
        {{ $matches->links() }}
    </div>
</div>
@endsection
