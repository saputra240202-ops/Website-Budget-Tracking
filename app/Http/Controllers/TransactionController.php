<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Category;
use Inertia\Inertia;
use Carbon\Carbon;

class TransactionController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // HELPER: Hitung ulang used_amount dari total transaksi nyata
    // ──────────────────────────────────────────────────────────────
    private function recalculateBudget(int $userId, ?int $categoryId): void
    {
        if (!$categoryId) return;

        $budget = Budget::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->first();

        if (!$budget) return;

        $totalUsed = Transaction::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->where('type', 'expense')
            ->sum('amount');

        $budget->update(['used_amount' => $totalUsed]);
    }

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = Transaction::where('user_id', $user->id)->with('category');

        // Filter pencarian
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Filter bulan (format: YYYY-MM)
        if ($request->filled('month')) {
            $query->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$request->month]);
        }

        // Filter tipe
        // Filter tipe: income | expense | transfer
        if ($request->filled('type') && in_array($request->type, ['income', 'expense', 'transfer'])) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($tx) => [
                'id'          => $tx->id,
                'description' => $tx->description,
                'amount'      => number_format($tx->amount, 0, ',', '.'),
                'amount_raw'  => (float) $tx->amount,
                'type'        => $tx->type,
                'date'        => Carbon::parse($tx->date)->format('Y-m-d'),
                'date_label'  => Carbon::parse($tx->date)->translatedFormat('d M Y'),
                'notes'       => $tx->notes,
                'category_id' => $tx->category_id,
                'category'    => $tx->category?->name ?? ($tx->type === 'transfer' ? 'Transfer' : 'Uncategorized'),
                'color'       => $tx->category?->color ?? '#64748b',
            ]);

        $categories = Category::where('user_id', $user->id)->orderBy('name')->get(['id', 'name', 'type', 'color']);

        // ── Ambil SEMUA bulan yang ada transaksi (tanpa filter apapun) ──
        // Query terpisah tanpa filter supaya dropdown selalu lengkap
        $availableMonths = Transaction::where('user_id', $user->id)
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as ym")
            ->distinct()
            ->orderBy('ym', 'asc')
            ->pluck('ym')
            ->map(function ($ym) {
                $date = Carbon::createFromFormat('Y-m', $ym);
                return [
                    'value' => $ym,
                    'label' => $date->translatedFormat('F Y'),
                ];
            })
            ->values();

        return Inertia::render('Transactions', [
            'transactions'    => $transactions,
            'categories'      => $categories,
            'availableMonths' => $availableMonths,
            'filters'         => $request->only(['search', 'month', 'type']),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'type'        => 'required|in:income,expense',
            'date'        => 'required|date',
            'category_id' => 'nullable|exists:categories,id',
            'notes'       => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();

        Transaction::create([
            'user_id'     => $userId,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'amount'      => $request->amount,
            'type'        => $request->type,
            'date'        => $request->date,
            'notes'       => $request->notes,
        ]);

        if ($request->type === 'expense') {
            $this->recalculateBudget($userId, $request->category_id);
        }

        return redirect()->back()->with('success', 'Transaksi berhasil ditambahkan.');
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, Transaction $transaction)
    {
        abort_if($transaction->user_id !== Auth::id(), 403);

        $request->validate([
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'type'        => 'required|in:income,expense',
            'date'        => 'required|date',
            'category_id' => 'nullable|exists:categories,id',
            'notes'       => 'nullable|string|max:500',
        ]);

        $userId        = Auth::id();
        $oldCategoryId = $transaction->category_id;
        $oldType       = $transaction->type;

        $transaction->update([
            'category_id' => $request->category_id,
            'description' => $request->description,
            'amount'      => $request->amount,
            'type'        => $request->type,
            'date'        => $request->date,
            'notes'       => $request->notes,
        ]);

        if ($oldType === 'expense' && $oldCategoryId) {
            $this->recalculateBudget($userId, $oldCategoryId);
        }

        if ($request->type === 'expense' && $request->category_id) {
            if ($request->category_id != $oldCategoryId) {
                $this->recalculateBudget($userId, $request->category_id);
            }
        }

        return redirect()->back()->with('success', 'Transaksi berhasil diupdate.');
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(Transaction $transaction)
    {
        abort_if($transaction->user_id !== Auth::id(), 403);

        $userId     = Auth::id();
        $categoryId = $transaction->category_id;
        $type       = $transaction->type;

        $transaction->delete();

        if ($type === 'expense') {
            $this->recalculateBudget($userId, $categoryId);
        }

        return redirect()->back()->with('success', 'Transaksi berhasil dihapus.');
    }
}