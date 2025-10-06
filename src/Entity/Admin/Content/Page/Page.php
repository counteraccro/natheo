<?php

namespace App\Entity\Admin\Content\Page;

use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\System\User;
use App\Repository\Admin\Content\Page\PageRepository;
use App\Utils\Installation\InstallationConst;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\Table(name: 'page')]
#[ORM\HasLifecycleCallbacks]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: InstallationConst::STRATEGY)]
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

    #[ORM\Column]
    private ?int $category = null;

    #[ORM\Column]
    private ?bool $landingPage = null;

    #[ORM\Column]
    private ?bool $isOpenComment = null;

    #[ORM\Column]
    private ?int $nbComment = 0;

    #[ORM\Column]
    private ?int $ruleComment = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'update_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updateAt = null;

    #[ORM\OneToMany(targetEntity: PageTranslation::class, mappedBy: 'page', cascade: ['persist'], orphanRemoval: true)]
    private Collection $pageTranslations;

    #[ORM\OneToMany(targetEntity: PageContent::class, mappedBy: 'page', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['renderBlock' => 'ASC'])]
    private Collection $pageContents;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'pages')]
    #[JoinTable(name: 'page_tag')]
    private Collection $tags;

    #[ORM\OneToMany(targetEntity: PageStatistique::class, mappedBy: 'page', cascade: ['persist'], orphanRemoval: true)]
    private Collection $pageStatistiques;

    /**
     * @var Collection<int, MenuElement>
     */
    #[ORM\OneToMany(targetEntity: MenuElement::class, mappedBy: 'page')]
    private Collection $menuElements;

    /**
     * @var Collection<int, Menu>
     */
    #[ORM\ManyToMany(targetEntity: Menu::class, inversedBy: 'pages', cascade: ['persist', 'remove'])]
    #[JoinTable(name: 'page_menu')]
    private Collection $menus;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'page', orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var Collection<int, PageMeta>
     */
    #[
        ORM\OneToMany(
            targetEntity: PageMeta::class,
            mappedBy: 'page',
            cascade: ['persist', 'remove'],
            orphanRemoval: true,
        ),
    ]
    private Collection $pageMetas;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $headerImg = null;

    public function __construct()
    {
        $this->pageTranslations = new ArrayCollection();
        $this->pageContents = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->pageStatistiques = new ArrayCollection();
        $this->menuElements = new ArrayCollection();
        $this->menus = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->pageMetas = new ArrayCollection();
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
        return $this->getPageTranslations()
            ->filter(function (PageTranslation $pageTranslation) use ($locale) {
                return $pageTranslation->getLocale() === $locale;
            })
            ->first();
    }

    /**
     * @param string $key
     * @return PageStatistique;
     */
    public function getPageStatistiqueByKey(string $key): PageStatistique
    {
        return $this->getPageStatistiques()
            ->filter(function (PageStatistique $pageStatistique) use ($key) {
                return $pageStatistique->getKey() === $key;
            })
            ->first();
    }

    /**
     * @return Collection<int, MenuElement>
     */
    public function getMenuElements(): Collection
    {
        return $this->menuElements;
    }

    public function addMenuElement(MenuElement $menuElement): static
    {
        if (!$this->menuElements->contains($menuElement)) {
            $this->menuElements->add($menuElement);
            $menuElement->setPage($this);
        }

        return $this;
    }

    public function removeMenuElement(MenuElement $menuElement): static
    {
        if ($this->menuElements->removeElement($menuElement)) {
            // set the owning side to null (unless already changed)
            if ($menuElement->getPage() === $this) {
                $menuElement->setPage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Menu>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): static
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->addPage($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): static
    {
        if ($this->menus->removeElement($menu)) {
            $menu->removePage($this);
        }

        return $this;
    }

    public function getCategory(): ?int
    {
        return $this->category;
    }

    public function setCategory(int $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function isLandingPage(): ?bool
    {
        return $this->landingPage;
    }

    public function setLandingPage(bool $landingPage): static
    {
        $this->landingPage = $landingPage;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPage($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPage() === $this) {
                $comment->setPage(null);
            }
        }

        return $this;
    }

    public function isOpenComment(): ?bool
    {
        return $this->isOpenComment;
    }

    public function setIsOpenComment(bool $isOpenComment): static
    {
        $this->isOpenComment = $isOpenComment;

        return $this;
    }

    public function getNbComment(): ?int
    {
        return $this->nbComment;
    }

    public function setNbComment(int $nbComment): static
    {
        $this->nbComment = $nbComment;

        return $this;
    }

    public function getRuleComment(): ?int
    {
        return $this->ruleComment;
    }

    public function setRuleComment(int $ruleComment): static
    {
        $this->ruleComment = $ruleComment;

        return $this;
    }

    /**
     * @return Collection<int, PageMeta>
     */
    public function getPageMetas(): Collection
    {
        return $this->pageMetas;
    }

    public function addPageMeta(PageMeta $pageMeta): static
    {
        if (!$this->pageMetas->contains($pageMeta)) {
            $this->pageMetas->add($pageMeta);
            $pageMeta->setPage($this);
        }

        return $this;
    }

    public function removePageMeta(PageMeta $pageMeta): static
    {
        if ($this->pageMetas->removeElement($pageMeta)) {
            // set the owning side to null (unless already changed)
            if ($pageMeta->getPage() === $this) {
                $pageMeta->setPage(null);
            }
        }

        return $this;
    }

    public function getHeaderImg(): ?string
    {
        return $this->headerImg;
    }

    public function setHeaderImg(?string $headerImg): static
    {
        $this->headerImg = $headerImg;

        return $this;
    }
}
