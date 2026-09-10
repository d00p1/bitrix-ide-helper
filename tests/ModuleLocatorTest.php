<?php

declare(strict_types=1);

namespace BitrixIdeHelper\Tests;

use BitrixIdeHelper\ModuleLocator;
use PHPUnit\Framework\TestCase;

final class ModuleLocatorTest extends TestCase
{
    public function testItFindsCoreAndLocalModules(): void
    {
        $root = sys_get_temp_dir() . '/bitrix-ide-helper-' . bin2hex(random_bytes(4));
        mkdir($root . '/bitrix/modules/main', 0777, true);
        mkdir($root . '/local/modules/vendor.module', 0777, true);

        $modules = (new ModuleLocator())->locate($root);

        self::assertSame(['main', 'vendor.module'], array_column($modules, 'name'));
        self::assertSame(['core', 'local'], array_column($modules, 'scope'));
    }
}
