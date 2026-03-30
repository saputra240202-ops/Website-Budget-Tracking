<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Saving;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ── 1. Bulan aktif dari transaksi income/expense terbaru ───────────
        $latestTransaction = Transaction::where('user_id', $user->id)
            ->whereIn('type', ['income', 'expense'])
            ->orderBy('date', 'desc')
            ->first();

        $currentMonth   = $latestTransaction
            ? Carbon::parse($latestTransaction->date)
            : Carbon::now();

        $startOfMonth   = $currentMonth->copy()->startOfMonth();
        $endOfMonth     = $currentMonth->copy()->endOfMonth();
        $lastMonthStart = $currentMonth->copy()->subMonth()->startOfMonth();
        $lastMonthEnd   = $currentMonth->copy()->subMonth()->endOfMonth();

        // ── 2. Total Balance — OPSI B ──────────────────────────────────────
        // Rumus: income - expense - transfer_keluar + transfer_masuk
        //
        // "Transfer keluar"  = setor tabungan   → deskripsi mengandung "Transfer ke Tabungan"
        // "Transfer masuk"   = tarik tabungan   → deskripsi mengandung "Penarikan dari Tabungan"
        //
        // Cara paling bersih: gunakan SUM dengan CASE langsung di DB,
        // setor = amount negatif, tarik = amount positif terhadap saldo dompet
        //
        // Transfer ke tabungan   → KURANGI saldo (transfer keluar dari dompet)
        // Penarikan dari tabungan → TAMBAH saldo (transfer masuk ke dompet)

        $totalIncome  = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->sum('amount');

        $totalExpense = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->sum('amount');

        // Transfer keluar (setor ke tabungan) → kurangi saldo dompet
        $transferOut  = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'transfer')
            ->where('description', 'like', 'Transfer ke Tabungan%')
            ->sum('amount');

        // Transfer masuk (tarik dari tabungan) → tambah saldo dompet
        $transferIn   = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'transfer')
            ->where('description', 'like', 'Penarikan dari Tabungan%')
            ->sum('amount');

        // Saldo dompet = income - expense - uang_yang_disimpan_di_tabungan
        $totalBalance = $totalIncome - $totalExpense - $transferOut + $transferIn;

        // ── 3. Stat Cards bulan aktif ──────────────────────────────────────
        $monthlyIncome = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $monthlyExpense = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $monthlySavingsDeposit = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'transfer')
            ->where('description', 'like', 'Transfer ke Tabungan%')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $freeCash = $monthlyIncome - $monthlyExpense - $monthlySavingsDeposit;

        // ── 4. % Perubahan bulan sebelumnya ───────────────────────────────
        $lastMonthIncome  = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('date', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');

        $lastMonthExpense = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');

        $incomeChange  = $lastMonthIncome  > 0
            ? round((($monthlyIncome  - $lastMonthIncome)  / $lastMonthIncome)  * 100, 1) : 0;
        $expenseChange = $lastMonthExpense > 0
            ? round((($monthlyExpense - $lastMonthExpense) / $lastMonthExpense) * 100, 1) : 0;

        // ── 5. Chart income vs expense 6 bulan ────────────────────────────
        $firstTx = Transaction::where('user_id', $user->id)
            ->whereIn('type', ['income', 'expense'])
            ->orderBy('date', 'asc')
            ->first();

        $chartStart = $firstTx
            ? Carbon::parse($firstTx->date)->startOfMonth()
            : Carbon::now()->subMonths(5)->startOfMonth();

        $chartData = collect(range(0, 5))->map(function ($offset) use ($chartStart, $user) {
            $date  = $chartStart->copy()->addMonths($offset);
            $start = $date->copy()->startOfMonth();
            $end   = $date->copy()->endOfMonth();
            return [
                'month'   => $date->format("M'y"),
                'income'  => (float) Transaction::where('user_id', $user->id)
                    ->where('type', 'income')
                    ->whereBetween('date', [$start, $end])
                    ->sum('amount'),
                'expense' => (float) Transaction::where('user_id', $user->id)
                    ->where('type', 'expense')
                    ->whereBetween('date', [$start, $end])
                    ->sum('amount'),
            ];
        });

        // ── 6. Expense by Category bulan aktif (donut) ────────────────────
        $expenseByCategory = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($group) {
                $category = $group->first()->category;
                return [
                    'name'  => $category ? $category->name  : 'Uncategorized',
                    'color' => $category ? $category->color : '#64748b',
                    'total' => (float) $group->sum('amount'),
                ];
            })
            ->values();

        // ── 7. Recent Transactions 5 terbaru (semua tipe) ─────────────────
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->with('category')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($tx) => [
                'id'       => $tx->id,
                'name'     => $tx->description,
                'date'     => Carbon::parse($tx->date)->translatedFormat('d M Y'),
                'amount'   => number_format((float) $tx->amount, 0, ',', '.'),
                'type'     => $tx->type,
                'income'   => $tx->type === 'income',
                'transfer' => $tx->type === 'transfer',
                'category' => $tx->category?->name ?? ($tx->type === 'transfer' ? 'Transfer' : 'Uncategorized'),
            ]);

        // ── 8. Data Tabungan untuk Dashboard ──────────────────────────────

        // 8a. Progress bar: semua tabungan aktif
        $savingGoals = Saving::where('user_id', $user->id)
            ->where('status', 'active')
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
                    'target'  => number_format($target,  0, ',', '.'),
                    'current' => number_format($current, 0, ',', '.'),
                    'pct'     => $pct,
                ];
            });

        // 8b. Line chart: akumulasi total tabungan per bulan (6 bulan)
        // Dihitung dari transfer keluar (setor) dikurangi transfer masuk (tarik)
        // bersifat kumulatif — tiap bulan dijumlahkan dari awal
        $savingChartData = collect(range(0, 5))->map(function ($offset) use ($chartStart, $user) {
            $date    = $chartStart->copy()->addMonths($offset);
            $monthEnd = $date->copy()->endOfMonth();

            // Akumulatif: total setor sampai akhir bulan ini
            $totalDeposit = (float) Transaction::where('user_id', $user->id)
                ->where('type', 'transfer')
                ->where('description', 'like', 'Transfer ke Tabungan%')
                ->where('date', '<=', $monthEnd)
                ->sum('amount');

            // Akumulatif: total tarik sampai akhir bulan ini
            $totalWithdraw = (float) Transaction::where('user_id', $user->id)
                ->where('type', 'transfer')
                ->where('description', 'like', 'Penarikan dari Tabungan%')
                ->where('date', '<=', $monthEnd)
                ->sum('amount');

            return [
                'month'   => $date->format("M'y"),
                'amount'  => max($totalDeposit - $totalWithdraw, 0),
            ];
        });

        // Total tabungan aktif saat ini
        $totalActiveSavings = (float) Saving::where('user_id', $user->id)
            ->whereIn('status', ['active', 'completed'])
            ->sum('current_amount');

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalBalance'   => number_format($totalBalance,          0, ',', '.'),
                'monthlyIncome'  => number_format($monthlyIncome,         0, ',', '.'),
                'monthlyExpense' => number_format($monthlyExpense,        0, ',', '.'),
                'savings'        => number_format($freeCash,              0, ',', '.'),
                'monthlySavings' => number_format($monthlySavingsDeposit, 0, ',', '.'),
                'totalSavings'   => number_format($totalActiveSavings,    0, ',', '.'),
                'incomeChange'   => $incomeChange,
                'expenseChange'  => $expenseChange,
                'activeMonth'    => $currentMonth->translatedFormat('F Y'),
            ],
            'chartData'          => $chartData,
            'savingChartData'    => $savingChartData,
            'savingGoals'        => $savingGoals,
            'expenseByCategory'  => $expenseByCategory,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}