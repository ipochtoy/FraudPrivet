@extends('layouts.app')

@section('title', 'Matching Rules')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-800">Matching Rules</h1>
        <a href="{{ route('rules.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm font-medium">
            New Rule
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pattern</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Platform</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Used</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($rules as $rule)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $rule->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $rule->vendor_pattern ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $rule->platform ?? 'All' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $rule->priority }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $rule->times_used }}</td>
                    <td class="px-6 py-4">
                        @if($rule->is_active)
                            <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm space-x-2">
                        <a href="{{ route('rules.edit', $rule) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                        <form method="POST" action="{{ route('rules.toggle', $rule) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-yellow-600 hover:text-yellow-800">
                                {{ $rule->is_active ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No rules defined</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-gray-200">
        {{ $rules->links() }}
    </div>
</div>
@endsection
