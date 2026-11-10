<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant de modale de l'administration
 */
namespace App\Twig\Components\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ModaleComponent
{
    public string $id = 'id-modal';
}
