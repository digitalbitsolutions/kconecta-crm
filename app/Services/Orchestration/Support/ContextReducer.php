<?php

namespace App\Services\Orchestration\Support;

class ContextReducer
{
    public function reduce(array $paths, ?int $maxPaths = null): array
    {
        $limit = $maxPaths ?? (int) config('orchestrator.context.max_paths_per_worker', 6);
        $limit = max(1, $limit);

        $seen = [];
        $reduced = [];

        foreach ($paths as $path) {
            if (! is_string($path)) {
                continue;
            }

            $normalized = trim(str_replace('\\', '/', $path));
            if ($normalized === '' || isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $reduced[] = $normalized;

            if (count($reduced) >= $limit) {
                break;
            }
        }

        return $reduced;
    }
}

