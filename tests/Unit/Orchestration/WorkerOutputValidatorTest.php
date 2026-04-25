<?php

namespace Tests\Unit\Orchestration;

use App\Services\Orchestration\Support\WorkerOutputValidator;
use PHPUnit\Framework\TestCase;

class WorkerOutputValidatorTest extends TestCase
{
    public function test_it_validates_required_worker_contract(): void
    {
        $validator = new WorkerOutputValidator();

        $validOutput = [
            'summary' => 'Cambio backend y frontend',
            'files_to_change' => ['routes/api.php'],
            'proposed_patch' => "diff --git a/a b/b\n+test",
            'risks' => ['Ninguno critico'],
            'tests_to_run' => ['php artisan test'],
            'confidence' => 0.75,
        ];

        $invalidOutput = [
            'summary' => 'Incompleto',
            'files_to_change' => ['routes/api.php'],
            'proposed_patch' => 'x',
            'confidence' => 2,
        ];

        $this->assertTrue($validator->isValid($validOutput));
        $this->assertFalse($validator->isValid($invalidOutput));
    }
}

