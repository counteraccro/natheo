<?php

declare(strict_types=1);

namespace App\Entity\Admin\Content\Media;

use App\Entity\Admin\System\User;
use App\Enum\Installation\DoctrineStrategy;
use App\Repository\Admin\Content\Media\MediaFolderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MediaFolderRepository::class)]
#[ORM\Table(name: 'media_folder')]
#[ORM\Index(name: 'IDX_MEDIA_FOLDER_TREE_PATH', columns: ['tree_path'])]
#[ORM\HasLifecycleCallbacks]
class MediaFolder
{
    public const string DEFAULT_ALIAS = 'mediaFolder';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: DoctrineStrategy::CURRENT)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $children;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    /**
     * Chemin des identifiants depuis la racine (/3/7/), pour lire un sous-arbre en une requête
     */
    #[ORM\Column(name: 'tree_path', length: 255)]
    private string $treePath = '/';

    #[ORM\Column(name: 'trashed_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $trashedAt = null;

    /**
     * Élément mis à la corbeille lui-même, et non avec son dossier parent
     */
    #[ORM\Column(name: 'trash_root')]
    private bool $trashRoot = false;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'update_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updateAt = null;

    #[ORM\OneToMany(mappedBy: 'mediaFolder', targetEntity: Media::class)]
    private Collection $medias;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->medias = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt ??= new \DateTime();
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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getTreePath(): string
    {
        return $this->treePath;
    }

    public function setTreePath(string $treePath): static
    {
        $this->treePath = $treePath;

        return $this;
    }

    /**
     * Chemin des identifiants calculé depuis le parent, une fois l'identifiant connu
     * @return string
     */
    public function buildTreePath(): string
    {
        return ($this->parent?->getTreePath() ?? '/') . $this->id . '/';
    }

    public function getTrashedAt(): ?\DateTimeInterface
    {
        return $this->trashedAt;
    }

    public function setTrashedAt(?\DateTimeInterface $trashedAt): static
    {
        $this->trashedAt = $trashedAt;

        return $this;
    }

    public function isTrashed(): bool
    {
        return $this->trashedAt !== null;
    }

    public function isTrashRoot(): bool
    {
        return $this->trashRoot;
    }

    public function setTrashRoot(bool $trashRoot): static
    {
        $this->trashRoot = $trashRoot;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(\DateTimeInterface $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }
}
