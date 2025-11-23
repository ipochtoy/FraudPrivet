<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MatchingRule;

class MatchingRuleController extends Controller
{
    public function index()
    {
        $rules = MatchingRule::orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('rules.index', compact('rules'));
    }

    public function create()
    {
        return view('rules.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'vendor_pattern' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:50',
            'amount_min' => 'nullable|numeric',
            'amount_max' => 'nullable|numeric',
            'amount_tolerance' => 'nullable|numeric',
            'date_tolerance_days' => 'nullable|integer|min:0|max:30',
            'discount_tolerance' => 'nullable|numeric',
            'allow_splits' => 'boolean',
            'max_split_parts' => 'nullable|integer|min:2|max:10',
            'priority' => 'nullable|integer|min:0|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = 'manual';

        $rule = MatchingRule::create($validated);

        return redirect()->route('rules.index')
            ->with('success', "Rule '{$rule->name}' created successfully");
    }

    public function show(MatchingRule $rule)
    {
        return view('rules.show', compact('rule'));
    }

    public function edit(MatchingRule $rule)
    {
        return view('rules.edit', compact('rule'));
    }

    public function update(Request $request, MatchingRule $rule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'vendor_pattern' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:50',
            'amount_min' => 'nullable|numeric',
            'amount_max' => 'nullable|numeric',
            'amount_tolerance' => 'nullable|numeric',
            'date_tolerance_days' => 'nullable|integer|min:0|max:30',
            'discount_tolerance' => 'nullable|numeric',
            'allow_splits' => 'boolean',
            'max_split_parts' => 'nullable|integer|min:2|max:10',
            'priority' => 'nullable|integer|min:0|max:1000',
            'is_active' => 'boolean',
        ]);

        $rule->update($validated);

        return redirect()->route('rules.index')
            ->with('success', "Rule '{$rule->name}' updated successfully");
    }

    public function destroy(MatchingRule $rule)
    {
        $name = $rule->name;
        $rule->delete();

        return redirect()->route('rules.index')
            ->with('success', "Rule '{$name}' deleted successfully");
    }

    public function toggle(MatchingRule $rule)
    {
        $rule->update(['is_active' => !$rule->is_active]);

        $status = $rule->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Rule '{$rule->name}' {$status}");
    }
}
