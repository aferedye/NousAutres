<?php

namespace App\Entity;

use App\Repository\EchoTraceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EchoTraceRepository::class)]
class EchoTrace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenuAnalysé = null;

    #[ORM\Column(length: 255)]
    private ?string $intentionDétectée = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $encadréProposé = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ressourceEnRésonnance = null;

    #[ORM\Column]
    private ?\DateTime $horodatage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenuAnalysé(): ?string
    {
        return $this->contenuAnalysé;
    }

    public function setContenuAnalysé(string $contenuAnalysé): static
    {
        $this->contenuAnalysé = $contenuAnalysé;

        return $this;
    }

    public function getIntentionDétectée(): ?string
    {
        return $this->intentionDétectée;
    }

    public function setIntentionDétectée(string $intentionDétectée): static
    {
        $this->intentionDétectée = $intentionDétectée;

        return $this;
    }

    public function getEncadréProposé(): ?string
    {
        return $this->encadréProposé;
    }

    public function setEncadréProposé(?string $encadréProposé): static
    {
        $this->encadréProposé = $encadréProposé;

        return $this;
    }

    public function getRessourceEnRésonnance(): ?string
    {
        return $this->ressourceEnRésonnance;
    }

    public function setRessourceEnRésonnance(?string $ressourceEnRésonnance): static
    {
        $this->ressourceEnRésonnance = $ressourceEnRésonnance;

        return $this;
    }

    public function getHorodatage(): ?\DateTime
    {
        return $this->horodatage;
    }

    public function setHorodatage(\DateTime $horodatage): static
    {
        $this->horodatage = $horodatage;

        return $this;
    }
}
