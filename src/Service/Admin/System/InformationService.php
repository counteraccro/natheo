<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Information sur le CMS
 */

namespace App\Service\Admin\System;

class InformationService
{

    /**
     * Retourne l'ensemble des informations du cms
     * @return array
     */
    public function getAllInformation(): array
    {
        return [
            'serveur' => $this->getInformationServer()
        ];
    }

    public function getInformationServer(): array
    {
        return [
            'php_version' => phpversion()
        ];
    }
}
