<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1400575591_BasketBlobToLongBlob extends Migration
{
	public function up()
	{
		$this->run("ALTER TABLE basket MODIFY contents LONGBLOB;");
	}

	public function down()
	{
		$this->run("ALTER TABLE basket MODIFY contents BLOB;");
	}
}