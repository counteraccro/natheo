<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Activation d'un module du back-office, avec ses opérations d'API
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
use App\Api\V2\Exception\ConflictException;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Exception\PreconditionFailedException;
use App\Api\V2\Input\Admin\System\UpdateActivationInput;
use App\Api\V2\Metadata\AcceptLanguageParameter;
use App\Api\V2\Metadata\LocaleParameter;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\State\Processor\Admin\System\UpdateAdminModuleActivationProcessor;
use App\Api\V2\State\Provider\Admin\System\AdminModuleActivationCollectionProvider;
use App\Api\V2\State\Provider\Admin\System\AdminModuleActivationProvider;
use App\Enum\Admin\Module\CoreModuleId;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'AdminModuleActivation',
        extraProperties: [
            OperationSecurityMetadataFactory::MODULE => CoreModuleId::MODULE_ACTIVATION->value,
            OperationSecurityMetadataFactory::LOCKED => true,
        ],
        normalizationContext: ['groups' => ['admin_module_activation:read']],
        operations: [
            new GetCollection(
                name: 'admin_module_activation_get_collection',
                uriTemplate: '/admin/module_activations',
                paginationEnabled: false,
                errors: [],
                openapi: new OpenApiOperation(
                    summary: 'L\'activation des modules du back-office',
                    description: 'Tous les modules, dans l\'ordre du menu, avec leurs opérations d\'API.',
                ),
                normalizationContext: ['groups' => ['admin_module_activation:list']],
                provider: AdminModuleActivationCollectionProvider::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Get(
                name: 'admin_module_activation_get',
                uriTemplate: '/admin/module_activations/{id}',
                requirements: ['id' => '[a-z0-9_]+'],
                errors: [NotFoundException::class],
                openapi: new OpenApiOperation(summary: 'L\'activation d\'un module du back-office'),
                provider: AdminModuleActivationProvider::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
            new Patch(
                name: 'admin_module_activation_patch',
                uriTemplate: '/admin/module_activations/{id}',
                requirements: ['id' => '[a-z0-9_]+'],
                errors: [
                    NotFoundException::class,
                    ConflictException::class,
                    PreconditionFailedException::class,
                    ValidationException::class,
                ],
                openapi: new OpenApiOperation(
                    summary: 'Active ou désactive un module du back-office',
                    description: 'Un module désactivé disparaît du menu, ses pages et ses opérations répondent ' .
                        '404 ; refusé en 409 pour un module verrouillé. Le site public n\'est pas concerné.',
                ),
                input: UpdateActivationInput::class,
                provider: AdminModuleActivationProvider::class,
                processor: UpdateAdminModuleActivationProcessor::class,
                parameters: [new LocaleParameter(), new AcceptLanguageParameter()],
            ),
        ],
    ),
]
final class AdminModuleActivation implements VersionedResourceInterface
{
    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['admin_module_activation:read', 'admin_module_activation:list'])]
    public string $id = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_module_activation:read', 'admin_module_activation:list'])]
    public string $label = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_module_activation:read', 'admin_module_activation:list'])]
    public string $description = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_module_activation:read', 'admin_module_activation:list'])]
    public bool $locked = false;

    #[ApiProperty(required: true)]
    #[Groups(['admin_module_activation:read', 'admin_module_activation:list'])]
    public bool $disabled = false;

    /**
     * @var AdminOperationActivation[]
     */
    #[ApiProperty(required: true)]
    #[Groups(['admin_module_activation:read', 'admin_module_activation:list'])]
    public array $operations = [];

    /**
     * État du module et de ses opérations
     * @return string|null
     */
    public function getVersion(): ?string
    {
        $operations = array_map(fn(AdminOperationActivation $operation) => $operation->disabled, $this->operations);
        return json_encode([$this->disabled, $operations], JSON_THROW_ON_ERROR);
    }
}
