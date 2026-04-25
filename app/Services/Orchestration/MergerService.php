<?php

namespace App\Services\Orchestration;

class MergerService
{
    public function merge(array $workerOutputs, array $acceptanceCriteria = [], array $constraints = []): array
    {
        $decisionLog = [];
        $chosenByFile = [];

        foreach ($workerOutputs as $index => $output) {
            $files = array_values(array_filter((array) ($output['files_to_change'] ?? []), fn ($file) => is_string($file) && trim($file) !== ''));
            if ($files === []) {
                continue;
            }

            $score = $this->score($output, $acceptanceCriteria, $constraints);
            foreach ($files as $file) {
                $current = $chosenByFile[$file] ?? null;
                if ($current === null || $this->wins($score, $current['score'])) {
                    $chosenByFile[$file] = [
                        'source_index' => $index,
                        'score' => $score,
                        'output' => $output,
                    ];
                }
            }
        }

        $finalChangeSet = [];
        $testMatrix = [];

        foreach ($chosenByFile as $file => $entry) {
            $output = $entry['output'];
            $finalChangeSet[] = [
                'file' => $file,
                'proposed_patch' => (string) ($output['proposed_patch'] ?? ''),
                'from_worker_summary' => (string) ($output['summary'] ?? ''),
            ];

            foreach ((array) ($output['tests_to_run'] ?? []) as $test) {
                if (is_string($test) && trim($test) !== '' && ! in_array($test, $testMatrix, true)) {
                    $testMatrix[] = $test;
                }
            }

            $decisionLog[] = [
                'file' => $file,
                'selected_worker_output_index' => $entry['source_index'],
                'priority_trace' => [
                    'acceptance_criteria_match' => $entry['score']['acceptance'],
                    'data_safety_score' => $entry['score']['safety'],
                    'change_surface_score' => $entry['score']['surface'],
                    'test_strength_score' => $entry['score']['tests'],
                    'confidence' => $entry['score']['confidence'],
                ],
            ];
        }

        return [
            'final_change_set' => $finalChangeSet,
            'decision_log' => $decisionLog,
            'test_matrix' => $testMatrix,
        ];
    }

    private function score(array $output, array $acceptanceCriteria, array $constraints): array
    {
        $text = strtolower(
            trim(
                (string) ($output['summary'] ?? '')
                . ' '
                . (string) ($output['proposed_patch'] ?? '')
                . ' '
                . implode(' ', array_filter((array) ($output['risks'] ?? []), 'is_string'))
            )
        );

        $acceptance = 0;
        foreach ($acceptanceCriteria as $criterion) {
            if (! is_string($criterion) || trim($criterion) === '') {
                continue;
            }
            $tokens = preg_split('/\s+/', strtolower($criterion)) ?: [];
            foreach ($tokens as $token) {
                $token = trim($token, " \t\n\r\0\x0B,.;:()[]{}\"'");
                if (strlen($token) < 4) {
                    continue;
                }
                if (str_contains($text, $token)) {
                    $acceptance++;
                }
            }
        }

        $safetyKeywords = ['null', 'valid', 'required', 'optional', 'sanitize', 'guard', 'fallback'];
        foreach ($constraints as $constraint) {
            if (is_string($constraint) && str_contains(strtolower($constraint), 'null')) {
                $safetyKeywords[] = 'null';
            }
        }
        $safety = 0;
        foreach (array_unique($safetyKeywords) as $keyword) {
            if (str_contains($text, $keyword)) {
                $safety++;
            }
        }

        $filesCount = max(1, count((array) ($output['files_to_change'] ?? [])));
        $surface = 100 - min(90, ($filesCount - 1) * 10);

        $tests = count(array_filter((array) ($output['tests_to_run'] ?? []), fn ($test) => is_string($test) && trim($test) !== ''));
        $confidence = (float) ($output['confidence'] ?? 0);

        return [
            'acceptance' => $acceptance,
            'safety' => $safety,
            'surface' => $surface,
            'tests' => $tests,
            'confidence' => $confidence,
        ];
    }

    private function wins(array $a, array $b): bool
    {
        $priorityChecks = [
            'acceptance',
            'safety',
            'surface',
            'tests',
            'confidence',
        ];

        foreach ($priorityChecks as $key) {
            if (($a[$key] ?? 0) === ($b[$key] ?? 0)) {
                continue;
            }

            return ($a[$key] ?? 0) > ($b[$key] ?? 0);
        }

        return false;
    }
}

