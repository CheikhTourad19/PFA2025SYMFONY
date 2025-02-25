<?php

namespace App\Entity;

use App\Repository\OrdonnanceMedicamentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: 'ordonnance_medicament')]
class OrdonnanceMedicament
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $quantite = 1;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $instructions = null;

    #[ORM\ManyToOne(targetEntity: Ordonnance::class, inversedBy: 'ordonnanceMedicaments')]
    #[ORM\JoinColumn(name: 'ordonnance_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Ordonnance $ordonnance;

    #[ORM\ManyToOne(targetEntity: Medicament::class)]
    #[ORM\JoinColumn(name: 'medicament_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Medicament $medicament;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): static
    {
        $this->instructions = $instructions;

        return $this;
    }

    public function getOrdonnance(): ?Ordonnance
    {
        return $this->ordonnance;
    }

    public function setOrdonnance(?Ordonnance $ordonnance): static
    {
        $this->ordonnance = $ordonnance;

        return $this;
    }

    public function getMedicament(): ?Medicament
    {
        return $this->medicament;
    }

    public function setMedicament(?Medicament $medicament): static
    {
        $this->medicament = $medicament;

        return $this;
    }


}
