<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer le formulaire de saisie des options systèmes
 */

namespace App\Twig\Runtime\Admin;

use Twig\Extension\RuntimeExtensionInterface;

class OptionSystemExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    /**
     * Point d'entrée pour la génération du formulaire des options systèmes
     * @return string
     */
    public function getOptionSystem(): string
    {
        $html = 'OKi';

        return $html;
    }
}
