<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Activation d'une opération d'API d'un module du back-office
 */

namespace App\Api\V2\Resource\Admin\System;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Api\V2\Concurrency\VersionedResourceInterface;
use App\Api\V2\Exception\ConflictException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Exception\PreconditionFailedException;
use App\Api\V2\Input\Admin\System\UpdateActivationInput;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\State\Processor\Admin\System\UpdateAdminOperationActivationProcessor;
use App\Api\V2\State\Provider\Admin\System\AdminOperationActivationProvider;
use App\Enum\Admin\Module\CoreModuleId;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'AdminOperationActivation',
        extraProperties: [
            OperationSecurityMetadataFactory::MODULE => CoreModuleId::MODULE_ACTIVATION->value,
            OperationSecurityMetadataFactory::LOCKED => true,
        ],
        normalizationContext: ['groups' => ['admin_operation_activation:read']],
        operations: [
            new Get(
                name: 'admin_operation_activation_get',
                uriTemplate: '/admin/operation_activations/{id}',
                requirements: ['id' => '[a-z0-9_]+'],
                errors: [NotFoundException::class],
                openapi: new OpenApiOperation(summary: 'L\'activation d\'une opération d\'API du back-office'),
                provider: AdminOperationActivationProvider::class,
            ),
            new Patch(
                name: 'admin_operation_activation_patch',
                uriTemplate: '/admin/operation_activations/{id}',
                requirements: ['id' => '[a-z0-9_]+'],
                errors: [
                    NotFoundException::class,
                    ConflictException::class,
                    PreconditionFailedException::class,
                    ValidationException::class,
                ],
                openapi: new OpenApiOperation(
                    summary: 'Active ou désactive une opération d\'API du back-office',
                    description: 'Une opération désactivée répond 404 et disparaît de la documentation ; ' .
                        'refusé en 409 pour une opération verrouillée.',
                ),
                input: UpdateActivationInput::class,
                provider: AdminOperationActivationProvider::class,
                processor: UpdateAdminOperationActivationProcessor::class,
            ),
        ],
    ),
]
final class AdminOperationActivation implements VersionedResourceInterface
{
    /**
     * Nom de l'opération
     */
    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['admin_operation_activation:read', 'admin_module_activation:read', 'admin_module_activation:list'])]
    public string $id = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_operation_activation:read', 'admin_module_activation:read', 'admin_module_activation:list'])]
    public string $module = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_operation_activation:read', 'admin_module_activation:read', 'admin_module_activation:list'])]
    public string $method = '';

    /**
     * Adresse sous le préfixe de l'API
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_operation_activation:read', 'admin_module_activation:read', 'admin_module_activation:list'])]
    public string $path = '';

    /**
     * Nature de l'opération : get_collection, get, post, put, patch ou delete
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_operation_activation:read', 'admin_module_activation:read', 'admin_module_activation:list'])]
    public string $kind = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_operation_activation:read', 'admin_module_activation:read', 'admin_module_activation:list'])]
    public bool $locked = false;

    /**
     * Désactivation propre de l'opération, sans tenir compte de son module
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_operation_activation:read', 'admin_module_activation:read', 'admin_module_activation:list'])]
    public bool $disabled = false;

    /**
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->disabled ? 'disabled' : 'enabled';
    }
}
