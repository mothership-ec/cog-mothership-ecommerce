<?php

namespace Message\Mothership\Ecommerce\Controller\ProductPage;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\Ecommerce\Form\Product\ProductPagePublish;

/**
 * @author Sam Trangmar-Keates <sam@message.co.uk>
 */
class PageList extends Controller
{
	/**
	 * Show a list of all product pages with form to create a new one.
	 * 
	 * @param  int $productID the product id
	 */
	public function show($productID)
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

	private function _getForm()
	{
		return $this->get('product.form.product_page_create');
	}
}