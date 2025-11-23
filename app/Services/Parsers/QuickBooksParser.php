<?php

namespace App\Services\Parsers;

use App\Models\Transaction;
use Carbon\Carbon;

class QuickBooksParser
{
    /**
     * Parse QuickBooks CSV export
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
                $this->createTransaction($data);
                $results['imported']++;
            } catch (\Exception $e) {
                $results['errors'][] = $e->getMessage();
            }
        }

        fclose($handle);
        return $results;
    }

    /**
     * Parse JSON data from QuickBooks API
     */
    public function parseJson(array $data): array
    {
        $results = ['imported' => 0, 'errors' => []];

        foreach ($data as $item) {
            try {
                $this->createTransactionFromApi($item);
                $results['imported']++;
            } catch (\Exception $e) {
                $results['errors'][] = $e->getMessage();
            }
        }

        return $results;
    }

    protected function createTransaction(array $data): Transaction
    {
        return Transaction::create([
            'external_id' => $data['Num'] ?? $data['RefNumber'] ?? null,
            'transaction_date' => Carbon::parse($data['Date'] ?? $data['TxnDate']),
            'amount' => abs((float) str_replace(['$', ','], '', $data['Amount'] ?? $data['Total'] ?? 0)),
            'vendor' => $this->normalizeVendor($data['Name'] ?? $data['Vendor'] ?? null),
            'vendor_raw' => $data['Name'] ?? $data['Vendor'] ?? null,
            'memo' => $data['Memo'] ?? $data['Description'] ?? null,
            'category' => $data['Account'] ?? $data['Category'] ?? null,
            'source' => 'quickbooks',
            'match_status' => 'pending',
        ]);
    }

    protected function createTransactionFromApi(array $data): Transaction
    {
        return Transaction::create([
            'external_id' => $data['Id'] ?? null,
            'transaction_date' => Carbon::parse($data['TxnDate'] ?? $data['MetaData']['CreateTime']),
            'amount' => abs((float) ($data['TotalAmt'] ?? $data['Amount'] ?? 0)),
            'vendor' => $this->normalizeVendor($data['EntityRef']['name'] ?? null),
            'vendor_raw' => $data['EntityRef']['name'] ?? null,
            'memo' => $data['PrivateNote'] ?? null,
            'category' => $data['AccountRef']['name'] ?? null,
            'source' => 'quickbooks',
            'match_status' => 'pending',
        ]);
    }

    protected function normalizeVendor(?string $vendor): ?string
    {
        if (!$vendor) {
            return null;
        }

        // Remove common suffixes and clean up
        $vendor = preg_replace('/\s+(LLC|INC|CORP|LTD)\.?$/i', '', $vendor);
        $vendor = preg_replace('/\s+/', ' ', $vendor);

        return trim($vendor);
    }
}
