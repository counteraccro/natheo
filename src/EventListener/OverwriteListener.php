<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Listener qui va surcharger un controller en fonction de ce qui est défini dans overwrite.yaml
 */

namespace App\EventListener;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;

class OverwriteListener
{
    /**
     * @var ServiceLocator
     */
    private ServiceLocator $serviceLocator;

    /**
     * @var RouterInterface
     */
    private RouterInterface $router;

    /**
     * Path fichier de config
     * @var string
     */
    private const CONFIG_OVERWRITE_FILE = '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR . 'overwrite.yaml';

    /**
     * Clé du fichier de config
     * @var string
     */
    private const KEY_CONTROLLER = 'controller';

    /**
     * Clé du fichier de config
     * @var string
     */
    private const KEY_ROUTE = 'route';

    /**
     * Clé du fichier de config
     * @var string
     */
    private const KEY_OVERWRITE = 'overwrite';

    /**
     * Clé du fichier de config
     * @var string
     */
    private const KEY_PARAMETERS = 'parameters';


    /**
     * @param ServiceLocator $serviceLocator
     * @param RouterInterface $router
     */
    public function __construct(
        ServiceLocator  $serviceLocator,
        RouterInterface $router
    )
    {
        $this->serviceLocator = $serviceLocator;
        $this->router = $router;
    }

    /**
     * Permet overwrite un controller en fonction de la config
     * @param ControllerEvent $event
     * @return void
     */
    public function onControllerRequest(ControllerEvent $event): void
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
     */
    private function loadOverwriteConfigFile(): array|null
    {
        $tabOverwriteConfig = Yaml::parseFile(self::CONFIG_OVERWRITE_FILE);

        if (!isset($tabOverwriteConfig[self::KEY_OVERWRITE]) || empty($tabOverwriteConfig[self::KEY_OVERWRITE])) {
            return null;
        }

        return $tabOverwriteConfig;
    }
}
