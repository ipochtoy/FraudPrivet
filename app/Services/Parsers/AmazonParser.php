<?php

namespace App\Services\Parsers;

use App\Models\Order;
use Carbon\Carbon;

class AmazonParser
{
    /**
     * Parse Amazon Seller Central report
     */
    public function parseCsv(string $filePath): array
    {
        $results = ['imported' => 0, 'errors' => []];

        if (!file_exists($filePath)) {
            $results['errors'][] = "File not found: {$filePath}";
            return $results;
        }

        $handle = fopen($filePath, 'r');

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xef\xbb\xbf") {
            rewind($handle);
        }

        $headers = fgetcsv($handle, 0, "\t"); // Amazon uses tabs

        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            if (count($row) !== count($headers)) {
                continue;
            }

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
     * Parse Amazon SP-API response
     */
    public function parseApiResponse(array $data): array
    {
        $results = ['imported' => 0, 'errors' => []];

        $orders = $data['Orders'] ?? $data['payload']['Orders'] ?? [];

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
        $amount = (float) ($data['item-price'] ?? $data['total-price'] ?? 0);
        $promotionDiscount = (float) ($data['item-promotion-discount'] ?? 0);

        $order = Order::create([
            'order_id' => $data['amazon-order-id'] ?? $data['order-id'],
            'platform' => 'amazon',
            'store' => $data['sales-channel'] ?? 'Amazon.com',
            'order_date' => Carbon::parse($data['purchase-date'] ?? $data['payments-date']),
            'amount' => $amount - abs($promotionDiscount),
            'original_amount' => $amount,
            'discount' => abs($promotionDiscount),
            'client_email' => $data['buyer-email'] ?? null,
            'match_status' => 'pending',
        ]);

        // Create order item
        if (isset($data['product-name']) || isset($data['sku'])) {
            $order->items()->create([
                'item_name' => $data['product-name'] ?? $data['sku'],
                'quantity' => (int) ($data['quantity-purchased'] ?? 1),
                'price' => (float) ($data['item-price'] ?? 0),
            ]);
        }

        return $order;
    }

    protected function createOrderFromApi(array $data): Order
    {
        $orderTotal = $data['OrderTotal'] ?? [];
        $amount = (float) ($orderTotal['Amount'] ?? 0);

        $order = Order::create([
            'order_id' => $data['AmazonOrderId'],
            'platform' => 'amazon',
            'store' => $data['SalesChannel'] ?? 'Amazon.com',
            'order_date' => Carbon::parse($data['PurchaseDate']),
            'amount' => $amount,
            'original_amount' => $amount,
            'discount' => 0,
            'client_email' => $data['BuyerEmail'] ?? null,
            'match_status' => 'pending',
        ]);

        return $order;
    }
}
