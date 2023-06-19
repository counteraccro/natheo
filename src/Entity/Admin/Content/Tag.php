<?php

namespace App\Entity\Admin\Content;

use App\Repository\Admin\Content\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'natheo.tag')]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 7)]
    private ?string $color = null;

    #[ORM\Column]
    private ?bool $disabled = false;

    #[Gedmo\Timestampable(on: "create")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[Gedmo\Timestampable(on: "update")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $update_at = null;

    #[ORM\OneToMany(mappedBy: 'tag', targetEntity: TagTranslation::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $tagTranslations;

    public function __construct()
    {
        $this->tagTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->update_at;
    }

    public function setUpdateAt(\DateTimeInterface $update_at): self
    {
        $this->update_at = $update_at;

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
}
