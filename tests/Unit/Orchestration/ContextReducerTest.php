<?php

namespace Tests\Unit\Orchestration;

use App\Services\Orchestration\Support\ContextReducer;
use PHPUnit\Framework\TestCase;

class ContextReducerTest extends TestCase
{
    public function test_reduce_keeps_unique_paths_and_respects_limit(): void
    {
        $reducer = new ContextReducer();

        $result = $reducer->reduce([
            'app/Http/Controllers/ApiController.php',
            'resources/views/page/details.blade.php',
            'resources/views/page/details.blade.php',
            'routes/api.php',
            'bootstrap/app.php',
            'config/app.php',
            'tests/Feature/Orchestration/OrchestratorApiTest.php',
        ], 4);

        $this->assertCount(4, $result);
        $this->assertSame([
            'app/Http/Controllers/ApiController.php',
            'resources/views/page/details.blade.php',
            'routes/api.php',
            'bootstrap/app.php',
        ], $result);
    }
}

