<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture création de sqlManager
 */
namespace App\Tests\Helper\Fixtures\Tools;

use App\Entity\Admin\System\User;
use App\Entity\Admin\Tools\SqlManager;
use App\Tests\Helper\FakerTrait;

trait SqlManagerFixturesTrait
{
    use FakerTrait;

    /**
     * Créer un SqlManager
     * @param User|null $user
     * @param array $customData
     * @param bool $persist
     * @return SqlManager
     */
    public function createSqlManager(?User $user = null, array $customData = [], bool $persist = true) : SqlManager
    {
        $data = [
            'user' => $user ?? $this->createUserSuperAdmin(),
            'name' => self::getFaker()->text(),
            'query' => self::getFaker()->text(),
            'disabled' => self::getFaker()->boolean(),
        ];

        $sqlManager = $this->initEntity(SqlManager::class, array_merge($data, $customData));

        if ($persist) {
            $this->persistAndFlush($sqlManager);
        }
        return $sqlManager;
    }
}