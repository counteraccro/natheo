<?php

namespace App\Entity\Admin\System;

use App\Repository\Admin\System\MailRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


#[ORM\Table(name : "natheo.mail")]
#[ORM\Entity(repositoryClass: MailRepository::class)]
class Mail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $key = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $keyWords = null;

    #[Gedmo\Timestampable(on : "create")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[Gedmo\Timestampable(on : "update")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $update_at = null;

    #[ORM\OneToMany(mappedBy: 'mail', targetEntity: MailTranslation::class, cascade: ["persist"], orphanRemoval: true)]
    private Collection $mailTranslations;

    public function __construct()
    {
        $this->mailTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeyWords(): ?string
    {
        return $this->keyWords;
    }

    public function setKeyWords(string $keyWords): self
    {
        $this->keyWords = $keyWords;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->created_at = $createdAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->update_at;
    }

    public function setUpdateAt(\DateTimeInterface $updateAt): self
    {
        $this->update_at = $updateAt;

        return $this;
    }

    /**
     * @return Collection<int, MailTranslation>
     */
    public function getMailTranslations(): Collection
    {
        return $this->mailTranslations;
    }

    public function addMailTranslation(MailTranslation $mailTranslation): self
    {
        if (!$this->mailTranslations->contains($mailTranslation)) {
            $this->mailTranslations->add($mailTranslation);
            $mailTranslation->setMail($this);
        }

        return $this;
    }

    public function removeMailTranslation(MailTranslation $mailTranslation): self
    {
        if ($this->mailTranslations->removeElement($mailTranslation)) {
            // set the owning side to null (unless already changed)
            if ($mailTranslation->getMail() === $this) {
                $mailTranslation->setMail(null);
            }
        }

        return $this;
    }

    /**
     * Retourne la traduction en fonction de la locale
     * @param string $locale
     * @return MailTranslation
     */
    public function geMailTranslationByLocale(string $locale): MailTranslation
    {
        return $this->getMailTranslations()->filter(function (MailTranslation $mailTranslation) use ($locale) {
            return $mailTranslation->getLocale() === $locale;
        })->first();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }
}