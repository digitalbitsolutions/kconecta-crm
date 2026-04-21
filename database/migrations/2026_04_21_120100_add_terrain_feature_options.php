<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('feature')) {
            return;
        }

        $features = [
            ['id' => 15, 'name' => 'Agua', 'id_type' => 9, 'category_id' => null],
            ['id' => 16, 'name' => 'Luz', 'id_type' => 9, 'category_id' => null],
            ['id' => 17, 'name' => 'Alcantarillado', 'id_type' => 9, 'category_id' => null],
            ['id' => 18, 'name' => 'Gas natural', 'id_type' => 9, 'category_id' => null],
            ['id' => 19, 'name' => 'Alumbrado publico', 'id_type' => 9, 'category_id' => null],
            ['id' => 20, 'name' => 'Aceras', 'id_type' => 9, 'category_id' => null],
        ];

        foreach ($features as $feature) {
            DB::table('feature')->updateOrInsert(
                ['id' => $feature['id']],
                [
                    'name' => $feature['name'],
                    'id_type' => $feature['id_type'],
                    'category_id' => $feature['category_id'],
                ]
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('feature')) {
            return;
        }

        DB::table('feature')
            ->where('id_type', 9)
            ->whereIn('name', [
                'Agua',
                'Luz',
                'Alcantarillado',
                'Gas natural',
                'Alumbrado publico',
                'Aceras',
            ])
            ->delete();
    }
};
