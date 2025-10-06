<?php

namespace App\Entity\Admin\Content\Page;

use App\Repository\Admin\Content\Page\PageStatistiqueRepository;
use App\Utils\Installation\InstallationConst;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageStatistiqueRepository::class)]
#[ORM\Table(name: 'page_statistique')]
class PageStatistique
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: InstallationConst::STRATEGY)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pageStatistiques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Page $page = null;

    #[ORM\Column(name: '`key`', length: 255)]
    private ?string $key = null;

    #[ORM\Column(length: 255)]
    private ?string $value = null;

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

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
