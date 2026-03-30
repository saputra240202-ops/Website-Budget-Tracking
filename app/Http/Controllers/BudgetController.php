<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Budget;
use App\Models\Category;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $budgets = Budget::where('user_id', $user->id)
            ->with('category')
            ->get()
            ->map(fn($b) => [
                'id'        => $b->id,
                'category'  => $b->category?->name ?? 'Uncategorized',
                'category_id'=> $b->category_id,
                'color'     => $b->category?->color ?? '#64748b',
                'limit'     => (float) $b->limit_amount,
                'used'      => (float) $b->used_amount,
                'pct'       => $b->pct,
                'remaining' => $b->remaining,
                'period'    => $b->period,
            ]);

        $categories = Category::where('user_id', $user->id)
            ->where('type', 'expense')
            ->get(['id', 'name', 'color']);

        return Inertia::render('Budget', [
            'budgets'    => $budgets,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'limit_amount' => 'required|numeric|min:1',
            'used_amount'  => 'nullable|numeric|min:0',
            'period'       => 'required|in:monthly,weekly,yearly',
        ]);

        Budget::create([
            'user_id'      => Auth::id(),
            'category_id'  => $request->category_id,
            'limit_amount' => $request->limit_amount,
            'used_amount'  => $request->used_amount ?? 0,
            'period'       => $request->period,
        ]);

        return redirect()->back()->with('success', 'Budget set successfully.');
    }

    public function update(Request $request, Budget $budget)
    {
        abort_if($budget->user_id !== Auth::id(), 403);

        $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'limit_amount' => 'required|numeric|min:1',
            'used_amount'  => 'nullable|numeric|min:0',
            'period'       => 'required|in:monthly,weekly,yearly',
        ]);

        $budget->update([
            'category_id'  => $request->category_id,
            'limit_amount' => $request->limit_amount,
            'used_amount'  => $request->used_amount ?? 0,
            'period'       => $request->period,
        ]);

        return redirect()->back()->with('success', 'Budget updated successfully.');
    }

    public function destroy(Budget $budget)
    {
        abort_if($budget->user_id !== Auth::id(), 403);
        $budget->delete();
        return redirect()->back()->with('success', 'Budget deleted successfully.');
    }
}