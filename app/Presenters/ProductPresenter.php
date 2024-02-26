<?php
namespace App\Presenters;

use Nette;
use App\Model\EntityManager;
use App\Model\Entity\Product;
use App\Model\Entity\Order;

final class ProductPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(
		private EntityManager $em,
	) {
	}

	public function renderDefault(): void
	{
        $productRepository = $this->em->getRepository(Order::class);
        $products = $productRepository->findAll();

        // foreach ($products)
        // var_dump($products[0]->getItems()[0]);
        // die;
        $this->template->products = $products;
	}

}
