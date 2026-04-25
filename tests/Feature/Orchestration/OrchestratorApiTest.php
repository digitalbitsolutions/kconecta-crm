<?php

namespace Tests\Feature\Orchestration;

use Tests\TestCase;

class OrchestratorApiTest extends TestCase
{
    public function test_plan_endpoint_routes_subtasks_to_expected_workers(): void
    {
        $response = $this->postJson('/api/orchestrate/plan', [
            'task_id' => 'task-routing-001',
            'goal' => 'Actualizar validaciones backend y vistas frontend.',
            'repo_context_paths' => [
                'app/Http/Controllers/ApiController.php',
                'resources/views/page/details.blade.php',
                'resources/views/page/details.blade.php',
            ],
            'constraints' => [
                'No romper flujos actuales',
            ],
            'acceptance_criteria' => [
                'Rutas funcionando',
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('task_id', 'task-routing-001')
            ->assertJsonCount(3, 'subtasks')
            ->assertJsonPath('subtasks.0.worker', 'deepseek')
            ->assertJsonPath('subtasks.1.worker', 'mistral')
            ->assertJsonPath('subtasks.2.worker', 'gemma');
    }

    public function test_run_endpoint_returns_worker_outputs_with_contract_fields(): void
    {
        $response = $this->postJson('/api/orchestrate/run', [
            'task_id' => 'task-contract-001',
            'goal' => 'Aplicar cambios backend y frontend con auditoria.',
            'repo_context_paths' => [
                'app/Http/Controllers/ApiController.php',
                'resources/views/page/details.blade.php',
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('task_id', 'task-contract-001')
            ->assertJsonCount(3, 'worker_outputs');

        foreach ((array) $response->json('worker_outputs') as $output) {
            $this->assertArrayHasKey('summary', $output);
            $this->assertArrayHasKey('files_to_change', $output);
            $this->assertArrayHasKey('proposed_patch', $output);
            $this->assertArrayHasKey('risks', $output);
            $this->assertArrayHasKey('tests_to_run', $output);
            $this->assertArrayHasKey('confidence', $output);
        }
    }
}

