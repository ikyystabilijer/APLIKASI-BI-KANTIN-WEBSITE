<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            if (! Schema::hasColumn('pesanan', 'metode_pembayaran')) {
                $table->string('metode_pembayaran', 50)->default('Cash')->after('total_harga');
            }
            if (! Schema::hasColumn('pesanan', 'status_pembayaran')) {
                $table->string('status_pembayaran', 50)->default('Belum Dibayar')->after('metode_pembayaran');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('pesanan', 'status_pembayaran')) {
                $columnsToDrop[] = 'status_pembayaran';
            }
            if (Schema::hasColumn('pesanan', 'metode_pembayaran')) {
                $columnsToDrop[] = 'metode_pembayaran';
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
