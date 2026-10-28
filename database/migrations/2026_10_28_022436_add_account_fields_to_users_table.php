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
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->unique();
            $table->string('password', 255)->change();
            $table->string('role', 20)->change();
            $table->boolean('status')->default(true);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'status', 'deleted_at']);
            $table->string('password', 50)->change();
            $table->enum('role', ['admin', 'siswa'])->change();
        });
    }
};
