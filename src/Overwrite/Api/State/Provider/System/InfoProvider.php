<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Exemple de surcharge du provider de GET /api/v2/info (config/cms/overwrite.yaml) : reprend la réponse du provider
 * d'origine et remplit son champ extra
 */

namespace App\Overwrite\Api\State\Provider\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Resource\System\Info;
use App\Api\V2\State\Provider\System\InfoProvider as BaseInfoProvider;

/**
 * @implements ProviderInterface<Info>
 */
final readonly class InfoProvider implements ProviderInterface
{
    public function __construct(private BaseInfoProvider $baseInfoProvider) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return Info
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Info
    {
        $info = $this->baseInfoProvider->provide($operation, $uriVariables, $context);
        return new Info(
            siteName: $info->siteName,
            natheoVersion: $info->natheoVersion,
            apiVersion: $info->apiVersion,
            extra: ['overwritten' => true],
        );
    }
}
