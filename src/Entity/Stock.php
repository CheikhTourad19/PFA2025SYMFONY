<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: 'stock',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'pharmacie_medicament_unique', columns: ['pharmacie_id', 'medicament_id'])
    ]
)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $quantite = 0;

    #[ORM\ManyToOne(targetEntity: Pharmacie::class)]
    #[ORM\JoinColumn(name: 'pharmacie_id', referencedColumnName: 'pharmacie_id', onDelete: 'CASCADE')]
    private ?Pharmacie $pharmacie = null;

    #[ORM\JoinColumn(name: 'medicament_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Medicament::class, inversedBy: 'stocks')]

    private ?Medicament $medicament = null;

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

    public function getPharmacie(): ?Pharmacie
    {
        return $this->pharmacie;
    }

    public function setPharmacie(?Pharmacie $pharmacie): static
    {
        $this->pharmacie = $pharmacie;

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
