<?php

namespace Message\Mothership\Ecommerce\Controller\ProductPage;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\Ecommerce\Form\Product\ProductPagePublish;
use Message\Mothership\Ecommerce\ProductPage\Options;

/**
 * @author Sam Trangmar-Keates <sam@message.co.uk>
 */
class Create extends Controller
{
	/**
	 * Show a list of all product pages with form to create a new one.
	 * 
	 * @param  int $productID the product id
	 */
	public function form($productID)
	{
		$product = $this->get('product.loader')->getByID($productID);

		$mapper = $this->get('product.page_mapper.simple');
		$pages = $mapper->getPagesForProduct($product);

		$form = $this->_getForm();
		$form = $this->createForm($form, null, ['product' => $product, 'method' => 'post']);

		return $this->render('Message:Mothership:Ecommerce::product:page-list', [
			'product' => $product,
			'pages'   => $pages,
			'form'    => $form,
		]);
	}

	/**
	 * Creates the product page
	 */
	public function action($productID)
	{
		$product = $this->get('product.loader')->getByID($productID);

		$form = $this->createForm($this->_getForm(), null, ['product' => $product]);
		$form->handleRequest();

		if ($form->isValid()) {
			$data = $form->getData();

			$pageCreate = $this->get('product.page.create');

			if ($data['product'] !== $productID) {
				$this->addFlash('error', 'Field product ID does not match that given in the request URI');
				return $this->redirectToReferer();
			}

			$product = $this->get('product.loader')->getByID($productID);

			$units = $product->getUnits();
			$unit = null;
			foreach($units as $testUnit) {
				if ($testUnit->hasOption($data['option_name']) && $testUnit->getOption($data['option_name']) === $data['option_value']) {
					$unit = $testUnit;
					break;
				}
			}

			if ($unit === null) {
				$this->addFlash('error', 'No units matching the given variants found');
				return $this->redirectToReferer();
			}

			$page = $pageCreate->create($product, [
				Options::PARENT => $data['parent'],
				Options::LISTING_TYPE => OPTIONS::SHOP,
				Options::PAGE_VARIANTS => $data['option_name'],
			], $unit, $data['option_name']);

			if(!$page) {
				$this->addFlash('error', 'Product page could not be created. Does a page for this product already exist?');
				return $this->redirectToReferer();
			} else {
				return $this->redirectToRoute('ms.cp.cms.edit', [
					'pageID' => $page->id,
				]);
			}
		}

		return $this->redirectToReferer();
	}

	private function _getForm()
	{
		return $this->get('product.form.product_page_create');
	}
}