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
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'faqs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $disabled = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'update_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updateAt = null;

    #[ORM\OneToMany(mappedBy: 'faq', targetEntity: FaqTranslation::class, orphanRemoval: true)]
    private Collection $faqTranslations;

    #[ORM\OneToMany(mappedBy: 'faq', targetEntity: FaqCategory::class, orphanRemoval: true)]
    private Collection $faqCategories;

    public function __construct()
    {
        $this->faqTranslations = new ArrayCollection();
        $this->faqCategories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUsers(?User $user): static
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeImmutable $updateAt): static
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
}
