<?php
/**
 * Commande pour simplifier l'installation du CMS
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Command;

use App\Service\Installation\InstallationService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'natheo:install',
    description: 'Create new database, create tables and run fixtures with dev datas',
)]
class InstallCommand extends Command
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly InstallationService $installationService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title($this->translator->trans('install.title', domain: 'command'));

        $io->text($this->translator->trans('install.description', domain: 'command'));
        $io->listing([
            $this->translator->trans('install.description.create.database', domain: 'command'),
            $this->translator->trans('install.description.create.schema', domain: 'command'),
            $this->translator->trans('install.description.fixtures.load', domain: 'command'),
            $this->translator->trans('install.description.clear.cache', domain: 'command'),
        ]);

        $delete = false;
        if ($this->installationService->checkSchema()) {
            $delete = true;
            $io->warning($this->translator->trans('install.description.schema.warning', domain: 'command'));
        }

        if (!$io->confirm($this->translator->trans('install.description.confirm', domain: 'command'), false)) {
            $io->text($this->translator->trans('install.description.confirm.no', domain: 'command'));
            return Command::SUCCESS;
        }

        // Drop database
        if ($delete) {
            $io->title($this->translator->trans('install.drop.database', domain: 'command'));

            $commandInput = new ArrayInput([
                'command' => 'doctrine:database:drop',
                '--force' => true,
            ]);

            $returnCode = $this->getApplication()->doRun($commandInput, $output);
            if ($returnCode === 0) {
                $io->text($this->translator->trans('install.drop.database.success', domain: 'command'));
            } else {
                $io->text($this->translator->trans('install.error', domain: 'command'));
                return Command::FAILURE;
            }
        }

        // Create database
        $io->title($this->translator->trans('install.create.database', domain: 'command'));

        $commandInput = new ArrayInput([
            'command' => 'doctrine:database:create',
        ]);

        $returnCode = $this->getApplication()->doRun($commandInput, $output);
        if ($returnCode === 0) {
            $io->text($this->translator->trans('install.create.database.success', domain: 'command'));
        } else {
            $io->text($this->translator->trans('install.error', domain: 'command'));
            return Command::FAILURE;
        }

        // Create schema
        $io->title($this->translator->trans('install.create.schema', domain: 'command'));

        $commandInput = new ArrayInput([
            'command' => 'doctrine:schema:create',
        ]);

        $returnCode = $this->getApplication()->doRun($commandInput, $output);
        if ($returnCode === 0) {
            $io->text($this->translator->trans('install.create.schema.success', domain: 'command'));
        } else {
            $io->text($this->translator->trans('install.error', domain: 'command'));
            return Command::FAILURE;
        }

        // Create fixtures
        $io->title($this->translator->trans('install.fixture.load', domain: 'command'));

        $commandInput = new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '--append' => true
        ]);

        $returnCode = $this->getApplication()->doRun($commandInput, $output);
        if ($returnCode === 0) {
            $io->text($this->translator->trans('install.fixture.load.success', domain: 'command'));
        } else {
            $io->text($this->translator->trans('install.error', domain: 'command'));
            return Command::FAILURE;
        }

        // clear cache
        $io->title($this->translator->trans('install.clear.cache', domain: 'command'));

        $commandInput = new ArrayInput([
            'command' => 'cache:clear',
        ]);

        $returnCode = $this->getApplication()->doRun($commandInput, $output);
        if ($returnCode === 0) {
            $io->text($this->translator->trans('install.clear.cache.success', domain: 'command'));
        } else {
            $io->text($this->translator->trans('install.error', domain: 'command'));
            return Command::FAILURE;
        }

        $io->success($this->translator->trans('install.success', domain: 'command'));

        return Command::SUCCESS;
    }
}
