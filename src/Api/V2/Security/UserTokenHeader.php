<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Paramètre d'en-tête X-User-Token, lu par UserTokenResolver
 */

namespace App\Api\V2\Security;

use ApiPlatform\Metadata\HeaderParameter;
use ApiPlatform\OpenApi\Model\Parameter as OpenApiParameter;

final class UserTokenHeader extends HeaderParameter
{
    /**
     * @param bool $documentedAsRequired obligatoire dans l'OpenAPI seulement : son absence renvoie 403 et non 422
     */
    public function __construct(bool $documentedAsRequired = false)
    {
        parent::__construct(
            schema: ['type' => 'string'],
            openApi: $documentedAsRequired
                ? new OpenApiParameter(name: UserTokenResolver::HEADER, in: 'header', required: true)
                : null,
            description: UserTokenResolver::HEADER_DESCRIPTION,
        );
    }
}
