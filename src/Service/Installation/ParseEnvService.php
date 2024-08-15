<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Analyse du fichier .env pour l'installation
 */

namespace App\Service\Installation;

use App\Utils\Installation\InstallationConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ParseEnvService
{
    /**
     * Compte le nombre de remplacement
     * @var int
     */
    private int $count = 0;

    /**
     * Tableau des erreurs
     * @var array
     */
    private array $tabError = [];

    public function __construct(#[AutowireLocator([
        'kernel' => KernelInterface::class,
        'translator' => TranslatorInterface::class
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
        /** @var TranslatorInterface $translate */
        $translate = $this->handlers->get('translator');
        $envFile = $this->readFile($this->getPathEnvFile());
        $envFileParse = $this->checkAppSecret($envFile, $translate);



        return [
            'errors' => $this->tabError,
            'file' => $envFileParse,
            'rowFile' => $envFile
        ];
    }

    /**
     * Vérifie si APP_SECRET est bon ou non
     * @param string $envFile
     * @param TranslatorInterface $translator
     * @return string
     */
    private function checkAppSecret(string $envFile, TranslatorInterface $translator): string
    {
        $this->count = 0;
        $pattern = '/' . InstallationConst::DEFAULT_SECRET. '/i';
        $replacement =  $this->formatReplace(InstallationConst::DEFAULT_SECRET) . " <-- " .  $this->formatReplace($translator->trans('installation.connectionException.error.default.secret', domain: 'installation'));
        $return = $this->replace($pattern, $replacement, $envFile);
        if($this->count > 0)
        {
            $this->tabError[] = $translator->trans('installation.connectionException.error.default.secret.info', domain: 'installation');
        }

        return $return;
    }

    private function formatReplace(string $string)
    {
        return '<span class="error-config-file">' . $string . '</span>';
    }

    /**
     * @param string $pattern
     * @param string $replacement
     * @param string $subject
     * @return array|string|null
     */
    private function replace(string $pattern, string $replacement, string $subject): array|string|null
    {
        echo $pattern;
        return preg_replace($pattern, $replacement, $subject, 1, $this->count);
    }

    /**
     * Permet de lire un fichier
     * @param string $filePath
     * @return string
     */
    private function readFile(string $filePath): string
    {
        $file = new Filesystem();
        return $file->readFile($filePath);
    }
}
