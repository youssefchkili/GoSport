<?php

namespace App\Entity;

use App\Repository\CouponRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CouponRepository::class)]
class Coupon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $code = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $discount_type = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $discount_value = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $min_purchase = null;

    #[ORM\Column(nullable: true)]
    private ?int $usage_limit = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $valid_from = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $valid_to = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_active = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(int $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDiscountType(): ?string
    {
        return $this->discount_type;
    }

    public function setDiscountType(string $discount_type): static
    {
        $this->discount_type = $discount_type;

        return $this;
    }

    public function getDiscountValue(): ?string
    {
        return $this->discount_value;
    }

    public function setDiscountValue(string $discount_value): static
    {
        $this->discount_value = $discount_value;

        return $this;
    }

    public function getMinPurchase(): ?string
    {
        return $this->min_purchase;
    }

    public function setMinPurchase(string $min_purchase): static
    {
        $this->min_purchase = $min_purchase;

        return $this;
    }

    public function getUsageLimit(): ?int
    {
        return $this->usage_limit;
    }

    public function setUsageLimit(?int $usage_limit): static
    {
        $this->usage_limit = $usage_limit;

        return $this;
    }

    public function getValidFrom(): ?\DateTimeImmutable
    {
        return $this->valid_from;
    }

    public function setValidFrom(?\DateTimeImmutable $valid_from): static
    {
        $this->valid_from = $valid_from;

        return $this;
    }

    public function getValidTo(): ?\DateTimeImmutable
    {
        return $this->valid_to;
    }

    public function setValidTo(?\DateTimeImmutable $valid_to): static
    {
        $this->valid_to = $valid_to;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(?bool $is_active): static
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
