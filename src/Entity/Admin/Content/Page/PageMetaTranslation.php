<?php

namespace App\Entity\Admin\Content\Page;

use App\Repository\Admin\Content\Page\PageMetaTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageMetaTranslationRepository::class)]
class PageMetaTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pageMetaTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PageMeta $pageMeta = null;

    #[ORM\Column(length: 10)]
    private ?string $locale = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPageMeta(): ?PageMeta
    {
        return $this->pageMeta;
    }

    public function setPageMeta(?PageMeta $pageMeta): static
    {
        $this->pageMeta = $pageMeta;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
