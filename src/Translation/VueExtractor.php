<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Extracteur de translation:extract pour les clés des appels t('domaine.ecran.element') du code Vue et TypeScript
 */

namespace App\Translation;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;

#[AutoconfigureTag('translation.extractor', ['alias' => 'vue'])]
final class VueExtractor implements ExtractorInterface
{
    /**
     * Appel t() dont la clé est une chaîne littérale, sans interpolation
     * @var string
     */
    private const string CALL_PATTERN = '/(?<![\w$])t\(\s*([\'"`])([^\'"`$\\\\]+)\1/';

    /**
     * Clé au format de la convention : domaine, écran et élément au minimum
     * @var string
     */
    private const string KEY_PATTERN = '/^[a-z][a-z0-9_]*(\.[a-z0-9_]+){2,}$/';

    private string $prefix = '';

    /**
     * Catalogues déjà remplis : la commande appelle l'extracteur une fois par dossier de code PHP et Twig
     * @var \WeakMap<MessageCatalogue, true>
     */
    private \WeakMap $extracted;

    /**
     * @param string[] $directories dossiers du code Vue et TypeScript, surcharges comprises
     */
    public function __construct(
        #[
            Autowire(['%kernel.project_dir%/assets/vue', '%kernel.project_dir%/assets/overwrite/vue']),
        ]
        private readonly array $directories,
    ) {
        $this->extracted = new \WeakMap();
    }

    /**
     * Ajoute au catalogue les clés trouvées dans les dossiers du code Vue, quel que soit $resource
     * @param string|iterable<string> $resource dossiers donnés par la commande (src, templates), ignorés
     * @param MessageCatalogue $catalogue
     * @return void
     */
    public function extract(string|iterable $resource, MessageCatalogue $catalogue): void
    {
        $directories = array_values(array_filter($this->directories, is_dir(...)));
        if (isset($this->extracted[$catalogue]) || $directories === []) {
            return;
        }
        $this->extracted[$catalogue] = true;

        $files = (new Finder())
            ->files()
            ->in($directories)
            ->name(['*.vue', '*.ts'])
            ->notPath('__tests__');
        foreach ($files as $file) {
            foreach ($this->extractKeys($file->getContents()) as $key) {
                $catalogue->set($key, $this->prefix . $key, strstr($key, '.', true));
            }
        }
    }

    /**
     * @param string $prefix
     * @return void
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * Clés des appels t() d'un fichier, au format de la convention
     * @param string $content
     * @return string[]
     */
    public function extractKeys(string $content): array
    {
        preg_match_all(self::CALL_PATTERN, $content, $matches);
        return array_values(
            array_unique(array_filter($matches[2], fn(string $key): bool => preg_match(self::KEY_PATTERN, $key) === 1)),
        );
    }
}
