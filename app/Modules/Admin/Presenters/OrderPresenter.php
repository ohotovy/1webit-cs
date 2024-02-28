<?php
namespace App\Modules\Admin\Presenters;

use Nette;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Application\UI\Form;

use App\Model\EntityManager;
use App\Model\Entity\Order;
use App\Model\Entity\OrderStatus;

final class OrderPresenter extends Nette\Application\UI\Presenter
{
    private $product = null;

	public function __construct(
		private EntityManager $em,
	) {
	}

    public function startup(): void
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }

    public function renderDefault() : void
    {
        $orderRepository = $this->em->getRepository(Order::class);
        $this->template->orders = $orderRepository->findBy([],['id' => 'ASC']);
    }

    public function renderEdit(int $orderId) : void
    {
        $this->template->order = $this->em->find(Order::class, $orderId);
    }

    protected function createComponentEditOrderForm(): Form
    {
        $form = new Form;

        // POZN. Tady urcite neco delam blbe s Nette Form, protoze je tam po odeslani evidentne jeden run, kdy $this-template
        // neodkazuje na OrderPresenter a neni v nem nic, tudiz ten new Order je last minute hack po vzoru ProductPresenteru
        // (v ProductPresenteru to hack uplne neni, protoze se stejny form pouziva pro edit i create)
        $order = $this->template->order ?? new Order();

        $statuses = $this->em->getRepository(OrderStatus::class)->findBy([],['id' => 'ASC']);

        $statusOptions = [];
        foreach ($statuses as $status) {
            $statusOptions[$status->getId()] = $status->getName();
        }

        $form->addHidden('order_id')
            ->setDefaultValue($order->getId())
        ;
        $form->addSelect('status_id','Status',$statusOptions)
            ->setDefaultValue($order->getStatus() ? $order->getStatus()->getId() : null)
        ;

        $form->addSubmit('edit', 'Update');

        $form->onSuccess[] = $this->editOrderFormSucceeded(...);

        return $form;
    }

    private function editOrderFormSucceeded(\stdClass $data) : void
    {
        $order = $this->em->find(Order::class,$data->order_id);

        $status = $this->em->find(OrderStatus::class, $data->status_id);

        $order->setStatus($status);
        $this->em->persist($order);
        $this->em->flush();

        $this->redirect('this');
    }
}