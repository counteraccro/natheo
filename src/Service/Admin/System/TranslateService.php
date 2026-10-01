<?php

declare(strict_types=1);
/**
 * Translation service, traitement des données liés au traductions
 * @author Gourdon Aymeric
 * @version 1.4
 */

namespace App\Service\Admin\System;

use App\Service\Admin\AppAdminService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
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
        $finder
            ->files()
            ->in($pathLog)
            ->name('*.' . $language . '.*');

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
        return Yaml::parseFile($this->getSafeTranslationFilePath($fileName)) ?? [];
    }

    /**
     * Met à jour un fichier de traduction après validation de chaque valeur, puis purge le cache des traductions.
     * Aucune écriture n'est faite si au moins une valeur est invalide
     * @param string $fileName
     * @param array $updateContent liste de ['key' => string, 'value' => string]
     * @return array<string, string> erreurs indexées par clé de traduction, vide si la sauvegarde a été faite
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateTranslateFile(string $fileName, array $updateContent): array
    {
        $filePath = $this->getSafeTranslationFilePath($fileName);
        $kernel = $this->getContainerBag()->get('kernel.project_dir');

        // Verrou global : le fichier cible est remplacé par rename(), un flock sur lui ne protégerait pas
        $lock = fopen($kernel . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'translation.lock', 'c');
        flock($lock, LOCK_EX);

        try {
            $tab = Yaml::parseFile($filePath) ?? [];
            $errors = $this->validateTranslates($fileName, $tab, $updateContent);
            if (!empty($errors)) {
                return $errors;
            }

            foreach ($updateContent as $update) {
                $tab[$update['key']] = $update['value'];
            }
            $filesystem = new Filesystem();
            $filesystem->dumpFile($filePath, Yaml::dump($tab));
            // Symfony recompile seul un catalogue absent, inutile de vider tout le cache applicatif
            $filesystem->remove(
                $this->getContainerBag()->get('kernel.cache_dir') . DIRECTORY_SEPARATOR . 'translations',
            );
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        return [];
    }

    /**
     * Vérifie que chaque mise à jour cible une clé existante avec une valeur texte valide.
     * Pour les fichiers ICU, la syntaxe du message est contrôlée pour éviter de casser le rendu des pages
     * @param string $fileName
     * @param array $tab contenu actuel du fichier
     * @param array $updateContent
     * @return array<string, string> erreurs indexées par clé de traduction
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function validateTranslates(string $fileName, array $tab, array $updateContent): array
    {
        $translator = $this->getTranslator();
        $isIcu = str_contains($fileName, MessageCatalogue::INTL_DOMAIN_SUFFIX);
        $locale = explode('.', basename($fileName))[1] ?? 'fr';

        $errors = [];
        foreach ($updateContent as $index => $update) {
            $key = $update['key'] ?? null;
            if (!is_string($key) || !array_key_exists($key, $tab)) {
                $errors[is_string($key) ? $key : (string) $index] = $translator->trans(
                    'translate.save.error.unknown_key',
                    domain: 'translate',
                );
                continue;
            }

            $value = $update['value'] ?? null;
            if (!is_string($value)) {
                $errors[$key] = $translator->trans('translate.save.error.not_string', domain: 'translate');
                continue;
            }

            if ($isIcu && $value !== '' && \MessageFormatter::create($locale, $value) === null) {
                $errors[$key] = $translator->trans(
                    'translate.save.error.icu',
                    ['error' => intl_error_name(intl_get_error_code())],
                    domain: 'translate',
                );
            }
        }
        return $errors;
    }

    /**
     * Retourne le chemin réel d'un fichier de traduction en s'assurant qu'il reste dans le dossier translations
     * @param string $fileName
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getSafeTranslationFilePath(string $fileName): string
    {
        $kernel = $this->getContainerBag()->get('kernel.project_dir');
        $realRoot = realpath($kernel . DIRECTORY_SEPARATOR . 'translations');
        $realFilePath = realpath($realRoot . DIRECTORY_SEPARATOR . basename($fileName));

        if ($realFilePath === false || !str_starts_with($realFilePath, $realRoot . DIRECTORY_SEPARATOR)) {
            throw new \RuntimeException('Invalid file path: attempt to escape the translations directory.');
        }
        return $realFilePath;
    }
}
