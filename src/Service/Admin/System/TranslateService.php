<?php
/**
 * Translation service, traitement des données liés au traductions
 * @author Gourdon Aymeric
 * @version 1.2
 */

namespace App\Service\Admin\System;

use App\Service\Admin\AppAdminService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class TranslateService extends AppAdminService
{
    /**
     * Retourne la liste des langues prises en charge par le site au format local => langue
     * @return array
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function getListLanguages(): array
    {
        $containerBag = $this->getContainerBag();
        $translator = $this->getTranslator();

        $tab = explode('|', $containerBag->get('app.supported_locales'));

        $return = [];
        foreach ($tab as $language) {
            $return[$language] = $translator->trans('global.' . $language);
        }
        return $return;
    }

    /**
     * Retourne la liste de fichiers de traduction en fonction de la langue
     * @param string $language
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTranslationFilesByLanguage(string $language): array
    {
        $containerBag = $this->getContainerBag();
        $kernel = $containerBag->get('kernel.project_dir');
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
     * @param string $fileName
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTranslationFile(string $fileName): array
    {
        $containerBag = $this->getContainerBag();

        $kernel = $containerBag->get('kernel.project_dir');
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

    /**
     * Met à jour un fichier de traduction
     * @param string $fileName
     * @param array $updateContent
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateTranslateFile(string $fileName, array $updateContent): void
    {
        $containerBag = $this->getContainerBag();
        $kernel = $containerBag->get('kernel.project_dir');
        $pathLog = $kernel . DIRECTORY_SEPARATOR . 'translations' . DIRECTORY_SEPARATOR;
        $finder = new Finder();
        $finder->files()->in($pathLog)->name($fileName);

        if ($finder->hasResults() && $finder->count() === 1) {
            $iterator = $finder->getIterator();
            $iterator->rewind();
            $file = $iterator->current();

            $tab = Yaml::parseFile($file->getPathname());

            foreach ($tab as $key => $value) {
                foreach ($updateContent as $update) {
                    if ($key === $update['key']) {
                        $tab[$key] = $update['value'];
                    }
                }
            }
            $yaml = Yaml::dump($tab);
            file_put_contents($file->getPathname(), $yaml);
        }
    }
}

