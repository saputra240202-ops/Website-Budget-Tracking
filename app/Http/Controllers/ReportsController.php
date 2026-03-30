<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Saving;
use App\Models\SavingDeposit;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $user   = Auth::user();
        $months = (int) ($request->period ?? 6);

        // ── Periode chart ──────────────────────────────────────────────────
        $firstTransaction = Transaction::where('user_id', $user->id)
            ->whereIn('type', ['income', 'expense'])
            ->orderBy('date', 'asc')
            ->first();

        $chartStart = $firstTransaction
            ? Carbon::parse($firstTransaction->date)->startOfMonth()
            : Carbon::now()->subMonths($months - 1)->startOfMonth();

        $startDate = $chartStart->copy();
        $endDate   = $chartStart->copy()->addMonths($months - 1)->endOfMonth();
        $prevStart = $startDate->copy()->subMonths($months);
        $prevEnd   = $startDate->copy()->subDay();

        // ── Stat Cards income/expense ──────────────────────────────────────
        $totalIncome  = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $totalExpense = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $netBalance = $totalIncome - $totalExpense;

        $prevIncome  = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('date', [$prevStart, $prevEnd])
            ->sum('amount');

        $prevExpense = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$prevStart, $prevEnd])
            ->sum('amount');

        $incomeChange  = $prevIncome  > 0
            ? round((($totalIncome  - $prevIncome)  / $prevIncome)  * 100, 1) : 0;
        $expenseChange = $prevExpense > 0
            ? round((($totalExpense - $prevExpense) / $prevExpense) * 100, 1) : 0;
        $savingsRate   = $totalIncome > 0
            ? round(($netBalance / $totalIncome) * 100, 1) : 0;

        // ── Bar Chart monthly expense ──────────────────────────────────────
        $monthlyExpenses = collect(range(0, $months - 1))->map(function ($offset) use ($chartStart, $user) {
            $date  = $chartStart->copy()->addMonths($offset);
            $start = $date->copy()->startOfMonth();
            $end   = $date->copy()->endOfMonth();
            return [
                'month'      => $date->format("M'y"),
                'month_full' => $date->translatedFormat('F Y'),
                'value'      => (float) Transaction::where('user_id', $user->id)
                    ->where('type', 'expense')
                    ->whereBetween('date', [$start, $end])
                    ->sum('amount'),
            ];
        });

        // ── Donut spending by category ─────────────────────────────────────
        $spendingByCategory = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($group) use ($totalExpense) {
                $category = $group->first()->category;
                $total    = (float) $group->sum('amount');
                return [
                    'name'  => $category ? $category->name  : 'Uncategorized',
                    'color' => $category ? $category->color : '#64748b',
                    'total' => $total,
                    'pct'   => $totalExpense > 0 ? round(($total / $totalExpense) * 100) : 0,
                ];
            })
            ->sortByDesc('total')
            ->values();

        // ── Section Tabungan ───────────────────────────────────────────────

        // Total disisihkan ke tabungan dalam periode ini
        $totalDeposited = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'transfer')
            ->where('description', 'like', 'Transfer ke Tabungan%')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        // Total ditarik dari tabungan dalam periode ini
        $totalWithdrawn = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'transfer')
            ->where('description', 'like', 'Penarikan dari Tabungan%')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $netSaved = $totalDeposited - $totalWithdrawn;

        // Setor per bulan untuk bar chart tabungan
        $monthlySavings = collect(range(0, $months - 1))->map(function ($offset) use ($chartStart, $user) {
            $date  = $chartStart->copy()->addMonths($offset);
            $start = $date->copy()->startOfMonth();
            $end   = $date->copy()->endOfMonth();

            $deposited  = (float) Transaction::where('user_id', $user->id)
                ->where('type', 'transfer')
                ->where('description', 'like', 'Transfer ke Tabungan%')
                ->whereBetween('date', [$start, $end])
                ->sum('amount');

            $withdrawn  = (float) Transaction::where('user_id', $user->id)
                ->where('type', 'transfer')
                ->where('description', 'like', 'Penarikan dari Tabungan%')
                ->whereBetween('date', [$start, $end])
                ->sum('amount');

            return [
                'month'      => $date->format("M'y"),
                'month_full' => $date->translatedFormat('F Y'),
                'deposited'  => $deposited,
                'withdrawn'  => $withdrawn,
                'net'        => max($deposited - $withdrawn, 0),
            ];
        });

        // Progress semua tabungan (active + completed)
        $savingProgress = Saving::where('user_id', $user->id)
            ->whereIn('status', ['active', 'completed'])
            ->orderByRaw("FIELD(status, 'active', 'completed')")
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($s) {
                $target  = (float) $s->target_amount;
                $current = (float) $s->current_amount;
                $pct     = $target > 0 ? min(round(($current / $target) * 100), 100) : 0;
                return [
                    'id'      => $s->id,
                    'name'    => $s->name,
                    'icon'    => $s->icon,
                    'color'   => $s->color,
                    'status'  => $s->status,
                    'target'  => number_format($target,  0, ',', '.'),
                    'current' => number_format($current, 0, ',', '.'),
                    'pct'     => $pct,
                ];
            });

        return Inertia::render('Reports', [
            'stats' => [
                'totalIncome'   => number_format($totalIncome,  0, ',', '.'),
                'totalExpense'  => number_format($totalExpense, 0, ',', '.'),
                'netBalance'    => number_format($netBalance,   0, ',', '.'),
                'incomeChange'  => $incomeChange,
                'expenseChange' => $expenseChange,
                'savingsRate'   => $savingsRate,
            ],
            'monthlyExpenses'    => $monthlyExpenses,
            'spendingByCategory' => $spendingByCategory,
            'selectedPeriod'     => $months,
            // ── Data tabungan ──
            'savingStats' => [
                'totalDeposited' => number_format($totalDeposited, 0, ',', '.'),
                'totalWithdrawn' => number_format($totalWithdrawn, 0, ',', '.'),
                'netSaved'       => number_format($netSaved,       0, ',', '.'),
            ],
            'monthlySavings'  => $monthlySavings,
            'savingProgress'  => $savingProgress,
        ]);
    }
}