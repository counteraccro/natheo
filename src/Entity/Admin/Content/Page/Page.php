<?php

namespace App\Entity\Admin\Content\Page;

use App\Entity\Admin\System\User;
use App\Repository\Admin\Content\Page\PageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\Table(name: 'natheo.page')]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?int $render = null;

    #[ORM\Column]
    private ?int $status = null;

    #[Gedmo\Timestampable(on: "create")]
    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: "update")]
    #[ORM\Column(name: 'update_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updateAt = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsers(): ?User
    {
        return $this->user;
    }

    public function setUsers(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getRender(): ?int
    {
        return $this->render;
    }

    public function setRender(int $render): static
    {
        $this->render = $render;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeImmutable $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }
}
