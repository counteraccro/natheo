<?php

namespace App\Service\Admin\Tools;

use App\Kernel;
use App\Service\Admin\AppAdminService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class AdvancedOptionsService extends AppAdminService
{
    /**
     * Path fichier env de l'application
     */
    private const FILE_ENV = '..' . DIRECTORY_SEPARATOR . '.env';

    /**
     * Path fichier env.local de l'application
     */
    private const FILE_ENV_LOCAL = '..' . DIRECTORY_SEPARATOR . '.env.local';

    /**
     * Dev mode
     */
    private const ENV_DEV = 'dev';

    /**
     * Prod mode
     */
    private const ENV_PROD = 'prod';

    /**
     * Permet de changer de l'environment de l'application
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function switchEnv(): void
    {
        $parameterBag = $this->getParameterBag();
        $env = $parameterBag->get('kernel.environment');

        $filesystem = new Filesystem();

        $envFile = self::FILE_ENV;
        if($filesystem->exists(self::FILE_ENV_LOCAL)) {
            $envFile = self::FILE_ENV_LOCAL;
        }

        $contents = $filesystem->readFile($envFile);

        if($env === self::ENV_DEV) {
            $contents = str_replace('APP_ENV=' . self::ENV_DEV, 'APP_ENV=' . self::ENV_PROD, $contents);
            $contents = str_replace('APP_DEBUG=1', 'APP_DEBUG=0', $contents);
        }
        else {
            $contents = str_replace('APP_ENV=' . self::ENV_PROD, 'APP_ENV=' . self::ENV_DEV, $contents);
            $contents = str_replace('APP_DEBUG=0', 'APP_DEBUG=1', $contents);
        }
        $filesystem->dumpFile($envFile, $contents);
    }
}
