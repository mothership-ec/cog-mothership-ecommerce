<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1440409241_PageUnitRecord extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE
				product_page_unit_record
				(
					page_id INT(11) NOT NULL,
					product_id INT(11) NOT NULL,
					unit_id INT(11) DEFAULT NULL,
					PRIMARY KEY (page_id, unit_id)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	}

	public function down()
	{
		$this->run("
			DROP TABLE product_page_unit_record
		");
	}
}