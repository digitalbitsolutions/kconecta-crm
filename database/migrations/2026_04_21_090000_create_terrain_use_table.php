<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terrain_use', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->charset = 'utf8mb4';

            $table->integer('id', true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->string('name', 50);
        });

        DB::table('terrain_use')->insert([
            ['name' => 'Servicios'],
            ['name' => 'Residencial'],
            ['name' => 'Industrial'],
            ['name' => 'Agrícola'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('terrain_use');
    }
};
