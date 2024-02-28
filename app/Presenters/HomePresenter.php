<?php
namespace App\Presenters;

use Nette;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Application\UI\Form;

use App\Model\EntityManager;
use App\Model\Entity\Product;
use App\Model\Entity\Order;
use App\Model\Entity\OrderItem;
use App\Model\Entity\OrderStatus;

use App\DTO\ProductBasketInsert;
use App\DTO\ProductBasketIncrease;


final class HomePresenter extends Nette\Application\UI\Presenter
{
	public function __construct(
		private EntityManager $em,
		private Request $httpRequest,
		private Response $httpResponse,
	) {
	}

	public function renderDefault(?string $query = null): void
	{
        $this->template->query = $query;
        $productRepository = $this->em->getRepository(Product::class);
        // handle case insensitive search if needed (query parameter exists) if query exists
        if (!is_null($query)) {
            $qb = $this->em->createQueryBuilder();
            $qb->select(array('u'))
                ->from(Product::class, 'u')
                ->where('LOWER (u.name) LIKE ?1')
                ->orWhere('LOWER (u.description) LIKE ?1')
                ->orderBy('u.id', 'ASC')
                ->setParameter(1, '%'.strtolower($query).'%');
            $query = $qb->getQuery();
            $products = $query->getResult();
        } else {
            // return all products if not
            $products = $productRepository->findBy([],['id' => 'ASC']);
        }
        $this->template->products = $products;

		$basketId = $this->httpRequest->getCookie('basketId');

		$this->template->basketId = $basketId;
	}

	protected function createComponentAddToBasketForm(): Form
    {
        $form = new Form;

		$form->addHidden('product_id');
        $form->addInteger('qty');
        $form->addSubmit('send');

        $form->onSuccess[] = $this->addToBasketFormSucceeded(...);

        return $form;
    }

	private function addToBasketFormSucceeded(\stdClass $data): void
    {
        $dataValid = true;
        if (!is_numeric($data->qty) || $data->qty <= 0) {
            $dataValid = false;
        }

        if ($dataValid !== true) {
            $this->flashMessage('Basket Addition Failed', 'failure');
            $this->redirect('this');
        }

        $basketId = $this->httpRequest->getCookie('basketId');

        if (is_null($basketId)) {
            // create order
            $order = new Order();
            $order->setStatus($this->em->getRepository(OrderStatus::class)->findOneBy(['slug' => 'in-cart']));
            $this->em->persist($order);
            $this->em->flush();
            // set $basketId to ID of created order
            $basketId = $order->getId();
            $this->httpResponse->setCookie('basketId', $basketId, '14 days');
        } else {
            $order = $this->em->getRepository(Order::class)
                ->find($basketId);
        }

        $product = $this->em->getRepository(Product::class)
            ->find($data->product_id);

        // check if OrderItem for basket & product exists
        $orderItem = $this->em->getRepository(OrderItem::class)
            ->findOneBy(['productId' => (int) $data->product_id, 'orderId' => $basketId]);

        if (is_null($orderItem)) {
            // save OrderItem
            $dataObject = new ProductBasketInsert(
                $product,
                $order,
                $data->qty,
            );
            $orderItem = new OrderItem();
            $orderItem->addNewToBasket($dataObject);
        } else {
            $dataObject = new ProductBasketIncrease(
                $data->qty
            );
            $orderItem->addBasketIncrease($dataObject);
        }
        $this->em->persist($orderItem);
        $this->em->flush();

        $this->flashMessage('Basket Addition Successful', 'success');
        $this->redirect('this');
    }

    protected function createComponentSearchForm(): Form
    {
        $form = new Form;

		$form->addText('search_query');
        $form->addSubmit('send');

        $form->onSuccess[] = $this->searchFormSucceeded(...);

        return $form;
    }

    private function searchFormSucceeded(\stdClass $data): void
    {
        $this->redirect('this',['query' => $data->search_query]);
    }
}
