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
    private ?string $code = null;


    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $discount_value = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Product $product = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

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
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

}
