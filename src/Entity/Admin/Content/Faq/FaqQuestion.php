<?php

namespace App\Entity\Admin\Content\Faq;

use App\Repository\Admin\Content\Faq\FaqQuestionRepository;
use App\Utils\Installation\InstallationConst;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FaqQuestionRepository::class)]
#[ORM\Table(name: 'faq_question')]
class FaqQuestion
{
    public const string DEFAULT_ALIAS = 'faq_question';
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: InstallationConst::STRATEGY)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'faqQuestions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FaqCategory $faqCategory = null;

    #[ORM\Column]
    private ?bool $disabled = null;

    #[ORM\Column(name: 'render_order')]
    private ?int $renderOrder = null;

    #[
        ORM\OneToMany(
            mappedBy: 'FaqQuestion',
            targetEntity: FaqQuestionTranslation::class,
            cascade: ['persist'],
            orphanRemoval: true,
        ),
    ]
    private Collection $faqQuestionTranslations;

    public function __construct()
    {
        $this->faqQuestionTranslations = new ArrayCollection();
    }

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

    public function isDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function getRenderOrder(): ?int
    {
        return $this->renderOrder;
    }

    public function setRenderOrder(int $renderOrder): static
    {
        $this->renderOrder = $renderOrder;

        return $this;
    }

    /**
     * @return Collection<int, FaqQuestionTranslation>
     */
    public function getFaqQuestionTranslations(): Collection
    {
        return $this->faqQuestionTranslations;
    }

    public function addFaqQuestionTranslation(FaqQuestionTranslation $faqQuestionTranslation): static
    {
        if (!$this->faqQuestionTranslations->contains($faqQuestionTranslation)) {
            $this->faqQuestionTranslations->add($faqQuestionTranslation);
            $faqQuestionTranslation->setFaqQuestion($this);
        }

        return $this;
    }

    public function removeFaqQuestionTranslation(FaqQuestionTranslation $faqQuestionTranslation): static
    {
        if ($this->faqQuestionTranslations->removeElement($faqQuestionTranslation)) {
            // set the owning side to null (unless already changed)
            if ($faqQuestionTranslation->getFaqQuestion() === $this) {
                $faqQuestionTranslation->setFaqQuestion(null);
            }
        }

        return $this;
    }

    /**
     * Retourne la traduction en fonction de la locale
     * @param string $locale
     * @return FaqQuestionTranslation
     */
    public function getFaqQuestionTranslationByLocale(string $locale): FaqQuestionTranslation
    {
        return $this->faqQuestionTranslations
            ->filter(function (FaqQuestionTranslation $faqQuestionTranslation) use ($locale) {
                return $faqQuestionTranslation->getLocale() === $locale;
            })
            ->first();
    }
}
