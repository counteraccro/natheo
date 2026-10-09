<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Object de Transfère de Données global pour API
 */
namespace App\Dto\Api;

class AppApiDto
{
    /**
     * Locale prise en charge
     */
    protected const LOCALES = ['fr', 'es', 'en'];

    /**
     * Nombre maximum d'éléments retournés par page
     */
    protected const MAX_LIMIT = 100;
}
