<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PropertyWorkflowStore
{
    public function getAssignment(int $propertyId): ?array
    {
        $assignment = Cache::get($this->assignmentKey($propertyId));
        if (! is_array($assignment) || ! isset($assignment['provider_id'])) {
            return null;
        }

        return [
            'provider_id' => (int) $assignment['provider_id'],
            'assigned_at' => (string) ($assignment['assigned_at'] ?? ''),
            'note' => isset($assignment['note']) ? (string) $assignment['note'] : null,
        ];
    }

    public function setAssignment(int $propertyId, int $providerId, ?string $note = null): array
    {
        $assignment = [
            'provider_id' => $providerId,
            'assigned_at' => now()->toIso8601String(),
            'note' => $note,
        ];

        Cache::put($this->assignmentKey($propertyId), $assignment, now()->addDays(30));

        return $assignment;
    }

    public function getQueueCompletion(string $queueItemId): ?array
    {
        $item = Cache::get($this->queueCompletionKey($queueItemId));
        if (! is_array($item) || ! isset($item['completed_at'])) {
            return null;
        }

        return [
            'completed_at' => (string) $item['completed_at'],
            'resolution_code' => isset($item['resolution_code']) ? (string) $item['resolution_code'] : null,
            'note' => isset($item['note']) ? (string) $item['note'] : null,
        ];
    }

    public function completeQueueItem(string $queueItemId, ?string $resolutionCode, ?string $note): array
    {
        $completion = [
            'completed_at' => now()->toIso8601String(),
            'resolution_code' => $resolutionCode,
            'note' => $note,
        ];

        Cache::put($this->queueCompletionKey($queueItemId), $completion, now()->addDays(30));

        return $completion;
    }

    private function assignmentKey(int $propertyId): string
    {
        return 'mobile.properties.assignment.' . $propertyId;
    }

    private function queueCompletionKey(string $queueItemId): string
    {
        return 'mobile.properties.queue.' . $queueItemId;
    }
}
