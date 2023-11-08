<?php

namespace App\Entity\Admin\Content\Page;

use App\Repository\Admin\Content\Page\PageContentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageContentRepository::class)]
#[ORM\Table(name: 'natheo.page_content')]
class PageContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pageContents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Page $page = null;

    #[ORM\Column]
    private ?int $renderBlock = null;

    #[ORM\Column(name: 'render_order')]
    private ?int $renderOrder = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\OneToMany(mappedBy: 'pageContent', targetEntity: PageContentTranslation::class,
        cascade: ['persist'] ,orphanRemoval: true)]
    private Collection $pageContentTranslations;

    #[ORM\Column(nullable: true)]
    private ?int $typeId = null;

    public function __construct()
    {
        $this->pageContentTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRenderOrder(): ?int
    {
        return $this->renderOrder;
    }

    public function setRenderOrder(int $renderOrder): static
    {
        $this->renderOrder = $renderOrder;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, PageContentTranslation>
     */
    public function getPageContentTranslations(): Collection
    {
        return $this->pageContentTranslations;
    }

    public function addPageContentTranslation(PageContentTranslation $pageContentTranslation): static
    {
        if (!$this->pageContentTranslations->contains($pageContentTranslation)) {
            $this->pageContentTranslations->add($pageContentTranslation);
            $pageContentTranslation->setPageContent($this);
        }

        return $this;
    }

    public function removePageContentTranslation(PageContentTranslation $pageContentTranslation): static
    {
        if ($this->pageContentTranslations->removeElement($pageContentTranslation)) {
            // set the owning side to null (unless already changed)
            if ($pageContentTranslation->getPageContent() === $this) {
                $pageContentTranslation->setPageContent(null);
            }
        }

        return $this;
    }

    public function getTypeId(): ?int
    {
        return $this->typeId;
    }

    public function setTypeId(?int $typeId): static
    {
        $this->typeId = $typeId;

        return $this;
    }

    public function getRenderBlock(): ?int
    {
        return $this->renderBlock;
    }

    public function setRenderBlock(int $renderBlock): static
    {
        $this->renderBlock = $renderBlock;

        return $this;
    }
}
