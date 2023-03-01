<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer le menu sidebar de l'administration
 */

namespace App\Twig\Runtime\Admin;

use App\Entity\Admin\SidebarElement;
use App\Service\Admin\SidebarElementService;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class SidebarExtensionRuntime extends AdminAppExtension implements RuntimeExtensionInterface
{

    /**
     * @var SidebarElementService
     */
    private SidebarElementService $sidebarElementService;

    private string $current_route = '';


    /**
     * @param SidebarElementService $sidebarElementService
     * @param RouterInterface $router
     */
    public function __construct(SidebarElementService $sidebarElementService, RouterInterface $router, TranslatorInterface $translator)
    {
        $this->sidebarElementService = $sidebarElementService;
        parent::__construct($router, $translator);
    }

    /**
     * Point d'entrée pour générer le menu sidebar
     * @param string $current_route
     * @return string
     */
    public function getSidebar(string $current_route): string
    {
        $this->current_route = $current_route;
        $sidebarElements = $this->sidebarElementService->getAllParent();

        $html = '';
        foreach ($sidebarElements as $element) {
            /** @var SidebarElement $element */
            if ($element->getChildren()->count() === 0) {
                $html .= $this->singleElement($element);
            } else {
                $html .= $this->childrenElement($element);
            }
        }
        return $html;
    }

    /**
     * Permet de générer des liens directs
     * @param SidebarElement $sidebarElement
     * @return string
     */
    private function singleElement(SidebarElement $sidebarElement): string
    {
        $url = $this->generateRealUrl($sidebarElement->getRoute());
        $active = $this->isClassActive($sidebarElement->getRoute(), false);

        return '<li ' . $active . '>
            <a href="' . $url . '">
                <i class="bi ' . $sidebarElement->getIcon() . '"></i> 
                <span class="d-none-mini">' . $this->translator->trans($sidebarElement->getLabel()) . '</span>
            </a>
        </li>';
    }

    /**
     * Permet de générer un sous menu
     * @param SidebarElement $sidebarElement
     * @return string
     */
    public function childrenElement(SidebarElement $sidebarElement): string
    {
        $tabToggle = [
            'collapsed' => 'collapsed',
            'aria-expanded' => false,
            'show' => '',
            'active' => ''
        ];

        $html = '';
        foreach ($sidebarElement->getChildren() as $child) {
            /* @var SidebarElement $child */

            if($child->isDisabled())
            {
                continue;
            }

            $active = $this->isClassActive($child->getRoute(), true);

            if($active !== '')
            {
                $tabToggle = [
                    'collapsed' => '',
                    'aria-expanded' => true,
                    'show' => 'show',
                    'active' => 'class="active"'
                ];
            }

            $url = $this->generateRealUrl($child->getRoute());
            $html .= '<li ' . $active . '>
                    <a href="' . $url . '">
                        <i class="bi ' . $child->getIcon() . '"></i> 
                        <span class="d-none-mini">' . $this->translator->trans($child->getLabel()) . '</span>
                    </a>
                 </li>';
        }

        $html .= '</ul></li>';

        $route = $sidebarElement->getRoute();
        $routeId = substr($route,1);

        return '<li ' . $tabToggle['active'] . '>
            <a class="' .  $tabToggle['collapsed'] .' nav-toggle" href="' . $route . '" data-bs-toggle="collapse" data-bs-target="' . $route . '" aria-current="page" aria-expanded="' . $tabToggle['aria-expanded'] . '">
                <i class="bi ' . $sidebarElement->getIcon() . '"></i> <span class="d-none-mini">' . $this->translator->trans($sidebarElement->getLabel()) . '</span>
                <i class="bi bi-chevron-right float-end d-none-mini"></i>
            </a>
            <ul class="collapse list-unstyled ' .  $tabToggle['show'] .'" id="' . $routeId . '" data-bs-parent="#sidebar">' . $html;
    }

    /**
     * Génère une vraie URL en fonction d'une route si elle existe
     * @param $route
     * @return string
     */
    private function generateRealUrl($route): string
    {
        $url = '#';
        if ($route !== '#') {
            $url = $this->router->generate($route);
        }
        return $url;
    }

    /**
     * Génère la class active si le lien est la route courante
     * @param $route
     * @param bool $child
     * @return string
     */
    private function isClassActive($route, bool $child = false): string
    {
        $return = '';
        if($route === $this->current_route)
        {
            $return = 'class="active"';
            if($child)
            {
                $return = 'class="sub-active"';
            }
        }
        return $return;
    }
}
