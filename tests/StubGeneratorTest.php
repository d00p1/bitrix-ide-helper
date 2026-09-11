<?php

declare(strict_types=1);

namespace BitrixIdeHelper\Tests;

use BitrixIdeHelper\StubGenerator;
use PHPUnit\Framework\TestCase;

final class StubGeneratorTest extends TestCase
{
    public function testItRemovesExecutableCodeContainingAnonymousFunctions(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'bitrix-stub-');
        self::assertIsString($file);

        file_put_contents($file, <<<'PHP'
            <?php

            class Example
            {
                public function register(): void
                {
                    array_map(static fn (int $value): int => $value * 2, [1, 2]);
                    $callback = static function (): string {
                        return 'result';
                    };
                }
            }

            $globalCallback = static function (): void {};
            PHP);

        try {
            $stub = (new StubGenerator())->generate($file);
        } finally {
            unlink($file);
        }

        self::assertStringContainsString('public function register(): void', $stub);
        self::assertStringNotContainsString('array_map', $stub);
        self::assertStringNotContainsString('$callback', $stub);
        self::assertStringNotContainsString('$globalCallback', $stub);
    }
}
