<?php

namespace App\Entity\Admin\Content\Page;

use App\Repository\Admin\Content\Page\PageContentTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageContentTranslationRepository::class)]
#[ORM\Table(name: 'natheo.page_content_translation')]
class PageContentTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pageContentTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PageContent $pageContent = null;

    #[ORM\Column(length: 10)]
    private ?string $locale = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $texte = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPageContent(): ?PageContent
    {
        return $this->pageContent;
    }

    public function setPageContent(?PageContent $pageContent): static
    {
        $this->pageContent = $pageContent;

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

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(string $texte): static
    {
        $this->texte = $texte;

        return $this;
    }
}
