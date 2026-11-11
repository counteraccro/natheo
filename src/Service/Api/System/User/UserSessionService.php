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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
     * Clé de la session PHP qui garde la session API d'un utilisateur du back-office sur le site public
     * @var string
     */
    public const string FRONT_SESSION_KEY = 'front_api_user_session';

    /**
     * Durée de vie de l'access token du site public, en minutes : le front ne sait pas le renouveler
     * @var int
     */
    public const int FRONT_ACCESS_TOKEN_LIFETIME = 60;

    /**
     * Durée de validité restante sous laquelle le site public renouvelle l'access token, en minutes
     * @var int
     */
    public const int FRONT_RENEW_MARGIN = 45;

    /**
     * Longueur des tokens générés
     * @var int
     */
    private const int TOKEN_SIZE = 64;

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
     * @param int $accessTokenLifetime durée de vie de l'access token, en minutes
     * @return UserSessionTokens
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(
        User $user,
        ApiToken $apiToken,
        ?string $userAgent,
        ?string $ip,
        int $accessTokenLifetime = self::ACCESS_TOKEN_LIFETIME,
    ): UserSessionTokens {
        $this->getUserSessionRepository()->deleteByUser($user, true);

        $session = new UserSession();
        $session->setUser($user)->setApiToken($apiToken)->setUserAgent($userAgent)->setIp($ip);
        return $this->issueTokens($session, $accessTokenLifetime);
    }

    /**
     * Renouvelle les tokens d'une session à partir de son refresh token, qui est remplacé.
     * Un refresh token déjà remplacé révoque la session
     * @param string $refreshToken
     * @param ApiToken $apiToken token d'API de la requête, qui doit être celui de la session
     * @param int $accessTokenLifetime durée de vie du nouvel access token, en minutes
     * @return UserSessionTokens|null null si le refresh token est inconnu, expiré, réutilisé ou d'une autre application
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function refresh(
        string $refreshToken,
        ApiToken $apiToken,
        int $accessTokenLifetime = self::ACCESS_TOKEN_LIFETIME,
    ): ?UserSessionTokens
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
        return $this->issueTokens($session, $accessTokenLifetime);
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
     * Access token d'un utilisateur du back-office qui consulte le site public. La session API est gardée dans la
     * session PHP, renouvelée quand l'access token approche de son expiration, créée s'il n'y en a pas
     * @param User $user
     * @param string $apiToken token d'API du site public, en clair
     * @param SessionInterface $phpSession
     * @param string|null $userAgent
     * @param string|null $ip
     * @return string chaîne vide si le token d'API du site public est absent ou invalide
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFrontAccessToken(
        User $user,
        string $apiToken,
        SessionInterface $phpSession,
        ?string $userAgent,
        ?string $ip,
    ): string {
        /** @var ApiToken|null $frontApiToken */
        $frontApiToken = $this->findOneBy(ApiToken::class, 'token', TokenHasher::hash($apiToken));
        if ($frontApiToken === null || $frontApiToken->isDisabled() || $frontApiToken->isExpired()) {
            return '';
        }

        $tokens = null;
        $stored = $phpSession->get(self::FRONT_SESSION_KEY);
        if (is_array($stored) && ($stored['userId'] ?? null) === $user->getId()) {
            $session = $this->findByAccessToken((string) $stored['accessToken']);
            $renewAt = new \DateTimeImmutable('+' . self::FRONT_RENEW_MARGIN . ' minutes');
            if ($session !== null && $session->getAccessTokenExpiresAt() > $renewAt) {
                return (string) $stored['accessToken'];
            }
            $tokens = $this->refresh(
                (string) $stored['refreshToken'],
                $frontApiToken,
                self::FRONT_ACCESS_TOKEN_LIFETIME,
            );
        }

        $tokens ??= $this->create($user, $frontApiToken, $userAgent, $ip, self::FRONT_ACCESS_TOKEN_LIFETIME);
        $phpSession->set(self::FRONT_SESSION_KEY, [
            'userId' => $user->getId(),
            'accessToken' => $tokens->accessToken,
            'refreshToken' => $tokens->refreshToken,
        ]);
        return $tokens->accessToken;
    }

    /**
     * Révoque la session API gardée dans la session PHP par le site public
     * @param SessionInterface $phpSession
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function revokeFrontSession(SessionInterface $phpSession): void
    {
        $stored = $phpSession->get(self::FRONT_SESSION_KEY);
        $phpSession->remove(self::FRONT_SESSION_KEY);
        if (!is_array($stored)) {
            return;
        }

        $session = $this->getUserSessionRepository()->findOneBy([
            'refreshTokenHash' => TokenHasher::hash((string) ($stored['refreshToken'] ?? '')),
        ]);
        if ($session !== null) {
            $this->revoke($session);
        }
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
        // find() relit la session déjà chargée dans la requête sans nouvelle requête SQL
        $session = $this->getUserSessionRepository()->find($id);
        return $session !== null &&
            $session->getUser()->getId() === $user->getId() &&
            $session->getRefreshTokenExpiresAt() > new \DateTimeImmutable()
            ? $session
            : null;
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
     * @param int $accessTokenLifetime durée de vie de l'access token, en minutes
     * @return UserSessionTokens
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function issueTokens(UserSession $session, int $accessTokenLifetime): UserSessionTokens
    {
        $accessToken = ByteString::fromRandom(self::TOKEN_SIZE)->toString();
        $refreshToken = ByteString::fromRandom(self::TOKEN_SIZE)->toString();
        $now = new \DateTimeImmutable();

        $session
            ->setAccessTokenHash(TokenHasher::hash($accessToken))
            ->setAccessTokenExpiresAt($now->modify('+' . $accessTokenLifetime . ' minutes'))
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
        if (!$session->isLastUsedAtOutdated($now)) {
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
