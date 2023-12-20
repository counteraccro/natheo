<?php

namespace App\Entity\Admin\Content\Faq;

use App\Repository\Admin\Content\Faq\FaqQuestionTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FaqQuestionTranslationRepository::class)]
#[ORM\Table(name: 'natheo.faq_question_translation')]
class FaqQuestionTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'faqQuestionTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FaqQuestion $FaqQuestion = null;

    #[ORM\Column(length: 10)]
    private ?string $locale = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $answer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFaqQuestion(): ?FaqQuestion
    {
        return $this->FaqQuestion;
    }

    public function setFaqQuestion(?FaqQuestion $FaqQuestion): static
    {
        $this->FaqQuestion = $FaqQuestion;

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

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): static
    {
        $this->answer = $answer;

        return $this;
    }
}
