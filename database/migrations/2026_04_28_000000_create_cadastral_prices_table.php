<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('cadastral_prices')) {
            return;
        }

        Schema::create('cadastral_prices', function (Blueprint $table) {
            $table->id();
            $table->string('province');
            $table->string('municipality');
            $table->string('neighborhood')->nullable();
            $table->string('postal_code', 10);
            $table->decimal('price_m2_eur', 10, 2);
            $table->string('import_batch_id');
            $table->timestamps();

            $table->index('postal_code');
            $table->index('municipality');
            $table->index(['postal_code', 'municipality']);
            $table->unique(['postal_code', 'municipality', 'neighborhood'], 'cadastral_unique_idx');
        });
    }

    public function down(): void {
        Schema::dropIfExists('cadastral_prices');
    }
};
