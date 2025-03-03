<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Création fixture ApiToken
 */

namespace App\Tests\Helper\Fixtures\System;

use App\Entity\Admin\System\ApiToken;
use App\Tests\Helper\FakerTrait;

trait ApiTokenFixturesTrait
{
    use FakerTrait;

    /**
     * Création d'un apiToken
     * @param array $customData
     * @param bool $persist
     * @return ApiToken
     */
    public function createApiToken(array $customData = [], bool $persist = true): ApiToken
    {
        $data = [
            'name' => self::getFaker()->text(),
            'token' => self::getFaker()->text(),
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