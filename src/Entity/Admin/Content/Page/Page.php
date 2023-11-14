<?php

namespace App\Entity\Admin\Content\Page;

use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\System\User;
use App\Repository\Admin\Content\Page\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Gedmo\Mapping\Annotation as Gedmo;


#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\Table(name: 'natheo.page')]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?int $render = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\Column]
    private ?bool $disabled = false;

    #[Gedmo\Timestampable(on: "create")]
    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[Gedmo\Timestampable(on: "update")]
    #[ORM\Column(name: 'update_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updateAt = null;

    #[ORM\OneToMany(mappedBy: 'page', targetEntity: PageTranslation::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $pageTranslations;

    #[ORM\OneToMany(mappedBy: 'page', targetEntity: PageContent::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(["renderBlock" => "ASC"])]
    private Collection $pageContents;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'pages')]
    #[JoinTable(name: 'natheo.page_tag')]
    private Collection $tags;

    #[ORM\OneToMany(mappedBy: 'page', targetEntity: PageStatistique::class, cascade: ['persist'] , orphanRemoval: true)]
    private Collection $pageStatistiques;

    public function __construct()
    {
        $this->pageTranslations = new ArrayCollection();
        $this->pageContents = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->pageStatistiques = new ArrayCollection();
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

    public function getRender(): ?int
    {
        return $this->render;
    }

    public function setRender(int $render): static
    {
        $this->render = $render;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, PageTranslation>
     */
    public function getPageTranslations(): Collection
    {
        return $this->pageTranslations;
    }

    public function addPageTranslation(PageTranslation $pageTranslation): static
    {
        if (!$this->pageTranslations->contains($pageTranslation)) {
            $this->pageTranslations->add($pageTranslation);
            $pageTranslation->setPage($this);
        }

        return $this;
    }

    public function removePageTranslation(PageTranslation $pageTranslation): static
    {
        if ($this->pageTranslations->removeElement($pageTranslation)) {
            // set the owning side to null (unless already changed)
            if ($pageTranslation->getPage() === $this) {
                $pageTranslation->setPage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PageContent>
     */
    public function getPageContents(): Collection
    {
        return $this->pageContents;
    }

    public function addPageContent(PageContent $pageContent): static
    {
        if (!$this->pageContents->contains($pageContent)) {
            $this->pageContents->add($pageContent);
            $pageContent->setPage($this);
        }

        return $this;
    }

    public function removePageContent(PageContent $pageContent): static
    {
        if ($this->pageContents->removeElement($pageContent)) {
            // set the owning side to null (unless already changed)
            if ($pageContent->getPage() === $this) {
                $pageContent->setPage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<int, PageStatistique>
     */
    public function getPageStatistiques(): Collection
    {
        return $this->pageStatistiques;
    }

    public function addPageStatistique(PageStatistique $pageStatistique): static
    {
        if (!$this->pageStatistiques->contains($pageStatistique)) {
            $this->pageStatistiques->add($pageStatistique);
            $pageStatistique->setPage($this);
        }

        return $this;
    }

    public function removePageStatistique(PageStatistique $pageStatistique): static
    {
        if ($this->pageStatistiques->removeElement($pageStatistique)) {
            // set the owning side to null (unless already changed)
            if ($pageStatistique->getPage() === $this) {
                $pageStatistique->setPage(null);
            }
        }

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

    /**
     * Retourne la traduction en fonction de la locale
     * @param string $locale
     * @return PageTranslation
     */
    public function getPageTranslationByLocale(string $locale): PageTranslation
    {
        return $this->getPageTranslations()->filter(function (PageTranslation $pageTranslation) use ($locale) {
            return $pageTranslation->getLocale() === $locale;
        })->first();
    }

    /**
     * @param string $key
     * @return PageStatistique;
     */
    public function getPageStatistiqueByKey(string $key): PageStatistique
    {
        return $this->getPageStatistiques()->filter(function (PageStatistique $pageStatistique) use ($key) {
            return $pageStatistique->getKey() === $key;
        })->first();
    }
}
