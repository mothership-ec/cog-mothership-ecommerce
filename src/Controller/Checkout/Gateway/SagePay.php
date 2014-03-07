<?php

class SagePay implements Filter\GatewayIsActiveFilter
{
	public function submit(PayableInterface $payable)
	{
		// send the stuff to sagepay, include callback URI
		// redirect them to the url sagepay rturn
	}

	public function callback()
	{
		// validate the request
		// create the order
		// redirect the customer to the confirmation page
	}

	public function getGatewayName()
	{
		return 'sagepay';
	}
}