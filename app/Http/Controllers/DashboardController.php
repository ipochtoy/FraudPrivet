<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\Match;

class DashboardController extends Controller
{
    public function index()
    {
        // Mock data for now to verify UI
        $stats = [
            'new_transactions' => 47,
            'auto_matched' => 44,
            'new_orders' => 52,
            'needs_attention' => 3,
            'unmatched_amount' => 487.50
        ];

        $unmatched = [
            (object)[
                'id' => 1,
                'amount' => 309.59,
                'vendor' => 'VF OUTDOOR',
                'date' => 'Nov 21',
                'ai_suggestion' => 'Вероятно заказ #439892 со скидкой $50'
            ],
            (object)[
                'id' => 2,
                'amount' => 108.45,
                'vendor' => 'eBay Shipping',
                'date' => 'Nov 21',
                'ai_suggestion' => 'Часть сплита с $45.32 (Nov 20)'
            ],
            (object)[
                'id' => 3,
                'amount' => 69.46,
                'vendor' => 'PAYPAL *UNKNOWN',
                'date' => 'Nov 21',
                'ai_suggestion' => 'Не могу определить vendor'
            ]
        ];

        return view('dashboard', compact('stats', 'unmatched'));
    }
}
