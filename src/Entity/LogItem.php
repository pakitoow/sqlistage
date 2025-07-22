<?php

namespace App\Entity;

use App\Repository\LogItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogItemRepository::class)]
class LogItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: LogSet::class, inversedBy: 'logItems')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['unsigned' => true])]
    private ?LogSet $logSet = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $severity = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $timestamp = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $lineNumber = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $rawLine = null;

    #[ORM\Column(type: 'boolean')]
    private bool $parsed = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogSet(): ?LogSet
    {
        return $this->logSet;
    }

    public function setLogSet(?LogSet $logSet): self
    {
        $this->logSet = $logSet;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getSeverity(): ?string
    {
        return $this->severity;
    }

    public function setSeverity(?string $severity): self
    {
        $this->severity = $severity;
        return $this;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(?\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function getLineNumber(): ?int
    {
        return $this->lineNumber;
    }

    public function setLineNumber(?int $lineNumber): self
    {
        $this->lineNumber = $lineNumber;
        return $this;
    }

    public function getRawLine(): ?string
    {
        return $this->rawLine;
    }

    public function setRawLine(?string $rawLine): self
    {
        $this->rawLine = $rawLine;
        return $this;
    }

    public function isParsed(): bool
    {
        return $this->parsed;
    }

    public function setParsed(bool $parsed): self
    {
        $this->parsed = $parsed;
        return $this;
    }
}
