<?php

namespace Message\Mothership\Ecommerce\Bootstrap;

use Message\Cog\Bootstrap\TasksInterface;
use Message\Mothership\Ecommerce\Task;

class Tasks implements TasksInterface
{
	public function registerTasks($tasks)
	{
		$tasks->add(new Task\CreateProductPages('ecommerce:product_pages:sync'), 'Adds all non-existing product pages');
	}
}