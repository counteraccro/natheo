<?php
/**
 * Translation service, traitement des données liés au traductions
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Service\Admin;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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
    public function getTranslatationFileByLanguage(string $language): array
    {
        echo $language;
        return [];
    }
}
