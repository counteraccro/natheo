<?php

declare(strict_types=1);

namespace App\Entity\Admin\Content\Page;

use App\Entity\Trait\TranslationByLocaleTrait;
use App\Enum\Installation\DoctrineStrategy;
use App\Repository\Admin\Content\Page\PageMetaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageMetaRepository::class)]
class PageMeta
{
    use TranslationByLocaleTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: DoctrineStrategy::CURRENT)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pageMetas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Page $page = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    /**
     * @var Collection<int, PageMetaTranslation>
     */
    #[
        ORM\OneToMany(
            targetEntity: PageMetaTranslation::class,
            mappedBy: 'pageMeta',
            cascade: ['persist', 'remove'],
            orphanRemoval: true,
        ),
    ]
    private Collection $pageMetaTranslations;

    public function __construct()
    {
        $this->pageMetaTranslations = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, PageMetaTranslation>
     */
    public function getPageMetaTranslations(): Collection
    {
        return $this->pageMetaTranslations;
    }

    /**
     * Retourne la traduction de $locale, à défaut celle de $defaultLocale, à défaut la première disponible
     * @param string $locale
     * @param string|null $defaultLocale
     * @return PageMetaTranslation|null
     */
    public function getPageMetaTranslationByLocale(string $locale, ?string $defaultLocale = null): ?PageMetaTranslation
    {
        return $this->findTranslationByLocale($this->getPageMetaTranslations(), $locale, $defaultLocale);
    }

    public function addPageMetaTranslation(PageMetaTranslation $pageMetaTranslation): static
    {
        if (!$this->pageMetaTranslations->contains($pageMetaTranslation)) {
            $this->pageMetaTranslations->add($pageMetaTranslation);
            $pageMetaTranslation->setPageMeta($this);
        }

        return $this;
    }

    public function removePageMetaTranslation(PageMetaTranslation $pageMetaTranslation): static
    {
        if ($this->pageMetaTranslations->removeElement($pageMetaTranslation)) {
            // set the owning side to null (unless already changed)
            if ($pageMetaTranslation->getPageMeta() === $this) {
                $pageMetaTranslation->setPageMeta(null);
            }
        }

        return $this;
    }
}
