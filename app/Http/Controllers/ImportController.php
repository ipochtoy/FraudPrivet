<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Parsers\QuickBooksParser;
use App\Services\Parsers\EbayParser;
use App\Services\Parsers\AmazonParser;
use App\Services\MatchingEngine;

class ImportController extends Controller
{
    public function index()
    {
        return view('import.index');
    }

    public function importTransactions(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'source' => 'required|in:quickbooks',
        ]);

        $parser = new QuickBooksParser();
        $results = $parser->parseCsv($request->file('file')->getPathname());

        return redirect()->route('import.index')
            ->with('success', "Imported {$results['imported']} transactions")
            ->with('errors', $results['errors']);
    }

    public function importOrders(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'platform' => 'required|in:ebay,amazon',
        ]);

        $parser = match ($request->platform) {
            'ebay' => new EbayParser(),
            'amazon' => new AmazonParser(),
        };

        $results = $parser->parseCsv($request->file('file')->getPathname());

        return redirect()->route('import.index')
            ->with('success', "Imported {$results['imported']} orders")
            ->with('errors', $results['errors']);
    }

    public function runAutoMatch(Request $request)
    {
        $minConfidence = (float) $request->input('min_confidence', 0.95);

        $engine = new MatchingEngine();
        $results = $engine->autoMatch($minConfidence);

        return redirect()->route('dashboard')
            ->with('success', "Auto-matched {$results['matched']} transactions, skipped {$results['skipped']}")
            ->with('errors', $results['errors']);
    }
}
