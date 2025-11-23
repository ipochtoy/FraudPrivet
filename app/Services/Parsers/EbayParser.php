<?php

namespace App\Services\Parsers;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

class EbayParser
{
    /**
     * Parse eBay orders from CSV export
     */
    public function parseCsv(string $filePath): array
    {
        $results = ['imported' => 0, 'errors' => []];

        if (!file_exists($filePath)) {
            $results['errors'][] = "File not found: {$filePath}";
            return $results;
        }

        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            try {
                $data = array_combine($headers, $row);
                $this->createOrder($data);
                $results['imported']++;
            } catch (\Exception $e) {
                $results['errors'][] = $e->getMessage();
            }
        }

        fclose($handle);
        return $results;
    }

    /**
     * Parse eBay API response
     */
    public function parseApiResponse(array $data): array
    {
        $results = ['imported' => 0, 'errors' => []];

        $orders = $data['orders'] ?? $data;

        foreach ($orders as $orderData) {
            try {
                $this->createOrderFromApi($orderData);
                $results['imported']++;
            } catch (\Exception $e) {
                $results['errors'][] = $e->getMessage();
            }
        }

        return $results;
    }

    protected function createOrder(array $data): Order
    {
        $amount = (float) str_replace(['$', ','], '', $data['Total'] ?? $data['Order Total'] ?? 0);
        $discount = (float) str_replace(['$', ','], '', $data['Discount'] ?? $data['Seller Discount'] ?? 0);

        $order = Order::create([
            'order_id' => $data['Order Number'] ?? $data['Sales Record Number'],
            'platform' => 'ebay',
            'store' => $data['Store'] ?? null,
            'order_date' => Carbon::parse($data['Sale Date'] ?? $data['Paid on Date']),
            'amount' => $amount - $discount,
            'original_amount' => $amount,
            'discount' => $discount,
            'client_id' => $data['Buyer User ID'] ?? null,
            'client_email' => $data['Buyer Email'] ?? null,
            'match_status' => 'pending',
        ]);

        // Create order item if present
        if (isset($data['Item Title'])) {
            $order->items()->create([
                'item_name' => $data['Item Title'],
                'quantity' => (int) ($data['Quantity'] ?? 1),
                'price' => (float) str_replace(['$', ','], '', $data['Item Price'] ?? $data['Total'] ?? 0),
            ]);
        }

        return $order;
    }

    protected function createOrderFromApi(array $data): Order
    {
        $pricingSummary = $data['pricingSummary'] ?? [];
        $total = (float) ($pricingSummary['total']['value'] ?? 0);
        $discount = (float) ($pricingSummary['discount']['value'] ?? 0);

        $order = Order::create([
            'order_id' => $data['orderId'] ?? $data['legacyOrderId'],
            'platform' => 'ebay',
            'order_date' => Carbon::parse($data['creationDate'] ?? $data['paidTime']),
            'amount' => $total,
            'original_amount' => $total + $discount,
            'discount' => $discount,
            'client_id' => $data['buyer']['username'] ?? null,
            'client_email' => $data['buyer']['buyerRegistrationAddress']['email'] ?? null,
            'match_status' => 'pending',
        ]);

        // Create line items
        if (isset($data['lineItems'])) {
            foreach ($data['lineItems'] as $item) {
                $order->items()->create([
                    'item_name' => $item['title'],
                    'quantity' => (int) ($item['quantity'] ?? 1),
                    'price' => (float) ($item['lineItemCost']['value'] ?? 0),
                ]);
            }
        }

        return $order;
    }
}
