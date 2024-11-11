<?php

namespace App\Entity\Admin\Content\Menu;

use App\Entity\Admin\Content\Page\Page;
use App\Repository\Admin\Content\Menu\MenuElementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuElementRepository::class)]
#[ORM\Table(name: 'menu_element')]
class MenuElement
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'menuElements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Menu $menu = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    private ?self $parent = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, cascade: ['remove', 'persist'])]
    #[ORM\OrderBy(['columnPosition' => 'ASC', 'rowPosition' => 'ASC'])]
    private Collection $children;

    /**
     * @var Collection<int, MenuElementTranslation>
     */
    #[ORM\OneToMany(mappedBy: 'menuElement', targetEntity: MenuElementTranslation::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $menuElementTranslations;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'menuElements')]
    private ?Page $page = null;

    #[ORM\Column]
    private ?int $columnPosition = null;

    #[ORM\Column]
    private ?int $rowPosition = null;

    #[ORM\Column(length: 100)]
    private ?string $linkTarget = null;

    #[ORM\Column]
    private ?bool $disabled = null;


    public function __construct()
    {
        $this->menuElementTranslations = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;

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
     * @return Collection<int, MenuElementTranslation>
     */
    public function getMenuElementTranslations(): Collection
    {
        return $this->menuElementTranslations;
    }

    public function addMenuElementTranslation(MenuElementTranslation $menuElementTranslation): static
    {
        if (!$this->menuElementTranslations->contains($menuElementTranslation)) {
            $this->menuElementTranslations->add($menuElementTranslation);
            $menuElementTranslation->setMenuElement($this);
        }

        return $this;
    }

    public function removeMenuElementTranslation(MenuElementTranslation $menuElementTranslation): static
    {
        if ($this->menuElementTranslations->removeElement($menuElementTranslation)) {
            // set the owning side to null (unless already changed)
            if ($menuElementTranslation->getMenuElement() === $this) {
                $menuElementTranslation->setMenuElement(null);
            }
        }

        return $this;
    }

    /**
     * Retourne la traduction en fonction de la locale
     * @param string $locale
     * @return MenuElementTranslation
     */
    public function getMenuElementTranslationByLocale(string $locale): MenuElementTranslation
    {
        return $this->getMenuElementTranslations()->filter(function (MenuElementTranslation $menuElementTranslation) use ($locale) {
            return $menuElementTranslation->getLocale() === $locale;
        })->first();
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): static
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getColumnPosition(): ?int
    {
        return $this->columnPosition;
    }

    public function setColumnPosition(int $columnPosition): static
    {
        $this->columnPosition = $columnPosition;

        return $this;
    }

    public function getRowPosition(): ?int
    {
        return $this->rowPosition;
    }

    public function setRowPosition(int $rowPosition): static
    {
        $this->rowPosition = $rowPosition;

        return $this;
    }

    public function getLinkTarget(): ?string
    {
        return $this->linkTarget;
    }

    public function setLinkTarget(string $linkTarget): static
    {
        $this->linkTarget = $linkTarget;

        return $this;
    }
}
