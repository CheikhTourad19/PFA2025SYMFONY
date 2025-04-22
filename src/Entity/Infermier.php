<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Infermier
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: 'User',inversedBy: 'infermier')]
    #[ORM\JoinColumn(name: 'infermier_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $user;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $service;

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->user->getId();
    }
    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): static
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
    public function getRoles(): array
    {
        return ['ROLE_INFERMIER'];
    }
}