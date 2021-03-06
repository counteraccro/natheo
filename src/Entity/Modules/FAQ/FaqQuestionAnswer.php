<?php

namespace App\Entity\Modules\FAQ;

use App\Repository\Modules\FAQ\FaqQuestionAnswerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FaqQuestionAnswerRepository::class)
 * @ORM\Table(name="`cms_faq_question_answer`")
 */
class FaqQuestionAnswer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=FaqCategory::class, inversedBy="faqQuestionAnswers", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $faqCategory;

    /**
     * @ORM\OneToMany(targetEntity=FaqQuestionAnswerTranslation::class, mappedBy="FaqQuestionAnswer", orphanRemoval=true, cascade={"persist"})
     */
    private $faqQuestionAnswerTranslations;

    /**
     * @ORM\Column(type="datetime")
     */
    private $create_on;

    /**
     * @ORM\OneToMany(targetEntity=FaqQuestionAnswerTag::class, mappedBy="FaqQuestionAnswer", orphanRemoval=true, cascade={"persist"})
     */
    private $faqQuestionAnswerTags;

    public function __construct()
    {
        $this->faqQuestionAnswerTranslations = new ArrayCollection();
        $this->faqQuestionAnswerTags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getFaqCategory(): ?FaqCategory
    {
        return $this->faqCategory;
    }

    public function setFaqCategory(?FaqCategory $faqCategory): self
    {
        $this->faqCategory = $faqCategory;

        return $this;
    }

    /**
     * @return Collection|FaqQuestionAnswerTranslation[]
     */
    public function getFaqQuestionAnswerTranslations(): Collection
    {
        return $this->faqQuestionAnswerTranslations;
    }

    public function addFaqQuestionAnswerTranslation(FaqQuestionAnswerTranslation $faqQuestionAnswerTranslation): self
    {
        if (!$this->faqQuestionAnswerTranslations->contains($faqQuestionAnswerTranslation)) {
            $this->faqQuestionAnswerTranslations[] = $faqQuestionAnswerTranslation;
            $faqQuestionAnswerTranslation->setFaqQuestionAnswer($this);
        }

        return $this;
    }

    public function removeFaqQuestionAnswerTranslation(FaqQuestionAnswerTranslation $faqQuestionAnswerTranslation): self
    {
        if ($this->faqQuestionAnswerTranslations->removeElement($faqQuestionAnswerTranslation)) {
            // set the owning side to null (unless already changed)
            if ($faqQuestionAnswerTranslation->getFaqQuestionAnswer() === $this) {
                $faqQuestionAnswerTranslation->setFaqQuestionAnswer(null);
            }
        }

        return $this;
    }

    public function getCreateOn(): ?\DateTimeInterface
    {
        return $this->create_on;
    }

    public function setCreateOn(\DateTimeInterface $create_on): self
    {
        $this->create_on = $create_on;

        return $this;
    }

    /**
     * @return Collection|FaqQuestionAnswerTag[]
     */
    public function getFaqQuestionAnswerTags(): Collection
    {
        return $this->faqQuestionAnswerTags;
    }

    public function addFaqQuestionAnswerTag(FaqQuestionAnswerTag $faqQuestionAnswerTag): self
    {
        if (!$this->faqQuestionAnswerTags->contains($faqQuestionAnswerTag)) {
            $this->faqQuestionAnswerTags[] = $faqQuestionAnswerTag;
            $faqQuestionAnswerTag->setFaqQuestionAnswer($this);
        }

        return $this;
    }

    public function removeFaqQuestionAnswerTag(FaqQuestionAnswerTag $faqQuestionAnswerTag): self
    {
        if ($this->faqQuestionAnswerTags->removeElement($faqQuestionAnswerTag)) {
            // set the owning side to null (unless already changed)
            if ($faqQuestionAnswerTag->getFaqQuestionAnswer() === $this) {
                $faqQuestionAnswerTag->setFaqQuestionAnswer(null);
            }
        }

        return $this;
    }
}
