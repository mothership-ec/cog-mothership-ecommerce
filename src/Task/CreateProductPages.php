<?php

namespace Message\Mothership\Ecommerce\Task;

use Message\Cog\Console\Task\Task;
use Symfony\Component\Console\Input\InputArgument;
use Message\Mothership\Ecommerce\ProductPage\Options;
/**
 * @author Samuel Trangmar-Keates
 * 
 * This class creates product pages for all products in the system.
 * arguments:
 *   - defining_variant: unit variant on which to create separate pages, for example to create 
 *     product pages by colour this would be 'colour'
 *   - parent_page: the id of the page into which to insert all the product pages under
 *   - listing_type: the list page type, grouping by category
 */
class CreateProductPages extends Task
{
	protected function configure()
	{
		$this
			->addArgument(
				'defining_variant',
				InputArgument::OPTIONAL,
				'The name of a defining variant'
			)->addArgument(
				'parent_page',
				InputArgument::OPTIONAL,
				'The id of the parent page'
			)->addArgument(
				'listing_type',
				InputArgument::OPTIONAL,
				'The listing category'
			);
	}

	public function process()
	{
		$productLoader = $this->get('product.loader');
		$pageCreate    = $this->get('product.page.create');
		$pageExists    = $this->get('product.page.exists');

		$products = $productLoader->getAll();
		$definingVariant = $this->getRawInput()->getArgument('defining_variant');

		foreach ($products as $product) {
			foreach ($product->getUnits() as $unit) {
				if ($unit->hasOption($definingVariant) && !$pageExists->exists($product, $definingVariant, $unit->getOption($definingVariant))) {
					$pageCreate->create($product, [
							Options::PARENT => $this->getRawInput()->getArgument('parent_page') ?: null,
							Options::PAGE_VARIANTS => [
								$definingVariant => $unit->getOption($definingVariant)
							],
							Options::LISTING_TYPE => $this->getRawInput()->getArgument('listing_type') ?: Create::INDIVIDUAL,
						], $unit, $definingVariant);
				}
			}
		}
	}
}
