<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['event_list'])]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    #[Assert\NotBlank(message: "L'évènement doit avoir un nom.")]
    #[Groups(['event_list'])]
    private ?string $name = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: "L'évènement doit avoir une date de début.")]
    #[Assert\GreaterThanOrEqual('now', message: "La date de début doit être une date future.")]
    #[Assert\GreaterThan(propertyPath: 'registrationEndDate',
        message: "La date de début est forcément ultérieure à la date de fermeture de l'inscription.")]
    #[Groups(['event_list'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(notInRangeMessage: "L'évènement doit duré au minimum 30 minutes et au maximum 10 heures.",
        min: 30, max: 600)]
    #[Groups(['event_list'])]
    private ?int $duration = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: "L'évènement doit avoir une date de fermeture d'inscription.")]
    #[Assert\GreaterThanOrEqual('now', groups: ['Default'], message: "La date de fermeture de l'inscription doit être une date future.")]
    #[Assert\LessThan(propertyPath: 'startDate',
        message: "La date de fermeture de l'inscription est forcément antérieure à la date de début de l'inscription.")]
    #[Groups(['event_list'])]
    private ?\DateTimeInterface $registrationEndDate = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "L'évènement doit avoir un nombre de participant maximum.")]
    #[Assert\Positive(message: "Le nombre de participant maximum ne peut pas pas être négatif.")]
    #[Assert\Range(notInRangeMessage: "Le nombre de participant maximum ne peut dépassé 300 participants.",
        min: 1, max: 300)]
    #[Groups(['event_list'])]
    private ?int $maxRegistrations = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['event_list'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['event_list'])]
    private ?Location $location = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['event_list'])]
    private ?State $state = null;

    #[ORM\ManyToOne(inversedBy: 'organizedEvents')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['event_list'])]
    private ?User $organizer = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'events')]
    #[Groups(['event_list'])]
    private Collection $participants;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['event_list'])]
    private ?Site $organizerSite = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['event_list'])]
    private ?string $cancellationMotive = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getRegistrationEndDate(): ?\DateTimeInterface
    {
        return $this->registrationEndDate;
    }

    public function setRegistrationEndDate(\DateTimeInterface $registrationEndDate): self
    {
        $this->registrationEndDate = $registrationEndDate;

        return $this;
    }

    public function getMaxRegistrations(): ?int
    {
        return $this->maxRegistrations;
    }

    public function setMaxRegistrations(int $maxRegistrations): self
    {
        $this->maxRegistrations = $maxRegistrations;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): self
    {
        $this->organizer = $organizer;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */

    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): self
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getOrganizerSite(): ?Site
    {
        return $this->organizerSite;
    }

    public function setOrganizerSite(?Site $organizerSite): self
    {
        $this->organizerSite = $organizerSite;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getCancellationMotive(): ?string
    {
        return $this->cancellationMotive;
    }

    public function setCancellationMotive(?string $cancellationMotive): static
    {
        $this->cancellationMotive = $cancellationMotive;

        return $this;
    }
}
