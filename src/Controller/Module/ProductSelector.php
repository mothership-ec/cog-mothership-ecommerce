<?php

namespace Message\Mothership\Ecommerce\Controller\Module;

use Message\Mothership\Commerce\Events;
use Message\Mothership\Commerce\Event;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Order;

use Message\Mothership\Commerce\Field\ProductUnitInStockOnlyChoiceType;

use Message\Mothership\CMS\Page\Content;

use Message\Cog\Controller\Controller;
use Message\Mothership\Commerce\Event\CurrencyChangeEvent;

class ProductSelector extends Controller
{
	protected $_availableUnits = array();

	/**
	 * Displays the product selector
	 * 
	 * @param  Product $product             Product for which to show the selector
	 * @param  array   $options             Product options
	 * @param  boolean $showVariablePricing Whether to show the price of the unit next
	 *                                      to its name in the dropdown, if unit prices
	 *                                      differ from product price.
	 *                                      
	 * @return Message\Cog\HTTP\Response    Response object
	 */
	public function index(Product $product, array $options = [], $showVariablePricing = false)
	{
		$options  = array_filter($options);
		$units    = $this->_getAvailableUnits($product, $options);
		$oosUnits = $this->_getOutOfStockUnits($units);

		if (count($units) === count($oosUnits)) {
			return $this->render('Message:Mothership:Ecommerce::module:product-selector-oos', array(
				'product' => $product,
				'units'   => $units,
			));
		}

		return $this->render('Message:Mothership:Ecommerce::module:product-selector', array(
			'product'     => $product,
			'units'       => $units,
			'form'        => $this->_getForm($product, $units, key($options), array_keys($oosUnits)),
		));
	}

	public function process($productID)
	{
		$product = $this->get('product.unit.loader')->getByID($productID);
		$units   = $product->getUnits();

		$form->handleRequest();

		if ($form->isValid()) {de();
			$data = $form->getData();
			$basket   = $this->get('basket');
			$unit     = $product->getUnit($data['unit_id']);
			$quantity = $data['quantity'];
			de($quantity);

			$item->order         = $basket->getOrder();
			$item->stockLocation = $this->get('stock.locations')->get('web');
			$item->populate($unit);

			$item = $this->get('event.dispatcher')->dispatch(
				Events::PRODUCT_SELECTOR_PROCESS,
				new Event\ProductSelectorProcessEvent($form, $product, $item)
			)->getItem();

			if ($basket->addItem($item)) {
				$this->addFlash('success', 'The item has been added to your basket');
			} else {
				$this->addFlash('error', 'The item could not be added to your basket');
			}
		}

		return $this->redirectToReferer();
	}

	protected function _getForm(Product $product, array $units, $variantKey = null, $outOfStock = [])
	{
		$form  = $this->get('shop.form.product-selector');

		$form = $this->createForm($form, null, [
			'units'       => $units,
			'location'    => $this->get('stock.locations')->get('web'),
			'variant_key' => $variantKey,
			'show_out_of_stock'  => true,
			'out_of_stock_units' => $outOfStock,
			'action'      => $this->generateUrl('ms.commerce.product.add.basket', ['productID' => $product->id]),
		]);

		return $form;
	}

	protected function _getAvailableUnits(Product $product, array $options = [])
	{
		$key = md5(serialize(array($product->id, $options)));

		if (!array_key_exists($key, $this->_availableUnits)) {
			$this->_availableUnits[$key] = [];

			foreach ($product->getVisibleUnits() as $unit) {
				// Skip units that don't meet the options criteria, if set
				if ($options
				 && $options !== array_intersect_assoc($options, $unit->options)) {
					continue;
				}

				$this->_availableUnits[$key][$unit->id] = $unit;
			}
		}

		return $this->_availableUnits[$key];
	}

	protected function _getOutOfStockUnits(array $units)
	{
		$return = [];
		$locs   = $this->get('stock.locations');

		foreach ($units as $key => $unit) {
			if (!($unit instanceof Unit)) {
				throw new \InvalidArgumentException('Expected instance of Product\Unit\Unit');
			}

			if (1 > $unit->getStockForLocation($locs->getRoleLocation($locs::SELL_ROLE))) {
				$return[$key] = $unit;
			}
		}

		return $return;
	}
}