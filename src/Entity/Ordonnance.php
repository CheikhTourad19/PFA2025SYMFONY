<?php

namespace App\Entity;

use App\Repository\OrdonnanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ordonnance')]
class Ordonnance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $date_creation;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_validite = null;

    #[ORM\ManyToOne(targetEntity: Medecin::class)]
    #[ORM\JoinColumn(name: 'medecin_id', referencedColumnName: 'medecin_id', onDelete: 'CASCADE')]
    private Medecin $medecin;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $patient;

    #[ORM\OneToMany(targetEntity: OrdonnanceMedicament::class, mappedBy: 'ordonnance')]
    private Collection $ordonnanceMedicaments;

    public function __construct()
    {
        $this->date_creation = new \DateTime();
        $this->ordonnanceMedicaments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDateValidite(): ?\DateTimeInterface
    {
        return $this->date_validite;
    }

    public function setDateValidite(?\DateTimeInterface $date_validite): static
    {
        $this->date_validite = $date_validite;

        return $this;
    }

    public function getMedecin(): ?Medecin
    {
        return $this->medecin;
    }

    public function setMedecin(?Medecin $medecin): static
    {
        $this->medecin = $medecin;

        return $this;
    }

    public function getPatient(): ?User
    {
        return $this->patient;
    }

    public function setPatient(?User $patient): static
    {
        $this->patient = $patient;

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
            $ordonnanceMedicament->setOrdonnance($this);
        }

        return $this;
    }

    public function removeOrdonnanceMedicament(OrdonnanceMedicament $ordonnanceMedicament): static
    {
        if ($this->ordonnanceMedicaments->removeElement($ordonnanceMedicament)) {
            // set the owning side to null (unless already changed)
            if ($ordonnanceMedicament->getOrdonnance() === $this) {
                $ordonnanceMedicament->setOrdonnance(null);
            }
        }

        return $this;
    }


}
