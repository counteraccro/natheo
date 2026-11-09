<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource API v2 chargée uniquement en env test pour tester le socle (rôles, formats, erreurs)
 */

namespace App\Tests\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ApiResource(
        operations: [
            new GetCollection(security: "is_granted('ROLE_READ_API')", provider: [self::class, 'provide']),
            new Get(security: "is_granted('ROLE_READ_API')", provider: [self::class, 'provide']),
            new Post(security: "is_granted('ROLE_WRITE_API')", processor: [self::class, 'process']),
            new Delete(
                security: "is_granted('ROLE_ADMIN_API')",
                provider: [self::class, 'provide'],
                processor: [self::class, 'process'],
            ),
        ],
    ),
]
final class Probe
{
    /**
     * Identifiant de la seule sonde existante
     * @var int
     */
    public const int EXISTING_ID = 1;

    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    #[Assert\NotBlank]
    public string $name = '';

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return Probe|array<Probe>
     */
    public static function provide(Operation $operation, array $uriVariables = [], array $context = []): self|array
    {
        $probe = new self();
        $probe->id = self::EXISTING_ID;
        $probe->name = 'probe';

        if ($operation instanceof GetCollection) {
            return [$probe];
        }
        if ((int) ($uriVariables['id'] ?? 0) !== self::EXISTING_ID) {
            throw new NotFoundHttpException();
        }
        return $probe;
    }

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return Probe|null
     */
    public static function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): ?self {
        if ($operation instanceof Delete) {
            return null;
        }
        $data->id = 2;
        return $data;
    }
}
