<?php

namespace App\Entity\Admin;

use App\Repository\Admin\RouteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RouteRepository::class)
 * @ORM\Table(name="`cms_route`")
 */
class Route
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $route;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $module;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_depreciate;

    /**
     * @ORM\OneToMany(targetEntity=RouteRight::class, mappedBy="route", orphanRemoval=true)
     */
    private $routeRights;

    public function __construct()
    {
        $this->routeRights = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function setModule(string $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getIsDepreciate(): ?bool
    {
        return $this->is_depreciate;
    }

    public function setIsDepreciate(bool $is_depreciate): self
    {
        $this->is_depreciate = $is_depreciate;

        return $this;
    }

    /**
     * @return Collection|RouteRight[]
     */
    public function getRouteRights(): Collection
    {
        return $this->routeRights;
    }

    public function addRouteRight(RouteRight $routeRight): self
    {
        if (!$this->routeRights->contains($routeRight)) {
            $this->routeRights[] = $routeRight;
            $routeRight->setRoute($this);
        }

        return $this;
    }

    public function removeRouteRight(RouteRight $routeRight): self
    {
        if ($this->routeRights->removeElement($routeRight)) {
            // set the owning side to null (unless already changed)
            if ($routeRight->getRoute() === $this) {
                $routeRight->setRoute(null);
            }
        }

        return $this;
    }
}
