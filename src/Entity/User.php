<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
//use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Username cannot be empty.")]
    #[Assert\Length(
        min: 3,
        max: 30,
        minMessage: "Username must be at least {{ limit }} characters long.",
        maxMessage: "Username cannot be longer than {{ limit }} characters."
    )]
    private ?string $username = null;

    #[ORM\Column(length: 40)]
    #[Assert\NotBlank(message: "Last name cannot be empty.")]
    #[Assert\Length(
        min: 2,
        max: 40,
        minMessage: "Last name must be at least {{ limit }} characters long.",
        maxMessage: "Last name cannot be longer than {{ limit }} characters."
    )]
    private ?string $lastName = null;

    #[ORM\Column(length: 40)]
    #[Assert\NotBlank(message: "First name cannot be empty.")]
    #[Assert\Length(
        min: 2,
        max: 40,
        minMessage: "First name must be at least {{ limit }} characters long.",
        maxMessage: "First name cannot be longer than {{ limit }} characters."
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    #[Assert\Email(message: "The email '{{ value }}' is not a valid email.")]
    #[Assert\NotBlank(message: "Email cannot be empty.")]
    private ?string $email = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Assert\Length(
        max: 15,
        maxMessage: "Phone number cannot be longer than {{ limit }} characters."
    )]
    #[Assert\Regex(
        '/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/',
        message: "La valeur {{ value }} n'est pas un numéro de téléphone valide.")]
    private ?string $phone = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Password cannot be empty.")]
    #[Assert\Length(
        min: 6,
        minMessage: "Password must be at least {{ limit }} characters long."
    )]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $isAdmin = false;

    #[ORM\Column]
    private ?bool $isActive = false;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Site $site = null;

    #[ORM\OneToMany(mappedBy: 'organizer', targetEntity: Event::class, orphanRemoval: true)]
    private Collection $organizedEvents;

    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participants')]
    private Collection $events;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isVerified = false;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $verificationToken = null;

    // #[ORM\Column(type: 'datetime', nullable: true)]
    // private ?\DateTimeInterface $verificationTokenExpiresAt = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $profilePicture = null;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: "followers")]
    #[ORM\JoinTable(name: "user_follows")]
    private Collection $following;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: "following")]
    private Collection $followers;

    #[ORM\ManyToMany(targetEntity: Group::class, mappedBy: 'users')]
    private Collection $groups;


    public function __construct()
    {
        $this->organizedEvents = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getIsAdmin(): ?bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */

    public function getOrganizedEvents(): Collection
    {
        return $this->organizedEvents;
    }
    public function addOrganizedEvent(Event $organizedEvent): self
    {
        if (!$this->organizedEvents->contains($organizedEvent)) {
            $this->organizedEvents->add($organizedEvent);
            $organizedEvent->setOrganizer($this);
        }

        return $this;
    }

    public function removeOrganizedEvent(Event $organizedEvent): self
    {
        if ($this->organizedEvents->removeElement($organizedEvent)) {
            if ($organizedEvent->getOrganizer() === $this) {
                $organizedEvent->setOrganizer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->addParticipant($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            $event->removeParticipant($this);
        }

        return $this;
    }

    public function getRoles(): array
    {
        return $this->isAdmin ? ['ROLE_ADMIN'] : ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // Cette méthode est utilisée pour effacer les informations sensibles temporaires
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(?string $verificationToken): self
    {
        $this->verificationToken = $verificationToken;

        return $this;
    }

    public function hashPassword(UserInterface $user, string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function isPasswordValid(UserInterface $user, string $raw): bool
    {
        return password_verify($raw, $user->getPassword());
    }

    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }

    public function setAdmin(bool $isAdmin): static
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): self
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    public function follow(User $user): self
    {
        if (!$this->following->contains($user)) {
            $this->following->add($user);
            $user->addFollower($this);
        }
    
        return $this;
    }
    
    public function unfollow(User $user): self
    {
        if ($this->following->contains($user)) {
            $this->following->removeElement($user);
            $user->removeFollower($this);
        }
    
        return $this;
    }
    
    public function getFollowing(): Collection
    {
        return $this->following;
    }
    
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(User $user): self
    {
        if (!$this->followers->contains($user)) {
            $this->followers->add($user);
        }

        return $this;
    }

    public function removeFollower(User $user): self
    {
        if ($this->followers->contains($user)) {
            $this->followers->removeElement($user);
        }

        return $this;
    }

    
    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->addUser($this);
        }

        return $this;
    }

    public function removeGroup(Group $group): self
    {
        if ($this->groups->removeElement($group)) {
            $group->removeUser($this);
        }

        return $this;
    }
}
