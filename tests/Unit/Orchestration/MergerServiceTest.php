<?php

namespace Tests\Unit\Orchestration;

use App\Services\Orchestration\MergerService;
use PHPUnit\Framework\TestCase;

class MergerServiceTest extends TestCase
{
    public function test_merge_prioritizes_acceptance_and_data_safety(): void
    {
        $service = new MergerService();

        $safeOutput = [
            'summary' => 'Incluye validaciones required y manejo null.',
            'files_to_change' => ['app/Http/Controllers/UserController.php'],
            'proposed_patch' => "diff --git a/x b/x\n+validate required nullable null guard",
            'risks' => ['Regresion baja'],
            'tests_to_run' => ['php artisan test --filter=UserController'],
            'confidence' => 0.8,
        ];

        $unsafeOutput = [
            'summary' => 'Cambio rapido sin validar.',
            'files_to_change' => ['app/Http/Controllers/UserController.php'],
            'proposed_patch' => "diff --git a/x b/x\n+quick patch",
            'risks' => ['Riesgo alto'],
            'tests_to_run' => [],
            'confidence' => 0.81,
        ];

        $result = $service->merge(
            [$unsafeOutput, $safeOutput],
            ['validaciones null requeridas'],
            ['priorizar null']
        );

        $this->assertCount(1, $result['final_change_set']);
        $this->assertStringContainsString('validate required nullable null guard', $result['final_change_set'][0]['proposed_patch']);
    }
}

