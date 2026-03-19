<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ProviderAvailabilityStore
{
    public function get(int $providerId): array
    {
        $key = $this->cacheKey($providerId);
        $current = Cache::get($key);

        if (is_array($current) && isset($current['revision'], $current['timezone'], $current['slots'])) {
            return [
                'provider_id' => $providerId,
                'revision' => (int) $current['revision'],
                'timezone' => (string) $current['timezone'],
                'slots' => $this->normalizeSlots($current['slots']),
                'updated_at' => (string) ($current['updated_at'] ?? now()->toIso8601String()),
                'source' => 'in_memory',
            ];
        }

        $default = [
            'provider_id' => $providerId,
            'revision' => 1,
            'timezone' => 'Europe/Madrid',
            'slots' => $this->defaultSlots(),
            'updated_at' => now()->toIso8601String(),
            'source' => 'in_memory',
        ];

        Cache::put($key, [
            'revision' => $default['revision'],
            'timezone' => $default['timezone'],
            'slots' => $default['slots'],
            'updated_at' => $default['updated_at'],
        ], now()->addDays(30));

        return $default;
    }

    public function update(int $providerId, int $expectedRevision, string $timezone, array $slots): ?array
    {
        $current = $this->get($providerId);
        if ($expectedRevision !== (int) $current['revision']) {
            return null;
        }

        $next = [
            'revision' => $expectedRevision + 1,
            'timezone' => $timezone,
            'slots' => $this->normalizeSlots($slots),
            'updated_at' => now()->toIso8601String(),
        ];

        Cache::put($this->cacheKey($providerId), $next, now()->addDays(30));

        return [
            'provider_id' => $providerId,
            'revision' => $next['revision'],
            'timezone' => $next['timezone'],
            'slots' => $next['slots'],
            'updated_at' => $next['updated_at'],
            'source' => 'in_memory',
        ];
    }

    private function cacheKey(int $providerId): string
    {
        return 'mobile.providers.availability.' . $providerId;
    }

    private function defaultSlots(): array
    {
        return [
            ['day' => 'mon', 'start' => '09:00', 'end' => '18:00', 'enabled' => true],
            ['day' => 'tue', 'start' => '09:00', 'end' => '18:00', 'enabled' => true],
            ['day' => 'wed', 'start' => '09:00', 'end' => '18:00', 'enabled' => true],
            ['day' => 'thu', 'start' => '09:00', 'end' => '18:00', 'enabled' => true],
            ['day' => 'fri', 'start' => '09:00', 'end' => '18:00', 'enabled' => true],
            ['day' => 'sat', 'start' => '10:00', 'end' => '14:00', 'enabled' => false],
            ['day' => 'sun', 'start' => '10:00', 'end' => '14:00', 'enabled' => false],
        ];
    }

    private function normalizeSlots(array $slots): array
    {
        $normalized = [];
        foreach ($slots as $slot) {
            if (! is_array($slot)) {
                continue;
            }

            $day = isset($slot['day']) ? (string) $slot['day'] : '';
            $start = isset($slot['start']) ? (string) $slot['start'] : '';
            $end = isset($slot['end']) ? (string) $slot['end'] : '';
            $enabled = (bool) ($slot['enabled'] ?? false);

            $normalized[] = [
                'day' => $day,
                'start' => $start,
                'end' => $end,
                'enabled' => $enabled,
            ];
        }

        return $normalized;
    }
}
