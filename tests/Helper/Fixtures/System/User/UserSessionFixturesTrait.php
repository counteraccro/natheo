<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Création fixture UserSession
 */

namespace App\Tests\Helper\Fixtures\System\User;

use App\Entity\Admin\System\UserSession;
use App\Service\Global\Token\TokenHasher;
use App\Tests\Helper\FakerTrait;

trait UserSessionFixturesTrait
{
    use FakerTrait;

    /**
     * Création d'une session API valide. Les clés 'accessTokenHash' et 'refreshTokenHash' attendent un hash
     * (voir TokenHasher)
     * @param array $customData
     * @param bool $persist
     * @return UserSession
     */
    public function createUserSession(array $customData = [], bool $persist = true): UserSession
    {
        $data = [
            'user' => $customData['user'] ?? $this->createUserContributeur(['disabled' => false, 'anonymous' => false]),
            'apiToken' =>
                $customData['apiToken'] ?? $this->createApiToken(['roles' => ['ROLE_READ_API'], 'disabled' => false]),
            'accessTokenHash' => TokenHasher::hash(self::getFaker()->uuid()),
            'accessTokenExpiresAt' => new \DateTimeImmutable('+15 minutes'),
            'refreshTokenHash' => TokenHasher::hash(self::getFaker()->uuid()),
            'refreshTokenExpiresAt' => new \DateTimeImmutable('+7 days'),
            'userAgent' => self::getFaker()->userAgent(),
            'ip' => self::getFaker()->ipv4(),
        ];
        $session = $this->initEntity(UserSession::class, array_merge($data, $customData));

        if ($persist) {
            $this->persistAndFlush($session);
        }
        return $session;
    }
}
