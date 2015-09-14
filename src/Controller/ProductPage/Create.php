<?php

namespace Message\Mothership\Ecommerce\Controller\ProductPage;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\Ecommerce\Form\Product\ProductPagePublish;
use Message\Mothership\Ecommerce\ProductPage\Options;
use Message\Mothership\Ecommerce\ProductPage\Create as ProductPageCreate;

/**
 * @author Sam Trangmar-Keates <sam@message.co.uk>
 */
class Create extends Controller
{
	const DATA_SESSION_NAME  = 'cms.page_create.form';

	/**
	 * Creates the product page
	 */
	public function action($productID)
	{
		$product = $this->get('product.loader')->getByID($productID);

		$form = $this->createForm($this->_getForm(), null, ['product' => $product]);
		$form->handleRequest();

		$data = $form->getData();
		$this->get('http.session')->set(self::DATA_SESSION_NAME, $data);

		if ($form->isValid()) {
			$pageCreate = $this->get('product.page.create')->allowDuplicates();

			if ($data['product'] !== $productID) {
				$this->addFlash('error', 'Field product ID does not match that given in the request URI');
				return $this->redirectToReferer();
			}

			$product = $this->get('product.loader')->getByID($productID);

			$optionName = null;
			$units = $product->getUnits();
			$unit = null;

			// 'none' is annoyingly an unavoidable thing in the LinkedChoice field type. Treating
			// 'none' and empty the same seems like the best behaviour.
			if (!empty($data['option_name']) && $data['option_name'] !== 'none') {
				$optionName = $data['option_name'];

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
			}

			$page = $pageCreate->create($product, [
				Options::PARENT => $data['parent'],
				Options::LISTING_TYPE => OPTIONS::SHOP,
				Options::PAGE_VARIANTS => $optionName ?: ProductPageCreate::INDIVIDUAL,
			], $unit, $optionName);

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