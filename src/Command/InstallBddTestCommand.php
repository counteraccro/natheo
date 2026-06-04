<?php
/**
 * Commande pour simplifier l'installation des Tests
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'natheo:install-bdd-test', description: 'Create or update test database environment')]
class InstallBddTestCommand extends Command
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
        parent::__construct();
    }

    protected function configure(): void {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->getApplication()->getKernel()->getEnvironment() !== 'test') {
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

        $commandInput = new ArrayInput([
            'command' => 'doctrine:database:drop',
            '--force' => true,
            '--if-exists' => true,
        ]);

        $returnCode = $this->getApplication()->doRun($commandInput, $output);
        if ($returnCode === 0) {
            $io->text($this->translator->trans('install.test.drop_database.success', domain: 'command'));
        } else {
            $io->text($this->translator->trans('install.test.error', domain: 'command'));

            return Command::FAILURE;
        }

        $io->section($this->translator->trans('install.test.step.create_database', domain: 'command'));

        $commandInput = new ArrayInput([
            'command' => 'doctrine:database:create',
        ]);

        $returnCode = $this->getApplication()->doRun($commandInput, $output);
        if ($returnCode === 0) {
            $io->text($this->translator->trans('install.test.create_database.success', domain: 'command'));
        } else {
            $io->text($this->translator->trans('install.test.error', domain: 'command'));

            return Command::FAILURE;
        }

        $io->section($this->translator->trans('install.test.step.create_schema', domain: 'command'));

        $commandInput = new ArrayInput([
            'command' => 'doctrine:schema:create',
        ]);

        $returnCode = $this->getApplication()->doRun($commandInput, $output);
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
}
