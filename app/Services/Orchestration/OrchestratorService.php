<?php

namespace App\Services\Orchestration;

use App\Services\Orchestration\Contracts\WorkerDriver;
use App\Services\Orchestration\Support\WorkerOutputValidator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OrchestratorService
{
    public function __construct(
        private readonly PlannerService $plannerService,
        private readonly MergerService $mergerService,
        private readonly WorkerDriver $workerDriver,
        private readonly WorkerOutputValidator $workerOutputValidator
    ) {
    }

    public function plan(array $payload): array
    {
        $plan = $this->plannerService->buildPlan($payload);
        $taskId = $plan['task_id'];
        $cacheKey = $this->cacheKey($taskId, 'plan');

        Cache::put($cacheKey, $plan, $this->cacheTtlSeconds());

        return $plan;
    }

    public function run(array $payload): array
    {
        $plan = $payload['plan'] ?? $this->plan($payload);
        $taskId = (string) ($plan['task_id'] ?? Str::uuid()->toString());
        $subtasks = (array) ($plan['subtasks'] ?? []);
        $workerOutputs = [];
        $executionLog = [];

        foreach ($subtasks as $subtask) {
            $worker = (string) ($subtask['worker'] ?? '');
            if ($worker === '') {
                continue;
            }

            $attempts = 0;
            $maxAttempts = 2;
            $resolved = false;
            $lastError = null;
            $workerOutput = [];

            while ($attempts < $maxAttempts && ! $resolved) {
                $attempts++;
                $workerPayload = [
                    'task_id' => $taskId,
                    'goal' => (string) ($plan['goal'] ?? ''),
                    'constraints' => (array) ($plan['constraints'] ?? []),
                    'acceptance_criteria' => (array) ($plan['acceptance_criteria'] ?? []),
                    'repo_context_paths' => (array) ($subtask['repo_context_paths'] ?? []),
                    'summary' => 'Respuesta de ' . $worker . ' para subtask ' . (string) ($subtask['id'] ?? 'sin-id'),
                    'attempt' => $attempts,
                ];

                if ($attempts > 1) {
                    $workerPayload['goal'] = $this->compressPrompt($workerPayload['goal']);
                }

                try {
                    $workerOutput = $this->workerDriver->run($worker, $workerPayload);
                    $resolved = $this->workerOutputValidator->isValid($workerOutput);
                    if (! $resolved) {
                        $lastError = 'Contrato JSON invalido';
                    }
                } catch (\Throwable $exception) {
                    $lastError = $exception->getMessage();
                }
            }

            if (! $resolved) {
                $fallbackWorker = $this->fallbackWorker($worker);
                $fallbackPayload = [
                    'task_id' => $taskId,
                    'goal' => $this->compressPrompt((string) ($plan['goal'] ?? '')),
                    'repo_context_paths' => (array) ($subtask['repo_context_paths'] ?? []),
                    'summary' => 'Fallback desde ' . $worker . ' hacia ' . $fallbackWorker,
                ];
                $workerOutput = $this->workerDriver->run($fallbackWorker, $fallbackPayload);
                $resolved = $this->workerOutputValidator->isValid($workerOutput);

                $executionLog[] = [
                    'subtask_id' => (string) ($subtask['id'] ?? ''),
                    'worker' => $worker,
                    'attempts' => $attempts,
                    'fallback_worker' => $fallbackWorker,
                    'fallback_used' => true,
                    'error' => $lastError,
                    'contract_valid' => $resolved,
                ];
            } else {
                $executionLog[] = [
                    'subtask_id' => (string) ($subtask['id'] ?? ''),
                    'worker' => $worker,
                    'attempts' => $attempts,
                    'fallback_worker' => null,
                    'fallback_used' => false,
                    'error' => null,
                    'contract_valid' => true,
                ];
            }

            if ($resolved) {
                $workerOutputs[] = $workerOutput;
            }
        }

        $response = [
            'task_id' => $taskId,
            'worker_outputs' => $workerOutputs,
            'execution_log' => $executionLog,
        ];

        Cache::put($this->cacheKey($taskId, 'run'), $response, $this->cacheTtlSeconds());

        return $response;
    }

    public function merge(array $payload): array
    {
        $taskId = (string) ($payload['task_id'] ?? Str::uuid()->toString());
        $workerOutputs = (array) ($payload['worker_outputs'] ?? []);
        $acceptanceCriteria = (array) ($payload['acceptance_criteria'] ?? []);
        $constraints = (array) ($payload['constraints'] ?? []);

        $merged = $this->mergerService->merge($workerOutputs, $acceptanceCriteria, $constraints);
        $merged['task_id'] = $taskId;

        Cache::put($this->cacheKey($taskId, 'merge'), $merged, $this->cacheTtlSeconds());

        return $merged;
    }

    private function compressPrompt(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return $text;
        }

        if (mb_strlen($text) <= 180) {
            return $text;
        }

        return mb_substr($text, 0, 180);
    }

    private function fallbackWorker(string $worker): string
    {
        return match ($worker) {
            'deepseek' => 'gemma',
            'mistral' => 'gemma',
            'gemma' => 'deepseek',
            default => 'gemma',
        };
    }

    private function cacheTtlSeconds(): int
    {
        return max(60, (int) config('orchestrator.cache.ttl_seconds', 900));
    }

    private function cacheKey(string $taskId, string $segment): string
    {
        return 'orchestrator:task:' . $taskId . ':' . $segment;
    }
}

