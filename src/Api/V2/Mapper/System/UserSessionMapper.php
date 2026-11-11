<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Convertit une session API en ressource UserSession de l'API v2
 */

namespace App\Api\V2\Mapper\System;

use App\Api\V2\Resource\System\UserSession;
use App\Entity\Admin\System\UserSession as UserSessionEntity;
use App\Service\Api\System\User\UserSessionTokens;

final readonly class UserSessionMapper
{
    /**
     * @param int|null $currentId id de la session de la requête
     */
    public function __construct(private ?int $currentId = null) {}

    /**
     * @param UserSessionEntity $entity
     * @return UserSession
     */
    public function map(UserSessionEntity $entity): UserSession
    {
        $session = new UserSession();
        $session->id = (int) $entity->getId();
        $session->current = $this->currentId !== null && $entity->getId() === $this->currentId;
        $session->userAgent = $entity->getUserAgent();
        $session->ip = $entity->getIp();
        $session->createdAt = $entity->getCreatedAt();
        $session->lastUsedAt = $entity->getLastUsedAt();
        return $session;
    }

    /**
     * Session avec ses tokens en clair, à sa création ou à son renouvellement
     * @param UserSessionTokens $tokens
     * @return UserSession
     */
    public function mapWithTokens(UserSessionTokens $tokens): UserSession
    {
        $session = $this->map($tokens->session);
        $session->accessToken = $tokens->accessToken;
        $session->accessTokenExpiresAt = $tokens->session->getAccessTokenExpiresAt();
        $session->refreshToken = $tokens->refreshToken;
        $session->refreshTokenExpiresAt = $tokens->session->getRefreshTokenExpiresAt();
        return $session;
    }
}
