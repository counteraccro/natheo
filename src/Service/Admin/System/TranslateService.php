<?php
/**
 * Translation service, traitement des données liés au traductions
 * @author Gourdon Aymeric
 * @version 1.0
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
     * @param string $fileName
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
        $kernel = $this->containerBag->get('kernel.project_dir');
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

    /**
     * Retourne les traductions du module translate
     * @return array
     */
    public function getTranslate():array
    {
        return [
            'translate_select_language' => $this->translator->trans('translate.select.language', domain: 'translate'),
            'translate_select_file' => $this->translator->trans('translate.select.file', domain: 'translate'),
            'translate_empty_file' => $this->translator->trans('translate.empty.file', domain: 'translate'),
            'translate_btn_save' => $this->translator->trans('translate.btn.save', domain: 'translate'),
            'translate_btn_cache' => $this->translator->trans('translate.btn.cache', domain: 'translate'),
            'translate_info_edit' => $this->translator->trans('translate.info.edit', domain: 'translate'),
            'translate_link_revert' => $this->translator->trans('translate.link.revert', domain: 'translate'),
            'translate_nb_edit' => $this->translator->trans('translate.nb.edit', domain: 'translate'),
            'translate_loading' => $this->translator->trans('translate.loading', domain: 'translate'),
            'translate_cache_titre' => $this->translator->trans('translate.cache.titre', domain: 'translate'),
            'translate_cache_info' => $this->translator->trans('translate.cache.info', domain: 'translate'),
            'translate_cache_wait' => $this->translator->trans('translate.cache.wait', domain: 'translate'),
            'translate_cache_btn_close' => $this->translator->trans('translate.cache.btn.close', domain: 'translate'),
            'translate_cache_btn_accept' => $this->translator->trans('translate.cache.btn.accept', domain: 'translate'),
            'translate_cache_success' => $this->translator->trans('translate.cache.success', domain: 'translate'),
            'translate_confirm_leave' => $this->translator->trans('translate.confirm.leave', domain: 'translate'),
            'translate_toast_title_success' =>
                $this->translator->trans('translate.toast.title.success', domain: 'translate'),
            'translate_toast_title_error' =>
                $this->translator->trans('translate.toast.title.error', domain: 'translate'),
            'translate_toast_time' =>
                $this->translator->trans('translate.toast.time', domain: 'translate'),
        ];
    }
}

