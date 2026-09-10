<?php

declare(strict_types=1);

namespace BitrixIdeHelper\Command;

use BitrixIdeHelper\ModuleLocator;
use BitrixIdeHelper\SourceScanner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'discover', description: 'Discover Bitrix modules and PHP symbols.')]
final class DiscoverCommand extends Command
{
    public function __construct(
        private readonly ModuleLocator $locator,
        private readonly SourceScanner $scanner,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('root', InputArgument::REQUIRED, 'Bitrix document root');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = [];
        foreach ($this->locator->locate((string) $input->getArgument('root')) as $module) {
            $symbols = [];
            foreach ($this->scanner->files($module) as $file) {
                foreach ($this->scanner->symbols($file) as $symbol) {
                    $symbols[] = ['name' => $symbol->name, 'type' => $symbol->type, 'file' => $symbol->file];
                }
            }
            $data[] = ['module' => $module->name, 'scope' => $module->scope, 'path' => $module->path, 'symbols' => $symbols];
        }

        $output->writeln((string) json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        return Command::SUCCESS;
    }
}
