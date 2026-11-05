<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * En-tête commun des pages de l'admin : fil d'Ariane, titre avec icône, description et actions
 * Blocs : icon (svg du titre), description (remplace la prop description), actions (boutons à droite)
 */
namespace App\Twig\Components\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class PageHeaderComponent
{
    /**
     * Fil d'Ariane au format de BreadcrumbExtension ; vide = pas de fil d'Ariane
     * @var array
     */
    public array $breadcrumb = [];

    /**
     * Titre de la page (h1)
     * @var string
     */
    public string $title = '';

    /**
     * Description affichée sous le titre (texte échappé ; HTML via le bloc description)
     * @var string
     */
    public string $description = '';
}
