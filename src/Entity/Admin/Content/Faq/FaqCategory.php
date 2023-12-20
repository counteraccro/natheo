<?php

namespace App\Entity\Admin\Content\Faq;

use App\Repository\Admin\Content\Faq\FaqCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FaqCategoryRepository::class)]
#[ORM\Table(name: 'natheo.faq_category')]
class FaqCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'faqCategories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Faq $faq = null;

    #[ORM\Column]
    private ?bool $disabled = null;

    #[ORM\Column(name: 'render_order')]
    private ?int $renderOrder = null;

    #[ORM\OneToMany(mappedBy: 'faqCategory', targetEntity: FaqCategoryTranslation::class, orphanRemoval: true)]
    private Collection $faqCategoryTranslations;

    #[ORM\OneToMany(mappedBy: 'faqCategory', targetEntity: FaqQuestion::class, orphanRemoval: true)]
    private Collection $faqQuestions;

    public function __construct()
    {
        $this->faqCategoryTranslations = new ArrayCollection();
        $this->faqQuestions = new ArrayCollection();
    }

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
     * @return Collection<int, FaqCategoryTranslation>
     */
    public function getFaqCategoryTranslations(): Collection
    {
        return $this->faqCategoryTranslations;
    }

    public function addFaqCategoryTranslation(FaqCategoryTranslation $faqCategoryTranslation): static
    {
        if (!$this->faqCategoryTranslations->contains($faqCategoryTranslation)) {
            $this->faqCategoryTranslations->add($faqCategoryTranslation);
            $faqCategoryTranslation->setFaqCategory($this);
        }

        return $this;
    }

    public function removeFaqCategoryTranslation(FaqCategoryTranslation $faqCategoryTranslation): static
    {
        if ($this->faqCategoryTranslations->removeElement($faqCategoryTranslation)) {
            // set the owning side to null (unless already changed)
            if ($faqCategoryTranslation->getFaqCategory() === $this) {
                $faqCategoryTranslation->setFaqCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FaqQuestion>
     */
    public function getFaqQuestions(): Collection
    {
        return $this->faqQuestions;
    }

    public function addFaqQuestion(FaqQuestion $faqQuestion): static
    {
        if (!$this->faqQuestions->contains($faqQuestion)) {
            $this->faqQuestions->add($faqQuestion);
            $faqQuestion->setFaqCategory($this);
        }

        return $this;
    }

    public function removeFaqQuestion(FaqQuestion $faqQuestion): static
    {
        if ($this->faqQuestions->removeElement($faqQuestion)) {
            // set the owning side to null (unless already changed)
            if ($faqQuestion->getFaqCategory() === $this) {
                $faqQuestion->setFaqCategory(null);
            }
        }

        return $this;
    }
}