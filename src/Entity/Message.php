<?php

namespace App\Entity;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\ManyToOne(targetEntity: Medecin::class, inversedBy: "sentMessages")]
    #[ORM\JoinColumn(name: 'sender_id', referencedColumnName: 'medecin_id', nullable: false)]
    private ?Medecin $sender = null;

    #[ORM\ManyToOne(targetEntity: Medecin::class, inversedBy: "receivedMessages")]
    #[ORM\JoinColumn(name: 'receiver_id', referencedColumnName: 'medecin_id', nullable: false)]
    private ?Medecin $receiver = null;


    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $sentAt = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }



    public function getSender(): ?Medecin
    {
        return $this->sender;
    }

    public function setSender(?Medecin $sender): static
    {
        $this->sender = $sender;
        return $this;
    }

    public function getReceiver(): ?Medecin
    {
        return $this->receiver;
    }

    public function setReceiver(?Medecin $receiver): static
    {
        $this->receiver = $receiver;
        return $this;
    }
    public function setSentAt(\DateTimeInterface $sentAt): static
    {
        $this->sentAt = \DateTime::createFromInterface($sentAt); // Convertit en DateTime mutable
        return $this;
    }
}