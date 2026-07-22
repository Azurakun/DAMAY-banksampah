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
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->date('batch_date');
            $table->string('route'); // 'agent' (Jual ke Agen), 'unit' (Unit Pengolahan Internal)
            $table->decimal('total_weight', 8, 2)->default(0.00);
            $table->integer('total_value')->default(0); // Rp value (0 if unit route)
            $table->string('agent_name')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('distribution_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_id')->constrained('distributions')->onDelete('cascade');
            $table->foreignId('waste_category_id')->nullable()->constrained('waste_categories')->onDelete('set null');
            $table->decimal('weight', 8, 2)->default(0.00);
            $table->integer('price_per_kg')->default(0);
            $table->integer('value')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_items');
        Schema::dropIfExists('distributions');
    }
};
