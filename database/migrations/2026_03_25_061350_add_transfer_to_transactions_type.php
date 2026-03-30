<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration 
{
    public function up(): void
    {
        // Tambah nilai 'transfer' ke kolom enum type di tabel transactions
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('income', 'expense', 'transfer') NOT NULL");
    }

    public function down(): void
    {
        // Rollback: hapus semua transfer dulu sebelum hapus enum value-nya
        DB::statement("DELETE FROM transactions WHERE type = 'transfer'");
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('income', 'expense') NOT NULL");
    }
};