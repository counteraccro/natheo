<?php

namespace App\Service\Installation;

use App\Service\Admin\AppAdminService;
use App\Utils\Global\EnvFile;
use App\Utils\Installation\InstallationConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class InstallationService extends AppAdminService
{

    /**
     * Retourne une valeur en fonction d'un clé dans le fichier .env
     * @param string $key
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getValueByKeyInEnvFile(string $key): string
    {
        $envFile = $this->getEnvFile();
        return $envFile->getValueByKey($key);
    }

    /**
     * Met à jour la valeur de $key avec $value
     * @param string $key
     * @param string $value
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateValueByKeyInEnvFile(string $key, string $value): void
    {
        $envFile = $this->getEnvFile();
        $envFile->updateValueByKey($key, $value);
    }

    /**
     * Formate les données database_url pour l'enregistrement dans le fichier .env
     * @param array $data
     * @param string $option
     * @return string
     */
    public function formatDatabaseUrlForEnvFile(array $data, string $option): string
    {
        $return = EnvFile::KEY_DATABASE_URL . '="';
        $return .= $data['type'] . '://' . $data['login'] . ':' . $data['password']  . '@' . $data['ip'] . ':' . $data['port'];

        if($option === InstallationConst::OPTION_DATABASE_URL_CREATE_DATABASE)
        {

        }

        $return .= '"';

        return $return;
    }

    /**
     * Retourne la valeur de DATABASE_URL formaté
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDatabaseUrl(): array
    {
        $databaseUrl = $this->getValueByKeyInEnvFile(EnvFile::KEY_DATABASE_URL);
        $pattern = '/DATABASE_URL="(.*):\/\/(.*):(.*)@(.*):(.*)\/(.*)\?serverVersion=(.*)\&charset=(.*)"/';
        preg_match_all($pattern, $databaseUrl, $matches, PREG_SET_ORDER, 0);

        $return = [
            'type' => '', 'login' => '', 'password' => '',
            'ip' => '', 'port' => '', 'dbb_name' => '',
            'version' => '', 'charset' => '',
        ];

        if (empty($matches)) {
            $pattern = '/DATABASE_URL="(.*):\/\/(.*):(.*)@(.*):([[:digit:]]+)/';
            preg_match_all($pattern, $databaseUrl, $matches, PREG_SET_ORDER, 0);
        }

        if (!empty($matches)) {
            $tmp = [
                'type' => $matches[0][1],
                'login' => $matches[0][2],
                'password' => $matches[0][3],
                'ip' => $matches[0][4],
                'port' => $matches[0][5],
            ];
            $return  = array_replace($return, $tmp);

            if (isset($matches[0][6])) {
                $return['dbb_name'] = $matches[0][6];
                $return['version'] = $matches[0][7];
                $return['charset'] = $matches[0][8];
            }
        }
        return $return;
    }
}
