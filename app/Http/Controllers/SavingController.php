<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Saving;
use App\Models\SavingDeposit;
use App\Models\Transaction;
use Inertia\Inertia;
use Carbon\Carbon;

class SavingController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $user    = Auth::user();
        $savings = Saving::where('user_id', $user->id)
            ->orderByRaw("FIELD(status, ?, ?, ?)", [Saving::STATUS_ACTIVE, Saving::STATUS_COMPLETED, Saving::STATUS_CANCELLED])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($s) => $this->formatSaving($s));

        $totalTarget = Saving::where('user_id', $user->id)->where('status', Saving::STATUS_ACTIVE)->sum('target_amount');
        $totalSaved  = Saving::where('user_id', $user->id)->where('status', Saving::STATUS_ACTIVE)->sum('current_amount');
        $totalDone   = Saving::where('user_id', $user->id)->where('status', Saving::STATUS_COMPLETED)->count();

        return Inertia::render('Savings', [
            'savings' => $savings,
            'summary' => [
                'totalTarget' => number_format((float) $totalTarget, 0, ',', '.'),
                'totalSaved'  => number_format((float) $totalSaved,  0, ',', '.'),
                'totalDone'   => $totalDone,
                'activePct'   => $totalTarget > 0
                    ? round(($totalSaved / $totalTarget) * 100)
                    : 0,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'target_amount' => 'required|numeric|min:1',
            'deadline'      => 'nullable|date|after:today',
            'color'         => 'nullable|string|max:20',
            'icon'          => 'nullable|string|max:10',
            'notes'         => 'nullable|string|max:300',
        ]);

        Saving::create([
            'user_id'        => Auth::id(),
            'name'           => $request->name,
            'target_amount'  => $request->target_amount,
            'current_amount' => 0,
            'deadline'       => $request->deadline,
            'color'          => $request->color ?? '#10b981',
            'icon'           => $request->icon  ?? '🎯',
            'status'         => Saving::STATUS_ACTIVE,
            'notes'          => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Tabungan berhasil dibuat.');
    }

    // ─────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────
    public function update(Request $request, Saving $saving)
    {
        abort_if($saving->user_id !== Auth::id(), 403);

        $request->validate([
            'name'          => 'required|string|max:100',
            'target_amount' => 'required|numeric|min:1',
            'deadline'      => 'nullable|date',
            'color'         => 'nullable|string|max:20',
            'icon'          => 'nullable|string|max:10',
            'notes'         => 'nullable|string|max:300',
        ]);

        $saving->update([
            'name'          => $request->name,
            'target_amount' => $request->target_amount,
            'deadline'      => $request->deadline,
            'color'         => $request->color ?? $saving->color,
            'icon'          => $request->icon  ?? $saving->icon,
            'notes'         => $request->notes,
        ]);

        $saving->fresh()->autoComplete();

        return redirect()->back()->with('success', 'Tabungan berhasil diupdate.');
    }

    // ─────────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────────
    public function destroy(Saving $saving)
    {
        abort_if($saving->user_id !== Auth::id(), 403);
        $saving->delete();
        return redirect()->back()->with('success', 'Tabungan berhasil dihapus.');
    }

    // ─────────────────────────────────────────────────────────────
    // DEPOSIT — Setor uang ke tabungan
    //
    // Prinsip: Transfer (bukan expense)
    //   • Saldo utama berkurang  → type = 'transfer', deskripsi "Transfer ke Tabungan"
    //   • Saldo tabungan bertambah → increment current_amount
    //   • Tidak masuk laporan income/expense
    // ─────────────────────────────────────────────────────────────
    public function deposit(Request $request, Saving $saving)
    {
        abort_if($saving->user_id !== Auth::id(), 403);
        abort_if(! $saving->isActive(), 422, 'Tabungan sudah selesai atau dibatalkan.');

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'note'   => 'nullable|string|max:200',
        ]);

        $userId = Auth::id();
        $amount = (float) $request->amount;

        DB::transaction(function () use ($saving, $userId, $amount, $request) {
            // 1. Catat sebagai TRANSFER — bukan expense
            $tx = Transaction::create([
                'user_id'     => $userId,
                'category_id' => null,
                'description' => 'Transfer ke Tabungan: ' . $saving->name,
                'amount'      => $amount,
                'type'        => 'transfer',
                'date'        => now()->toDateString(),
                'notes'       => $request->note,
            ]);

            // 2. Catat detail setoran
            SavingDeposit::create([
                'saving_id'      => $saving->id,
                'user_id'        => $userId,
                'amount'         => $amount,
                'type'           => 'deposit',
                'note'           => $request->note,
                'transaction_id' => $tx->id,
            ]);

            // 3. Tambah saldo tabungan
            $saving->increment('current_amount', $amount);

            // 4. Auto-complete jika target tercapai
            $saving->fresh()->autoComplete();
        });

        return redirect()->back()->with('success', 'Setoran berhasil ditambahkan.');
    }

    // ─────────────────────────────────────────────────────────────
    // WITHDRAW — Tarik uang dari tabungan
    //
    // Prinsip: Transfer (bukan income)
    //   • Saldo utama bertambah  → type = 'transfer', deskripsi "Penarikan dari Tabungan"
    //   • Saldo tabungan berkurang → decrement current_amount
    //   • Tidak masuk laporan income/expense
    // ─────────────────────────────────────────────────────────────
    public function withdraw(Request $request, Saving $saving)
    {
        abort_if($saving->user_id !== Auth::id(), 403);

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'note'   => 'nullable|string|max:200',
        ]);

        $amount = (float) $request->amount;

        if ($amount > (float) $saving->current_amount) {
            return redirect()->back()->withErrors([
                'amount' => 'Jumlah penarikan melebihi saldo tabungan.',
            ]);
        }

        $userId = Auth::id();

        DB::transaction(function () use ($saving, $userId, $amount, $request) {
            // 1. Catat sebagai TRANSFER — bukan income
            $tx = Transaction::create([
                'user_id'     => $userId,
                'category_id' => null,
                'description' => 'Penarikan dari Tabungan: ' . $saving->name,
                'amount'      => $amount,
                'type'        => 'transfer',
                'date'        => now()->toDateString(),
                'notes'       => $request->note,
            ]);

            // 2. Catat detail penarikan
            SavingDeposit::create([
                'saving_id'      => $saving->id,
                'user_id'        => $userId,
                'amount'         => $amount,
                'type'           => 'withdrawal',
                'note'           => $request->note,
                'transaction_id' => $tx->id,
            ]);

            // 3. Kurangi saldo tabungan
            $saving->decrement('current_amount', $amount);

            // 4. Jika sebelumnya completed, kembalikan ke active
            if ($saving->isCompleted()) {
                $saving->update(['status' => Saving::STATUS_ACTIVE]);
            }
        });

        return redirect()->back()->with('success', 'Penarikan berhasil.');
    }

    // ─────────────────────────────────────────────────────────────
    // DEPOSITS — Riwayat setoran/penarikan
    // ─────────────────────────────────────────────────────────────
    public function deposits(Saving $saving)
    {
        abort_if($saving->user_id !== Auth::id(), 403);

        $deposits = SavingDeposit::where('saving_id', $saving->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($d) => [
                'id'         => $d->id,
                'amount'     => number_format((float) $d->amount, 0, ',', '.'),
                'type'       => $d->type,
                'note'       => $d->note,
                'created_at' => Carbon::parse($d->created_at)->translatedFormat('d M Y, H:i'),
            ]);

        return response()->json($deposits);
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER: Format satu saving untuk Vue
    // ─────────────────────────────────────────────────────────────
    private function formatSaving(Saving $s): array
    {
        $target  = (float) $s->target_amount;
        $current = (float) $s->current_amount;
        $pct     = $target > 0 ? (int) min(round(($current / $target) * 100), 100) : 0;

        $monthlyNeeded = null;
        if ($s->deadline && $s->status === 'active') {
            $remaining = max($target - $current, 0);
            $months    = max(Carbon::now()->diffInMonths($s->deadline, false), 1);
            $monthlyNeeded = $months > 0 ? round($remaining / $months) : $remaining;
        }

        return [
            'id'             => $s->id,
            'name'           => $s->name,
            'icon'           => $s->icon,
            'color'          => $s->color,
            'status'         => $s->status,
            'notes'          => $s->notes,
            'target_amount'  => number_format($target,  0, ',', '.'),
            'current_amount' => number_format($current, 0, ',', '.'),
            'target_raw'     => $target,
            'current_raw'    => $current,
            'remaining'      => number_format(max($target - $current, 0), 0, ',', '.'),
            'pct'            => $pct,
            'deadline'       => $s->deadline ? $s->deadline->format('Y-m-d') : null,
            'deadline_label' => $s->deadline ? $s->deadline->translatedFormat('d F Y') : null,
            'monthly_needed' => $monthlyNeeded ? number_format($monthlyNeeded, 0, ',', '.') : null,
        ];
    }
}