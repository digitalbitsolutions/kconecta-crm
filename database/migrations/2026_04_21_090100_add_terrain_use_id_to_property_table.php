<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property', function (Blueprint $table) {
            if (! Schema::hasColumn('property', 'terrain_use_id')) {
                $table->integer('terrain_use_id')->nullable()->after('type_of_terrain_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('property', function (Blueprint $table) {
            if (Schema::hasColumn('property', 'terrain_use_id')) {
                $table->dropColumn('terrain_use_id');
            }
        });
    }
};
