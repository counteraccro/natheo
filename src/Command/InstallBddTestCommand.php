<?php

declare(strict_types=1);
/**
 * Commande pour simplifier l'installation des Tests
 * @author Gourdon Aymeric
 * @version 1.1
 */
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'natheo:install-bdd-test', description: 'Create or update test database environment')]
class InstallBddTestCommand extends Command
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function configure(): void {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->kernel->getEnvironment() !== 'test') {
            $io->warning($this->translator->trans('install.test.warning.change.env', domain: 'command'));
            passthru('APP_ENV=test php bin/console natheo:install-bdd-test --ansi', $code);
            return $code === 0 ? Command::SUCCESS : Command::FAILURE;
        }

        $io->title($this->translator->trans('install.test.title', domain: 'command'));

        $io->note($this->translator->trans('install.test.note', domain: 'command'));

        if (!$io->confirm($this->translator->trans('install.test.confirm', domain: 'command'), false)) {
            $io->text($this->translator->trans('install.test.confirm.cancel', domain: 'command'));

            return Command::SUCCESS;
        }

        $io->listing([
            $this->translator->trans('install.test.summary.drop_database', domain: 'command'),
            $this->translator->trans('install.test.summary.create_database', domain: 'command'),
            $this->translator->trans('install.test.summary.create_schema', domain: 'command'),
        ]);

        $io->section($this->translator->trans('install.test.step.drop_database', domain: 'command'));

        $returnCode = $this->runConsoleCommand(['doctrine:database:drop', '--force', '--if-exists'], $io);
        if ($returnCode === 0) {
            $io->text($this->translator->trans('install.test.drop_database.success', domain: 'command'));
        } else {
            $io->text($this->translator->trans('install.test.error', domain: 'command'));

            return Command::FAILURE;
        }

        $io->section($this->translator->trans('install.test.step.create_database', domain: 'command'));

        $returnCode = $this->runConsoleCommand(['doctrine:database:create'], $io);
        if ($returnCode === 0) {
            $io->text($this->translator->trans('install.test.create_database.success', domain: 'command'));
        } else {
            $io->text($this->translator->trans('install.test.error', domain: 'command'));

            return Command::FAILURE;
        }

        $io->section($this->translator->trans('install.test.step.create_schema', domain: 'command'));

        // Sync metadata storage (create doctrine_migration_versions table if missing)
        $returnCode = $this->runConsoleCommand(['doctrine:migrations:sync-metadata-storage'], $io);
        if ($returnCode !== 0) {
            $io->text($this->translator->trans('install.test.error', domain: 'command'));

            return Command::FAILURE;
        }

        // Run migrations
        $returnCode = $this->runConsoleCommand(
            ['doctrine:migrations:migrate', '--no-interaction', '--allow-no-migration'],
            $io,
        );
        if ($returnCode === 0) {
            $io->text($this->translator->trans('install.test.create_schema.success', domain: 'command'));
        } else {
            $io->text($this->translator->trans('install.test.error', domain: 'command'));

            return Command::FAILURE;
        }

        $io->success($this->translator->trans('install.test.success', domain: 'command'));
        $io->note($this->translator->trans('install.test.help.phpunit', domain: 'command'));
        $io->text('php bin/phpunit');

        return Command::SUCCESS;
    }

    /**
     * Exécute une commande console Symfony dans un process PHP isolé.
     * Chaque appel bénéficie d'une connexion DB et d'un EntityManager totalement neufs,
     * ce qui évite tout partage d'état (transactions, savepoints) entre les étapes.
     * @param array $commandArgs Arguments de la commande, ex: ['doctrine:fixtures:load', '--append']
     * @param SymfonyStyle $io
     * @return int
     */
    private function runConsoleCommand(array $commandArgs, SymfonyStyle $io): int
    {
        $consolePath = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'console';

        $process = new Process(
            array_merge([PHP_BINARY, $consolePath], $commandArgs, ['--ansi']),
            $this->kernel->getProjectDir(),
            ['APP_ENV' => 'test'],
        );
        $process->setTimeout(300);

        $process->run(static function (string $type, string $buffer) use ($io): void {
            $io->write($buffer);
        });

        return $process->getExitCode() ?? Command::FAILURE;
    }
}
