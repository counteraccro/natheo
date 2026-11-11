<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Session API d'un utilisateur du back-office : access token et refresh token opaques, stockés hachés
 */

namespace App\Entity\Admin\System;

use App\Entity\Trait\LastUsedAtTrait;
use App\Enum\Installation\DoctrineStrategy;
use App\Repository\Admin\System\UserSessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserSessionRepository::class)]
#[ORM\Table(name: 'user_session')]
#[ORM\HasLifecycleCallbacks]
class UserSession
{
    use LastUsedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: DoctrineStrategy::CURRENT)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    /**
     * Token d'API qui a créé la session : la session porte ses rôles
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'api_token_id', nullable: false, onDelete: 'CASCADE')]
    private ?ApiToken $apiToken = null;

    /**
     * Hash SHA-256 de l'access token, le token en clair n'est jamais stocké
     */
    #[ORM\Column(name: 'access_token_hash', length: 64, unique: true)]
    private ?string $accessTokenHash = null;

    #[ORM\Column(name: 'access_token_expires_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $accessTokenExpiresAt = null;

    /**
     * Hash SHA-256 du refresh token
     */
    #[ORM\Column(name: 'refresh_token_hash', length: 64, unique: true)]
    private ?string $refreshTokenHash = null;

    #[ORM\Column(name: 'refresh_token_expires_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $refreshTokenExpiresAt = null;

    /**
     * Hash du refresh token remplacé à la dernière rotation, dont la réutilisation révoque la session
     */
    #[ORM\Column(name: 'previous_refresh_token_hash', length: 64, nullable: true)]
    private ?string $previousRefreshTokenHash = null;

    #[ORM\Column(name: 'user_agent', length: 255, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'last_used_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastUsedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getApiToken(): ?ApiToken
    {
        return $this->apiToken;
    }

    public function setApiToken(?ApiToken $apiToken): static
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getAccessTokenHash(): ?string
    {
        return $this->accessTokenHash;
    }

    public function setAccessTokenHash(string $accessTokenHash): static
    {
        $this->accessTokenHash = $accessTokenHash;

        return $this;
    }

    public function getAccessTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->accessTokenExpiresAt;
    }

    public function setAccessTokenExpiresAt(\DateTimeImmutable $accessTokenExpiresAt): static
    {
        $this->accessTokenExpiresAt = $accessTokenExpiresAt;

        return $this;
    }

    public function getRefreshTokenHash(): ?string
    {
        return $this->refreshTokenHash;
    }

    public function setRefreshTokenHash(string $refreshTokenHash): static
    {
        $this->refreshTokenHash = $refreshTokenHash;

        return $this;
    }

    public function getRefreshTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->refreshTokenExpiresAt;
    }

    public function setRefreshTokenExpiresAt(\DateTimeImmutable $refreshTokenExpiresAt): static
    {
        $this->refreshTokenExpiresAt = $refreshTokenExpiresAt;

        return $this;
    }

    public function getPreviousRefreshTokenHash(): ?string
    {
        return $this->previousRefreshTokenHash;
    }

    public function setPreviousRefreshTokenHash(?string $previousRefreshTokenHash): static
    {
        $this->previousRefreshTokenHash = $previousRefreshTokenHash;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent === null ? null : mb_substr($userAgent, 0, 255);

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastUsedAt(): ?\DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(?\DateTimeImmutable $lastUsedAt): static
    {
        $this->lastUsedAt = $lastUsedAt;

        return $this;
    }
}
