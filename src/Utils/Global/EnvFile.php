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
     * @var string
     */
    public const NAME_FILE_ENV = '.env';

    /**
     * Nom fichier env.local de l'application
     * @var string
     */
    public const NAME_FILE_ENV_LOCAL = '.env.local';

    /**
     * Dev mode
     * @var string
     */
    public const ENV_DEV = 'dev';

    /**
     * Prod mode
     * @var string
     */
    public const ENV_PROD = 'prod';

    /**
     * Clé DATABASE_URL
     * @var string
     */
    public const KEY_DATABASE_URL = 'DATABASE_URL';

    /**
     * Clé APP_SECRET
     * @var string
     */
    public const KEY_APP_SECRET = 'APP_SECRET';

    /**
     * Clé KEY_APP_ENV
     * @var string
     */
    public const KEY_APP_ENV = 'APP_ENV';

    /**
     * Clé NATHEO_SCHEMA
     * @var string
     */
    public const KEY_NATHEO_SCHEMA = 'NATHEO_SCHEMA';

    /**
     * @param ContainerInterface $handlers
     */
    public function __construct(
        #[
            AutowireLocator([
                'kernel' => KernelInterface::class,
                'parameterBag' => ParameterBagInterface::class,
            ]),
        ]
        protected ContainerInterface $handlers,
    ) {}

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
        if ($filesystem->exists($kernel->getProjectDir() . DIRECTORY_SEPARATOR . self::NAME_FILE_ENV_LOCAL)) {
            $envFile = $kernel->getProjectDir() . DIRECTORY_SEPARATOR . self::NAME_FILE_ENV_LOCAL;
        }
        return $envFile;
    }

    /**
     * Retourne le contenu du fichier .env
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getContentEnvFile(): string
    {
        $filesystem = new Filesystem();
        $pathEnv = $this->getPathEnvFile();
        return $filesystem->readFile($pathEnv);
    }

    /**
     * Ecrase le fichier .env avec $content
     * @param string $content
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function dumpEnvFile(string $content): void
    {
        $filesystem = new Filesystem();
        $pathEnv = $this->getPathEnvFile();
        $filesystem->dumpFile($pathEnv, $content);
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
        if ($env === self::ENV_DEV) {
            $value = self::KEY_APP_ENV . '=' . self::ENV_DEV;
        } else {
            $value = self::KEY_APP_ENV . '=' . self::ENV_PROD;
        }
        $this->updateValueByKey(self::KEY_APP_ENV, $value);
    }

    /**
     * Retourne une valeur en fonction de sa clé
     * @param string $key
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getValueByKey(string $key): string
    {
        $pattern = '/[^# ](' . $key . '.*\r\n)/m';
        $contents = $this->getContentEnvFile();
        preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER, 0);
        return trim($matches[0][0]);
    }

    /**
     * @param string $key
     * @param string $newValue
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateValueByKey(string $key, string $newValue): void
    {
        $oldValue = $this->getValueByKey($key);
        $content = $this->getContentEnvFile();
        $content = str_replace($oldValue, $newValue, $content);
        $this->dumpEnvFile($content);
    }
}
