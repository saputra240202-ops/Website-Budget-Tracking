<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Memperbaiki ENUM status pada tabel savings:
     *   - 'complete' → 'completed'  (konsisten dengan controller)
     * Nilai existing yang mungkin 'complete' di-migrate ke 'completed' sebelum
     * definisi ENUM diubah, sehingga data lama tidak rusak.
     */
    public function up(): void
    {
        // 1. Ubah sementara kolom menjadi VARCHAR agar bisa menampung nilai lama
        //    dan nilai baru secara bersamaan selama proses migrasi.
        DB::statement("ALTER TABLE savings MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'");

        // 2. Ganti nilai lama 'complete' → 'completed' agar data existing aman
        DB::statement("UPDATE savings SET status = 'completed' WHERE status = 'complete'");

        // 3. Terapkan ENUM baru yang sudah diperbaiki
        DB::statement("ALTER TABLE savings MODIFY COLUMN status ENUM('active','completed','cancelled') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan 'completed' → 'complete' lalu pasang ENUM lama
        DB::statement("ALTER TABLE savings MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'");
        DB::statement("UPDATE savings SET status = 'complete' WHERE status = 'completed'");
        DB::statement("ALTER TABLE savings MODIFY COLUMN status ENUM('active','complete','cancelled') NOT NULL DEFAULT 'active'");
    }
};
