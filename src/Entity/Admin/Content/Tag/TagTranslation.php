<?php

namespace App\Entity\Admin\Content\Tag;

use App\Repository\Admin\Content\Tag\TagTranslationRepository;
use App\Utils\Installation\InstallationConst;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagTranslationRepository::class)]
#[ORM\Table(name: 'tag_translation')]
class TagTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: InstallationConst::STRATEGY)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tagTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tag $tag = null;

    #[ORM\Column(length: 10)]
    private ?string $locale = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
