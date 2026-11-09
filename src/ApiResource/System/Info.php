<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 : informations générales de l'installation
 */

namespace App\ApiResource\System;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\Api\System\InfoProvider;

#[
    ApiResource(
        shortName: 'Info',
        operations: [
            new Get(
                uriTemplate: '/info',
                description: 'Nom du site et versions de Nathéo et de l\'API',
                security: "is_granted('ROLE_READ_API')",
                provider: InfoProvider::class,
            ),
        ],
    ),
]
final readonly class Info
{
    public function __construct(
        public string $siteName,
        public string $natheoVersion,
        public string $apiVersion,
    ) {}
}
