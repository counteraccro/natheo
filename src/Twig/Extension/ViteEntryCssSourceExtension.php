<?php
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Permet de convertir en inline_css le CSS compilé généré par Vite
 */
namespace App\Twig\Extension;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Twig\Attribute\AsTwigFunction;

class ViteEntryCssSourceExtension
{
    /**
     * @var ContainerBagInterface
     */
    private ContainerBagInterface $container;

    /**
     * @var string
     */
    private string $publicDir;

    /**
     * @var string
     */
    private string $buildDir;

    /**
     * @var array|null
     */
    private ?array $manifest = null;

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[AutowireLocator([
            'container' => ContainerBagInterface::class,
        ])]
        private readonly ContainerInterface $handlers)
    {
        $this->container = $this->handlers->get('container');
        $this->publicDir = $this->container->get('kernel.project_dir') . DIRECTORY_SEPARATOR . 'public';
        $this->buildDir = 'build';
    }

    /**
     * Retourne les sources du CSS Vite en fonction de son nom
     * @param $entryName
     * @return string
     */
    #[AsTwigFunction('vite_entry_css_source')]
    public function getViteEntryCssSource($entryName): string
    {
        try {
            $files = $this->getCssFiles($entryName);

            $source = '';
            foreach ($files as $file) {
                $filePath = $this->publicDir . DIRECTORY_SEPARATOR . $this->buildDir . DIRECTORY_SEPARATOR . $file;
                if (file_exists($filePath)) {
                    $content = file_get_contents($filePath);
                    if ($content !== false) {
                        $source .= $content;
                    }
                }
            }
            return $source;
        } catch (\Exception $e) {
            // En mode dev ou si le manifest n'existe pas, retourner une chaîne vide
            return '';
        }
    }

    /**
     * Récupère les fichiers CSS d'une entry depuis le manifest Vite
     * @param string $entryName
     * @return array
     * @throws \RuntimeException
     */
    private function getCssFiles(string $entryName): array
    {
        $manifest = $this->getManifest();

        // Vite utilise le chemin relatif comme clé (ex: "assets/app.js")
        // On cherche d'abord avec le chemin complet
        $possibleKeys = [
            $entryName,
            'assets/' . $entryName,
            './assets/' . $entryName,
            'assets/styles/' . $entryName,
            './assets/styles/' . $entryName,
        ];

        $entry = null;
        foreach ($possibleKeys as $key) {
            if (isset($manifest[$key])) {
                $entry = $manifest[$key];
                break;
            }
        }

        if ($entry === null) {
            return [];
        }

        return $entry['css'] ?? [];
    }

    /**
     * Charge le manifest.json généré par Vite
     * @return array
     * @throws \RuntimeException
     */
    private function getManifest(): array
    {
        if ($this->manifest === null) {
            $manifestPath = $this->publicDir . DIRECTORY_SEPARATOR . $this->buildDir . DIRECTORY_SEPARATOR . '.vite' . DIRECTORY_SEPARATOR . 'manifest.json';

            if (!file_exists($manifestPath)) {
                throw new \RuntimeException(sprintf(
                    'Le fichier manifest Vite n\'existe pas : %s. Avez-vous lancé "yarn build" ?',
                    $manifestPath
                ));
            }

            $manifestContent = file_get_contents($manifestPath);
            if ($manifestContent === false) {
                throw new \RuntimeException('Impossible de lire le fichier manifest Vite');
            }

            $this->manifest = json_decode($manifestContent, true);
            if (!is_array($this->manifest)) {
                throw new \RuntimeException('Le fichier manifest Vite est invalide');
            }
        }

        return $this->manifest;
    }
}