<?php

/**
 * @author Gourdon Aymeric
 * @version 2.0
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

        return '<a href="' .
            $url .
            '" class="sidebar-item ' .
            $active .
            ' flex items-center px-4 py-3 rounded-lg">
                <svg class="w-5 h-5 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' .
            $sidebarElement->getIcon() .
            '"/>
                </svg>
                <span>' .
            $this->translator->trans($sidebarElement->getLabel()) .
            '</span>
            </a>';
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

        $open = $html = '';
        foreach ($sidebarElement->getChildren() as $child) {
            /* @var SidebarElement $child */

            if ($child->isDisabled() || !$this->security->isGranted($child->getRole())) {
                continue;
            }

            $active = $this->isClassActive($child->getRoute(), true);
            if ($active !== '') {
                $open = 'open';
            }

            $notification = '';
            if (isset($this->tabNotification[$child->getLabel()])) {
                $notification = $this->getHTMLNotification($this->tabNotification[$child->getLabel()]);
            }

            $html .=
                '<a href="' .
                $this->generateRealUrl($child->getRoute()) .
                '" class="' .
                $active .
                ' sidebar-item flex items-center px-4 py-2 rounded-lg text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' .
                $child->getIcon() .
                '"></path></svg>
                ' .
                $this->translator->trans($child->getLabel()) .
                $notification .
                '</a>';
        }

        return '<div>
            <button class="sidebar-item flex items-center justify-between w-full px-4 py-3 rounded-lg" onclick="toggleSubmenu(\'' .
            $sidebarElement->getId() .
            '\')">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' .
            $sidebarElement->getIcon() .
            '"></path>
                    </svg>
                    <span>' .
            $this->translator->trans($sidebarElement->getLabel()) .
            '</span></div><svg class="w-4 h-4 chevron" id="chevron-' .
            $sidebarElement->getId() .
            '" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div class="submenu ml-8 mt-1 space-y-1 ' .
            $open .
            '" id="submenu-' .
            $sidebarElement->getId() .
            '">' .
            $html .
            '</div></div>';
    }

    /**
     * Génère une notification
     * @param int $nb
     * @return string
     */
    private function getHTMLNotification(int $nb): string
    {
        return '<span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full">' .
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
            $return = 'active';
            if ($child) {
                $return = 'active';
            }
        }
        return $return;
    }
}
