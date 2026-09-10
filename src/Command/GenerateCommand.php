<?php

declare(strict_types=1);

namespace BitrixIdeHelper\Command;

use BitrixIdeHelper\Module;
use BitrixIdeHelper\ModuleLocator;
use BitrixIdeHelper\SourceScanner;
use BitrixIdeHelper\StubGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'generate', description: 'Generate body-free PHP stubs for installed Bitrix modules.')]
final class GenerateCommand extends Command
{
    public function __construct(
        private readonly ModuleLocator $locator,
        private readonly SourceScanner $scanner,
        private readonly StubGenerator $generator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('root', InputArgument::REQUIRED, 'Bitrix document root')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output directory', '.ide-helper/bitrix')
            ->addOption('module', 'm', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Only generate selected module(s)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = rtrim((string) $input->getOption('output'), DIRECTORY_SEPARATOR);
        $selected = array_filter((array) $input->getOption('module'));
        $count = 0;

        foreach ($this->locator->locate((string) $input->getArgument('root')) as $module) {
            if ($selected !== [] && !in_array($module->name, $selected, true)) {
                continue;
            }
            $count += $this->generateModule($module, $target);
        }

        $output->writeln(sprintf('<info>Generated %d stub files in %s</info>', $count, $target));
        return Command::SUCCESS;
    }

    private function generateModule(Module $module, string $target): int
    {
        $count = 0;
        foreach ($this->scanner->files($module) as $file) {
            if ($this->scanner->symbols($file) === []) {
                continue;
            }

            $relative = ltrim(substr($file, strlen($module->path)), DIRECTORY_SEPARATOR);
            $destination = sprintf('%s/%s/%s/%s', $target, $module->scope, $module->name, $relative);
            $directory = dirname($destination);
            if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new \RuntimeException(sprintf('Unable to create %s', $directory));
            }
            file_put_contents($destination, $this->generator->generate($file));
            ++$count;
        }
        return $count;
    }
}
