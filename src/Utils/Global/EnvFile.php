<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de lire et manipuler le fichier .env
 */
namespace App\Utils\Global;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class EnvFile
{

    /**
     * Nom fichier env de l'application
     */
    public const NAME_FILE_ENV = '.env';

    /**
     * Nom fichier env.local de l'application
     */
    public const NAME_FILE_ENV_LOCAL = '.env.local';

    /**
     * Dev mode
     */
    public const ENV_DEV = 'dev';

    /**
     * Prod mode
     */
    public const ENV_PROD = 'prod';

    public function __construct(#[AutowireLocator([
        'kernel' => KernelInterface::class,
        'parameterBag' => ParameterBagInterface::class,
    ])] protected ContainerInterface $handlers)
    {
    }

    /**
     * Retourne le Path du fichier env
     * Si .env.local existe, retour le .env.local
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPathEnvFile(): string
    {
        $filesystem = new Filesystem();

        $kernel = $this->handlers->get('kernel');
        $envFile = $kernel->getProjectDir() . DIRECTORY_SEPARATOR . self::NAME_FILE_ENV;
        if($filesystem->exists($kernel->getProjectDir() . DIRECTORY_SEPARATOR . self::NAME_FILE_ENV_LOCAL)) {
            $envFile = $kernel->getProjectDir() . DIRECTORY_SEPARATOR . self::NAME_FILE_ENV_LOCAL;
        }
        return $envFile;
    }

    /**
     * Switch le APP_ENV de DEV vers PROD ou PROD vers DEV
     * en fonction de APP_ENV défini par le kernel
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function switchAppEnv(): void
    {
        $parameterBag = $this->handlers->get('parameterBag');
        $env = $parameterBag->get('kernel.environment');

        $filesystem = new Filesystem();
        $pathEnv = $this->getPathEnvFile();
        $contents = $filesystem->readFile($pathEnv);

        if($env === self::ENV_DEV) {
            $contents = str_replace('APP_ENV=' . self::ENV_DEV,
                'APP_ENV=' . self::ENV_PROD, $contents);
            $contents = str_replace('APP_DEBUG=1', 'APP_DEBUG=0', $contents);
        }
        else {
            $contents = str_replace('APP_ENV=' . self::ENV_PROD,
                'APP_ENV=' . self::ENV_DEV, $contents);
            $contents = str_replace('APP_DEBUG=0', 'APP_DEBUG=1', $contents);
        }
        $filesystem->dumpFile($pathEnv, $contents);
    }
}
