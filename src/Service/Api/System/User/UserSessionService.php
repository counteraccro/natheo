<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Sessions API des utilisateurs du back-office : authentification, création, renouvellement et révocation
 */

namespace App\Service\Api\System\User;

use App\Entity\Admin\System\ApiToken;
use App\Entity\Admin\System\User;
use App\Entity\Admin\System\UserSession;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Enum\Admin\System\User\Role;
use App\Repository\Admin\System\UserSessionRepository;
use App\Service\Api\AppApiService;
use App\Service\Global\Token\TokenHasher;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\String\ByteString;

class UserSessionService extends AppApiService
{
    /**
     * Durée de vie de l'access token, en minutes
     * @var int
     */
    public const int ACCESS_TOKEN_LIFETIME = 15;

    /**
     * Durées de vie possibles du refresh token, en minutes (1, 7 et 30 jours)
     * @var int[]
     */
    public const array REFRESH_TOKEN_LIFETIMES = [1440, 10080, 43200];

    /**
     * Durée de vie par défaut du refresh token, en minutes
     * @var int
     */
    public const int DEFAULT_REFRESH_TOKEN_LIFETIME = 10080;

    /**
     * Longueur des tokens générés
     * @var int
     */
    private const int TOKEN_SIZE = 64;

    /**
     * Intervalle minimal entre deux mises à jour de la date de dernière utilisation, en secondes
     * @var int
     */
    private const int LAST_USED_REFRESH_INTERVAL = 60;

