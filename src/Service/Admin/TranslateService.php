<?php
/**
 * Translation service, traitement des données liés au traductions
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Service\Admin;

use Symfony\Component\Yaml\Yaml;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Finder\Finder;

class TranslateService extends AppAdminService
{
    /**
     * Retourne la liste des langues prises en charge par le site
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return array
     */
    public function getListLanguages(): array
    {
        $tab = explode('|', $this->containerBag->get('app.supported_locales'));

        $return = [];
        foreach ($tab as $language) {
            $return[$language] = $this->translator->trans('global.' . $language);
        }
        return $return;
    }

    /**
     * Retourne la liste de fichiers de traduction en fonction de la langue
     * @param string $language
     * @return array
     */
    public function getTranslationFilesByLanguage(string $language): array
    {
        $kernel = $this->containerBag->get('kernel.project_dir');
        $pathLog = $kernel . DIRECTORY_SEPARATOR . 'translations' . DIRECTORY_SEPARATOR;
        $finder = new Finder();
        $finder->files()->in($pathLog)->name('*.' . $language . '.*');

        $return = [];
        foreach ($finder as $file) {
            $return[$file->getFilename()] = $file->getFilename();
        }
        return $return;
    }

    /**
     * Permet de retourner le contenu d'un fichier en fonction de son nom
     * @param string $file
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTranslationFile(string $fileName): array
    {
        $kernel = $this->containerBag->get('kernel.project_dir');
        $pathLog = $kernel . DIRECTORY_SEPARATOR . 'translations' . DIRECTORY_SEPARATOR;
        $finder = new Finder();
        $finder->files()->in($pathLog)->name($fileName);

        $return = [];
        if ($finder->hasResults() && $finder->count() === 1) {
            $iterator = $finder->getIterator();
            $iterator->rewind();
            $file = $iterator->current();

            $return = Yaml::parseFile($file->getPathname());
        }
        return $return;
    }
}
