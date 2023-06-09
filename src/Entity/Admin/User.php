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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity('email')]
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
    private ?bool $disabled = false;

    #[ORM\Column]
    private ?bool $anonymous = false;

    #[ORM\Column]
    private ?bool $founder = false;

    #[Gedmo\Timestampable(on: "create")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[Gedmo\Timestampable(on: "update")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $update_at = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: OptionUser::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $optionsUser;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserData::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $userData;

    public function __construct()
    {
        $this->optionsUser = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->userData = new ArrayCollection();
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
     * @return void
     */
    public function eraseCredentials(): void
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
     * @param Collection $optionsUsers
     * @return $this
     */
    public function removeAllOptionsUser(Collection $optionsUsers): self
    {
        foreach ($optionsUsers as $optionsUser) {
            if ($this->optionsUser->removeElement($optionsUser)) {
                // set the owning side to null (unless already changed)
                if ($optionsUser->getUser() === $this) {
                    $optionsUser->setUser(null);
                }
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
        $format = 'l d F Y H:i:s';
        return match ($dateName) {
            'update' => $this->getUpdateAt()->format($format),
            'create' => $this->getCreatedAt()->format($format),
            default => "",
        };
    }

    public function isAnonymous(): ?bool
    {
        return $this->anonymous;
    }

    public function setAnonymous(bool $anonymous): self
    {
        $this->anonymous = $anonymous;

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    public function isFounder(): ?bool
    {
        return $this->founder;
    }

    public function setFounder(bool $founder): self
    {
        $this->founder = $founder;

        return $this;
    }

    /**
     * @return Collection<int, UserData>
     */
    public function getUserData(): Collection
    {
        return $this->userData;
    }

    public function addUserData(UserData $userData): self
    {
        if (!$this->userData->contains($userData)) {
            $this->userData->add($userData);
            $userData->setUser($this);
        }

        return $this;
    }

    public function removeUserData(UserData $userData): self
    {
        if ($this->userData->removeElement($userData)) {
            // set the owning side to null (unless already changed)
            if ($userData->getUser() === $this) {
                $userData->setUser(null);
            }
        }

        return $this;
    }

    /**
     * Retourne un UserData en fonction de sa clé
     * @param string $key
     * @return UserData|null
     */
    public function getUserDataByKey(string $key): ?UserData
    {
        $result = $this->getUserData()->filter(function (UserData $userData) use ($key) {
            return $userData->getKey() === $key;
        });
        if (!$result->first() instanceof UserData) {
            return null;
        }
        return $result->first();
    }
}
