<?php

interface GatewayInterface
{
	public function getPaymentUri();

	public function getName();

	public function refund(PayableInterface $payable);
}

class SagePay\Gateway implements GatewayInterface
{
	public function getPaymentController()
	{
		//return 'epos.';
		return 'Message:Mothership:Ecommerce::Controller:Gateway:Sagepay#submit';
	}

	public function getName()
	{
		return 'sagepay';
	}
}

class SagePay\PaymentMethod implements MethodInterface
{
	public function getName()
	{
		return 'sagepay';
	}
}

// /checkout/payment/sagepay
// /payment/sagepay