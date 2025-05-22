<?php

// src/Entity/DossierMedical.php
namespace App\Entity;

use App\Repository\DossierMedicaleRepository;
use App\Repository\DossierMedicalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DossierMedicaleRepository::class)]
#[ORM\Table(name: 'dossier_medical')]

class DossierMedicale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $patient = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $groupeSanguin = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $allergies = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $antecedentsMedicaux = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $antecedentsFamiliaux = null;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getPatient(): ?User
    {
        return $this->patient;
    }

    public function setPatient(?User $patient): void
    {
        $this->patient = $patient;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getGroupeSanguin(): ?string
    {
        return $this->groupeSanguin;
    }

    public function setGroupeSanguin(?string $groupeSanguin): void
    {
        $this->groupeSanguin = $groupeSanguin;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function setAllergies(?string $allergies): void
    {
        $this->allergies = $allergies;
    }

    public function getAntecedentsMedicaux(): ?string
    {
        return $this->antecedentsMedicaux;
    }

    public function setAntecedentsMedicaux(?string $antecedentsMedicaux): void
    {
        $this->antecedentsMedicaux = $antecedentsMedicaux;
    }

    public function getAntecedentsFamiliaux(): ?string
    {
        return $this->antecedentsFamiliaux;
    }

    public function setAntecedentsFamiliaux(?string $antecedentsFamiliaux): void
    {
        $this->antecedentsFamiliaux = $antecedentsFamiliaux;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeImmutable $dateCreation): void
    {
        $this->dateCreation = $dateCreation;
    }

    // Getters et Setters ici...
}
