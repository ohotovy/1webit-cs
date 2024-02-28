<?php
namespace App\Modules\Admin\Presenters;

use Nette;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Application\UI\Form;

use App\Model\EntityManager;
use App\Model\Entity\Product;
use App\Model\Entity\Order;
use App\Model\Entity\OrderItem;
use App\Model\Entity\OrderStatus;


final class HomePresenter extends Nette\Application\UI\Presenter
{
    public function startup(): void
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
}