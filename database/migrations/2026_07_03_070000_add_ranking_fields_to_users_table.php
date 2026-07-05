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
            $table->string('league')->default('bronze');
            $table->integer('weekly_points')->default(0);
            $table->integer('last_weekly_points')->default(0);
            $table->integer('last_weekly_rank')->nullable();
            $table->string('last_weekly_status')->nullable();
            $table->boolean('seen_weekly_result')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'league',
                'weekly_points',
                'last_weekly_points',
                'last_weekly_rank',
                'last_weekly_status',
                'seen_weekly_result'
            ]);
        });
    }
};
