<?php
namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\DTO\OrderInvoiceData;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class Order
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(name: 'first_name', nullable: 'true')]
    private string $firstName;
    #[ORM\Column(name: 'last_name', nullable: 'true')]
    private string $lastName;
    #[ORM\Column(nullable: 'true')]
    private string $email;
    #[ORM\Column(nullable: 'true')]
    private string $city;
    #[ORM\Column(name: 'street_and_no', nullable: 'true')]
    private string $streetAndNo;
    #[ORM\Column(nullable: 'true')]
    private string $zip;
    #[ORM\Column(type: Types::TEXT, nullable: 'true')]
    private string $note;

    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order')]
    private Collection $items;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private DeliveryMethod $deliveryMethod;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private PaymentMethod $paymentMethod;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private OrderStatus $status;

    public function __construct()
    {
        $this->items = new ArrayCollection;
    }

    // getters

    public function getId() : ?int
    {
        return $this->id ?? null;
    }

    public function getFirstName() : ?string
    {
        return $this->firstName ?? null;
    }

    public function getLastName() : ?string
    {
        return $this->lastName ?? null;
    }

    public function getFullName() : string
    {
        return ($this->firstName ?? '').' '.($this->lastName ?? '');
    }

    public function getEmail() : ?string
    {
        return $this->email ?? null;
    }

    public function getCity() : ?string
    {
        return $this->city ?? null;
    }

    public function getStreetAndNo() : ?string
    {
        return $this->streetAndNo ?? null;
    }

    public function getZip() : ?string
    {
        return $this->zip ?? null;
    }

    public function getNote() : ?string
    {
        return $this->note ?? null;
    }

    public function getItems() : Collection
    {
        return $this->items;
    }

    public function getTotalPrice() : float
    {
        return $this->items->reduce(fn ($acc, $item) => $acc + $item->getTotalPrice(),0);
    }

    public function getDeliveryMethod() : ?DeliveryMethod
    {
        return $this->deliveryMethod ?? null;
    }

    public function getPaymentMethod() : ?PaymentMethod
    {
        return $this->paymentMethod ?? null;
    }

    public function getStatus() : ?OrderStatus
    {
        return $this->status ?? null;
    }

    // setters

    public function setInvoicingData(OrderInvoiceData $data) : void
    {
        $this->firstName = $data->firstName;
        $this->lastName = $data->lastName;
        $this->email = $data->email;
        $this->city = $data->city;
        $this->streetAndNo = $data->streetAndNo;
        $this->zip = $data->zip;
        $this->note = $data->note;
    }

    public function setDeliveryMethod(DeliveryMethod $method) : void
    {
        $this->deliveryMethod = $method;
    }

    public function setPaymentMethod(PaymentMethod $method) : void
    {
        $this->paymentMethod = $method;
    }

    public function setStatus(OrderStatus $status) : void
    {
        $this->status = $status;
    }
}