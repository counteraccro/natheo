<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
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
     * Clé de la session PHP qui garde la session API de l'application du back-office
     * @var string
     */
    public const string BACK_OFFICE_SESSION_KEY = 'back_office_api_user_session';

    /**
     * Durée de validité restante sous laquelle le back-office renouvelle l'access token, en minutes
     * @var int
     */
    public const int BACK_OFFICE_RENEW_MARGIN = 2;

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
     * @param User|null $impersonator utilisateur d'origine pendant une usurpation
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
        ?User $impersonator = null,
    ): UserSessionTokens {
        $this->getUserSessionRepository()->deleteByUser($user, true);

        $session = new UserSession();
        $session
            ->setUser($user)
            ->setApiToken($apiToken)
            ->setImpersonator($impersonator)
            ->setUserAgent($userAgent)
            ->setIp($ip);
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

        return $this->getStoredAccessToken(
            self::FRONT_SESSION_KEY,
            $user,
            null,
            $frontApiToken,
            self::FRONT_ACCESS_TOKEN_LIFETIME,
            self::FRONT_RENEW_MARGIN,
            $phpSession,
            $userAgent,
            $ip,
        )->token;
    }

    /**
     * Access token de l'application du back-office, sur le token interne. La session API est gardée dans la
     * session PHP, renouvelée quand l'access token approche de son expiration, créée s'il n'y en a pas
     * @param User $user utilisateur connecté (cible de l'usurpation le cas échéant)
     * @param User|null $impersonator utilisateur d'origine pendant une usurpation
     * @param SessionInterface $phpSession
     * @param string|null $userAgent
     * @param string|null $ip
     * @return UserSessionAccessToken
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getBackOfficeAccessToken(
        User $user,
        ?User $impersonator,
        SessionInterface $phpSession,
        ?string $userAgent,
        ?string $ip,
    ): UserSessionAccessToken {
        return $this->getStoredAccessToken(
            self::BACK_OFFICE_SESSION_KEY,
            $user,
            $impersonator,
            $this->getApiTokenService()->getInternalBackOfficeToken(),
            self::ACCESS_TOKEN_LIFETIME,
            self::BACK_OFFICE_RENEW_MARGIN,
            $phpSession,
            $userAgent,
            $ip,
        );
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
        $this->revokeStoredSession(self::FRONT_SESSION_KEY, $phpSession);
    }

    /**
     * Révoque la session API gardée dans la session PHP par l'application du back-office
     * @param SessionInterface $phpSession
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function revokeBackOfficeSession(SessionInterface $phpSession): void
    {
        $this->revokeStoredSession(self::BACK_OFFICE_SESSION_KEY, $phpSession);
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
     * Access token d'une session API gardée dans la session PHP sous $key : réutilisé tant qu'il reste valable
     * plus de $renewMargin minutes, sinon renouvelé par le refresh token, sinon d'une nouvelle session.
     * Une session gardée pour un autre utilisateur ou un autre utilisateur d'origine n'est pas reprise
     * @param string $key clé de la session PHP
     * @param User $user
     * @param User|null $impersonator utilisateur d'origine pendant une usurpation
     * @param ApiToken $apiToken token d'API dont la session porte les rôles
     * @param int $accessTokenLifetime durée de vie de l'access token, en minutes
     * @param int $renewMargin durée de validité restante sous laquelle l'access token est renouvelé, en minutes
     * @param SessionInterface $phpSession
     * @param string|null $userAgent
     * @param string|null $ip
     * @return UserSessionAccessToken
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getStoredAccessToken(
        string $key,
        User $user,
        ?User $impersonator,
        ApiToken $apiToken,
        int $accessTokenLifetime,
        int $renewMargin,
        SessionInterface $phpSession,
        ?string $userAgent,
        ?string $ip,
    ): UserSessionAccessToken {
        $tokens = null;
        $stored = $phpSession->get($key);
        if (
            is_array($stored) &&
            ($stored['userId'] ?? null) === $user->getId() &&
            ($stored['impersonatorId'] ?? null) === $impersonator?->getId()
        ) {
            $session = $this->findByAccessToken((string) $stored['accessToken']);
            $renewAt = new \DateTimeImmutable('+' . $renewMargin . ' minutes');
            if ($session !== null && $session->getAccessTokenExpiresAt() > $renewAt) {
                return new UserSessionAccessToken((string) $stored['accessToken'], $session->getAccessTokenExpiresAt());
            }
            $tokens = $this->refresh((string) $stored['refreshToken'], $apiToken, $accessTokenLifetime);
        }

        $tokens ??= $this->create($user, $apiToken, $userAgent, $ip, $accessTokenLifetime, $impersonator);
        $phpSession->set($key, [
            'userId' => $user->getId(),
            'impersonatorId' => $impersonator?->getId(),
            'accessToken' => $tokens->accessToken,
            'refreshToken' => $tokens->refreshToken,
        ]);
        return new UserSessionAccessToken($tokens->accessToken, $tokens->session->getAccessTokenExpiresAt());
    }

    /**
     * Révoque la session API gardée dans la session PHP sous $key et l'en retire
     * @param string $key
     * @param SessionInterface $phpSession
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function revokeStoredSession(string $key, SessionInterface $phpSession): void
    {
        $stored = $phpSession->get($key);
        $phpSession->remove($key);
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
