<?php

namespace App\Entity;

use App\Repository\ReplyRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReplyRepository::class)]
class Reply
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\ManyToOne(inversedBy: 'replies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contact $contactReference = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $replayDate = null;

    #[ORM\ManyToOne(inversedBy: 'replies')]
    private ?User $author = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getContactReference(): ?Contact
    {
        return $this->contactReference;
    }

    public function setContactReference(?Contact $contactReference): static
    {
        $this->contactReference = $contactReference;

        return $this;
    }

    public function getReplayDate(): ?\DateTimeInterface
    {
        return $this->replayDate;
    }

    public function setReplayDate(\DateTimeInterface $replayDate): static
    {
        $this->replayDate = new \DateTime(DateTimeImmutable::createFromFormat("u", $replayDate->getTimestamp()), new \DateTimeZone('Europe/Paris'));

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getAuthorString(): ?string
    {
        if (!$this->author) {
            return 'SAV';
        }

        return $this->author->getFirstName() . ' ' . $this->author->getLastName();
    }
}
