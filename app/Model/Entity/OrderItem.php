<?php
namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\DTO\ProductBasketInsert;
use App\DTO\ProductBasketIncrease;

#[ORM\Entity]
#[ORM\Table(name: 'order_product')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(name: 'order_id')]
    private int $orderId;
    #[ORM\Column(name: 'product_id')]
    private int $productId;

    #[ORM\ManyToOne(inversedBy: 'items')]
    private Order $order;
    #[ORM\ManyToOne(inversedBy: 'inOrders')]
    private Product $product;


    #[ORM\Column]
    private int $qty = 1;

    #[ORM\Column(name: 'unit_price', type: Types::DECIMAL, precision: 10, scale: 2)]
    private float $unitPrice;

    public function __construct()
    {
    }

    public function getProductName() : string
    {
        return $this->product->getName();
    }

    public function getProductPrice() : string
    {
        return $this->product->getPrice();
    }

    public function getTotalPrice() : float
    {
        return $this->unitPrice * $this->qty;
    }

    public function addNewToBasket(ProductBasketInsert $data) : void
    {
        $this->product = $data->product;
        $this->order = $data->order;
        $this->qty = $data->qty;
        $this->unitPrice = $this->getProductPrice();
    }

    // public function setUnitPrice()
    // {

    // }

    public function addBasketIncrease(ProductBasketIncrease $data) : void
    {
        $this->qty += $data->qty;
    }
}