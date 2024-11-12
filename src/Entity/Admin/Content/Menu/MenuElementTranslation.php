<?php

namespace App\Entity\Admin\Content\Menu;

use App\Entity\Admin\Content\Page\Page;
use App\Repository\Admin\Content\Menu\MenuElementTranslationRepository;
use App\Utils\Installation\InstallationConst;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuElementTranslationRepository::class)]
#[ORM\Table(name: 'menu_element_translation')]
class MenuElementTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: InstallationConst::STRATEGY)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'menuElementTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MenuElement $menuElement = null;

    #[ORM\Column(length: 10)]
    private ?string $locale = null;

    #[ORM\Column(length: 255)]
    private ?string $textLink = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $externalLink = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMenuElement(): ?MenuElement
    {
        return $this->menuElement;
    }

    public function setMenuElement(?MenuElement $menuElement): static
    {
        $this->menuElement = $menuElement;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getTextLink(): ?string
    {
        return $this->textLink;
    }

    public function setTextLink(string $textLink): static
    {
        $this->textLink = $textLink;

        return $this;
    }

    public function getExternalLink(): ?string
    {
        return $this->externalLink;
    }

    public function setExternalLink(?string $externalLink): static
    {
        $this->externalLink = $externalLink;

        return $this;
    }
}
