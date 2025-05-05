<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Enum\Role;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Index;


#[ORM\Entity]
#[UniqueEntity(fields: ['email'], message: 'Il existe déjà un compte avec cet email.')]
#[ORM\Index(name: "idx_user_role", columns: ["role"])]
#[ORM\Table(name: "user")]
#[ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    private string $nom;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prénom ne peut pas être vide.")]
    private string $prenom;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "L'email ne peut pas être vide.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas un email valide.")]
    private string $email;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le mot de passe ne peut pas être vide.")]
    #[Assert\Length(
        min: 8,
        max: 255,
        minMessage: "Votre mot de passe doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Votre mot de passe ne peut pas dépasser {{ limit }} caractères."
    )]
    private string $password;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(type: "string", enumType: Role::class)]
    private Role $role = Role::PATIENT;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Medecin::class, cascade: ['persist', 'remove'])]
    private ?Medecin $medecin = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Pharmacie::class, cascade: ['persist', 'remove'])]
    private ?Pharmacie $pharmacie = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Infermier::class, cascade: ['persist', 'remove'])]
    private ?Infermier $infermier = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Patient::class, cascade: ['persist', 'remove'])]
    private ?Patient $patient = null;
    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Message::class)]
    private Collection $sentMessages;

    #[ORM\OneToMany(mappedBy: 'receiver', targetEntity: Message::class)]
    private Collection $receivedMessages;

    #[ORM\OneToOne(mappedBy: "user", targetEntity: FirstTime::class, cascade: ["persist"])]
    private ?FirstTime $firstTime = null;

    public function __construct()
    {
        $this->sentMessages = new ArrayCollection();
        $this->receivedMessages = new ArrayCollection();
    }
    public function getRoles(): array
    {
        return ['ROLE_' . strtoupper($this->role->value)];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): static
    {
        $this->numero = $numero;
        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(Role $role): static
    {
        $this->role = $role;
        return $this;
    }
    public function getFirstTime(): ?FirstTime
    {
        return $this->firstTime;
    }

    public function setFirstTime(?FirstTime $firstTime): self
    {
        // unset the owning side of the relation if necessary
        if ($firstTime === null && $this->firstTime !== null) {
            $this->firstTime->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($firstTime !== null && $firstTime->getUser() !== $this) {
            $firstTime->setUser($this);
        }

        $this->firstTime = $firstTime;
        return $this;
    }
    public function getSentMessages(): Collection { return $this->sentMessages; }
    public function getReceivedMessages(): Collection { return $this->receivedMessages; }

    public function getMedecin(): ?Medecin
    {
        return $this->medecin;
    }
    public function setMedecin(?Medecin $medecin): static
    {
        // unset the owning side of the relation if necessary
        if ($medecin === null && $this->medecin !== null) {
            $this->medecin->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($medecin !== null && $medecin->getUser() !== $this) {
            $medecin->setUser($this);
        }

        $this->medecin = $medecin;

        return $this;
    }

    public function getPharmacie(): ?Pharmacie
    {
        return $this->pharmacie;
    }

    public function setPharmacie(?Pharmacie $pharmacie): static
    {
        // unset the owning side of the relation if necessary
        if ($pharmacie === null && $this->pharmacie !== null) {
            $this->pharmacie->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($pharmacie !== null && $pharmacie->getUser() !== $this) {
            $pharmacie->setUser($this);
        }

        $this->pharmacie = $pharmacie;

        return $this;
    }

    public function getInfermier(): ?Infermier
    {
        return $this->infermier;
    }

    public function setInfermier(?Infermier $infermier): static
    {
        // unset the owning side of the relation if necessary
        if ($infermier === null && $this->infermier !== null) {
            $this->infermier->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($infermier !== null && $infermier->getUser() !== $this) {
            $infermier->setUser($this);
        }

        $this->infermier = $infermier;

        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): static
    {
        // unset the owning side of the relation if necessary
        if ($patient === null && $this->patient !== null) {
            $this->patient->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($patient !== null && $patient->getUser() !== $this) {
            $patient->setUser($this);
        }

        $this->patient = $patient;

        return $this;
    }
    public function getFullName(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function addReceivedMessage(Message $message): static
    {
        if (!$this->receivedMessages->contains($message)) {
            $this->receivedMessages->add($message);
            $message->setReceiver($this);
        }
        return $this;
    }

    public function removeReceivedMessage(Message $message): static
    {
        if ($this->receivedMessages->removeElement($message)) {
            if ($message->getReceiver() === $this) {
                $message->setReceiver(null);
            }
        }
        return $this;
    }




}