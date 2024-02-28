<?php
namespace App\Modules\Admin\Presenters;

use Nette;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Application\UI\Form;

use App\Model\EntityManager;
use App\Model\Entity\Product;

use App\DTO\ProductEdit;

final class ProductPresenter extends Nette\Application\UI\Presenter
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
        $productRepository = $this->em->getRepository(Product::class);
        $this->template->products = $productRepository->findBy([],['id' => 'ASC']);
    }

    public function renderEdit(int $productId) : void
    {
        $this->template->product = $this->em->find(Product::class, $productId);
    }

    public function renderCreate() : void
    {
        $this->template->product = new Product();
    }

    protected function createComponentEditProductForm(): Form
    {
        $form = new Form;

        $product = $this->template->product ?? new Product();

        $isEdit = $product->getId();

        $form->addHidden('product_id')
            ->setDefaultValue($isEdit ? $product->getId() : null);
        $form->addText('name', 'Name')
            ->setDefaultValue($isEdit ? $product->getName() : null)
            ->setRequired()
            ;
        $form->addTextArea('description', 'Description')
            ->setDefaultValue($isEdit ? $product->getDescription() : null);
        $form->addText('short_desc', 'Short Description')
            ->setDefaultValue($isEdit ? $product->getShortDescription() : null)
            ->setRequired()
            ;
        $form->addText('unit_price', 'Price')
            ->setDefaultValue($isEdit ? $product->getPrice() : null)
            ->setRequired()
            ;
        $currentImageText = $isEdit ?( $product->getImageUrl() ? ' (current '.$product->getImageUrl().')' : '') : null;
        $form->addUpload('image', 'Image'.$currentImageText)
            // Doesn't work
            // ->addRule($form::Image, 'Avatar must be JPEG, PNG, GIF, WebP or AVIF')
            ->addRule($form::MaxFileSize, 'Maximum size is 1 MB', 1024 * 1024)
        ;

        $form->addSubmit('edit', $isEdit ? 'Update' : 'Create');

        $form->onSuccess[] = $this->editProductFormSucceeded(...);

        return $form;
    }

    private function editProductFormSucceeded(\stdClass $data) : void
    {
        if ($data->product_id) {
            $product = $this->em->find(Product::class,$data->product_id);
        } else {
            $product = new Product();
        }

        if ($data->image->hasFile()) {
            // POZN. getSanitizedName() by bylo lepsi, ale potrebuje PHP extension, ktera se mi nechtela nainstalovat (celkem
            // last minute, lepsi aspon takhle nez nic)
            $imageName = $data->image->getUntrustedName();
            $data->image->move('images/products/'.$imageName);
            $product->setImageUrl($imageName);
        }
        $productData = new ProductEdit(
            $data->name,
            $data->description,
            $data->short_desc,
            $data->unit_price
        );


        $product->setProductData($productData);
        $this->em->persist($product);
        $this->em->flush();

        $redirectTo = $data->product_id ? 'this' : 'Product:default';
        $this->redirect($redirectTo);
    }
}