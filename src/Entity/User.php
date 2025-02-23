<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Enum\Role;
use Symfony\Component\Validator\Constraints as Assert;



#[ORM\Entity]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{ #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
private int $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Nom cannot be blank.")]
    private string $nom;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Prenom cannot be blank.")]
    private string $prenom;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "Email cannot be blank.")]
    #[Assert\Email(message: "The email '{{ value }}' is not a valid email.")]
    private string $email;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Password cannot be blank.")]
    #[Assert\Length(
        min: 8,
        max: 255,
        minMessage: "Your password must be at least {{ limit }} characters long.",
        maxMessage: "Your password cannot be longer than {{ limit }} characters."
    )]
    private string $password;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(type: 'string', enumType: Role::class, options: ['default' => 'patient'])]
    private Role $role = Role::PATIENT;

    public function getRoles(): array
    {
        return ['ROLE_' . strtoupper($this->role->value)];
    }

    public function eraseCredentials(): void
    {}
    public function getUserIdentifier(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }

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
}