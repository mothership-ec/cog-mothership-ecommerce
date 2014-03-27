<?php

namespace Message\Mothership\Ecommerce\Gateway;

use Symfony\Component\HttpFoundation\JsonResponse;
use Message\Mothership\Commerce\Payable\PayableInterface;
use Message\Mothership\Commerce\Order\Entity\Payment\Method\MethodInterface;

/**
 * Interface for complete controllers.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
interface CompleteControllerInterface
{
	/**
	 * Complete a payable.
	 *
	 * @param  PayableInterface $payable
	 * @param  MethodInterface  $method
	 * @return
	 */
	public function complete(PayableInterface $payable, MethodInterface $method);
}