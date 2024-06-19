<?php

namespace App\Service\Admin\Tools;

use App\Kernel;
use App\Service\Admin\AppAdminService;
use App\Utils\Tools\AdvancedOptions\AdvancedOptionsConst;
use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class AdvancedOptionsService extends AppAdminService
{

    /**
     * Permet de changer d'environment de l'application
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function switchEnv(): void
    {
        $parameterBag = $this->getParameterBag();
        $env = $parameterBag->get('kernel.environment');

        $filesystem = new Filesystem();

        $envFile = AdvancedOptionsConst::FILE_ENV;
        if($filesystem->exists(AdvancedOptionsConst::FILE_ENV_LOCAL)) {
            $envFile = AdvancedOptionsConst::FILE_ENV_LOCAL;
        }

        $contents = $filesystem->readFile($envFile);

        if($env === AdvancedOptionsConst::ENV_DEV) {
            $contents = str_replace('APP_ENV=' . AdvancedOptionsConst::ENV_DEV,
                'APP_ENV=' . AdvancedOptionsConst::ENV_PROD, $contents);
            $contents = str_replace('APP_DEBUG=1', 'APP_DEBUG=0', $contents);
        }
        else {
            $contents = str_replace('APP_ENV=' . AdvancedOptionsConst::ENV_PROD,
                'APP_ENV=' . AdvancedOptionsConst::ENV_DEV, $contents);
            $contents = str_replace('APP_DEBUG=0', 'APP_DEBUG=1', $contents);
        }
        $filesystem->dumpFile($envFile, $contents);
    }
}
