<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\Orchestration\OrchestratorService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orchestrator:run {task_id} {goal} {--paths=} {--constraints=} {--acceptance=}', function (
    OrchestratorService $orchestratorService
) {
    $paths = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('paths')))));
    $constraints = array_values(array_filter(array_map('trim', explode('|', (string) $this->option('constraints')))));
    $acceptance = array_values(array_filter(array_map('trim', explode('|', (string) $this->option('acceptance')))));

    $result = $orchestratorService->run([
        'task_id' => (string) $this->argument('task_id'),
        'goal' => (string) $this->argument('goal'),
        'repo_context_paths' => $paths,
        'constraints' => $constraints,
        'acceptance_criteria' => $acceptance,
    ]);

    $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
})->purpose('Run local orchestrator pipeline');
