<?php
// src/Product.php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'dim_order_status')]
class OrderStatus
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private int|null $id = null;
    #[ORM\Column]
    private string $name;
    #[ORM\Column]
    private string $slug;

    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'status')]
    private Collection $orders;

    public function __construct()
    {
        $this->orders = new ArrayCollection;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getSlug() : string
    {
        return $this->slug;
    }

    public function getOrders() : Collection
    {
        return $this->orders;
    }



    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function setSlug(string $slug) : void
    {
        $this->slug = $slug;
    }

}