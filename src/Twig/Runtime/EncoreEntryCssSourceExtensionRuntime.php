<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de convertir en inline_css le CSS compilé généré par webpack
 */
namespace App\Twig\Runtime;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Attribute\AsTwigFunction;

class EncoreEntryCssSourceExtensionRuntime
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
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[AutowireLocator([
            'container' => ContainerBagInterface::class,
            'entrypointLookup' => EntrypointLookupInterface::class,
        ])]
        private readonly ContainerInterface $handlers)
    {
        $this->container = $this->handlers->get('container');
        $this->entrypointLookup = $this->handlers->get('entrypointLookup');
        $this->publicDir =$this->container->get('kernel.project_dir') . DIRECTORY_SEPARATOR . 'public';
    }


    /**
     * Retourne les sources du webpack en fonction de son nom
     * @param $entryName
     * @return string
     */
    #[AsTwigFunction('encore_entry_css_source')]
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
