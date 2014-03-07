<?php

class Stripe implements Filter\GatewayIsActiveFilter
{
	public function submit()
	{
		// render the form and include Stripe.js
		// tell Stripe what the callback URI is
	}



	public function getGatewayName()
	{
		return 'stripe';
	}
}