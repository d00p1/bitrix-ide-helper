<?php

declare(strict_types=1);

namespace BitrixIdeHelper;

use BitrixIdeHelper\Command\DiscoverCommand;
use BitrixIdeHelper\Command\GenerateCommand;
use Symfony\Component\Console\Application as ConsoleApplication;

final class Application extends ConsoleApplication
{
    public function __construct()
    {
        parent::__construct('Bitrix IDE Helper', '0.1.0');

        $locator = new ModuleLocator();
        $scanner = new SourceScanner();

        $this->add(new DiscoverCommand($locator, $scanner));
        $this->add(new GenerateCommand($locator, $scanner, new StubGenerator()));
    }
}
