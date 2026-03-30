<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $table = 'budget';

    protected $fillable = [
        'user_id',
        'category_id',
        'limit_amount',
        'used_amount',
        'period',
    ];

    protected $casts = [
        'limit_amount' => 'decimal:2',
        'used_amount'  => 'decimal:2',
    ];

    // Relasi
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Accessor: persentase pemakaian
    public function getPctAttribute(): int
    {
        if ($this->limit_amount <= 0) return 0;
        return (int) round(($this->used_amount / $this->limit_amount) * 100);
    }

    // Accessor: sisa budget
    public function getRemainingAttribute(): float
    {
        return (float) ($this->limit_amount - $this->used_amount);
    }
}