    /**
     * Vérifie les identifiants d'un utilisateur, avec une limite de tentatives par IP et email.
     * Refuse les comptes sans rôle d'administration
     * @param string $email
     * @param string $password
     * @param string $clientIp
     * @return AuthenticationResult
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function authenticate(string $email, string $password, string $clientIp): AuthenticationResult
    {
        $limiter = $this->getApiAuthUserLimiter()->create($clientIp . '-' . mb_strtolower($email));
        $limit = $limiter->consume();
        if (!$limit->isAccepted()) {
            return new AuthenticationResult(retryAfter: max(0, $limit->getRetryAfter()->getTimestamp() - time()));
        }

        $user = $this->getUserService()->getUserByEmailAndPassword($email, $password);
        if ($user === null || $user->getRoles() === [Role::USER->value]) {
            return new AuthenticationResult();
        }
        $limiter->reset();
        return new AuthenticationResult($user);
    }

    /**
     * Crée une session pour l'utilisateur et supprime ses sessions expirées
     * @param User $user
     * @param ApiToken $apiToken token d'API de la requête, dont la session porte les rôles
     * @param string|null $userAgent
     * @param string|null $ip
     * @return UserSessionTokens
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(User $user, ApiToken $apiToken, ?string $userAgent, ?string $ip): UserSessionTokens
    {
        $this->getUserSessionRepository()->deleteByUser($user, true);

        $session = new UserSession();
        $session->setUser($user)->setApiToken($apiToken)->setUserAgent($userAgent)->setIp($ip);
        return $this->issueTokens($session);
    }

    /**
     * Renouvelle les tokens d'une session à partir de son refresh token, qui est remplacé.
     * Un refresh token déjà remplacé révoque la session
     * @param string $refreshToken
     * @param ApiToken $apiToken token d'API de la requête, qui doit être celui de la session
     * @return UserSessionTokens|null null si le refresh token est inconnu, expiré, réutilisé ou d'une autre application
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function refresh(string $refreshToken, ApiToken $apiToken): ?UserSessionTokens
    {
        $repository = $this->getUserSessionRepository();
        $hash = TokenHasher::hash($refreshToken);

        $session = $repository->findOneBy(['refreshTokenHash' => $hash]);
        if ($session === null) {
            $reused = $repository->findOneBy(['previousRefreshTokenHash' => $hash]);
            if ($reused !== null) {
                $this->revoke($reused);
            }
            return null;
        }

        if (
            $session->getApiToken()->getId() !== $apiToken->getId() ||
            $session->getRefreshTokenExpiresAt() <= new \DateTimeImmutable() ||
            !$this->isUsable($session)
        ) {
            return null;
        }

        $session->setPreviousRefreshTokenHash($hash);
        return $this->issueTokens($session);
    }

    /**
     * Session valide d'un access token : non expiré, compte actif, token d'API actif
     * @param string $accessToken
     * @return UserSession|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findByAccessToken(string $accessToken): ?UserSession
    {
        $session = $this->getUserSessionRepository()->findOneBy([
            'accessTokenHash' => TokenHasher::hash($accessToken),
        ]);
        if (
            $session === null ||
            $session->getAccessTokenExpiresAt() <= new \DateTimeImmutable() ||
            !$this->isUsable($session)
        ) {
            return null;
        }

        $this->updateLastUsedAt($session);
        return $session;
    }

    /**
     * Sessions actives de l'utilisateur
     * @param User $user
     * @return UserSession[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findActiveByUser(User $user): array
    {
        return $this->getUserSessionRepository()->findActiveByUser($user);
    }

    /**
     * Session active de l'utilisateur par son id
     * @param User $user
     * @param int $id
     * @return UserSession|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findActiveByUserAndId(User $user, int $id): ?UserSession
    {
        $session = $this->getUserSessionRepository()->findOneBy(['id' => $id, 'user' => $user]);
        return $session !== null && $session->getRefreshTokenExpiresAt() > new \DateTimeImmutable() ? $session : null;
    }

    /**
     * Révoque une session
     * @param UserSession $session
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function revoke(UserSession $session): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($session);
        $entityManager->flush();
    }

    /**
     * Révoque toutes les sessions de l'utilisateur
     * @param User $user
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function revokeAll(User $user): void
    {
        $this->getUserSessionRepository()->deleteByUser($user);
    }

    /**
     * Durée de vie du refresh token en minutes, celle par défaut si l'option a une valeur inconnue
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getRefreshTokenLifetime(): int
    {
        $minutes = intval(
            $this->getOptionSystemService()->getValueByKey(OptionSystem::OS_API_TIME_VALIDATE_USER_TOKEN->value),
        );
        return in_array($minutes, self::REFRESH_TOKEN_LIFETIMES, true)
            ? $minutes
            : self::DEFAULT_REFRESH_TOKEN_LIFETIME;
    }

    /**
     * Génère et enregistre de nouveaux tokens pour la session
     * @param UserSession $session
     * @return UserSessionTokens
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function issueTokens(UserSession $session): UserSessionTokens
    {
        $accessToken = ByteString::fromRandom(self::TOKEN_SIZE)->toString();
        $refreshToken = ByteString::fromRandom(self::TOKEN_SIZE)->toString();
        $now = new \DateTimeImmutable();

        $session
            ->setAccessTokenHash(TokenHasher::hash($accessToken))
            ->setAccessTokenExpiresAt($now->modify('+' . self::ACCESS_TOKEN_LIFETIME . ' minutes'))
            ->setRefreshTokenHash(TokenHasher::hash($refreshToken))
            ->setRefreshTokenExpiresAt($now->modify('+' . $this->getRefreshTokenLifetime() . ' minutes'))
            ->setLastUsedAt($now);
        $this->save($session);

        return new UserSessionTokens($session, $accessToken, $refreshToken);
    }

    /**
     * Compte actif et non anonyme, token d'API actif et non expiré
     * @param UserSession $session
     * @return bool
     */
    private function isUsable(UserSession $session): bool
    {
        $user = $session->getUser();
        $apiToken = $session->getApiToken();
        return !$user->isDisabled() && !$user->isAnonymous() && !$apiToken->isDisabled() && !$apiToken->isExpired();
    }

    /**
     * Met à jour la date de dernière utilisation, au plus une fois par intervalle
     * @param UserSession $session
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function updateLastUsedAt(UserSession $session): void
    {
        $now = new \DateTimeImmutable();
        $lastUsedAt = $session->getLastUsedAt();
        if (
            $lastUsedAt !== null &&
            $now->getTimestamp() - $lastUsedAt->getTimestamp() < self::LAST_USED_REFRESH_INTERVAL
        ) {
            return;
        }

        $session->setLastUsedAt($now);
        $this->save($session);
    }

    /**
     * @return UserSessionRepository
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getUserSessionRepository(): UserSessionRepository
    {
        /** @var UserSessionRepository */
        return $this->getRepository(UserSession::class);
    }
}
