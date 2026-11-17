<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource d'administration dont la version donne l'ETag, vérifié par If-Match avant une écriture
 */

namespace App\Api\V2\Concurrency;

interface VersionedResourceInterface
{
    /**
     * Version de l'élément (date de mise à jour), null si inconnue
     * @return string|null
     */
    public function getVersion(): ?string;
}
