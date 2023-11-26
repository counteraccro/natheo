<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant pour la génération de modale Bootstrap 5.3
 */
namespace App\Twig\Components\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent()]
final class ModaleComponent
{
    public string $id = 'id-modal';

    public string $bgColor = 'bg-secondary';
}
