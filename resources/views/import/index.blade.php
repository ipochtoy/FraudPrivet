@extends('layouts.app')

@section('title', 'Import Data')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Import Transactions -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-800">Import Transactions</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('import.transactions') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Source</label>
                    <select name="source" class="w-full px-3 py-2 border rounded">
                        <option value="quickbooks">QuickBooks</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">CSV File</label>
                    <input type="file" name="file" accept=".csv,.txt" class="w-full px-3 py-2 border rounded" required>
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Import Transactions
                </button>
            </form>
        </div>
    </div>

    <!-- Import Orders -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-800">Import Orders</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('import.orders') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Platform</label>
                    <select name="platform" class="w-full px-3 py-2 border rounded">
                        <option value="ebay">eBay</option>
                        <option value="amazon">Amazon</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">CSV File</label>
                    <input type="file" name="file" accept=".csv,.txt" class="w-full px-3 py-2 border rounded" required>
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    Import Orders
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Auto-Match -->
<div class="mt-6 bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-800">Auto-Match</h2>
    </div>
    <div class="p-6">
        <form method="POST" action="{{ route('import.auto-match') }}" class="flex items-end gap-4">
            @csrf
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Confidence</label>
                <select name="min_confidence" class="w-full px-3 py-2 border rounded">
                    <option value="0.95">95% (High confidence only)</option>
                    <option value="0.90">90%</option>
                    <option value="0.85">85%</option>
                    <option value="0.80">80%</option>
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">
                Run Auto-Match
            </button>
        </form>
    </div>
</div>
@endsection
