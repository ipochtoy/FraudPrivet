<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Order;
use App\Models\AISuggestion;
use App\Models\MatchingRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key', '');
        $this->model = config('services.anthropic.model', 'claude-sonnet-4-20250514');
    }

    /**
     * Generate match suggestions for a transaction
     */
    public function suggestMatches(Transaction $transaction, array $candidateOrders): ?AISuggestion
    {
        if (empty($this->apiKey)) {
            Log::warning('AI Service: API key not configured');
            return null;
        }

        $prompt = $this->buildMatchPrompt($transaction, $candidateOrders);

        try {
            $response = $this->callClaude($prompt);
            $suggestion = $this->parseMatchResponse($response, $transaction);

            if ($suggestion) {
                return AISuggestion::create([
                    'type' => 'match',
                    'transaction_id' => $transaction->id,
                    'order_id' => $suggestion['order_id'] ?? null,
                    'suggestion' => $suggestion,
                    'explanation' => $suggestion['explanation'] ?? 'AI suggested match',
                    'confidence' => $suggestion['confidence'] ?? 0.5,
                    'status' => 'pending',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('AI Service error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Suggest a new matching rule based on patterns
     */
    public function suggestRule(array $matchHistory): ?AISuggestion
    {
        if (empty($this->apiKey)) {
            return null;
        }

        $prompt = $this->buildRulePrompt($matchHistory);

        try {
            $response = $this->callClaude($prompt);
            $suggestion = $this->parseRuleResponse($response);

            if ($suggestion) {
                return AISuggestion::create([
                    'type' => 'rule',
                    'suggestion' => $suggestion,
                    'explanation' => $suggestion['explanation'] ?? 'AI suggested rule',
                    'confidence' => $suggestion['confidence'] ?? 0.7,
                    'status' => 'pending',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('AI Service error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Analyze unmatched transaction and provide explanation
     */
    public function analyzeUnmatched(Transaction $transaction): ?string
    {
        if (empty($this->apiKey)) {
            return null;
        }

        $prompt = "Analyze this unmatched financial transaction and suggest why it might not match any orders:\n\n";
        $prompt .= "Transaction:\n";
        $prompt .= "- Date: {$transaction->transaction_date->format('Y-m-d')}\n";
        $prompt .= "- Amount: \${$transaction->amount}\n";
        $prompt .= "- Vendor: {$transaction->vendor}\n";
        $prompt .= "- Memo: {$transaction->memo}\n\n";
        $prompt .= "Provide a brief explanation in Russian (1-2 sentences) about possible reasons for no match.";

        try {
            $response = $this->callClaude($prompt);
            return $response;
        } catch (\Exception $e) {
            Log::error('AI Service error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Call Claude API
     */
    protected function callClaude(string $prompt): string
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->model,
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('Claude API error: ' . $response->body());
        }

        $data = $response->json();
        return $data['content'][0]['text'] ?? '';
    }

    protected function buildMatchPrompt(Transaction $transaction, array $orders): string
    {
        $prompt = "You are a financial reconciliation assistant. Analyze this transaction and find the best matching order.\n\n";
        $prompt .= "Transaction:\n";
        $prompt .= "- Date: {$transaction->transaction_date->format('Y-m-d')}\n";
        $prompt .= "- Amount: \${$transaction->amount}\n";
        $prompt .= "- Vendor: {$transaction->vendor}\n";
        $prompt .= "- Memo: {$transaction->memo}\n\n";
        $prompt .= "Candidate Orders:\n";

        foreach ($orders as $i => $order) {
            $prompt .= ($i + 1) . ". Order #{$order->order_id} ({$order->platform})\n";
            $prompt .= "   - Date: {$order->order_date->format('Y-m-d')}\n";
            $prompt .= "   - Amount: \${$order->amount}\n";
            $prompt .= "   - Discount: \${$order->discount}\n\n";
        }

        $prompt .= "Respond in JSON format:\n";
        $prompt .= '{"order_id": <order_db_id>, "match_type": "direct|discount|split", "confidence": 0.0-1.0, "explanation": "brief explanation in Russian"}';
        $prompt .= "\n\nIf no good match, return {\"order_id\": null, \"confidence\": 0, \"explanation\": \"reason in Russian\"}";

        return $prompt;
    }

    protected function buildRulePrompt(array $matchHistory): string
    {
        $prompt = "Analyze these successful matches and suggest a new matching rule:\n\n";

        foreach ($matchHistory as $match) {
            $prompt .= "- Transaction: {$match['vendor']} \${$match['amount']} -> Order: {$match['platform']} #{$match['order_id']}\n";
        }

        $prompt .= "\nRespond in JSON format:\n";
        $prompt .= '{"name": "rule name", "vendor_pattern": "regex", "platform": "platform", "amount_tolerance": 0.00, "confidence": 0.0-1.0, "explanation": "why this rule in Russian"}';

        return $prompt;
    }

    protected function parseMatchResponse(string $response, Transaction $transaction): ?array
    {
        // Extract JSON from response
        if (preg_match('/\{[^}]+\}/', $response, $matches)) {
            $data = json_decode($matches[0], true);
            if ($data && isset($data['confidence'])) {
                return $data;
            }
        }

        return null;
    }

    protected function parseRuleResponse(string $response): ?array
    {
        if (preg_match('/\{[^}]+\}/', $response, $matches)) {
            $data = json_decode($matches[0], true);
            if ($data && isset($data['name'])) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Process feedback and update learning
     */
    public function processFeedback(AISuggestion $suggestion): void
    {
        if ($suggestion->status === 'accepted') {
            // Increase confidence for similar patterns
            Log::info("AI feedback: Suggestion #{$suggestion->id} accepted");
        } elseif ($suggestion->status === 'rejected') {
            // Learn from rejection
            Log::info("AI feedback: Suggestion #{$suggestion->id} rejected - {$suggestion->review_notes}");
        }

        $suggestion->update(['feedback_applied' => true]);
    }
}
