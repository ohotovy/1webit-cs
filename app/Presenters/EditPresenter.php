<?php
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

final class EditPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(
		private Nette\Database\Explorer $database,
	) {
	}

    public function renderCreate(): void
    {
    }

    public function renderEdit(int $postId): void
    {
        $post = $this->database
            ->table('posts')
            ->get($postId);

        if (!$post) {
            $this->error('Post not found');
        }

        $this->getComponent('postForm')
            ->setDefaults($post->toArray());
    }

    protected function createComponentPostForm(): Form
    {
        $form = new Form;

        $form->addText('title', 'Post Title:')
            ->setRequired();

        $form->addTextArea('text', 'Post Text:')
            ->setRequired();

        $form->addSubmit('send', 'Publish Post');

        $form->onSuccess[] = $this->commentFormSucceeded(...);

        return $form;
    }

    private function commentFormSucceeded(\stdClass $data): void
    {
        $this->database->table('posts')->insert([
            'title' => $data->title,
            'text' => $data->text,
        ]);

        $this->flashMessage('Post saved', 'success');
        $this->redirect('this');
    }
}
