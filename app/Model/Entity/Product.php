<?php
// src/Product.php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\DTO\ProductEdit;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
class Product
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private int|null $id = null;
    #[ORM\Column]
    private string $name;
    #[ORM\Column(name: 'description', type: Types::TEXT)]
    private string $description;
    #[ORM\Column(name: 'short_desc')]
    private string $shortDesc;
    #[ORM\Column(name: 'image_url', nullable: true)]
    private string $imageUrl;
    #[ORM\Column(name: 'unit_price', type: Types::DECIMAL, precision: 10, scale: 2)]
    private float $unitPrice;

    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'product')]
    private Collection $inOrders;

    public function __construct()
    {
        $this->inOrders = new ArrayCollection;
    }

    public function getId() : ?int
    {
        return $this->id ?? null;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function getShortDescription() : string
    {
        return $this->shortDesc;
    }

    public function getImageUrl() : string
    {
        return $this->imageUrl;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getPrice() : float
    {
        return $this->unitPrice;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function setDescription(string $description) : void
    {
        $this->description = $description;
    }

    public function setShortDescription(string $shortDescription) : void
    {
        $this->shortDesc = $shortDescription;
    }

    public function setImageUrl(string $url) : void
    {
        $this->imageUrl = $url;
    }

    public function setPrice(float|int $price) : void
    {
        $this->unitPrice = (float) $price;
    }

    public function setProductData(ProductEdit $data) : void
    {
        $this->name = $data->name;
        $this->description = $data->description;
        $this->shortDesc = $data->shortDesc;
        $this->unitPrice = $data->unitPrice;
    }
}