<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cek dulu sebelum tambah — hindari error "Duplicate column"
            if (!Schema::hasColumn('users', 'currency')) {
                $table->string('currency')->default('IDR (Rp)')->after('password');
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->default('Western Indonesia Time (WIB)')->after('currency');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('timezone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['currency', 'timezone', 'avatar'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};