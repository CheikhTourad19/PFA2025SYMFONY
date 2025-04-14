<?php

namespace App\Entity;

use App\Repository\MedicamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: 'medicament')]
class Medicament
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $nom;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $prix = null;

    // Relationships
    #[ORM\OneToMany(targetEntity: Stock::class, mappedBy: 'medicament')]
    private Collection $stocks;

    #[ORM\OneToMany(mappedBy: 'medicament', targetEntity: OrdonnanceMedicament::class)]
    private Collection $ordonnanceMedicaments;


    public function __construct()
    {
        $this->stocks = new ArrayCollection();
        $this->ordonnanceMedicaments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(?int $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * @return Collection<int, Stock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(Stock $stock): static
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->setMedicament($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): static
    {
        if ($this->stocks->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getMedicament() === $this) {
                $stock->setMedicament(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrdonnanceMedicament>
     */
    public function getOrdonnanceMedicaments(): Collection
    {
        return $this->ordonnanceMedicaments;
    }

    public function addOrdonnanceMedicament(OrdonnanceMedicament $ordonnanceMedicament): static
    {
        if (!$this->ordonnanceMedicaments->contains($ordonnanceMedicament)) {
            $this->ordonnanceMedicaments->add($ordonnanceMedicament);
            $ordonnanceMedicament->setMedicament($this);
        }

        return $this;
    }

    public function removeOrdonnanceMedicament(OrdonnanceMedicament $ordonnanceMedicament): static
    {
        if ($this->ordonnanceMedicaments->removeElement($ordonnanceMedicament)) {
            // set the owning side to null (unless already changed)
            if ($ordonnanceMedicament->getMedicament() === $this) {
                $ordonnanceMedicament->setMedicament(null);
            }
        }

        return $this;
    }



}