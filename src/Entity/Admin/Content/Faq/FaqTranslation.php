<?php

namespace App\Entity\Admin\Content\Faq;

use App\Repository\Admin\Content\Faq\FaqTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FaqTranslationRepository::class)]
#[ORM\Table(name: 'faq_translation')]
class FaqTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'faqTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Faq $faq = null;

    #[ORM\Column(length: 10)]
    private ?string $locale = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $title = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFaq(): ?Faq
    {
        return $this->faq;
    }

    public function setFaq(?Faq $faq): static
    {
        $this->faq = $faq;

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
