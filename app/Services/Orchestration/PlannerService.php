<?php

namespace App\Services\Orchestration;

use App\Services\Orchestration\Support\ContextReducer;
use Illuminate\Support\Str;

class PlannerService
{
    public function __construct(private readonly ContextReducer $contextReducer)
    {
    }

    public function buildPlan(array $payload): array
    {
        $taskId = (string) ($payload['task_id'] ?? Str::uuid()->toString());
        $goal = trim((string) ($payload['goal'] ?? ''));
        $constraints = array_values(array_filter((array) ($payload['constraints'] ?? []), fn ($v) => is_string($v)));
        $acceptanceCriteria = array_values(array_filter((array) ($payload['acceptance_criteria'] ?? []), fn ($v) => is_string($v)));
        $paths = $this->contextReducer->reduce((array) ($payload['repo_context_paths'] ?? []));

        $subtasks = [
            [
                'id' => $taskId . '-backend',
                'worker' => 'deepseek',
                'role' => 'worker-backend',
                'focus' => 'Validaciones, reglas, consultas y controladores.',
                'repo_context_paths' => $paths,
            ],
            [
                'id' => $taskId . '-frontend',
                'worker' => 'mistral',
                'role' => 'worker-frontend',
                'focus' => 'Blade, JS, CSS y consistencia UI.',
                'repo_context_paths' => $paths,
            ],
            [
                'id' => $taskId . '-auditor',
                'worker' => 'gemma',
                'role' => 'worker-auditor',
                'focus' => 'Nulos, edge cases y regresiones.',
                'repo_context_paths' => $paths,
            ],
        ];

        return [
            'task_id' => $taskId,
            'goal' => $goal,
            'constraints' => $constraints,
            'acceptance_criteria' => $acceptanceCriteria,
            'subtasks' => $subtasks,
        ];
    }
}

