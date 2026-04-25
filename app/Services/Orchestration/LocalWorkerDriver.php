<?php

namespace App\Services\Orchestration;

use App\Services\Orchestration\Contracts\WorkerDriver;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LocalWorkerDriver implements WorkerDriver
{
    public function run(string $worker, array $payload): array
    {
        $workerConfig = (array) data_get(config('orchestrator.workers'), $worker, []);
        if ($this->hasRemoteRuntime($workerConfig)) {
            return $this->runWithLocalModel($worker, $payload, $workerConfig);
        }

        return $this->runFallback($worker, $payload);
    }

    private function runWithLocalModel(string $worker, array $payload, array $workerConfig): array
    {
        $provider = strtolower(trim((string) ($workerConfig['provider'] ?? 'openai_compatible')));
        $endpoint = trim((string) ($workerConfig['endpoint'] ?? ''));
        $model = trim((string) ($workerConfig['model'] ?? ''));
        $apiKey = trim((string) ($workerConfig['api_key'] ?? ''));
        $timeoutSeconds = max(10, (int) ($workerConfig['timeout_seconds'] ?? 45));

        if ($endpoint === '' || $model === '') {
            throw new RuntimeException('Worker runtime incompleto para ' . $worker . '.');
        }

        $messages = $this->buildMessages($worker, $payload, $workerConfig);
        $response = match ($provider) {
            'ollama' => $this->callOllama($endpoint, $model, $messages, $timeoutSeconds),
            default => $this->callOpenAiCompatible($endpoint, $model, $messages, $timeoutSeconds, $apiKey),
        };

        $content = $this->extractModelContent($provider, $response);
        $json = $this->decodeJsonContent($content);
        $json = $this->normalizeContract($json, $payload);

        return $json;
    }

    private function runFallback(string $worker, array $payload): array
    {
        $role = (string) data_get(config('orchestrator.workers'), $worker . '.role', 'worker');
        $taskId = (string) ($payload['task_id'] ?? 'unknown-task');
        $goal = trim((string) ($payload['goal'] ?? ''));
        $paths = array_values(array_filter((array) ($payload['repo_context_paths'] ?? []), fn ($path) => is_string($path)));

        $summary = trim((string) ($payload['summary'] ?? ''));
        if ($summary === '') {
            $summary = 'Propuesta local para ' . $worker . ' (' . $role . ')';
        }

        $patchHeader = 'diff --git a/placeholder b/placeholder';
        $patchBody = '+ [' . $worker . '] Task ' . $taskId . ': ' . ($goal !== '' ? $goal : 'sin objetivo declarado');

        return [
            'summary' => $summary,
            'files_to_change' => $paths,
            'proposed_patch' => $patchHeader . "\n" . $patchBody,
            'risks' => [
                'Requiere validacion funcional y de regresion antes de publicar.',
            ],
            'tests_to_run' => [
                'php artisan test',
            ],
            'confidence' => 0.78,
        ];
    }

    private function hasRemoteRuntime(array $workerConfig): bool
    {
        return trim((string) ($workerConfig['endpoint'] ?? '')) !== ''
            && trim((string) ($workerConfig['model'] ?? '')) !== '';
    }

    private function buildMessages(string $worker, array $payload, array $workerConfig): array
    {
        $role = (string) ($workerConfig['role'] ?? 'worker');
        $description = (string) ($workerConfig['description'] ?? '');
        $taskId = (string) ($payload['task_id'] ?? 'unknown-task');
        $goal = (string) ($payload['goal'] ?? '');
        $constraints = (array) ($payload['constraints'] ?? []);
        $acceptanceCriteria = (array) ($payload['acceptance_criteria'] ?? []);
        $paths = (array) ($payload['repo_context_paths'] ?? []);

        $systemPrompt = implode("\n", [
            'Eres un worker local del orquestador CRM.',
            'Worker: ' . $worker . ' | Role: ' . $role,
            'Descripcion: ' . $description,
            'Responde EXCLUSIVAMENTE JSON valido.',
            'Contrato obligatorio:',
            '{"summary":string,"files_to_change":array,"proposed_patch":string,"risks":array,"tests_to_run":array,"confidence":number_0_1}',
            'No incluyas markdown ni texto fuera del JSON.',
        ]);

        $userPrompt = implode("\n", [
            'task_id: ' . $taskId,
            'goal: ' . $goal,
            'repo_context_paths: ' . json_encode(array_values($paths), JSON_UNESCAPED_UNICODE),
            'constraints: ' . json_encode(array_values($constraints), JSON_UNESCAPED_UNICODE),
            'acceptance_criteria: ' . json_encode(array_values($acceptanceCriteria), JSON_UNESCAPED_UNICODE),
            'Devuelve el contrato obligatorio.',
        ]);

        return [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];
    }

    private function callOpenAiCompatible(
        string $endpoint,
        string $model,
        array $messages,
        int $timeoutSeconds,
        string $apiKey
    ): array {
        $client = Http::timeout($timeoutSeconds)->acceptJson();
        if ($apiKey !== '') {
            $client = $client->withToken($apiKey);
        }

        $response = $client->post($endpoint, [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.1,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Worker openai_compatible error: HTTP ' . $response->status());
        }

        return (array) $response->json();
    }

    private function callOllama(string $endpoint, string $model, array $messages, int $timeoutSeconds): array
    {
        $response = Http::timeout($timeoutSeconds)
            ->acceptJson()
            ->post($endpoint, [
                'model' => $model,
                'messages' => $messages,
                'stream' => false,
                'options' => ['temperature' => 0.1],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Worker ollama error: HTTP ' . $response->status());
        }

        return (array) $response->json();
    }

    private function extractModelContent(string $provider, array $response): string
    {
        if ($provider === 'ollama') {
            $message = (array) ($response['message'] ?? []);
            $content = (string) ($message['content'] ?? '');
            if (trim($content) === '') {
                throw new RuntimeException('Worker ollama sin contenido.');
            }

            return $content;
        }

        $choices = (array) ($response['choices'] ?? []);
        $firstChoice = (array) ($choices[0] ?? []);
        $message = (array) ($firstChoice['message'] ?? []);
        $content = (string) ($message['content'] ?? '');
        if (trim($content) === '') {
            throw new RuntimeException('Worker openai_compatible sin contenido.');
        }

        return $content;
    }

    private function decodeJsonContent(string $content): array
    {
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $content, $matches) !== 1) {
            throw new RuntimeException('No se pudo extraer JSON del worker.');
        }

        $decoded = json_decode((string) $matches[0], true);
        if (! is_array($decoded)) {
            throw new RuntimeException('JSON invalido en respuesta del worker.');
        }

        return $decoded;
    }

    private function normalizeContract(array $payload, array $taskPayload): array
    {
        $paths = array_values(array_filter((array) ($taskPayload['repo_context_paths'] ?? []), fn ($path) => is_string($path)));

        return [
            'summary' => trim((string) ($payload['summary'] ?? '')) ?: 'Respuesta local sin summary',
            'files_to_change' => $this->normalizeStringArray((array) ($payload['files_to_change'] ?? $paths)),
            'proposed_patch' => trim((string) ($payload['proposed_patch'] ?? 'diff --git a/placeholder b/placeholder' . "\n" . '+ empty')),
            'risks' => $this->normalizeStringArray((array) ($payload['risks'] ?? ['Validar regresiones.'])),
            'tests_to_run' => $this->normalizeStringArray((array) ($payload['tests_to_run'] ?? ['php artisan test'])),
            'confidence' => $this->normalizeConfidence($payload['confidence'] ?? 0.5),
        ];
    }

    private function normalizeStringArray(array $items): array
    {
        $normalized = [];
        foreach ($items as $item) {
            if (! is_string($item)) {
                continue;
            }
            $value = trim($item);
            if ($value !== '') {
                $normalized[] = $value;
            }
        }

        return $normalized;
    }

    private function normalizeConfidence(mixed $value): float
    {
        $float = is_numeric($value) ? (float) $value : 0.5;
        if ($float < 0) {
            return 0.0;
        }
        if ($float > 1) {
            return 1.0;
        }

        return $float;
    }
}
