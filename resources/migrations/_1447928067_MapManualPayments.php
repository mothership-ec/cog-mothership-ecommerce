<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1447928067_MapManualPayments extends Migration
{
	public function up()
	{
		$this->run("INSERT INTO `payment_gateway` (
				SELECT `payment_id`, 'zero-payment' as `gateway`
				FROM `payment`
				WHERE `method` = 'manual' 
			);");
	}

	public function down()
	{
		$this->run("DELETE FROM `payment_gateway` WHERE `gateway` = 'zero-payment';");
	}
}