<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reconciliation Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">RECONCILIATION DASHBOARD</h1>
                <div class="text-gray-500">📅 {{ date('M d, Y') }}</div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
                <div class="text-gray-500 text-sm">📥 Новых транзакций</div>
                <div class="text-2xl font-bold">{{ $stats['new_transactions'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
                <div class="text-gray-500 text-sm">✅ Авто-matched</div>
                <div class="text-2xl font-bold">{{ $stats['auto_matched'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
                <div class="text-gray-500 text-sm">📦 Новых заказов</div>
                <div class="text-2xl font-bold">{{ $stats['new_orders'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
                <div class="text-gray-500 text-sm">⚠️ Требует внимания</div>
                <div class="text-2xl font-bold">{{ $stats['needs_attention'] }}</div>
            </div>
        </div>

        <!-- Attention Required -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="bg-red-50 px-6 py-4 border-b border-red-100">
                <h2 class="text-lg font-semibold text-red-800">
                    ⚠️ ТРЕБУЕТ ВНИМАНИЯ ({{ $stats['needs_attention'] }} транзакции на ${{ $stats['unmatched_amount'] }})
                </h2>
            </div>
            
            <div class="divide-y divide-gray-200">
                @foreach($unmatched as $item)
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <span class="text-xl font-bold text-gray-900">${{ number_format($item->amount, 2) }}</span>
                            <span class="ml-2 px-2 py-1 bg-gray-100 rounded text-sm font-medium text-gray-600">{{ $item->vendor }}</span>
                            <span class="ml-2 text-gray-500 text-sm">{{ $item->date }}</span>
                        </div>
                        <div class="flex space-x-2">
                            <button class="px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 text-sm font-medium">Подтвердить</button>
                            <button class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm font-medium">Отклонить</button>
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm font-medium">Разобраться</button>
                        </div>
                    </div>
                    
                    <div class="flex items-center bg-blue-50 p-3 rounded mt-3">
                        <div class="text-2xl mr-3">🤖</div>
                        <div>
                            <div class="text-sm font-bold text-blue-900">AI Suggestion:</div>
                            <div class="text-blue-800">{{ $item->ai_suggestion }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Weekly Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4">СТАТИСТИКА ЗА НЕДЕЛЮ</h3>
            <div class="w-full bg-gray-200 rounded-full h-4 mb-2">
                <div class="bg-green-500 h-4 rounded-full" style="width: 97.2%"></div>
            </div>
            <div class="text-right text-sm text-gray-600 mb-4">97.2% auto-matched</div>
            
            <div class="grid grid-cols-7 gap-2 text-center text-sm">
                <div class="p-2 bg-green-50 rounded text-green-700">Mon<br>45/47 ✓</div>
                <div class="p-2 bg-green-50 rounded text-green-700">Tue<br>38/40 ✓</div>
                <div class="p-2 bg-green-50 rounded text-green-700">Wed<br>44/44 ✓</div>
                <div class="p-2 bg-green-50 rounded text-green-700">Thu<br>52/53 ✓</div>
                <div class="p-2 bg-green-50 rounded text-green-700">Fri<br>61/63 ✓</div>
                <div class="p-2 bg-green-50 rounded text-green-700">Sat<br>33/35 ✓</div>
                <div class="p-2 bg-gray-50 rounded text-gray-400">Sun<br>-</div>
            </div>
        </div>
    </div>
</body>
</html>

