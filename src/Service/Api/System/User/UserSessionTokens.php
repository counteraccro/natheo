<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Session API et ses tokens en clair, connus seulement à leur création
 */

namespace App\Service\Api\System\User;

use App\Entity\Admin\System\UserSession;

final readonly class UserSessionTokens
{
    public function __construct(public UserSession $session, public string $accessToken, public string $refreshToken) {}
}
