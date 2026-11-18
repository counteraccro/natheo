<?php

declare(strict_types=1);

/**
 * @author Gourdon Aymeric
 * @version 3.0
 * Sidebar de l'administration, construite depuis les modules et les entrées de menu déclarés
 */

namespace App\Twig\Extension\Admin\System;

use App\Enum\Admin\Global\Breadcrumb;
use App\Service\Admin\Module\AdminMenuBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Attribute\AsTwigFunction;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SidebarExtension
{
    public function __construct(
        private readonly AdminMenuBuilder $menuBuilder,
        private readonly RequestStack $requestStack,
    ) {}

    /**
     * Génère la sidebar de la page courante
     * @param Environment $twig
     * @param string $currentRoute
     * @param array $breadcrumb fil d'Ariane de la page, ses routes gardent active l'entrée de la page parente
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    #[AsTwigFunction('getSidebar', isSafe: ['html'], needsEnvironment: true)]
    public function getSidebar(Environment $twig, string $currentRoute, array $breadcrumb = []): string
    {
        $tree = $this->menuBuilder->getTree();
        $this->menuBuilder->markActive(
            $tree,
            $currentRoute,
            $this->getRequestParameters(),
            Breadcrumb::getRoutes($breadcrumb),
        );

        return $twig->render('admin/includes/sidebar.html.twig', ['nodes' => $tree]);
    }

    /**
     * Paramètres de la route et de l'adresse de la requête courante
     * @return array<string, mixed>
     */
    private function getRequestParameters(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return [];
        }
        return [...$request->query->all(), ...$request->attributes->get('_route_params', [])];
    }
}
