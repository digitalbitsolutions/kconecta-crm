<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('type_of_terrain')) {
            return;
        }

        $now = now();
        $requiredNames = ['Urbano', 'Urbanizable', 'Rústico'];

        $existingNames = DB::table('type_of_terrain')
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->all();

        foreach ($requiredNames as $name) {
            if (in_array($name, $existingNames, true)) {
                continue;
            }

            DB::table('type_of_terrain')->insert([
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('type_of_terrain')) {
            return;
        }

        DB::table('type_of_terrain')
            ->where('name', 'Urbanizable')
            ->delete();
    }
};
