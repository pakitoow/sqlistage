<?php

namespace App\Entity;

use App\Repository\LogSetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogSetRepository::class)]
class LogSet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $importDate;

    #[ORM\Column(type: 'string', length: 255)]
    private string $filename;

    #[ORM\OneToMany(mappedBy: 'logSet', targetEntity: LogItem::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $logItems;

    public function __construct()
    {
        $this->importDate = new \DateTime();
        $this->logItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImportDate(): \DateTimeInterface
    {
        return $this->importDate;
    }

    public function setImportDate(\DateTimeInterface $importDate): self
    {
        $this->importDate = $importDate;
        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return Collection<int, LogItem>
     */
    public function getLogItems(): Collection
    {
        return $this->logItems;
    }

    public function addLogItem(LogItem $logItem): self
    {
        if (!$this->logItems->contains($logItem)) {
            $this->logItems[] = $logItem;
            $logItem->setLogSet($this);
        }

        return $this;
    }

    public function removeLogItem(LogItem $logItem): self
    {
        if ($this->logItems->removeElement($logItem)) {
            if ($logItem->getLogSet() === $this) {
                $logItem->setLogSet(null);
            }
        }

        return $this;
    }
}
