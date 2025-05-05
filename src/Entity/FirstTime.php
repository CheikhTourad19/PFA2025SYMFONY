<?php

// src/Entity/FirstTime.php

namespace App\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\FirstTimeRepository")]
class FirstTime
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: User::class, inversedBy: "firstTime")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private ?User $user = null;

    #[ORM\Column(type: "boolean")]
    private bool $isFirstTime = true;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function isFirstTime(): bool
    {
        return $this->isFirstTime;
    }

    public function setIsFirstTime(bool $isFirstTime): self
    {
        $this->isFirstTime = $isFirstTime;
        return $this;
    }
}
