<?php

namespace Message\Mothership\Ecommerce\Controller\ProductPage;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\Ecommerce\Form\Product\ProductPagePublish;


class Upload extends Controller
{
	public function confirm()
	{
		$records = $this->get('product.page.upload_record.loader')->getUnconfirmed();
		$form    = $this->createForm($this->get('product.form.product_page_publish')->setRecords($records));

		return $this->render('Message:Mothership:Ecommerce::product:upload', [
			'records' => $records,
			'form'    => $form,
		]);
	}

	public function confirmAction()
	{
		$records = $this->get('product.page.upload_record.loader')->getUnconfirmed();
		$form    = $this->createForm($this->get('product.form.product_page_publish')->setRecords($records));
		$form->handleRequest();

		if ($form->isValid()) {
			$data = $form->getData();
			$pages = $this->get('cms.page.loader')->getByID($data[ProductPagePublish::PUBLISH]);

			$transaction = $this->get('db.transaction');
			$pageEdit    = $this->get('cms.page.edit')->setTransaction($transaction);
			$recordEdit  = $this->get('product.page.upload_record.edit')->setTransaction($transaction);

			foreach ($pages as $page) {
				$page->setPublished();
				$pageEdit->save($page);
				$record = $records->get($page->id)
					->setConfirmedAt(new DateTimeImmutable)
					->setConfirmedBy($this->get('user.current'));
				$recordEdit->save($record);
			}

			$transaction->commit();
		}

		return $this->redirectToReferer();
	}
}