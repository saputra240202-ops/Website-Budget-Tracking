<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    // ─────────────────────────────────────────────────────────────
    // STATUS CONSTANTS — gunakan ini di seluruh aplikasi,
    // jangan hardcode string di controller / view.
    // ─────────────────────────────────────────────────────────────
    const STATUS_ACTIVE    = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /** Semua nilai status yang valid */
    const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'user_id', 'name', 'target_amount', 'current_amount',
        'deadline', 'color', 'icon', 'status', 'notes',
    ];

    protected $casts = [
        'target_amount'  => 'decimal:2',
        'current_amount' => 'decimal:2',
        'deadline'       => 'date',
    ];

    // ─────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deposits()
    {
        return $this->hasMany(SavingDeposit::class)->orderBy('created_at', 'desc');
    }

    // ─────────────────────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────────────────────

    /** Persentase kemajuan (0–100) */
    public function getPctAttribute(): int
    {
        if ($this->target_amount <= 0) return 0;
        return (int) min(round(($this->current_amount / $this->target_amount) * 100), 100);
    }

    /** Sisa yang masih perlu dikumpulkan */
    public function getRemainingAttribute(): float
    {
        return max((float) $this->target_amount - (float) $this->current_amount, 0);
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Tandai tabungan sebagai selesai jika current_amount ≥ target_amount.
     * Gunakan method ini daripada update(['status' => 'completed']) langsung.
     *
     * @return bool true jika status berubah menjadi completed
     */
    public function autoComplete(): bool
    {
        if ((float) $this->current_amount >= (float) $this->target_amount && $this->isActive()) {
            $this->update(['status' => self::STATUS_COMPLETED]);
            return true;
        }
        return false;
    }
}