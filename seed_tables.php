<?php
require_once "bootstrap.php";

$productsData = [
    [
        'name' => 'Jednicka',
        'description' => "The best product we have!!! Also, lorem ipsum dolor, sit amet consectetur adipisicing elit. Deserunt veritatis enim mollitia numquam itaque reprehenderit, minus ipsum esse maxime magnam eligendi distinctio ab provident repudiandae voluptatibus eius aperiam vero maiores!",
        'short_desc' => "The best product we have!!!",
        'image_url' => 'jednicka.png',
        'unit_price' => 100
    ],
    [
        'name' => 'Dvojka',
        'description' => "The second best product we have, only Jednicka is better!!! Also, lorem ipsum dolor, sit amet consectetur adipisicing elit. Deserunt veritatis enim mollitia numquam itaque reprehenderit, minus ipsum esse maxime magnam eligendi distinctio ab provident repudiandae voluptatibus eius aperiam vero maiores!",
        'short_desc' => "The second best product we have, only Jednicka is better!!!",
        'image_url' => 'dvojka.png',
        'unit_price' => 80
    ],
    [
        'name' => 'Trojka',
        'description' => "Frankly, trash. Also, lorem ipsum dolor, sit amet consectetur adipisicing elit. Deserunt veritatis enim mollitia numquam itaque reprehenderit, minus ipsum esse maxime magnam eligendi distinctio ab provident repudiandae voluptatibus eius aperiam vero maiores!",
        'short_desc' => "Frankly, trash.",
        'image_url' => 'trojka.png',
        'unit_price' => 4.50
    ],
];

$paymentMethodsData = [
    [
        'name' => "Dobirka",
        'slug' => "dobirka",
    ],
    [
        'name' => "Ledvina",
        'slug' => "ledvina",
    ],
];

$deliveryMethodsData = [
    [
        'name' => "Ceska Posta",
        'slug' => "ceska-posta",
    ],
    [
        'name' => "Pony Express",
        'slug' => "pony-express",
    ],
];

$orderStatusesData = [
    [
        'name' => "In Cart",
        'slug' => "in-cart",
    ],
    [
        'name' => "Ordered",
        'slug' => "ordered",
    ],
    [
        'name' => "Sent",
        'slug' => "sent",
    ],
    [
        'name' => "Canceled",
        'slug' => "canceled",
    ],
];

foreach ($productsData as $data) {
    $product = new \App\Model\Entity\Product();
    $product->setName($data['name']);
    $product->setDescription($data['description']);
    $product->setShortDescription($data['short_desc']);
    $product->setImageUrl($data['image_url']);
    $product->setPrice($data['unit_price']);
    $entityManager->persist($product);
}

foreach ($paymentMethodsData as $data) {
    $method = new \App\Model\Entity\PaymentMethod();
    $method->setName($data['name']);
    $method->setSlug($data['slug']);
    $entityManager->persist($method);
}

foreach ($deliveryMethodsData as $data) {
    $method = new \App\Model\Entity\DeliveryMethod();
    $method->setName($data['name']);
    $method->setSlug($data['slug']);
    $entityManager->persist($method);
}

foreach ($orderStatusesData as $data) {
    $method = new \App\Model\Entity\OrderStatus();
    $method->setName($data['name']);
    $method->setSlug($data['slug']);
    $entityManager->persist($method);
}



$entityManager->flush();
