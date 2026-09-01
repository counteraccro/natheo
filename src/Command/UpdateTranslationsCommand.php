<?php
/**
 * Commande pour générer automatiquement les traductions
 * @author Gourdon Aymeric
 * @version 1.0
 */
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[
    AsCommand(
        name: 'natheo:translations:update',
        description: 'Met à jour les fichiers de traduction pour les locales en, es et fr',
    ),
]
class UpdateTranslationsCommand extends Command
{
    private const array SUPPORTED_LOCALES = ['en', 'es', 'fr'];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach (self::SUPPORTED_LOCALES as $locale) {
            $this->extractLocale($locale, $output);
        }

        return Command::SUCCESS;
    }

    private function extractLocale(string $locale, OutputInterface $output): void
    {
        $command = $this->getApplication()->find('translation:extract');

        $input = new ArrayInput([
            'command' => 'translation:extract',
            'locale' => $locale,
            '--force' => true,
            '--format' => 'yaml',
        ]);

        $command->run($input, $output);
    }
}
