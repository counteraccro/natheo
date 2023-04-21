<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de convertir en inline_css le CSS compilé généré par webpack
 */
namespace App\Twig\Runtime;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Extension\RuntimeExtensionInterface;

class EncoreEntryCssSourceExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * @var ContainerBagInterface
     */
    private ContainerBagInterface $container;

    /**
     * @var EntrypointLookupInterface
     */
    private EntrypointLookupInterface $entrypointLookup;

    /**
     * @var string
     */
    private string $publicDir;

    /**
     * @param ContainerBagInterface $container
     * @param EntrypointLookupInterface $entrypointLookup
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(ContainerBagInterface $container, EntrypointLookupInterface $entrypointLookup)
    {
        $this->container = $container;
        $this->entrypointLookup = $entrypointLookup;
        $this->publicDir =$this->container->get('kernel.project_dir') . DIRECTORY_SEPARATOR . 'public';
    }


    /**
     * Retourne les sources du webpack en fonction de son nom
     * @param $entryName
     * @return string
     */
    public function getEncoreEntryCssSource($entryName): string
    {
        $files = $this->entrypointLookup->getCssFiles($entryName);

        $source = '';
        foreach ($files as $file) {
            $source .= file_get_contents($this->publicDir.'/'.$file);
        }
        return $source;
    }
}
