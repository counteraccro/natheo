<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Access token en clair d'une session API gardée dans la session PHP, avec son expiration
 */

namespace App\Service\Api\System\User;

final readonly class UserSessionAccessToken
{
    public function __construct(public string $token, public \DateTimeImmutable $expiresAt) {}
}
