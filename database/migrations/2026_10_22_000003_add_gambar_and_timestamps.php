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
        if (Schema::hasTable('menu') && ! Schema::hasColumn('menu', 'gambar')) {
            Schema::table('menu', function (Blueprint $table) {
                $table->string('gambar')->nullable()->after('kategori');
            });
        }

        if (Schema::hasTable('menu') && ! Schema::hasColumn('menu', 'created_at')) {
            Schema::table('menu', function (Blueprint $table) {
                $table->timestamps();
            });
        }

        if (Schema::hasTable('pesanan') && ! Schema::hasColumn('pesanan', 'created_at')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('menu') && Schema::hasColumn('menu', 'gambar')) {
            Schema::table('menu', function (Blueprint $table) {
                $table->dropColumn(['gambar', 'created_at', 'updated_at']);
            });
        }

        if (Schema::hasTable('pesanan') && Schema::hasColumn('pesanan', 'created_at')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->dropTimestamps();
            });
        }
    }
};
