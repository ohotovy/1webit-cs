<?php
namespace App\Presenters;

use Nette;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Application\UI\Form;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;

use App\Model\EntityManager;
use App\Model\Entity\Product;
use App\Model\Entity\Order;
use App\Model\Entity\OrderItem;
use App\Model\Entity\DeliveryMethod;
use App\Model\Entity\PaymentMethod;
use App\Model\Entity\OrderStatus;

use App\DTO\ProductBasketInsert;
use App\DTO\ProductBasketIncrease;
use App\DTO\OrderInvoiceData;


final class BasketPresenter extends Nette\Application\UI\Presenter
{
    private string $redirectTo;

	public function __construct(
		private EntityManager $em,
		private Request $httpRequest,
		private Response $httpResponse,
	) {
        $this->redirectTo = 'next';
        $this->setLayout('basket');
	}

	public function renderItems(): void
	{
        $basketId = $this->httpRequest->getCookie('basketId');

		$this->template->basketId = $basketId;

        if (!is_null($basketId)) {
            $this->template->basket = $this->em->find(Order::class, $basketId);
        }
	}

    // STEP 1 - confirm ordered items

	protected function createComponentConfirmBasketForm(): Form
    {
        $form = new Form;

        // Actual inputs in latte
        $form->addSubmit('adjust', 'Adjust')
            ->onClick[] = [$this, 'adjustButtonPressed'];
        $form->addSubmit('cac', 'Confirm and Continue')
            ->onClick[] = [$this, 'cacButtonPressed'];

        $form->onSuccess[] = function () use ($form) {

            $dataQtys = $form->getHttpData($form::DataLine, 'qty[]');
            $dataProdIds = $form->getHttpData($form::DataLine, 'product_id[]');
            $data = [];
            foreach ($dataQtys as $key => $qty) {
                $data[] = [
                    'id' =>  (int) $dataProdIds[$key],
                    'qty' => (int) $qty
                ];
            }
            $this->confirmBasketFormSucceeded($data);
        };

        return $form;
    }

	private function confirmBasketFormSucceeded($data): void
    {
        $dataValid = true;
        foreach ($data as $productData) {
            if (!is_numeric($productData['qty']) || $productData['qty'] < 0) {
                $dataValid = false;
            }
        }

        if ($dataValid !== true) {
            $this->flashMessage('Invalid Basket Status', 'failed');
            $this->redirect('this');
            $this->terminate();
        }

        $basketId = $this->httpRequest->getCookie('basketId');

        $order = $this->em->find(Order::class,$basketId);

        if (is_null($order)) {
            $this->flashMessage('Invalid Basket', 'failed');
            $this->redirect('this');
            $this->terminate();
        }

        $changeMade = false;
        $orderedItems = $order->getItems();

        // handle incoming data about products
        foreach ($data as $productData) {
            // find matching item in basket, with DIFFERENT ordered quantity than in incoming data
            // (if same, no need to manipulate the record)
            $item = $orderedItems->filter(function ($orderedItem) use ($productData) {
                return $orderedItem->getId() === $productData['id'] && $orderedItem->getQuantity() !== $productData['qty'];
            })->first();
            // if found
            if ($item) {
                // delete record if incoming data say 0 quantity
                if ($productData['qty'] === 0) {
                    $this->em->remove($item);
                } else {
                    // or simply change quantity
                    $qtyObject = new ProductBasketIncrease(
                        $productData['qty']
                    );
                    $item->setBasketValue($qtyObject);
                }
                $changeMade = true;
            }
        }

        $this->em->persist($order);
        $this->em->flush();

        if ($changeMade) {
            $this->flashMessage('Basket Change Successful', 'success');
        }
        $redirectTo = $this->redirectTo == 'this' ? 'this' : 'Basket:invoicing';
        $this->redirect($redirectTo);
    }

    // STEP 2 - Enter invoicing data

    public function renderInvoicing(): void
	{
        $basketId = $this->httpRequest->getCookie('basketId');

		$this->template->basketId = $basketId;

        if (!is_null($basketId)) {
            $this->template->basket = $this->em->find(Order::class, $basketId);
        } else {
            $this->redirect('Basket:items');
        }
	}

    protected function createComponentInvoicingDataForm(): Form
    {
        $form = new Form;

        $basketId = $this->httpRequest->getCookie('basketId');

        $basket = $this->em->find(Order::class, $basketId);

        $form->addText('first_name')
            ->setRequired();
        $form->addText('last_name')
            ->setRequired();
        $form->addEmail('email')
            ->setRequired();
        $form->addText('city')
            ->setRequired();
        $form->addText('street_and_no')
            ->setRequired();
        $form->addText('zip')
            ->setRequired();
        $form->addTextArea('note')
            ->setDefaultValue($basket->getNote());

        $form->addSubmit('adjust', 'Adjust')
            ->onClick[] = [$this, 'adjustButtonPressed'];

        $form->addSubmit('cac', 'Confirm and Continue')
            ->onClick[] = [$this, 'cacButtonPressed'];


        $form->onSuccess[] = $this->invoicingDataFormSucceeded(...);

        return $form;
    }

