<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Reconciliation System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <nav class="bg-white shadow mb-6">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex space-x-6">
                    <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-600 font-medium">Dashboard</a>
                    <a href="{{ route('transactions.index') }}" class="text-gray-700 hover:text-blue-600 font-medium">Transactions</a>
                    <a href="{{ route('orders.index') }}" class="text-gray-700 hover:text-blue-600 font-medium">Orders</a>
                    <a href="{{ route('matches.index') }}" class="text-gray-700 hover:text-blue-600 font-medium">Matches</a>
                    <a href="{{ route('import.index') }}" class="text-gray-700 hover:text-blue-600 font-medium">Import</a>
                </div>
                <div class="text-gray-500 text-sm">{{ date('M d, Y') }}</div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 pb-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
