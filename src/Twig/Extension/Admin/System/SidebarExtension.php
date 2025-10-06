<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer le menu sidebar de l'administration
 */

namespace App\Twig\Extension\Admin\System;

use App\Entity\Admin\System\SidebarElement;
use App\Service\Admin\System\SidebarElementService;
use App\Twig\Extension\Admin\AppAdminExtension;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Attribute\AsTwigFunction;

class SidebarExtension extends AppAdminExtension
{
    /**
     * @var SidebarElementService
     */
    private SidebarElementService $sidebarElementService;

    private string $currentRoute = '';

    /**
     * @var Security
     */
    private Security $security;

    private array $tabNotification = [];

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        #[
            AutowireLocator([
                'translator' => TranslatorInterface::class,
                'router' => RouterInterface::class,
                'sidebarElementService' => SidebarElementService::class,
                'security' => Security::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        $this->sidebarElementService = $this->handlers->get('sidebarElementService');
        $this->security = $this->handlers->get('security');
        parent::__construct($this->handlers);
    }

    /**
     * Point d'entrée pour générer le menu sidebar
     * @param string $currentRoute
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[AsTwigFunction('getSidebar', isSafe: ['html'])]
    public function getSidebar(string $currentRoute): string
    {
        $this->currentRoute = $currentRoute;
        $sidebarElements = $this->sidebarElementService->getAllParent();
        $this->getNbNotification();

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
        if (!$this->security->isGranted($sidebarElement->getRole()) || $sidebarElement->isDisabled()) {
            return '';
        }

        $url = $this->generateRealUrl($sidebarElement->getRoute());
        $active = $this->isClassActive($sidebarElement->getRoute(), false);

        return '<li ' .
            $active .
            '>
            <a href="' .
            $url .
            '">
                <i class="bi ' .
            $sidebarElement->getIcon() .
            '"></i>
                <span class="d-none-mini">' .
            $this->translator->trans($sidebarElement->getLabel()) .
            '</span>
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
        if (!$this->security->isGranted($sidebarElement->getRole())) {
            return '';
        }

        $tabToggle = [
            'collapsed' => 'collapsed',
            'aria-expanded' => false,
            'show' => '',
            'active' => '',
        ];

        $html = '';
        $nbTotalNotification = 0;
        foreach ($sidebarElement->getChildren() as $child) {
            /* @var SidebarElement $child */

            if ($child->isDisabled() || !$this->security->isGranted($child->getRole())) {
                continue;
            }

            $active = $this->isClassActive($child->getRoute(), true);

            if ($active !== '') {
                $tabToggle = [
                    'collapsed' => '',
                    'aria-expanded' => true,
                    'show' => 'show',
                    'active' => 'class="active"',
                ];
            }

            $notification = '';
            if (isset($this->tabNotification[$child->getLabel()])) {
                $nbTotalNotification += $this->tabNotification[$child->getLabel()];
                $notification = $this->getHTMLNotification($this->tabNotification[$child->getLabel()]);
            }

            $url = $this->generateRealUrl($child->getRoute());
            $html .=
                '<li ' .
                $active .
                '>
                    <a href="' .
                $url .
                '">
                        <i class="bi ' .
                $child->getIcon() .
                '"></i>
                        <span class="d-none-mini">' .
                $this->translator->trans($child->getLabel()) .
                ' ' .
                $notification .
                '</span>
                    </a>
                 </li>';
        }

        $html .= '</ul></li>';

        $route = $sidebarElement->getRoute();
        $routeId = substr($route, 1);

        $notification = '';
        if ($nbTotalNotification > 0) {
            $notification = $this->getHTMLNotification($nbTotalNotification);
        }

        return '<li ' .
            $tabToggle['active'] .
            '>
            <a class="' .
            $tabToggle['collapsed'] .
            ' nav-toggle no-control" href="' .
            $route .
            '" data-bs-toggle="collapse" data-bs-target="' .
            $route .
            '" aria-current="page" aria-expanded="' .
            $tabToggle['aria-expanded'] .
            '">
                <i class="bi ' .
            $sidebarElement->getIcon() .
            '"></i> <span class="d-none-mini">' .
            $this->translator->trans($sidebarElement->getLabel()) .
            '</span>
                <i class="bi bi-chevron-right float-end d-none-mini no-control"></i> ' .
            $notification .
            '
            </a>
            <ul class="collapse list-unstyled ' .
            $tabToggle['show'] .
            '" id="' .
            $routeId .
            '" data-bs-parent="#sidebar">' .
            $html;
    }

    /**
     * Génère une notification
     * @param int $nb
     * @return string
     */
    private function getHTMLNotification(int $nb): string
    {
        return '<span class="badge rounded-pill bg-danger float-end d-none-mini" style="margin-right: 10px">' .
            $nb .
            '</span>';
    }

    /**
     * Récupère les notifications à afficher
     * @return void
     */
    private function getNbNotification(): void
    {
        $this->tabNotification = [
            'global.update' => 1,
        ];
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
        if ($route === $this->currentRoute) {
            $return = 'class="active"';
            if ($child) {
                $return = 'class="sub-active"';
            }
        }
        return $return;
    }
}
