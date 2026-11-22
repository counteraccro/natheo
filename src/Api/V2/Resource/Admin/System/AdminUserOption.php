<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 d'administration : option de l'utilisateur de la session
 */

namespace App\Api\V2\Resource\Admin\System;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Concurrency\VersionedResourceInterface;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Exception\PreconditionFailedException;
use App\Api\V2\Input\Admin\System\UpdateUserOptionInput;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\Output\Admin\Global\Permissions;
use App\Api\V2\Output\Admin\System\AdminOptionChoice;
use App\Api\V2\State\Processor\Admin\System\UpdateAdminUserOptionProcessor;
use App\Api\V2\State\Provider\Admin\System\AdminUserOptionCollectionProvider;
use App\Api\V2\State\Provider\Admin\System\AdminUserOptionProvider;
use App\Enum\Admin\Module\CoreModuleId;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'AdminUserOption',
        extraProperties: [OperationSecurityMetadataFactory::MODULE => CoreModuleId::MY_ACCOUNT->value],
        normalizationContext: [
            'groups' => ['admin_user_option:read', Permissions::GROUP, AdminOptionChoice::GROUP],
        ],
        operations: [
            new GetCollection(
                name: 'admin_user_option_get_collection',
                uriTemplate: '/admin/user_options',
                paginationEnabled: false,
                errors: [ForbiddenException::class],
                openapi: new OpenApiOperation(
                    summary: 'Les options de l\'utilisateur',
                    description: 'Options de l\'utilisateur de la session, dans l\'ordre de la configuration ; ' .
                        'une option jamais modifiée a sa valeur par défaut.',
                ),
                normalizationContext: [
                    'groups' => ['admin_user_option:list', Permissions::GROUP, AdminOptionChoice::GROUP],
                ],
                provider: AdminUserOptionCollectionProvider::class,
            ),
            new Get(
                name: 'admin_user_option_get',
                uriTemplate: '/admin/user_options/{id}',
                requirements: ['id' => '[a-z_]+'],
                errors: [ForbiddenException::class, NotFoundException::class],
                openapi: new OpenApiOperation(summary: 'Une option de l\'utilisateur'),
                provider: AdminUserOptionProvider::class,
            ),
            new Patch(
                name: 'admin_user_option_patch',
                uriTemplate: '/admin/user_options/{id}',
                requirements: ['id' => '[a-z_]+'],
                errors: [
                    ForbiddenException::class,
                    NotFoundException::class,
                    PreconditionFailedException::class,
                    ValidationException::class,
                ],
                openapi: new OpenApiOperation(
                    summary: 'Modifie une option de l\'utilisateur',
                    description: 'La valeur doit être l\'une des valeurs de l\'option ; une option désactivée ' .
                        'n\'est pas modifiable.',
                ),
                input: UpdateUserOptionInput::class,
                provider: AdminUserOptionProvider::class,
                processor: UpdateAdminUserOptionProcessor::class,
            ),
        ],
    ),
]
final class AdminUserOption implements VersionedResourceInterface
{
    /**
     * Clé de l'option en minuscules (ou_admin_theme)
     */
    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['admin_user_option:read', 'admin_user_option:list'])]
    public string $id = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_user_option:read', 'admin_user_option:list'])]
    public string $label = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_user_option:read', 'admin_user_option:list'])]
    public ?string $help = null;

    #[ApiProperty(required: true)]
    #[Groups(['admin_user_option:read', 'admin_user_option:list'])]
    public string $value = '';

    /**
     * Valeurs proposées d'une option à liste, vide pour les autres
     * @var AdminOptionChoice[]
     */
    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_user_option:read', 'admin_user_option:list'])]
    public array $choices = [];

    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_user_option:read', 'admin_user_option:list'])]
    public ?Permissions $permissions = null;

    /**
     * La valeur suffit : la clé est dans l'adresse et la liste vient de la configuration
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->value;
    }
}
