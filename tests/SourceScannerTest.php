<?php

declare(strict_types=1);

namespace BitrixIdeHelper\Tests;

use BitrixIdeHelper\SourceScanner;
use PhpParser\Error;
use PHPUnit\Framework\TestCase;

final class SourceScannerTest extends TestCase
{
    public function testItCanSkipAnInvalidPhpFile(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'bitrix-invalid-');
        self::assertIsString($file);
        file_put_contents($file, '<?php class Broken {');

        $reportedFile = null;
        $reportedError = null;
        $symbols = (new SourceScanner())->symbols(
            $file,
            static function (string $file, Error $error) use (&$reportedFile, &$reportedError): void {
                $reportedFile = $file;
                $reportedError = $error;
            },
        );

        self::assertSame([], $symbols);
        self::assertSame($file, $reportedFile);
        self::assertInstanceOf(Error::class, $reportedError);
    }
}
