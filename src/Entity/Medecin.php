<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Medecin
{

    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(
        name: "medecin_id",
        referencedColumnName: "id",
        onDelete: "CASCADE"
    )]
    private User $user;

    #[ORM\Column(type: "string", length: 100)]
    private string $service;



    public function __construct()
    {

    }
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
    return $this->medecin_id;
}

}