<?php

namespace App\Service\Installation;

use App\Entity\Admin\System\User;
use App\Service\Admin\AppAdminService;
use App\Utils\Global\EnvFile;
use App\Utils\Installation\InstallationConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

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
     * Retourne un nouveau secret généré aléatoirement
     * @return string
     */
    public function generateSecret(): string
    {
        $a = '0123456789abcdef';
        $secret = '';
        for ($i = 0; $i < 32; $i++) {
            $secret .= $a[rand(0, strlen($a)-1)];
        }
        return EnvFile::KEY_APP_SECRET . '=' . $secret;
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
        $return .= $data['type'] . '://' . $data['login'] . ':' . $data['password'] . '@' . $data['ip'] . ':' . $data['port'];

        if ($option === InstallationConst::OPTION_DATABASE_URL_CREATE_DATABASE) {
            $return .= '/' . $data['bdd_name'] . '?serverVersion=' . $data['version'] . '&charset=' . $data['charset'];
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
            'ip' => '', 'port' => '', 'bdd_name' => '',
            'version' => '', 'charset' => 'utf8',
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
            $return = array_replace($return, $tmp);

            if (isset($matches[0][6])) {
                $return['bdd_name'] = $matches[0][6];
                $return['version'] = $matches[0][7];
                $return['charset'] = $matches[0][8];
            }
        }
        return $return;
    }

    /**
     * Si le schema et la table existe, retourne true sinon false
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function checkSchema(): bool
    {
        $dataBase = $this->getDatabase();
        if ($dataBase->isSchemaExist() && $dataBase->isTableExiste()) {
            return true;
        }
        return false;
    }

    /**
     * Si des données existent dans la entity renvoi true sinon renvoi false
     * @param string $entity
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function checkDataExiste(string $entity): bool
    {
        $dataBase = $this->getDatabase();
        if ($dataBase->isDataInTable($entity)) {
            return true;
        }
        return false;
    }

    /**
     * Créer un nouvel utilisateur
     * @param array $data
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createUser(array $data): array
    {
        $passwordHasher = $this->getUserPasswordHasher();
        $user = new User();

        $tab = [
            'user' => [
                'User' => [
                    'email' => $data['email'],
                    'password' => $passwordHasher->hashPassword($user, $data['password']),
                    'login' => $data['login'],
                    'firstname' => '',
                    'lastname' => '',
                    'roles' => 'ROLE_SUPER_ADMIN',
                    'disabled' => 0,
                    'anonymous' => 0,
                    'founder' => 1
                ],
            ]
        ];
        $yamlData = Yaml::dump($tab, 3, 2);
        $filesystem = new Filesystem();
        $path = $this->getPathFixture() . 'system' . DIRECTORY_SEPARATOR . 'user_fixtures_data.yaml';

        try {
            $filesystem->dumpFile($path, $yamlData);
            return ['success' => true];
        } catch (IOExceptionInterface $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }

    /**
     * Retourne le path du dossier de fixtures
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getPathFixture(): string
    {
        $container = $this->getContainerBag();

        $kernel = $container->get('kernel.project_dir');
        return $kernel . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR .
            'DataFixtures' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
    }


}
