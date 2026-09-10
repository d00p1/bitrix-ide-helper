<?php

declare(strict_types=1);

namespace BitrixIdeHelper;

use RuntimeException;

final class ModuleLocator
{
    /** @return list<Module> */
    public function locate(string $documentRoot): array
    {
        $documentRoot = rtrim($documentRoot, DIRECTORY_SEPARATOR);
        if (!is_dir($documentRoot)) {
            throw new RuntimeException(sprintf('Document root does not exist: %s', $documentRoot));
        }

        $modules = [];
        foreach (['bitrix' => 'core', 'local' => 'local'] as $directory => $scope) {
            $modulesRoot = $documentRoot . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . 'modules';
            if (!is_dir($modulesRoot)) {
                continue;
            }

            $entries = scandir($modulesRoot);
            if ($entries === false) {
                continue;
            }

            foreach ($entries as $name) {
                if ($name === '.' || $name === '..') {
                    continue;
                }

                $path = $modulesRoot . DIRECTORY_SEPARATOR . $name;
                if (is_dir($path)) {
                    $modules[] = new Module($name, $path, $scope);
                }
            }
        }

        usort($modules, static fn (Module $a, Module $b): int => [$a->name, $a->scope] <=> [$b->name, $b->scope]);

        return $modules;
    }
}
