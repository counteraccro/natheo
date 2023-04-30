<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Entité User, compte pour accéder à l'administration
 */

namespace App\Entity\Admin;

use App\Repository\Admin\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'natheo.user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $login = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column]
    private ?bool $disabled = null;

    #[Gedmo\Timestampable(on: "create")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[Gedmo\Timestampable(on: "update")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $update_at = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: OptionUser::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $optionsUser;

    public function __construct()
    {
        $this->optionsUser = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(?string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function isDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->update_at;
    }

    public function setUpdateAt(?\DateTimeInterface $update_at): self
    {
        $this->update_at = $update_at;

        return $this;
    }

    /**
     * @return Collection<int, OptionUser>
     */
    public function getOptionsUser(): Collection
    {
        return $this->optionsUser;
    }

    public function addOptionsUser(OptionUser $optionsUser): self
    {
        if (!$this->optionsUser->contains($optionsUser)) {
            $this->optionsUser->add($optionsUser);
            $optionsUser->setUser($this);
        }

        return $this;
    }

    public function removeOptionsUser(OptionUser $optionsUser): self
    {
        if ($this->optionsUser->removeElement($optionsUser)) {
            // set the owning side to null (unless already changed)
            if ($optionsUser->getUser() === $this) {
                $optionsUser->setUser(null);
            }
        }

        return $this;
    }

    /**
     * Retourne une date formatée en string
     * @param string $dateName - valeur update ou create
     * @return string
     */
    public function getFormatDate(string $dateName = 'update'): string
    {
        return match ($dateName) {
            'update' => $this->getUpdateAt()->format('l d F Y H:i:s'),
            'create' => $this->getCreatedAt()->format('l d F Y H:i:s'),
            default => "",
        };
    }
}
