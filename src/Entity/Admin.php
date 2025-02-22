<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
#[ORM\Table(name: '`admin`')]
class Admin
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'admin_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $user;

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
        return ['ROLE_ADMIN'];
    }

}
