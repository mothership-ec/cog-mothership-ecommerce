<?php

namespace Message\Mothership\Ecommerce\Form\Fulfillment;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Message\Mothership\Commerce\Order\Entity\Dispatch\Loader as DispatchLoader;
use Message\Mothership\Commerce\Shipping\MethodCollection as ShipingMethodCollection;
use Message\Mothership\Commerce\Order\Entity\Dispatch\Dispatch;

/**
 * @author Samuel Trangmar-Keates <sam@message.co.uk>
 * 
 * Form for the pickup stage of fulfillment.
 */
class Pickup extends AbstractType
{
	private $_dispatchLoader;

	public function __construct(DispatchLoader $dispatchLoader)
	{
		$this->_dispatchLoader = $dispatchLoader;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$methods = $options['methods'];

		foreach ($methods as $method) {
			$dispatches = $this->_dispatchLoader->getPostagedUnshipped($method);
			$dispatches = $this->_filterWebDispatches($dispatches);
			$dispatches = array_values($dispatches);

			$builder->add($method->getName(), 'choice', [
				'expanded' => true,
				'multiple' => true,
				'choices'  => $this->_getOrderChoices($dispatches),
			]);
		}
	}

	public function setDefaultOptions(OptionsResolverInterface $options)
	{
		$options->setRequired([
			'methods',
		]);

		$options->setAllowedTypes([
			'methods' => 'Message\\Mothership\\Commerce\\Order\\Entity\\Dispatch\\MethodCollection',
		]);
	}

	public function getName()
	{
		return 'pickup';
	}

	/**
	 * Get array for of orders for form
	 *
	 * @param $orders
	 *
	 * @return array
	 */
	protected function _getOrderChoices($orders)
	{
		$choices = array();
		foreach ($orders as $order) {
			$choices[$order->id] = $order->id;
		}

		return $choices;
	}

	/**
	 * Filter out any dispatches that do not have an order type of 'web'. 
	 * 
	 * @todo In the future it would be good to have this functionality on the dispatch loader.
	 *
	 * @param $dispatches
	 * 
	 * @return array
	 */
	protected function _filterWebDispatches($dispatches)
	{
		$webDispatches = [];

		foreach ($dispatches as $key => $dispatch) {
			if ($dispatch instanceof Dispatch && $dispatch->order && $dispatch->order->type =='web') {
				$webDispatches[$key] = $dispatch;
			}
		}

		return $webDispatches;
	}
}