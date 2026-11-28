<?php

declare(strict_types=1);

namespace App\Entity\Admin\Content\Media;

use App\Entity\Admin\System\User;
use App\Entity\Trait\TranslationByLocaleTrait;
use App\Enum\Admin\Content\Media\MediaType;
use App\Enum\Installation\DoctrineStrategy;
use App\Repository\Admin\Content\Media\MediaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\Table(name: 'media')]
#[ORM\Index(name: 'IDX_MEDIA_HASH', columns: ['hash'])]
#[ORM\HasLifecycleCallbacks]
class Media
{
    use TranslationByLocaleTrait;

    public const string DEFAULT_ALIAS = 'media';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: DoctrineStrategy::CURRENT)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'medias')]
    private ?MediaFolder $mediaFolder = null;

    #[ORM\ManyToOne(inversedBy: 'medias')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?User $user = null;

    /**
     * Nom du fichier d'origine
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 20, enumType: MediaType::class)]
    private ?MediaType $type = null;

    #[ORM\Column(length: 20)]
    private ?string $extension = null;

    #[ORM\Column(name: 'mime_type', length: 100)]
    private ?string $mimeType = null;

    /**
     * Poids en octets
     */
    #[ORM\Column]
    private ?int $size = null;

    #[ORM\Column(nullable: true)]
    private ?int $width = null;

    #[ORM\Column(nullable: true)]
    private ?int $height = null;

    /**
     * Durée d'une vidéo, en secondes
     */
    #[ORM\Column(nullable: true)]
    private ?int $duration = null;

    /**
     * Clé du fichier dans le stockage
     */
    #[ORM\Column(length: 255, unique: true)]
    private ?string $path = null;

    /**
     * SHA-256 du fichier envoyé
     */
    #[ORM\Column(length: 64)]
    private ?string $hash = null;

    /**
     * Miniature d'une image ou couverture d'une vidéo présente dans le stockage
     */
    #[ORM\Column(name: 'has_thumbnail')]
    private bool $hasThumbnail = false;

    #[ORM\Column(name: 'trashed_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $trashedAt = null;

    /**
     * Élément mis à la corbeille lui-même, et non avec son dossier
     */
    #[ORM\Column(name: 'trash_root')]
    private bool $trashRoot = false;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'update_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updateAt = null;

    #[
        ORM\OneToMany(
            mappedBy: 'media',
            targetEntity: MediaTranslation::class,
            cascade: ['persist'],
            orphanRemoval: true,
        ),
    ]
    private Collection $mediaTranslations;

    public function __construct()
    {
        $this->mediaTranslations = new ArrayCollection();
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

    public function getMediaFolder(): ?MediaFolder
    {
        return $this->mediaFolder;
    }

    public function setMediaFolder(?MediaFolder $mediaFolder): static
    {
        $this->mediaFolder = $mediaFolder;

        return $this;
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

    public function getType(): ?MediaType
    {
        return $this->type;
    }

    public function setType(MediaType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): static
    {
        $this->extension = $extension;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): static
    {
        $this->hash = $hash;

        return $this;
    }

    public function hasThumbnail(): bool
    {
        return $this->hasThumbnail;
    }

    public function setHasThumbnail(bool $hasThumbnail): static
    {
        $this->hasThumbnail = $hasThumbnail;

        return $this;
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
     * @return Collection<int, MediaTranslation>
     */
    public function getMediaTranslations(): Collection
    {
        return $this->mediaTranslations;
    }

    public function addMediaTranslation(MediaTranslation $mediaTranslation): static
    {
        if (!$this->mediaTranslations->contains($mediaTranslation)) {
            $this->mediaTranslations->add($mediaTranslation);
            $mediaTranslation->setMedia($this);
        }

        return $this;
    }

    public function removeMediaTranslation(MediaTranslation $mediaTranslation): static
    {
        $this->mediaTranslations->removeElement($mediaTranslation);

        return $this;
    }

    /**
     * Retourne la traduction de $locale, à défaut celle de $defaultLocale, à défaut la première disponible
     * @param string $locale
     * @param string|null $defaultLocale
     * @return MediaTranslation|null
     */
    public function getMediaTranslationByLocale(string $locale, ?string $defaultLocale = null): ?MediaTranslation
    {
        return $this->findTranslationByLocale($this->getMediaTranslations(), $locale, $defaultLocale);
    }
}
