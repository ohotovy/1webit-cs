<?php

namespace App\DTO;

class OrderInvoiceData
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $city,
        public readonly string $streetAndNo,
        public readonly string $zip,
        public readonly ?string $note=null,
    )
    {
    }
}