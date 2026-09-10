<?php

declare(strict_types=1);

namespace BitrixIdeHelper;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use RuntimeException;

final class StubGenerator
{
    public function generate(string $sourceFile): string
    {
        $code = file_get_contents($sourceFile);
        if ($code === false) {
            throw new RuntimeException(sprintf('Unable to read %s', $sourceFile));
        }

        $nodes = (new ParserFactory())->createForNewestSupportedVersion()->parse($code) ?? [];
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class extends NodeVisitorAbstract {
            public function leaveNode(Node $node): Node|int|null
            {
                if ($node instanceof Node\Stmt\ClassMethod || $node instanceof Node\Stmt\Function_) {
                    if ($node->stmts !== null) {
                        $node->stmts = [];
                    }
                    return $node;
                }

                if ($node instanceof Node\Expr\Closure || $node instanceof Node\Expr\ArrowFunction) {
                    return NodeTraverser::REMOVE_NODE;
                }

                if ($node instanceof Node\Stmt\Expression
                    || $node instanceof Node\Stmt\If_
                    || $node instanceof Node\Stmt\For_
                    || $node instanceof Node\Stmt\Foreach_
                    || $node instanceof Node\Stmt\While_
                    || $node instanceof Node\Stmt\Do_
                    || $node instanceof Node\Stmt\TryCatch
                    || $node instanceof Node\Stmt\Return_
                ) {
                    return NodeTraverser::REMOVE_NODE;
                }

                return null;
            }
        });

        return (new Standard())->prettyPrintFile($traverser->traverse($nodes)) . "\n";
    }
}
