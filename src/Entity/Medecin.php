<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Medecin
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: User::class ,inversedBy: "medecin")]
    #[ORM\JoinColumn(name: 'medecin_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'string', length: 100)]
    private string $service;

    // Getters and Setters

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(string $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_MEDECIN'];
    }
    public function getId(): ?int
    {
        return $this->user->getId();
    }
}