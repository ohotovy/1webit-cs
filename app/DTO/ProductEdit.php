<?php

namespace App\DTO;

class ProductEdit
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly string $shortDesc,
        public readonly float $unitPrice
    )
    {
    }
}