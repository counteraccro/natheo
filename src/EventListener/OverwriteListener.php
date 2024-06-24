<?php

namespace App\EventListener;

use App\Controller\Admin\DashboardController;
use App\Utils\Debug;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Yaml\Yaml;

class OverwriteListener
{
    private ServiceLocator $serviceLocator;

    private RouterInterface $router;

    public function __construct(
        ServiceLocator $serviceLocator,
        RouterInterface $router
    )
    {
        $this->serviceLocator = $serviceLocator;
        $this->router = $router;
    }

    public function onControllerRequest(ControllerEvent $event): void
    {
        $overwriteConfif = Yaml::parseFile('../config/cms/overwrite.yaml');
        if (!$event->isMainRequest()) {
            return;
        }


        if(empty($overwriteConfif) || empty($overwriteConfif['overwrite']))
        {
            return;
        }

        foreach($overwriteConfif['overwrite'] as $overwrite)
        {
            if($overwrite['controller'] === $event->getController()[0]::class
                && $overwrite['route'] === $event->getRequest()->attributes->get('_route'))
            {
                $controller = $this->serviceLocator->get($overwrite['overwrite']['controller']);
                $action = $overwrite['overwrite']['route'];
                $params = $event->getRequest()->attributes->get('_route_params');

                $newParams = [];
                if(!empty($params))
                {
                    foreach ($params as $key => $value) {
                        if(isset($overwrite['overwrite']['parameters'][$key]))
                        {
                            $newParams[$overwrite['overwrite']['parameters'][$key]] = $value;
                        }
                    }
                }

                var_dump($newParams);

                //Debug::printR($this->router->getRouteCollection());

                $event->setController(function () use ($action, $newParams) {
                    return  new RedirectResponse($this->router->generate($action, $newParams));
                });
            }
        }
    }
}
