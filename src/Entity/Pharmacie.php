<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Pharmacie
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: 'User',inversedBy: 'pharmacie')]
    #[ORM\JoinColumn(name: 'pharmacie_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $user;

    #[ORM\Column(type: 'string', length: 100)]
    private $cin;

    #[ORM\OneToOne(
        targetEntity: Adresse::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\JoinColumn(name: 'adresse_id', referencedColumnName: 'id')]
    private $adresse;

    // Getters and Setters

    public function getCin(): ?string
    {
        return $this->cin;
    }

    public function setCin(string $cin): static
    {
        $this->cin = $cin;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
    public function getRoles(): array
    {
        return ['ROLE_PHARMACIE'];
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAdresse(): ?Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresse $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }


    public function getId(): ?int
    {
        return $this->user->getId();
    }
}