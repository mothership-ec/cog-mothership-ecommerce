<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1447926198_PaymentGateway extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE
				`payment_gateway`
				(
					`payment_id` INT(11) NOT NULL,
					`gateway` VARCHAR(255) NOT NULL,
					PRIMARY KEY (`payment_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	}

	public function down()
	{
		$this->run("DROP TABLE IF EXISTS `payment_gateway`;");
	}
}