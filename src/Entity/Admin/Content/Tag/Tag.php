<?php

declare(strict_types=1);

namespace App\Entity\Admin\Content\Tag;

use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\User;
use App\Entity\Trait\TranslationByLocaleTrait;
use App\Enum\Installation\DoctrineStrategy;
use App\Repository\Admin\Content\Tag\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'tag')]
#[ORM\HasLifecycleCallbacks]
class Tag
{
    use TranslationByLocaleTrait;

    public const string DEFAULT_ALIAS = 'tag';

    public const array ALLOWED_ORDER_FIELDS = ['id', 'color', 'createdAt', 'updateAt'];

    /**
     * Format attendu pour la couleur d'un tag (#rrggbb)
     * @var string
     */
    public const string COLOR_PATTERN = '/^#[0-9a-fA-F]{6}$/';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: DoctrineStrategy::CURRENT)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(length: 7)]
    #[Assert\NotNull(message: 'tag.error.color')]
    #[Assert\Regex(pattern: self::COLOR_PATTERN, message: 'tag.error.color')]
    private ?string $color = null;

    #[ORM\Column]
    private ?bool $disabled = false;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'update_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updateAt = null;

    #[ORM\OneToMany(mappedBy: 'tag', targetEntity: TagTranslation::class, cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Valid]
    private Collection $tagTranslations;

    #[ORM\ManyToMany(targetEntity: Page::class, mappedBy: 'tags')]
    private Collection $pages;

    public function __construct()
    {
        $this->tagTranslations = new ArrayCollection();
        $this->pages = new ArrayCollection();
    }

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function isDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

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

    /**
     * @return Collection<int, TagTranslation>
     */
    public function getTagTranslations(): Collection
    {
        return $this->tagTranslations;
    }

    public function addTagTranslation(TagTranslation $tagTranslation): self
    {
        if (!$this->tagTranslations->contains($tagTranslation)) {
            $this->tagTranslations->add($tagTranslation);
            $tagTranslation->setTag($this);
        }

        return $this;
    }

    public function removeTagTranslation(TagTranslation $tagTranslation): self
    {
        if ($this->tagTranslations->removeElement($tagTranslation)) {
            // set the owning side to null (unless already changed)
            if ($tagTranslation->getTag() === $this) {
                $tagTranslation->setTag(null);
            }
        }

        return $this;
    }

    /**
     * Retourne la traduction de $locale, à défaut celle de $defaultLocale, à défaut la première disponible
     * @param string $locale
     * @param string|null $defaultLocale
     * @return TagTranslation|null
     */
    public function getTagTranslationByLocale(string $locale, ?string $defaultLocale = null): ?TagTranslation
    {
        return $this->findTranslationByLocale($this->getTagTranslations(), $locale, $defaultLocale);
    }

    /**
     * @return Collection<int, Page>
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): static
    {
        if (!$this->pages->contains($page)) {
            $this->pages->add($page);
            $page->addTag($this);
        }

        return $this;
    }

    public function removePage(Page $page): static
    {
        if ($this->pages->removeElement($page)) {
            $page->removeTag($this);
        }

        return $this;
    }
}
