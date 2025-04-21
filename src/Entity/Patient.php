<?php
namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Patient
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: User::class,inversedBy: "patient")]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'bigint', nullable: true)]
    private ?int $cin = null;

    // Getters and Setters

    public function getCin(): ?int
    {
        return $this->cin;
    }

    public function setCin(?int $cin): static
    {
        $this->cin = $cin;

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
        return ['ROLE_PATIENT'];
    }
}