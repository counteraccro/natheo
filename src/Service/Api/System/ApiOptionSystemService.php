<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service ApiOptionSystem
 */

namespace App\Service\Api\System;

use App\Service\Api\AppApiService;
use App\Utils\System\Options\OptionSystemKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiOptionSystemService extends AppApiService
{
    /**
     * Retourne un tableau de clé d'options system autorisée pour l'API
     * @return array
     */
    public function getWhiteListeOptionSystem(): array
    {
        return [
            OptionSystemKey::OS_SITE_NAME, OptionSystemKey::OS_FRONT_SCRIPT_TOP,
            OptionSystemKey::OS_FRONT_SCRIPT_START_BODY, OptionSystemKey::OS_FRONT_SCRIPT_END_BODY,
            OptionSystemKey::OS_OPEN_COMMENT, OptionSystemKey::OS_ADRESSE_SITE, OptionSystemKey::OS_THEME_FRONT_SITE,
            OptionSystemKey::OS_FRONT_FOOTER_TEXTE
        ];
    }

    /**
     * Retourne la liste des options system avec la value
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllOptionSystemWithValue(): array
    {
        $optionSystemService = $this->getOptionSystemService();

        $return = [];
        foreach ($this->getWhiteListeOptionSystem() as $option) {
            $return[$option] = $optionSystemService->getValueByKey($option);
        }

        return $return;
    }
}
