<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CadastralPrice;
use Illuminate\Support\Str;

class ImportCadastralPrices extends Command
{
    protected $signature = 'cadastral:import {file : Path to the CSV file}';
    protected $description = 'Import cadastral prices from a CSV file';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found at: {$filePath}");
            return 1;
        }

        $this->info("Importing from {$filePath}");
        $batchId = (string) Str::uuid();

        if (($handle = fopen($filePath, "r")) !== false) {
            $header = fgetcsv($handle, 1000, ","); // Assuming comma separated
            if (!$header) {
                $this->error("Could not read header");
                return 1;
            }

            // Expected columns: province, municipality, neighborhood, postal_code, price_m2_eur
            $batch = [];
            $batchSize = 1000;
            $count = 0;

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                // Check if row has enough data
                if (count($data) < 5) continue;

                $batch[] = [
                    'province' => mb_substr(trim($data[0]), 0, 255),
                    'municipality' => mb_substr(trim($data[1]), 0, 255),
                    'neighborhood' => mb_substr(trim($data[2] ?? ''), 0, 255) ?: null,
                    'postal_code' => mb_substr(trim($data[3]), 0, 10),
                    'price_m2_eur' => is_numeric($data[4]) ? (float) $data[4] : 0,
                    'import_batch_id' => $batchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) >= $batchSize) {
                    $this->insertBatch($batch);
                    $count += count($batch);
                    $batch = [];
                    $this->info("Imported {$count} records...");
                }
            }

            if (count($batch) > 0) {
                $this->insertBatch($batch);
                $count += count($batch);
                $this->info("Imported {$count} records...");
            }

            fclose($handle);
            $this->info("Import completed successfully! Total imported: {$count}");
        } else {
            $this->error("Failed to open file.");
            return 1;
        }

        return 0;
    }

    private function insertBatch(array $batch)
    {
        // Require at least Laravel 8 for upsert functionality
        CadastralPrice::upsert(
            $batch,
            ['postal_code', 'municipality', 'neighborhood'],
            ['province', 'price_m2_eur', 'import_batch_id', 'updated_at']
        );
    }
}
