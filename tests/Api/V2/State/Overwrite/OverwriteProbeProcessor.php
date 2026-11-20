<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Processor de surcharge de la modification d'une sonde OverwriteProbe
 */

namespace App\Tests\Api\V2\State\Overwrite;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Tests\Api\V2\Resource\Admin\OverwriteProbe;

/**
 * @implements ProcessorInterface<OverwriteProbe, OverwriteProbe>
 */
final readonly class OverwriteProbeProcessor implements ProcessorInterface
{
    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return OverwriteProbe
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): OverwriteProbe {
        $probe = OverwriteProbe::process($data, $operation, $uriVariables, $context);
        $probe->source = OverwriteProbe::OVERWRITE;
        return $probe;
    }
}
