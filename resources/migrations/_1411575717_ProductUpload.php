<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1411575717_ProductUpload extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE
				product_page_upload_record
				(
					page_id INT(11) NOT NULL,
					product_id INT(11) DEFAULT NULL,
					unit_id INT(11) DEFAULT NULL,
					confirmed_at INT(11),
					confirmed_by INT(11),
					PRIMARY KEY (page_id)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	}

	public function down()
	{
		$this->run("
			DROP TABLE product_page_upload_record
		");
	}
}