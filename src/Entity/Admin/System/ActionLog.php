<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Écriture faite dans le back-office, avec son auteur, son module, son action et l'élément concerné
 */

namespace App\Entity\Admin\System;

use App\Enum\Installation\DoctrineStrategy;
use App\Repository\Admin\System\ActionLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionLogRepository::class)]
#[ORM\Table(name: 'action_log')]
#[ORM\Index(name: 'IDX_ACTION_LOG_CREATED_AT', columns: ['created_at'])]
#[ORM\Index(name: 'IDX_ACTION_LOG_RESOURCE', columns: ['resource', 'resource_id'])]
#[ORM\HasLifecycleCallbacks]
class ActionLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: DoctrineStrategy::CURRENT)]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Auteur de l'action, null s'il a été supprimé depuis
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    /**
     * Utilisateur d'origine quand l'action est faite pendant une usurpation d'identité
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'impersonator_id', nullable: true, onDelete: 'SET NULL')]
    private ?User $impersonator = null;

    #[ORM\Column(length: 50)]
    private ?string $module = null;

    /**
     * Valeur de ActionLogAction ou action propre au module
     */
    #[ORM\Column(length: 50)]
    private ?string $action = null;

    /**
     * Ressource de l'API concernée (shortName)
     */
    #[ORM\Column(length: 100)]
    private ?string $resource = null;

    #[ORM\Column(name: 'resource_id', length: 50, nullable: true)]
    private ?string $resourceId = null;

    /**
     * Libellé de l'élément au moment de l'action
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

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

    public function getImpersonator(): ?User
    {
        return $this->impersonator;
    }

    public function setImpersonator(?User $impersonator): static
    {
        $this->impersonator = $impersonator;

        return $this;
    }

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function setModule(string $module): static
    {
        $this->module = $module;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function setResource(string $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    public function setResourceId(?string $resourceId): static
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $this->label = $label === null ? null : mb_substr($label, 0, 255);

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
}
