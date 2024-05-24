<?php

namespace App\Entity\Admin\Content\Faq;

use App\Repository\Admin\Content\Faq\FaqCategoryTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FaqCategoryTranslationRepository::class)]
#[ORM\Table(name: 'faq_category_translation')]
class FaqCategoryTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'faqCategoryTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FaqCategory $faqCategory = null;

    #[ORM\Column(length: 10)]
    private ?string $locale = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $title = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFaqCategory(): ?FaqCategory
    {
        return $this->faqCategory;
    }

    public function setFaqCategory(?FaqCategory $faqCategory): static
    {
        $this->faqCategory = $faqCategory;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
