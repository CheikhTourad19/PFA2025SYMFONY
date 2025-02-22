<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Medecin extends User
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'medecin_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $user;

    #[ORM\Column(type: 'string', length: 100)]
    private $service;

    // Getters and Setters

    public function getService(): ?string
    {
        return $this->service;
    }
    public function getRoles(): array
    {
        return ['ROLE_MEDECIN'];
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

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}