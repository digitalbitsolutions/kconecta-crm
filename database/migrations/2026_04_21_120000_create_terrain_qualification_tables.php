<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('terrain_qualification')) {
            Schema::create('terrain_qualification', function (Blueprint $table) {
                $table->integer('id', true);
                $table->dateTime('created_at')->nullable()->useCurrent();
                $table->dateTime('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
                $table->string('name', 120)->nullable();
            });
        }

        $qualifications = [
            ['id' => 1, 'name' => 'Residencial en altura (bloques)'],
            ['id' => 2, 'name' => 'Residencial unifamiliar (chalets)'],
            ['id' => 3, 'name' => 'Terciario oficinas'],
            ['id' => 4, 'name' => 'Terciario comercial'],
            ['id' => 5, 'name' => 'Terciario hoteles'],
            ['id' => 6, 'name' => 'Industrial'],
            ['id' => 7, 'name' => 'Dotaciones (hospitales, escuelas, museos)'],
            ['id' => 8, 'name' => 'Otra'],
        ];

        foreach ($qualifications as $qualification) {
            DB::table('terrain_qualification')->updateOrInsert(
                ['id' => $qualification['id']],
                ['name' => $qualification['name']]
            );
        }

        if (! Schema::hasTable('terrain_qualifications')) {
            Schema::create('terrain_qualifications', function (Blueprint $table) {
                $table->integer('id', true);
                $table->dateTime('created_at')->nullable()->useCurrent();
                $table->dateTime('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
                $table->integer('property_id');
                $table->integer('terrain_qualification_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('terrain_qualifications');
        Schema::dropIfExists('terrain_qualification');
    }
};
