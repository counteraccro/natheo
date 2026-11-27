<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Ressource API v2 : entrée du menu du back-office visible par l'utilisateur de la session
 */

namespace App\Api\V2\Resource\Admin\System;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Metadata\AcceptLanguageParameter;
use App\Api\V2\Metadata\LocaleParameter;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\State\Provider\Admin\System\AdminMenuItemCollectionProvider;
use App\Api\V2\State\Provider\Admin\System\AdminMenuItemProvider;
use App\Enum\Admin\Module\CoreModuleId;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'AdminMenuItem',
        extraProperties: [
            OperationSecurityMetadataFactory::MODULE => CoreModuleId::DASHBOARD->value,
            OperationSecurityMetadataFactory::LOCKED => true,
        ],
        operations: [
            new GetCollection(
                name: 'admin_menu_item_get_collection',
                uriTemplate: '/admin/menu_items',
                paginationEnabled: false,
                errors: [],
                openapi: new OpenApiOperation(
                    summary: 'Les entrées du menu du back-office visibles par l\'utilisateur',
                    description: 'Liste plate dans l\'ordre du menu : chaque entrée cite son parent, le client ' .
                        'reconstruit l\'arbre. Une entrée masquée n\'est listée que si elle est un raccourci.',
                ),
                normalizationContext: ['groups' => ['admin_menu_item:list']],
                provider: AdminMenuItemCollectionProvider::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Get(
                name: 'admin_menu_item_get',
                uriTemplate: '/admin/menu_items/{id}',
                errors: [NotFoundException::class],
                openapi: new OpenApiOperation(summary: 'Une entrée du menu du back-office visible par l\'utilisateur'),
                normalizationContext: ['groups' => ['admin_menu_item:read']],
                provider: AdminMenuItemProvider::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
        ],
    ),
]
final class AdminMenuItem
{
    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['admin_menu_item:read', 'admin_menu_item:list'])]
    public string $id = '';

    /**
     * Identifiant de l'entrée parente, null au premier niveau
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_menu_item:read', 'admin_menu_item:list'])]
    public ?string $parent = null;

    /**
     * Identifiant du module, null pour un dossier qui n'appartient à aucun module
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_menu_item:read', 'admin_menu_item:list'])]
    public ?string $module = null;

    /**
     * Libellé traduit
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_menu_item:read', 'admin_menu_item:list'])]
    public string $label = '';

    /**
     * Tracé SVG de l'icône (attribut d, viewBox 0 0 24 24)
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_menu_item:read', 'admin_menu_item:list'])]
    public string $icon = '';

    /**
     * Ordre d'affichage parmi les entrées du même parent
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_menu_item:read', 'admin_menu_item:list'])]
    public int $position = 0;

    /**
     * Adresse de la page Twig, null pour un dossier ou un écran de l'application
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_menu_item:read', 'admin_menu_item:list'])]
    public ?string $legacyUrl = null;

    /**
     * Nom de la route Vue d'un écran de l'application, null sinon
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_menu_item:read', 'admin_menu_item:list'])]
    public ?string $route = null;

    /**
     * Chemin de cet écran sous /admin/<langue>/, null sinon
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_menu_item:read', 'admin_menu_item:list'])]
    public ?string $path = null;

    /**
     * Entrée de création reprise dans le bouton « Nouveau »
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_menu_item:read', 'admin_menu_item:list'])]
    public bool $shortcut = false;

    /**
     * Entrée absente du menu, listée parce qu'elle est un raccourci ou le parent d'un raccourci
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_menu_item:read', 'admin_menu_item:list'])]
    public bool $hidden = false;

    /**
     * Nombre affiché à côté du libellé
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_menu_item:read', 'admin_menu_item:list'])]
    public ?int $badge = null;
}
