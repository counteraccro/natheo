<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer le menu sidebar de l'administration
 */
namespace App\Twig\Runtime\Admin;

use App\Service\Admin\SidebarElementService;
use Twig\Extension\RuntimeExtensionInterface;

class SidebarExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * @var SidebarElementService
     */
    private SidebarElementService $sidebarElementService;
    public function __construct(SidebarElementService $sidebarElementService)
    {
        $this->sidebarElementService = $sidebarElementService;
    }

    /**
     * Point d'entrée pour générer le menu sidebar
     * @return string
     */
    public function getSidebar(): string
    {
        $elements = $this->sidebarElementService->getAllParent();
        var_dump($elements);

        $html = '<div>Menu</div>';

        return $html;
    }
}
