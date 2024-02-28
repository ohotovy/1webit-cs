<?php

namespace App\DTO;

use App\Model\Entity\Product;
use App\Model\Entity\Order;

class ProductBasketInsert
{
    public function __construct(
        public readonly Product $product,
        public readonly Order $order,
        public readonly int $qty
    ) {
    }
}