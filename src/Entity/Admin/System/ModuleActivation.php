<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module ou opération d'API du back-office désactivé ; tout élément sans ligne est activé
 */

namespace App\Entity\Admin\System;

use App\Enum\Admin\Module\ActivationType;
use App\Enum\Installation\DoctrineStrategy;
use App\Repository\Admin\System\ModuleActivationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModuleActivationRepository::class)]
#[ORM\Table(name: 'module_activation')]
#[ORM\UniqueConstraint(name: 'UNIQ_MODULE_ACTIVATION_TYPE_NAME', columns: ['type', 'name'])]
#[ORM\HasLifecycleCallbacks]
class ModuleActivation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: DoctrineStrategy::CURRENT)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, enumType: ActivationType::class)]
    private ?ActivationType $type = null;

    /**
     * Identifiant du module ou nom de l'opération
     */
    #[ORM\Column(length: 150)]
    private ?string $name = null;

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

    public function getType(): ?ActivationType
    {
        return $this->type;
    }

    public function setType(ActivationType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
