<?php

namespace Message\Mothership\Ecommerce\Controller\ProductPage;

use Message\Cog\Controller\Controller;

class Upload extends Controller
{
	public function confirm()
	{
		$records = $this->get('product.page.upload_record.loader')->getUnconfirmed();

		return $this->render('Message:Mothership:Ecommerce::product:upload', [
			'records' => $records,
		]);
	}
}