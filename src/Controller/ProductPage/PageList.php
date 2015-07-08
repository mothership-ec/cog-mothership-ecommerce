<?php

namespace Message\Mothership\Ecommerce\Controller\ProductPage;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\Ecommerce\Form\Product\ProductPagePublish;

class PageList extends Controller
{
	public function show($productID)
	{
		$product = $this->get('product.loader')->getByID($productID);

		$mapper = $this->get('product.page_mapper.simple');
		$pages = $mapper->getPagesForProduct($product);

		return $this->render('Message:Mothership:Ecommerce::product:page-list', [
			'product' => $product,
			'pages'   => $pages,
		]);
	}
}