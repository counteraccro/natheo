<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Analyse du fichier .env pour l'installation
 */

namespace App\Service\Installation;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class ParseEnvService
{
    public function __construct(#[AutowireLocator([
        'kernel' => KernelInterface::class,
    ])] protected ContainerInterface $handlers)
    {
    }

    /**
     * Retourne le Path du fichier env
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPathEnvFile(): string
    {
        $kernel = $this->handlers->get('kernel');
        return $kernel->getProjectDir() . DIRECTORY_SEPARATOR . '.env';
    }

    /**
     * Analyse le fichier .env et remonte les problèmes rencontrés sous la forme d'un tableau
     * @return array contenant les clés suivantes : <br />
     * [errors][] : erreurs détectées <br />
     *  [file] (string) : .env modifié pour mettre en avant les erreurs <br />
     *  [rowFile] (string) : .env d'origine <br />
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function parseEnvFile(): array
    {
        $envFile = $this->readFile($this->getPathEnvFile());

        //if(preg_match())

        return [
            'errors' => [],
            'file' => $envFile,
            'rowFile' => $envFile
        ];
    }

    private function readFile(string $filePath): string
    {
        $file = new Filesystem();
        return $file->readFile($filePath);
    }
}
