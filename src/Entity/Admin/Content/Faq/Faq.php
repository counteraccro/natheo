<?php

namespace App\Entity\Admin\Content\Faq;

use App\Entity\Admin\System\User;
use App\Repository\Admin\Content\Faq\FaqRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FaqRepository::class)]
#[ORM\Table(name: 'natheo.faq')]
#[ORM\HasLifecycleCallbacks]
class Faq
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'faqs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $disabled = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'update_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updateAt = null;

    #[ORM\OneToMany(mappedBy: 'faq', targetEntity: FaqTranslation::class,  cascade: ['persist'],  orphanRemoval: true)]
    private Collection $faqTranslations;

    #[ORM\OneToMany(mappedBy: 'faq', targetEntity: FaqCategory::class,  cascade: ['persist'],  orphanRemoval: true)]
    private Collection $faqCategories;

    #[ORM\OneToMany(mappedBy: 'faq', targetEntity: FaqStatistique::class, orphanRemoval: true)]
    private Collection $faqStatistiques;

    public function __construct()
    {
        $this->faqTranslations = new ArrayCollection();
        $this->faqCategories = new ArrayCollection();
        $this->faqStatistiques = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updateAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updateAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeInterface $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    /**
     * @return Collection<int, FaqTranslation>
     */
    public function getFaqTranslations(): Collection
    {
        return $this->faqTranslations;
    }

    public function addFaqTranslation(FaqTranslation $faqTranslation): static
    {
        if (!$this->faqTranslations->contains($faqTranslation)) {
            $this->faqTranslations->add($faqTranslation);
            $faqTranslation->setFaq($this);
        }

        return $this;
    }

    public function removeFaqTranslation(FaqTranslation $faqTranslation): static
    {
        if ($this->faqTranslations->removeElement($faqTranslation)) {
            // set the owning side to null (unless already changed)
            if ($faqTranslation->getFaq() === $this) {
                $faqTranslation->setFaq(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FaqCategory>
     */
    public function getFaqCategories(): Collection
    {
        return $this->faqCategories;
    }

    public function addFaqCategory(FaqCategory $faqCategory): static
    {
        if (!$this->faqCategories->contains($faqCategory)) {
            $this->faqCategories->add($faqCategory);
            $faqCategory->setFaq($this);
        }

        return $this;
    }

    public function removeFaqCategory(FaqCategory $faqCategory): static
    {
        if ($this->faqCategories->removeElement($faqCategory)) {
            // set the owning side to null (unless already changed)
            if ($faqCategory->getFaq() === $this) {
                $faqCategory->setFaq(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FaqStatistique>
     */
    public function getFaqStatistiques(): Collection
    {
        return $this->faqStatistiques;
    }

    public function addFaqStatistique(FaqStatistique $faqStatistique): static
    {
        if (!$this->faqStatistiques->contains($faqStatistique)) {
            $this->faqStatistiques->add($faqStatistique);
            $faqStatistique->setFaq($this);
        }

        return $this;
    }

    public function removeFaqStatistique(FaqStatistique $faqStatistique): static
    {
        if ($this->faqStatistiques->removeElement($faqStatistique)) {
            // set the owning side to null (unless already changed)
            if ($faqStatistique->getFaq() === $this) {
                $faqStatistique->setFaq(null);
            }
        }

        return $this;
    }
}
