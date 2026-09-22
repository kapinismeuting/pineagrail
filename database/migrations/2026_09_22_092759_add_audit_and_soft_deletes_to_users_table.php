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
            $table->string('username')->nullable()->unique()->after('name');
            $table->softDeletes()->after('remember_token');
            $table->foreignId('action_by_id')
                ->nullable()
                ->after('deleted_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('status_message')->nullable()->after('action_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['action_by_id']);
            $table->dropColumn(['username', 'deleted_at', 'action_by_id', 'status_message']);
        });
    }
};
