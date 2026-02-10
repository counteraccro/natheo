<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Listener qui va surcharger un controller en fonction de ce qui est défini dans overwrite.yaml
 */

namespace App\EventListener;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;

class OverwriteListener
{

    /**
     * @var RouterInterface
     */
    private RouterInterface $router;

    /**
     * @var ContainerBagInterface
     */
    private ContainerBagInterface $containerBag;

    /**
     * Path fichier de config
     * @var string
     */
    private const string CONFIG_OVERWRITE_FILE = DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR . 'overwrite.yaml';

    /**
     * Clé du fichier de config
     * @var string
     */
    private const string KEY_CONTROLLER = 'controller';

    /**
     * Clé du fichier de config
     * @var string
     */
    private const string KEY_ROUTE = 'route';

    /**
     * Clé du fichier de config
     * @var string
     */
    private const string KEY_OVERWRITE = 'overwrite';

    /**
     * Clé du fichier de config
     * @var string
     */
    private const string KEY_PARAMETERS = 'parameters';


    /**
     * @param RouterInterface $router
     * @param ContainerBagInterface $containerBag
     */
    public function __construct(
        RouterInterface $router,
        ContainerBagInterface $containerBag
    )
    {
        $this->router = $router;
        $this->containerBag = $containerBag;
    }

    /**
     * Permet overwrite un controller en fonction de la config
     * @param ControllerEvent $event
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $tabOverwriteConfig = $this->loadOverwriteConfigFile();
        if ($tabOverwriteConfig === null) {
            return;
        }

        $tab = $this->getConfigByControllerAndRoute($event, $tabOverwriteConfig);

        if ($tab[self::KEY_ROUTE] !== '') {
            $route = $tab[self::KEY_ROUTE];
            $params = $tab[self::KEY_PARAMETERS];
            $event->setController(function () use ($route, $params) {
                return new RedirectResponse($this->router->generate($route, $params));
            });
        }
    }

    /**
     * Remonte une route et paramètres associés si ils existent dans la config
     * @param ControllerEvent $event
     * @param array $tabOverwriteConfig
     * @return array
     */
    private function getConfigByControllerAndRoute(ControllerEvent $event, array $tabOverwriteConfig): array
    {
        $tabReturn = [
            self::KEY_ROUTE => '',
            self::KEY_PARAMETERS => [],
        ];

        foreach ($tabOverwriteConfig[self::KEY_OVERWRITE] as $row) {
            if ($row[self::KEY_CONTROLLER] === $event->getController()[0]::class
                && $row[self::KEY_ROUTE] === $event->getRequest()->attributes->get('_route')) {
                $tabReturn[self::KEY_ROUTE] = $row[self::KEY_OVERWRITE][self::KEY_ROUTE];
                $params = $event->getRequest()->attributes->get('_route_params');

                if (!empty($params)) {
                    foreach ($params as $key => $value) {
                        if (isset($row[self::KEY_OVERWRITE][self::KEY_PARAMETERS][$key])) {
                            $tabReturn[self::KEY_PARAMETERS][$row[self::KEY_OVERWRITE][self::KEY_PARAMETERS][$key]] = $value;
                        }
                    }
                }
            }
        }
        return $tabReturn;
    }

    /**
     * Retourne le fichier de config Overwrite
     * @return array|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function loadOverwriteConfigFile(): array|null
    {
        $projetDir =  $this->containerBag->get('kernel.project_dir');
        $tabOverwriteConfig = Yaml::parseFile($projetDir . self::CONFIG_OVERWRITE_FILE);

        if (!isset($tabOverwriteConfig[self::KEY_OVERWRITE]) || empty($tabOverwriteConfig[self::KEY_OVERWRITE])) {
            return null;
        }

        return $tabOverwriteConfig;
    }
}
