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

    #[ORM\Column]
    private ?int $renderOrder = null;

    #[ORM\Column]
    private ?bool $disabled = null;

    /**
     * @var Collection<int, MenuElementTranslation>
     */
    #[ORM\OneToMany(mappedBy: 'menuElement', targetEntity: MenuElementTranslation::class, orphanRemoval: true)]
    private Collection $menuElementTranslations;

    #[ORM\ManyToOne(inversedBy: 'menuElements')]
    private ?Page $page = null;

    public function __construct()
    {
        $this->menuElementTranslations = new ArrayCollection();
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

    public function getRenderOrder(): ?int
    {
        return $this->renderOrder;
    }

    public function setRenderOrder(int $renderOrder): static
    {
        $this->renderOrder = $renderOrder;

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

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;

        return $this;
    }
}
