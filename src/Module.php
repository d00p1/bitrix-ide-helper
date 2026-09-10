<?php

declare(strict_types=1);

namespace BitrixIdeHelper;

final readonly class Module
{
    public function __construct(
        public string $name,
        public string $path,
        public string $scope,
    ) {
    }
}
