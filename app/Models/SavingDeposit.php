<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingDeposit extends Model
{
    /**
     * Laravel default convention: "saving_deposits" (tanpa 's' di 'saving')
     * Migration membuat tabel "savings_deposits" (dengan 's'), jadi kita
     * eksplisitkan di sini agar tidak terjadi mismatch.
     */
    protected $table = 'savings_deposits';

    protected $fillable = [
        'saving_id', 'user_id', 'amount', 'type', 'note', 'transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'type'   => 'string',
    ];

    // Renamed dari saving() → savingGoal() karena 'saving' adalah
    // reserved Eloquent event method (conflict dengan Model::saving())
    public function savingGoal()
    {
        return $this->belongsTo(Saving::class, 'saving_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}