    private function invoicingDataFormSucceeded(\stdClass $data) : void
    {
        $basketId = $this->httpRequest->getCookie('basketId');

        $order = $this->em->find(Order::class,$basketId);

        $invoiceData = new OrderInvoiceData(
            $data->first_name,
            $data->last_name,
            $data->email,
            $data->city,
            $data->street_and_no,
            $data->zip,
            $data->note
        );

        $order->setInvoicingData($invoiceData);
        $this->em->persist($order);
        $this->em->flush();

        $redirectTo = $this->redirectTo == 'this' ? 'this' : 'Basket:delivery';
        $this->redirect($redirectTo);
    }

    // STEP 3 - delivery and payment

    public function renderDelivery(): void
	{
        $basketId = $this->httpRequest->getCookie('basketId');

		if (!is_null($basketId)) {
            $this->template->basket = $this->em->find(Order::class, $basketId);
        } else {
            $this->redirect('Basket:items');
        }
	}

    protected function createComponentDeliveryDataForm(): Form
    {
        $form = new Form;

        $basketId = $this->httpRequest->getCookie('basketId');

		$this->template->basketId = $basketId;

        $basket = $this->em->find(Order::class,$basketId);

        $chosenDelivery = $basket->getDeliveryMethod() ?? false;
        $chosenDeliveryId = $chosenDelivery ? $chosenDelivery->getId() : 1;
        $chosenPayment = $basket->getPaymentMethod() ?? false;
        $chosenPaymentId = $chosenPayment ? $chosenPayment->getId() : 1;

        $deliveryMethodsObjects = $this->em->getRepository(DeliveryMethod::class)
            ->findBy([],['id' => 'ASC']);

        $deliveryMethodsArray = [];

        foreach ($deliveryMethodsObjects as $deliveryMethod) {
            $deliveryMethodsArray[$deliveryMethod->getId()] = $deliveryMethod->getName();
        }

        $paymentMethodsObjects = $this->em->getRepository(PaymentMethod::class)
            ->findBy([],['id' => 'ASC']);

        $paymentMethodsArray = [];

        foreach ($paymentMethodsObjects as $paymentMethod) {
            $paymentMethodsArray[$paymentMethod->getId()] = $paymentMethod->getName();
        }

        $form->addSelect('delivery_method_id','Delivery Method',$deliveryMethodsArray)
            ->setDefaultValue($chosenDeliveryId);

        $form->addSelect('payment_method_id','Payment Method',$paymentMethodsArray)
            ->setDefaultValue($chosenPaymentId);

        $form->addSubmit('adjust', 'Adjust')
            ->onClick[] = [$this, 'adjustButtonPressed'];

        $form->addSubmit('cac', 'Confirm and Continue')
            ->onClick[] = [$this, 'cacButtonPressed'];


        $form->onSuccess[] = $this->deliveryDataFormSucceeded(...);

        return $form;
    }

    private function deliveryDataFormSucceeded(\stdClass $data) : void
    {
        $basketId = $this->httpRequest->getCookie('basketId');

        $order = $this->em->find(Order::class,$basketId);

        if (is_null($order)) {
            $this->flashMessage('Invalid Basket', 'failed');
            $this->redirect('this');
            $this->terminate();
        }

        $order->setDeliveryMethod($this->em->find(DeliveryMethod::class,$data->delivery_method_id));
        $order->setPaymentMethod($this->em->find(PaymentMethod::class,$data->payment_method_id));
        $this->em->persist($order);
        $this->em->flush();

        $redirectTo = $this->redirectTo == 'this' ? 'this' : 'Basket:recapitulation';
        $this->redirect($redirectTo);
    }

    // STEP 4 - Final recap

    public function renderRecapitulation() : void
    {
        $basketId = $this->httpRequest->getCookie('basketId');

		$this->template->basketId = $basketId;

        if (!is_null($basketId)) {
            $this->template->basket = $this->em->find(Order::class, $basketId);
        } else {
            $this->redirect('Basket:items');
        }
    }

    protected function createComponentConfirmOrderForm(): Form
    {
        $form = new Form;

        $form->addSubmit('cac', 'Confirm Order');

        $form->onSuccess[] = $this->confirmOrderFormSucceeded(...);

        return $form;
    }

    private function confirmOrderFormSucceeded(\stdClass $data) : void
    {
        $basketId = $this->httpRequest->getCookie('basketId');

		$order = $this->em->find(Order::class, $basketId);

        $order->setStatus($this->em->getRepository(OrderStatus::class)
            ->findOneBy(['slug' => 'ordered']));
        $this->em->persist($order);
        $this->em->flush();

        $this->httpResponse->deleteCookie('basketId');

        $this->redirect('Basket:success');
    }

    // STEP 5 - confirmation, just view

    // handlers for pressing buttons "Adjust" (confirm new data) and "Confirm and Contiue" (confirm new data, go to next step)

    public function adjustButtonPressed() : void
    {
        $this->redirectTo = 'this';
    }

    public function cacButtonPressed() : void
    {
        $this->redirectTo = 'next';
    }

}
