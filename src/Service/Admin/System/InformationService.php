<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Information sur le CMS
 */

namespace App\Service\Admin\System;

use App\Entity\Admin\System\User;
use App\Service\Admin\AppAdminService;

class InformationService extends AppAdminService
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
        var_dump($_SERVER);

        return [
            'server_info' => $_SERVER['SERVER_SIGNATURE'],
            'server_software_version' => $_SERVER['SERVER_SOFTWARE'],
            'php_version' => phpversion(),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_file_size' => ini_get(' upload_max_filesize')
        ];
    }

    /**
     * Retourne les informations de la base de données
     * @return array
     */
    public function getInformationDatabase(): array
    {
        return [

        ];
    }
}
