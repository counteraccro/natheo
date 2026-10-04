<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Création fixture ApiToken
 */

namespace App\Tests\Helper\Fixtures\System;

use App\Entity\Admin\System\ApiToken;
use App\Tests\Helper\FakerTrait;
use App\Utils\System\ApiToken\TokenHasher;

trait ApiTokenFixturesTrait
{
    use FakerTrait;

    /**
     * Création d'un apiToken. La clé 'token' attend un hash (voir TokenHasher)
     * @param array $customData
     * @param bool $persist
     * @return ApiToken
     */
    public function createApiToken(array $customData = [], bool $persist = true): ApiToken
    {
        $data = [
            'name' => self::getFaker()->text(),
            'token' => TokenHasher::hash(self::getFaker()->uuid()),
            'roles' => ['ROLE_USER'],
            'comment' => self::getFaker()->text(),
            'disabled' => self::getFaker()->boolean(),
        ];
        $apiToken = $this->initEntity(ApiToken::class, array_merge($data, $customData));

        if ($persist) {
            $this->persistAndFlush($apiToken);
        }
        return $apiToken;
    }
}
