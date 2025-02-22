<?php
namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Patient extends User
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $user;

    #[ORM\Column(type: 'bigint')]
    private $cin;

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

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
    public function getRoles(): array
    {
        return ['ROLE_PATIENT'];
    }
}