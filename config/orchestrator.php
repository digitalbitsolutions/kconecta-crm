<?php

return [
    'enabled' => env('ORCHESTRATOR_ENABLED', true),

    'api_key' => env('ORCHESTRATOR_API_KEY'),

    'context' => [
        'max_paths_per_worker' => 6,
    ],

    'cache' => [
        'ttl_seconds' => 900,
    ],

    'workers' => [
        'deepseek' => [
            'role' => 'worker-backend',
            'description' => 'Validaciones, reglas, consultas y controladores.',
            'provider' => env('ORCH_DEEPSEEK_PROVIDER', 'openai_compatible'),
            'endpoint' => env('ORCH_DEEPSEEK_ENDPOINT'),
            'model' => env('ORCH_DEEPSEEK_MODEL', 'deepseek-coder'),
            'api_key' => env('ORCH_DEEPSEEK_API_KEY'),
            'timeout_seconds' => (int) env('ORCH_DEEPSEEK_TIMEOUT', 45),
        ],
        'mistral' => [
            'role' => 'worker-frontend',
            'description' => 'Vistas Blade, JS, CSS y consistencia visual.',
            'provider' => env('ORCH_MISTRAL_PROVIDER', 'openai_compatible'),
            'endpoint' => env('ORCH_MISTRAL_ENDPOINT'),
            'model' => env('ORCH_MISTRAL_MODEL', 'mistral'),
            'api_key' => env('ORCH_MISTRAL_API_KEY'),
            'timeout_seconds' => (int) env('ORCH_MISTRAL_TIMEOUT', 45),
        ],
        'gemma' => [
            'role' => 'worker-auditor',
            'description' => 'Edge cases, nulos, regresiones y coherencia de negocio.',
            'provider' => env('ORCH_GEMMA_PROVIDER', 'openai_compatible'),
            'endpoint' => env('ORCH_GEMMA_ENDPOINT'),
            'model' => env('ORCH_GEMMA_MODEL', 'gemma'),
            'api_key' => env('ORCH_GEMMA_API_KEY'),
            'timeout_seconds' => (int) env('ORCH_GEMMA_TIMEOUT', 45),
        ],
    ],
];
