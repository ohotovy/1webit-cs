<?php
namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class Order
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order')]
    private Collection $items;

    #[ORM\Column(nullable: 'true')]
    private string $first_name;
    #[ORM\Column(nullable: 'true')]
    private string $last_name;
    #[ORM\Column(nullable: 'true')]
    private string $city;
    #[ORM\Column(nullable: 'true')]
    private string $street_and_no;
    #[ORM\Column(nullable: 'true')]
    private string $zip;
    #[ORM\Column(nullable: 'true')]
    private string $note;

    #[ORM\Column(nullable: 'true')]
    private string $delivery_type_id;

    #[ORM\Column(nullable: 'true')]
    private string $payment_type_id;

    public function __construct()
    {
        $this->items = new ArrayCollection;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->first_name;
    }

    public function getItems() : Collection
    {
        return $this->items;
    }
}