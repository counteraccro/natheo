<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Information sur le CMS
 */

namespace App\Service\Admin\System;

use App\Entity\Admin\System\User;
use App\Kernel;
use App\Service\Admin\AppAdminService;
use App\Utils\Debug;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class InformationService extends AppAdminService
{

    /**
     * Retourne l'ensemble des informations du cms
     * @return array
     */
    public function getAllInformation(): array
    {
        try {
            return [
                'serveur' => $this->getInformationServer(),
                'database' => $this->getInformationDatabase(),
                'website' => $this->getInformationWebsite(),
                'navigateur' => $this->getInformationNavigateur()
            ];
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            die($e->getMessage());
        }
    }

    /**
     * Retourne les informations du server
     * @return array
     */
    public function getInformationServer(): array
    {
        return [
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
        $data = $_SERVER['DATABASE_URL'];
        $pattern = '/^[^\/]+|[^\/:\s]+/m';
        preg_match_all($pattern, $data, $matches, PREG_SET_ORDER, 0);

        $dataBaseServer = explode('@', $matches[2][0]);
        $dataBaseName = explode('?', $matches[4][0]);

        return [
            'database_type' => $matches[0][0],
            'database_user' => $matches[1][0],
            'database_server' => $dataBaseServer[1] . ':' . $matches[3][0],
            'database_name' => $dataBaseName[0]
        ];
    }

    /**
     * Retourne les informations sur le site web
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getInformationWebsite(): array
    {
        $this->containerBag->get('app.version');

        return [
            'website_version' => $this->containerBag->get('app.version'),
            'website_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
        ];
    }

    /**
     * Retourne les informations du navigateur
     * @return array
     */
    public function getInformationNavigateur():array
    {
        return [
            'navigateur_info' => $_SERVER['HTTP_USER_AGENT']
        ];
    }
}
