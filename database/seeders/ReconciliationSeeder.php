<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MatchingRule;
use App\Models\AISuggestion;
use Carbon\Carbon;

class ReconciliationSeeder extends Seeder
{
    public function run(): void
    {
        // Create matching rules
        $rules = [
            [
                'name' => 'eBay Direct Match',
                'description' => 'Direct match for eBay transactions',
                'vendor_pattern' => 'ebay|EBAY',
                'platform' => 'ebay',
                'amount_tolerance' => 0.01,
                'date_tolerance_days' => 3,
                'priority' => 100,
            ],
            [
                'name' => 'Amazon Direct Match',
                'description' => 'Direct match for Amazon transactions',
                'vendor_pattern' => 'amazon|AMZN',
                'platform' => 'amazon',
                'amount_tolerance' => 0.01,
                'date_tolerance_days' => 5,
                'priority' => 100,
            ],
            [
                'name' => 'PayPal Discount Match',
                'description' => 'Match PayPal with discount tolerance',
                'vendor_pattern' => 'paypal|PAYPAL',
                'amount_tolerance' => 5.00,
                'discount_tolerance' => 50.00,
                'date_tolerance_days' => 7,
                'priority' => 80,
            ],
            [
                'name' => 'Split Transaction Rule',
                'description' => 'Allow split matching for large orders',
                'amount_min' => 200.00,
                'allow_splits' => true,
                'max_split_parts' => 5,
                'date_tolerance_days' => 5,
                'priority' => 60,
            ],
        ];

        foreach ($rules as $rule) {
            MatchingRule::create($rule);
        }

        // Create sample transactions
        $transactions = [
            [
                'external_id' => 'QB-2024-001',
                'transaction_date' => Carbon::now()->subDays(1),
                'amount' => 149.99,
                'vendor' => 'EBAY *SELLER',
                'vendor_raw' => 'EBAY *SELLER MARKETPLACE',
                'category' => 'Sales',
                'source' => 'quickbooks',
                'card_last4' => '4532',
                'match_status' => 'pending',
            ],
            [
                'external_id' => 'QB-2024-002',
                'transaction_date' => Carbon::now()->subDays(1),
                'amount' => 309.59,
                'vendor' => 'VF OUTDOOR',
                'vendor_raw' => 'VF OUTDOOR LLC',
                'category' => 'Inventory',
                'source' => 'quickbooks',
                'card_last4' => '4532',
                'match_status' => 'pending',
            ],
            [
                'external_id' => 'QB-2024-003',
                'transaction_date' => Carbon::now()->subDays(2),
                'amount' => 89.99,
                'vendor' => 'AMAZON MKTP',
                'vendor_raw' => 'AMAZON MKTP US*RT5GH2K',
                'category' => 'Sales',
                'source' => 'quickbooks',
                'card_last4' => '7891',
                'match_status' => 'pending',
            ],
            [
                'external_id' => 'QB-2024-004',
                'transaction_date' => Carbon::now()->subDays(2),
                'amount' => 45.32,
                'vendor' => 'PAYPAL *EBAYINC',
                'vendor_raw' => 'PAYPAL *EBAYINC',
                'category' => 'Fees',
                'source' => 'quickbooks',
                'card_last4' => '4532',
                'match_status' => 'pending',
            ],
            [
                'external_id' => 'QB-2024-005',
                'transaction_date' => Carbon::now()->subDays(2),
                'amount' => 108.45,
                'vendor' => 'eBay Shipping',
                'vendor_raw' => 'EBAY SHIPPING LABEL',
                'category' => 'Shipping',
                'source' => 'quickbooks',
                'card_last4' => '4532',
                'match_status' => 'pending',
            ],
            [
                'external_id' => 'QB-2024-006',
                'transaction_date' => Carbon::now()->subDays(3),
                'amount' => 259.99,
                'vendor' => 'EBAY *BUYER',
                'vendor_raw' => 'EBAY *BUYER PAYMENT',
                'category' => 'Sales',
                'source' => 'quickbooks',
                'card_last4' => '4532',
                'match_status' => 'matched',
                'match_confidence' => 0.98,
            ],
            [
                'external_id' => 'QB-2024-007',
                'transaction_date' => Carbon::now(),
                'amount' => 69.46,
                'vendor' => 'PAYPAL *UNKNOWN',
                'vendor_raw' => 'PAYPAL *UNKNOWNVENDOR',
                'category' => 'Uncategorized',
                'source' => 'quickbooks',
                'card_last4' => '7891',
                'match_status' => 'unmatched',
            ],
        ];

        foreach ($transactions as $data) {
            Transaction::create($data);
        }

        // Create sample orders
        $orders = [
            [
                'order_id' => '12-34567-89012',
                'platform' => 'ebay',
                'store' => 'MyEbayStore',
                'order_date' => Carbon::now()->subDays(1),
                'amount' => 149.99,
                'original_amount' => 149.99,
                'discount' => 0,
                'client_email' => 'buyer1@email.com',
                'match_status' => 'pending',
                'items' => [
                    ['item_name' => 'Vintage Camera Lens', 'quantity' => 1, 'price' => 149.99],
                ],
            ],
            [
                'order_id' => '439892',
                'platform' => 'wholesale',
                'store' => 'VF Outdoor',
                'order_date' => Carbon::now()->subDays(2),
                'amount' => 309.59,
                'original_amount' => 359.59,
                'discount' => 50.00,
                'client_email' => 'wholesale@vfoutdoor.com',
                'match_status' => 'pending',
                'items' => [
                    ['item_name' => 'Hiking Backpack 40L', 'quantity' => 2, 'price' => 129.99],
                    ['item_name' => 'Water Bottle Set', 'quantity' => 1, 'price' => 49.99],
                ],
            ],
            [
                'order_id' => '113-4567890-1234567',
                'platform' => 'amazon',
                'store' => 'MyAmazonStore',
                'order_date' => Carbon::now()->subDays(3),
                'amount' => 89.99,
                'original_amount' => 89.99,
                'discount' => 0,
                'client_email' => 'amazonbuyer@email.com',
                'match_status' => 'pending',
                'items' => [
                    ['item_name' => 'Bluetooth Speaker', 'quantity' => 1, 'price' => 89.99],
                ],
            ],
            [
                'order_id' => '12-45678-90123',
                'platform' => 'ebay',
                'store' => 'MyEbayStore',
                'order_date' => Carbon::now()->subDays(3),
                'amount' => 259.99,
                'original_amount' => 259.99,
                'discount' => 0,
                'client_email' => 'buyer2@email.com',
                'match_status' => 'matched',
                'items' => [
                    ['item_name' => 'Mechanical Keyboard', 'quantity' => 1, 'price' => 259.99],
                ],
            ],
            [
                'order_id' => '12-56789-01234',
                'platform' => 'ebay',
                'store' => 'MyEbayStore',
                'order_date' => Carbon::now()->subDays(2),
                'amount' => 153.77,
                'original_amount' => 153.77,
                'discount' => 0,
                'client_email' => 'buyer3@email.com',
                'match_status' => 'pending',
                'items' => [
                    ['item_name' => 'USB Hub', 'quantity' => 1, 'price' => 45.32],
                    ['item_name' => 'Shipping Label', 'quantity' => 1, 'price' => 108.45],
                ],
            ],
        ];

        foreach ($orders as $orderData) {
            $items = $orderData['items'] ?? [];
            unset($orderData['items']);

            $order = Order::create($orderData);

            foreach ($items as $item) {
                $order->items()->create($item);
            }
        }

        // Create AI suggestions
        $aiSuggestions = [
            [
                'type' => 'match',
                'transaction_id' => 2, // VF OUTDOOR $309.59
                'order_id' => 2, // Order #439892
                'suggestion' => [
                    'match_type' => 'discount',
                    'order_id' => 2,
                    'transaction_id' => 2,
                    'confidence' => 0.87,
                    'discount_amount' => 50.00,
                ],
                'explanation' => 'Вероятно заказ #439892 со скидкой $50. Сумма после скидки совпадает.',
                'confidence' => 0.87,
                'status' => 'pending',
            ],
            [
                'type' => 'match',
                'transaction_id' => 5, // eBay Shipping $108.45
                'order_id' => 5, // Order with split
                'suggestion' => [
                    'match_type' => 'split',
                    'order_id' => 5,
                    'transaction_ids' => [4, 5],
                    'confidence' => 0.82,
                ],
                'explanation' => 'Часть сплита с $45.32 (Nov 20). Два платежа составляют полную сумму заказа.',
                'confidence' => 0.82,
                'status' => 'pending',
            ],
            [
                'type' => 'pattern',
                'transaction_id' => 7, // PAYPAL *UNKNOWN
                'suggestion' => [
                    'vendor_pattern' => 'UNKNOWNVENDOR',
                    'recommended_action' => 'manual_review',
                ],
                'explanation' => 'Не могу определить vendor. Рекомендую ручную проверку.',
                'confidence' => 0.35,
                'status' => 'pending',
            ],
        ];

        foreach ($aiSuggestions as $suggestion) {
            AISuggestion::create($suggestion);
        }
    }
}
