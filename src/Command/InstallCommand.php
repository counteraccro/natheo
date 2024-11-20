<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        private TranslatorInterface $translator
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        /*$this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('remove', null, InputOption::VALUE_NONE, 'Option description')
        ;*/
    }

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

        //$arg1 = $input->getArgument('arg1');

        /*if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            $io->note(sprintf('You passed an option: %s', $input->getOption('option1')));
        }*/

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
