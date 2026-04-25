<?php

namespace App\Services\Orchestration\Support;

class WorkerOutputValidator
{
    private const REQUIRED_FIELDS = [
        'summary',
        'files_to_change',
        'proposed_patch',
        'risks',
        'tests_to_run',
        'confidence',
    ];

    public function isValid(array $output): bool
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (! array_key_exists($field, $output)) {
                return false;
            }
        }

        if (! is_string($output['summary']) || trim($output['summary']) === '') {
            return false;
        }

        if (! is_array($output['files_to_change']) || ! is_array($output['risks']) || ! is_array($output['tests_to_run'])) {
            return false;
        }

        if (! is_string($output['proposed_patch']) || trim($output['proposed_patch']) === '') {
            return false;
        }

        if (! is_numeric($output['confidence'])) {
            return false;
        }

        $confidence = (float) $output['confidence'];

        return $confidence >= 0 && $confidence <= 1;
    }
}

