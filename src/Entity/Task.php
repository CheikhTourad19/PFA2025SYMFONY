<?php
// src/Entity/Task.php

namespace App\Entity;

use App\Enum\TaskStatus;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity(repositoryClass: "App\Repository\TaskRepository")]
#[ORM\Table(name: "tasks")]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "task_id", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;




    #[ORM\ManyToOne(targetEntity: Medecin::class)]
    #[ORM\JoinColumn(name: "assigned_to", referencedColumnName: "medecin_id")]
    private Medecin $assignedTo;

    #[ORM\ManyToOne(targetEntity: Medecin::class)]
    #[ORM\JoinColumn(name: "created_by", referencedColumnName: "medecin_id")]
    private Medecin $createdBy;



    #[ORM\Column(type: "datetime", nullable: true, options: ["default" => "CURRENT_TIMESTAMP"])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $deadline = null;
    #[ORM\Column(type: "string", enumType: TaskStatus::class)]
    private TaskStatus $status = TaskStatus::PENDING;

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function setStatus(TaskStatus $status): self
    {
        $this->status = $status;
        return $this;
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getAssignedTo(): Medecin
    {
        return $this->assignedTo;
    }


    public function getCreatedBy(): Medecin
    {
        return $this->createdBy;
    }

    public function setCreatedBy(Medecin $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }



    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;
        return $this;
    }
    public function setAssignedTo(Medecin $assignedTo): self
    {
        $this->assignedTo = $assignedTo;
        return $this;
    }

}