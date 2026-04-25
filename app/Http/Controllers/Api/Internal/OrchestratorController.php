<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Services\Orchestration\OrchestratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrchestratorController extends Controller
{
    public function __construct(private readonly OrchestratorService $orchestratorService)
    {
    }

    public function plan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'nullable|string|max:120',
            'goal' => 'required|string|min:3',
            'repo_context_paths' => 'nullable|array',
            'repo_context_paths.*' => 'string',
            'constraints' => 'nullable|array',
            'constraints.*' => 'string',
            'acceptance_criteria' => 'nullable|array',
            'acceptance_criteria.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Payload invalido.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();
        $payload['task_id'] = (string) ($payload['task_id'] ?? Str::uuid()->toString());
        $plan = $this->orchestratorService->plan($payload);

        return response()->json($plan);
    }

    public function run(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'plan' => 'nullable|array',
            'task_id' => 'nullable|string|max:120',
            'goal' => 'required_without:plan|string|min:3',
            'repo_context_paths' => 'nullable|array',
            'repo_context_paths.*' => 'string',
            'constraints' => 'nullable|array',
            'constraints.*' => 'string',
            'acceptance_criteria' => 'nullable|array',
            'acceptance_criteria.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Payload invalido.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();
        if (! isset($payload['plan'])) {
            $payload['task_id'] = (string) ($payload['task_id'] ?? Str::uuid()->toString());
        }

        return response()->json($this->orchestratorService->run($payload));
    }

    public function merge(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'nullable|string|max:120',
            'worker_outputs' => 'required|array|min:1',
            'worker_outputs.*.summary' => 'required|string',
            'worker_outputs.*.files_to_change' => 'required|array',
            'worker_outputs.*.proposed_patch' => 'required|string',
            'worker_outputs.*.risks' => 'required|array',
            'worker_outputs.*.tests_to_run' => 'required|array',
            'worker_outputs.*.confidence' => 'required|numeric|min:0|max:1',
            'acceptance_criteria' => 'nullable|array',
            'acceptance_criteria.*' => 'string',
            'constraints' => 'nullable|array',
            'constraints.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Payload invalido.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();
        $payload['task_id'] = (string) ($payload['task_id'] ?? Str::uuid()->toString());

        return response()->json($this->orchestratorService->merge($payload));
    }
}

