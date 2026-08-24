<?php

declare(strict_types=1);
/**
 * Commande pour simplifier l'installation du CMS
 * @author Gourdon Aymeric
 * @version 1.1
 */
namespace App\Command;

use App\Enum\Admin\Tools\DatabaseManager\DatabaseManagerData;
use App\Service\Installation\InstallationService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'natheo:install', description: 'Create new database, create tables and run fixtures with dev datas')]
class InstallCommand extends Command
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly InstallationService $installationService,
        private readonly ParameterBagInterface $parameterBag,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function configure(): void {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title($this->translator->trans('install.title', domain: 'command'));

        $env = $this->parameterBag->get('kernel.environment');
        if ('prod' === strtolower($env)) {
            $io->text($this->translator->trans('install.env.prod', domain: 'command'));
            return Command::SUCCESS;
        }

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

            $returnCode = $this->runConsoleCommand(['doctrine:database:drop', '--force'], $io);
            if ($returnCode === 0) {
                $io->text($this->translator->trans('install.drop.database.success', domain: 'command'));
            } else {
                $io->text($this->translator->trans('install.error', domain: 'command'));
                return Command::FAILURE;
            }
            $this->deleteDump();
        }

        // Create database
        $io->title($this->translator->trans('install.create.database', domain: 'command'));

        $returnCode = $this->runConsoleCommand(['doctrine:database:create'], $io);
        if ($returnCode === 0) {
            $io->text($this->translator->trans('install.create.database.success', domain: 'command'));
        } else {
            $io->text($this->translator->trans('install.error', domain: 'command'));
            return Command::FAILURE;
        }

        // Create schema
        $io->title($this->translator->trans('install.create.schema', domain: 'command'));

        // Sync metadata storage (create doctrine_migration_versions table if missing)
        $returnCode = $this->runConsoleCommand(['doctrine:migrations:sync-metadata-storage'], $io);
        if ($returnCode !== 0) {
            $io->text($this->translator->trans('install.error', domain: 'command'));
            return Command::FAILURE;
        }

        // Run migrations
        $returnCode = $this->runConsoleCommand(
            ['doctrine:migrations:migrate', '--no-interaction', '--allow-no-migration'],
            $io,
        );
        if ($returnCode === 0) {
            $io->text($this->translator->trans('install.create.schema.success', domain: 'command'));
        } else {
            $io->text($this->translator->trans('install.error', domain: 'command'));
            return Command::FAILURE;
        }

        // Create fixtures
        $io->title($this->translator->trans('install.fixture.load', domain: 'command'));

        $returnCode = $this->runConsoleCommand(['doctrine:fixtures:load', '--append', '--no-interaction'], $io);
        if ($returnCode === 0) {
            $io->text($this->translator->trans('install.fixture.load.success', domain: 'command'));
        } else {
            $io->text($this->translator->trans('install.error', domain: 'command'));
            return Command::FAILURE;
        }

        // clear cache
        $io->title($this->translator->trans('install.clear.cache', domain: 'command'));

        $returnCode = $this->runConsoleCommand(['cache:clear'], $io);
        if ($returnCode === 0) {
            $io->text($this->translator->trans('install.clear.cache.success', domain: 'command'));
        } else {
            $io->text($this->translator->trans('install.error', domain: 'command'));
            return Command::FAILURE;
        }

        $io->success($this->translator->trans('install.success', domain: 'command'));

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
        );
        $process->setTimeout(300);

        $process->run(static function (string $type, string $buffer) use ($io): void {
            $io->write($buffer);
        });

        return $process->getExitCode() ?? Command::FAILURE;
    }

    /**
     * Supprime le dossier dump
     * @return void
     */
    private function deleteDump(): void
    {
        $fileSystem = new Filesystem();

        if ($fileSystem->exists($this->kernel->getProjectDir() . DatabaseManagerData::getRootPath())) {
            $fileSystem->remove($this->kernel->getProjectDir() . DatabaseManagerData::getRootPath());
        }
    }
}
