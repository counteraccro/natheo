<?php
/**
 * Commande pour générer automatiquement les traductions
 * @author Gourdon Aymeric
 * @version 1.1
 */
declare(strict_types=1);

namespace App\Command;

use App\Service\Global\SupportedLocales;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[
    AsCommand(
        name: 'natheo:translations:update',
        description: 'Met à jour les fichiers de traduction pour toutes les locales supportées (app.supported_locales)',
    ),
]
class UpdateTranslationsCommand extends Command
{
    public function __construct(private readonly SupportedLocales $supportedLocales)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->supportedLocales->getAll() as $locale) {
            $this->extractLocale($locale, $output);
        }

        return Command::SUCCESS;
    }

    /**
     * Lance translation:extract pour une locale
     * @param string $locale
     * @param OutputInterface $output
     * @return void
     */
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
