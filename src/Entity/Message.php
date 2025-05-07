<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: "messages")]
class Message
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "sentMessages")]
    #[ORM\JoinColumn(
        name: "sender_id",
        referencedColumnName: "id",
        nullable: false,
        onDelete: "CASCADE"
    )]
    private ?User $sender = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "receivedMessages")]
    #[ORM\JoinColumn(
        name: "receiver_id",
        referencedColumnName: "id",
        nullable: false,
        onDelete: "CASCADE"
    )]
    private ?User $receiver = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(
        type: Types::DATETIMETZ_MUTABLE,
        options: ["default" => "CURRENT_TIMESTAMP"]
    )]
    private ?\DateTimeInterface $sentAt = null;

    public function __construct()
    {
        $this->sentAt = new \DateTime();
    }

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

    public function setSentAt(\DateTimeInterface $sentAt): static
    {
        $this->sentAt = $sentAt;
        return $this;
    }







    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        if ($this->sender !== $sender) {
            $this->sender?->getSentMessages()->removeElement($this);
            $this->sender = $sender;
            $sender?->addSentMessage($this);
        }
        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(?User $receiver): static
    {
        if ($this->receiver !== $receiver) {
            // Mise à jour bidirectionnelle sécurisée
            $oldReceiver = $this->receiver;
            $this->receiver = $receiver;

            if ($oldReceiver !== null) {
                $oldReceiver->removeReceivedMessage($this);
            }

            if ($receiver !== null) {
                $receiver->addReceivedMessage($this);
            }
        }
        return $this;
    }

}