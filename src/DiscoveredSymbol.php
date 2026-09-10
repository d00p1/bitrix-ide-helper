<?php

declare(strict_types=1);

namespace BitrixIdeHelper;

final readonly class DiscoveredSymbol
{
    public function __construct(
        public string $name,
        public string $type,
        public string $file,
    ) {
    }
}
