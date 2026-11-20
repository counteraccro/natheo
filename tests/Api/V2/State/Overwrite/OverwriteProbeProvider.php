<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de surcharge de la lecture d'une sonde OverwriteProbe : complète le résultat du provider d'origine
 */

namespace App\Tests\Api\V2\State\Overwrite;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Tests\Api\V2\Resource\Admin\OverwriteProbe;

/**
 * @implements ProviderInterface<OverwriteProbe>
 */
final readonly class OverwriteProbeProvider implements ProviderInterface
{
    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return OverwriteProbe
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): OverwriteProbe
    {
        $probe = OverwriteProbe::provide($operation, $uriVariables, $context);
        $probe->source = OverwriteProbe::OVERWRITE;
        return $probe;
    }
}
