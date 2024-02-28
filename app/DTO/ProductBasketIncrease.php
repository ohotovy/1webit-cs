<?php

namespace App\DTO;

class ProductBasketIncrease
{
    public function __construct(
        public readonly int $qty
    ) {
    }
}