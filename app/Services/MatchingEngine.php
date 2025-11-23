<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Order;
use App\Models\Match;
use App\Models\MatchingRule;
use Illuminate\Support\Collection;

class MatchingEngine
{
    protected Collection $rules;

    public function __construct()
    {
        $this->rules = MatchingRule::active()->byPriority()->get();
    }

    /**
     * Find potential matches for a transaction
     */
    public function findMatches(Transaction $transaction): array
    {
        $candidates = [];

        // Get unmatched orders within date range
        $orders = Order::where('match_status', 'pending')
            ->whereBetween('order_date', [
                $transaction->transaction_date->subDays(7),
                $transaction->transaction_date->addDays(7),
            ])
            ->get();

        foreach ($orders as $order) {
            $match = $this->evaluateMatch($transaction, $order);
            if ($match['confidence'] > 0.4) {
                $candidates[] = $match;
            }
        }

        // Sort by confidence descending
        usort($candidates, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        return $candidates;
    }

    /**
     * Evaluate match between transaction and order
     */
    protected function evaluateMatch(Transaction $transaction, Order $order): array
    {
        $confidence = 0;
        $matchType = 'direct';
        $factors = [];

        // Amount matching
        $amountDiff = abs($transaction->amount - $order->amount);

        if ($amountDiff < 0.01) {
            $confidence += 0.5;
            $factors[] = 'exact_amount';
        } elseif ($amountDiff <= $order->discount && $order->discount > 0) {
            $confidence += 0.4;
            $matchType = 'discount';
            $factors[] = 'discount_match';
        } elseif ($amountDiff < 5.00) {
            $confidence += 0.3;
            $factors[] = 'close_amount';
        }

        // Date matching
        $dateDiff = abs($transaction->transaction_date->diffInDays($order->order_date));

        if ($dateDiff === 0) {
            $confidence += 0.25;
            $factors[] = 'same_day';
        } elseif ($dateDiff <= 3) {
            $confidence += 0.15;
            $factors[] = 'within_3_days';
        } elseif ($dateDiff <= 7) {
            $confidence += 0.05;
            $factors[] = 'within_week';
        }

        // Vendor/Platform matching
        if ($this->matchVendorPlatform($transaction->vendor, $order->platform)) {
            $confidence += 0.2;
            $factors[] = 'vendor_platform_match';
        }

        // Apply rules
        foreach ($this->rules as $rule) {
            if ($this->ruleApplies($rule, $transaction, $order)) {
                $confidence += 0.05;
                $factors[] = "rule:{$rule->name}";
            }
        }

        // Cap confidence at 1.0
        $confidence = min($confidence, 1.0);

        return [
            'transaction_id' => $transaction->id,
            'order_id' => $order->id,
            'confidence' => round($confidence, 2),
            'match_type' => $matchType,
            'amount_difference' => $amountDiff,
            'factors' => $factors,
        ];
    }

    /**
     * Check if vendor matches platform
     */
    protected function matchVendorPlatform(?string $vendor, string $platform): bool
    {
        if (!$vendor) {
            return false;
        }

        $vendor = strtolower($vendor);
        $platform = strtolower($platform);

        $patterns = [
            'ebay' => ['ebay', 'paypal *ebay'],
            'amazon' => ['amazon', 'amzn', 'amz'],
            'wholesale' => ['vf outdoor', 'wholesale', 'bulk'],
        ];

        if (isset($patterns[$platform])) {
            foreach ($patterns[$platform] as $pattern) {
                if (str_contains($vendor, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if rule applies to transaction and order
     */
    protected function ruleApplies(MatchingRule $rule, Transaction $transaction, Order $order): bool
    {
        // Check vendor pattern
        if ($rule->vendor_pattern && $transaction->vendor) {
            if (!$rule->matchesVendor($transaction->vendor)) {
                return false;
            }
        }

        // Check platform
        if ($rule->platform && $rule->platform !== $order->platform) {
            return false;
        }

        // Check amount range
        if (!$rule->matchesAmount($transaction->amount)) {
            return false;
        }

        // Check amount tolerance
        $amountDiff = abs($transaction->amount - $order->amount);
        if ($amountDiff > $rule->amount_tolerance) {
            return false;
        }

        // Check date tolerance
        $dateDiff = abs($transaction->transaction_date->diffInDays($order->order_date));
        if ($dateDiff > $rule->date_tolerance_days) {
            return false;
        }

        return true;
    }

    /**
     * Auto-match transactions with high confidence
     */
    public function autoMatch(float $minConfidence = 0.95): array
    {
        $results = [
            'matched' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $transactions = Transaction::pending()->get();

        foreach ($transactions as $transaction) {
            $candidates = $this->findMatches($transaction);

            if (empty($candidates)) {
                $results['skipped']++;
                continue;
            }

            $best = $candidates[0];

            if ($best['confidence'] >= $minConfidence) {
                try {
                    $this->createMatch($transaction, $best);
                    $results['matched']++;
                } catch (\Exception $e) {
                    $results['errors'][] = "Transaction {$transaction->id}: {$e->getMessage()}";
                }
            } else {
                $results['skipped']++;
            }
        }

        return $results;
    }

    /**
     * Create a match record
     */
    public function createMatch(Transaction $transaction, array $matchData): Match
    {
        $order = Order::findOrFail($matchData['order_id']);

        $match = Match::create([
            'match_type' => $matchData['match_type'],
            'confidence' => $matchData['confidence'],
            'order_id' => $order->id,
            'transaction_id' => $transaction->id,
            'amount_difference' => $matchData['amount_difference'] ?? 0,
            'discount_amount' => $matchData['discount_amount'] ?? null,
        ]);

        // Update statuses
        $transaction->update([
            'match_status' => 'matched',
            'match_confidence' => $matchData['confidence'],
        ]);

        $order->update(['match_status' => 'matched']);

        // Update rule usage
        if (isset($matchData['factors'])) {
            foreach ($matchData['factors'] as $factor) {
                if (str_starts_with($factor, 'rule:')) {
                    $ruleName = substr($factor, 5);
                    MatchingRule::where('name', $ruleName)->increment('times_used');
                }
            }
        }

        return $match;
    }

    /**
     * Find split match candidates
     */
    public function findSplitMatches(Order $order): array
    {
        $candidates = [];

        $transactions = Transaction::pending()
            ->whereBetween('transaction_date', [
                $order->order_date->subDays(7),
                $order->order_date->addDays(7),
            ])
            ->where('amount', '<', $order->amount)
            ->orderBy('amount', 'desc')
            ->get();

        // Try to find combinations that sum to order amount
        $targetAmount = $order->amount;
        $combinations = $this->findCombinations($transactions->toArray(), $targetAmount, 0.10);

        foreach ($combinations as $combo) {
            $totalAmount = array_sum(array_column($combo, 'amount'));
            $diff = abs($totalAmount - $targetAmount);

            $confidence = max(0, 1 - ($diff / $targetAmount));

            $candidates[] = [
                'order_id' => $order->id,
                'transactions' => $combo,
                'total_amount' => $totalAmount,
                'target_amount' => $targetAmount,
                'difference' => $diff,
                'confidence' => round($confidence, 2),
                'match_type' => 'split',
            ];
        }

        usort($candidates, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        return array_slice($candidates, 0, 5);
    }

    /**
     * Find transaction combinations that sum to target
     */
    protected function findCombinations(array $transactions, float $target, float $tolerance): array
    {
        $results = [];
        $n = count($transactions);

        // Limit to max 4 parts for performance
        $maxParts = min(4, $n);

        for ($size = 2; $size <= $maxParts; $size++) {
            $this->combine($transactions, $size, 0, [], $target, $tolerance, $results);
        }

        return $results;
    }

    protected function combine(array $items, int $size, int $start, array $current, float $target, float $tolerance, array &$results): void
    {
        if (count($current) === $size) {
            $sum = array_sum(array_column($current, 'amount'));
            if (abs($sum - $target) <= $tolerance * $target) {
                $results[] = $current;
            }
            return;
        }

        for ($i = $start; $i < count($items); $i++) {
            $current[] = $items[$i];
            $this->combine($items, $size, $i + 1, $current, $target, $tolerance, $results);
            array_pop($current);
        }
    }
}
