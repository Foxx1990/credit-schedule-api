<?php

namespace App\Entity;

use App\Repository\CreditCalculationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreditCalculationRepository::class)]
class CreditCalculation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column]
    private ?int $numInstallments = null;

    #[ORM\Column]
    private ?float $interestRate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $calculationTime = null;

    #[ORM\Column]
    private array $schedule = [];

    #[ORM\Column(nullable: true)]
    private ?bool $excluded = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getNumInstallments(): ?int
    {
        return $this->numInstallments;
    }

    public function setNumInstallments(int $numInstallments): static
    {
        $this->numInstallments = $numInstallments;

        return $this;
    }

    public function getInterestRate(): ?float
    {
        return $this->interestRate;
    }

    public function setInterestRate(float $interestRate): static
    {
        $this->interestRate = $interestRate;

        return $this;
    }

    public function getCalculationTime(): ?\DateTimeInterface
    {
        return $this->calculationTime;
    }

    public function setCalculationTime(\DateTimeInterface $calculationTime): static
    {
        $this->calculationTime = $calculationTime;

        return $this;
    }

    public function getSchedule(): array
    {
        return $this->schedule;
    }

    public function setSchedule(array $schedule): static
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function isExcluded(): ?bool
    {
        return $this->excluded;
    }

    public function setExcluded(bool $excluded): static
    {
        $this->excluded = $excluded;

        return $this;
    }
}
