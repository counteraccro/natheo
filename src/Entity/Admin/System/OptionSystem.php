<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Entité Optionsystem, gestion des options globales du CMS
 */
namespace App\Entity\Admin\System;

use App\Repository\Admin\System\OptionSystemRepository;
use App\Utils\Installation\InstallationConst;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name : "option_system")]
#[ORM\Entity(repositoryClass: OptionSystemRepository::class)]
#[ORM\HasLifecycleCallbacks]
class OptionSystem
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: InstallationConst::STRATEGY)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name:'`key`', length: 255)]
    private ?string $key = null;

    #[ORM\Column(length: 65535)]
    private ?string $value = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'update_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updateAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updateAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updateAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(\DateTimeInterface $updateAt): self
    {
        $this->updateAt = $updateAt;

        return $this;
    }
}
