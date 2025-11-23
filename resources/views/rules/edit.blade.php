@extends('layouts.app')

@section('title', 'Edit Rule')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800">Edit Rule: {{ $rule->name }}</h1>
    </div>

    <form method="POST" action="{{ route('rules.update', $rule) }}" class="p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                <input type="text" name="name" value="{{ old('name', $rule->name) }}" required
                       class="w-full px-3 py-2 border rounded">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                <input type="number" name="priority" value="{{ old('priority', $rule->priority) }}" min="0" max="1000"
                       class="w-full px-3 py-2 border rounded">
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="2" class="w-full px-3 py-2 border rounded">{{ old('description', $rule->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Vendor Pattern</label>
                <input type="text" name="vendor_pattern" value="{{ old('vendor_pattern', $rule->vendor_pattern) }}"
                       class="w-full px-3 py-2 border rounded font-mono">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Platform</label>
                <select name="platform" class="w-full px-3 py-2 border rounded">
                    <option value="">All Platforms</option>
                    <option value="ebay" {{ old('platform', $rule->platform) == 'ebay' ? 'selected' : '' }}>eBay</option>
                    <option value="amazon" {{ old('platform', $rule->platform) == 'amazon' ? 'selected' : '' }}>Amazon</option>
                    <option value="wholesale" {{ old('platform', $rule->platform) == 'wholesale' ? 'selected' : '' }}>Wholesale</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Amount Min</label>
                <input type="number" name="amount_min" value="{{ old('amount_min', $rule->amount_min) }}" step="0.01"
                       class="w-full px-3 py-2 border rounded">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Amount Max</label>
                <input type="number" name="amount_max" value="{{ old('amount_max', $rule->amount_max) }}" step="0.01"
                       class="w-full px-3 py-2 border rounded">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Amount Tolerance</label>
                <input type="number" name="amount_tolerance" value="{{ old('amount_tolerance', $rule->amount_tolerance) }}" step="0.01"
                       class="w-full px-3 py-2 border rounded">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date Tolerance (days)</label>
                <input type="number" name="date_tolerance_days" value="{{ old('date_tolerance_days', $rule->date_tolerance_days) }}"
                       class="w-full px-3 py-2 border rounded">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Discount Tolerance</label>
                <input type="number" name="discount_tolerance" value="{{ old('discount_tolerance', $rule->discount_tolerance) }}" step="0.01"
                       class="w-full px-3 py-2 border rounded">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Max Split Parts</label>
                <input type="number" name="max_split_parts" value="{{ old('max_split_parts', $rule->max_split_parts) }}"
                       class="w-full px-3 py-2 border rounded">
            </div>

            <div class="flex items-center space-x-6">
                <label class="flex items-center">
                    <input type="checkbox" name="allow_splits" value="1" {{ old('allow_splits', $rule->allow_splits) ? 'checked' : '' }}
                           class="mr-2">
                    <span class="text-sm text-gray-700">Allow Split Matches</span>
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $rule->is_active) ? 'checked' : '' }}
                           class="mr-2">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex justify-between">
            <form method="POST" action="{{ route('rules.destroy', $rule) }}" onsubmit="return confirm('Delete this rule?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 text-red-600 hover:text-red-800">Delete</button>
            </form>

            <div class="space-x-4">
                <a href="{{ route('rules.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Save Changes</button>
            </div>
        </div>
    </form>
</div>
@endsection
