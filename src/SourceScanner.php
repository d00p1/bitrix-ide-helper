<?php

declare(strict_types=1);

namespace BitrixIdeHelper;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

final class SourceScanner
{
    /** @return list<string> */
    public function files(Module $module): array
    {
        $roots = array_filter([
            is_dir($module->path . '/lib') ? $module->path . '/lib' : null,
            is_dir($module->path . '/classes') ? $module->path . '/classes' : null,
        ]);

        foreach (['autoload.php', 'include.php'] as $file) {
            if (is_file($module->path . '/' . $file)) {
                $roots[] = $module->path . '/' . $file;
            }
        }

        $files = [];
        foreach ($roots as $root) {
            if (is_file($root)) {
                $files[] = $root;
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
            );
            foreach ($iterator as $item) {
                if ($item instanceof SplFileInfo && $item->isFile() && strtolower($item->getExtension()) === 'php') {
                    $files[] = $item->getPathname();
                }
            }
        }

        sort($files);
        return array_values(array_unique($files));
    }

    /**
     * @param null|callable(string, Error): void $onParseError
     * @return list<DiscoveredSymbol>
     */
    public function symbols(string $file, ?callable $onParseError = null): array
    {
        $code = file_get_contents($file);
        if ($code === false) {
            throw new RuntimeException(sprintf('Unable to read %s', $file));
        }

        try {
            $nodes = (new ParserFactory())->createForNewestSupportedVersion()->parse($code) ?? [];
        } catch (Error $error) {
            if ($onParseError !== null) {
                $onParseError($file, $error);
                return [];
            }

            throw new RuntimeException(sprintf('Unable to parse %s: %s', $file, $error->getMessage()), 0, $error);
        }

        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $nodes = $traverser->traverse($nodes);

        $result = [];
        foreach ((new NodeFinder())->findInstanceOf($nodes, Node\Stmt\ClassLike::class) as $node) {
            if ($node instanceof Node\Stmt\Class_ && $node->isAnonymous()) {
                continue;
            }
            if ($node->namespacedName === null) {
                continue;
            }

            $type = match (true) {
                $node instanceof Node\Stmt\Interface_ => 'interface',
                $node instanceof Node\Stmt\Trait_ => 'trait',
                $node instanceof Node\Stmt\Enum_ => 'enum',
                default => 'class',
            };
            $result[] = new DiscoveredSymbol($node->namespacedName->toString(), $type, $file);
        }

        return $result;
    }
}
