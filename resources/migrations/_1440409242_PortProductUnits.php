<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1440409242_PortProductUnits extends Migration
{
	public function up()
	{
		// Get units with options assigned
		$this->run("
			REPLACE INTO
				product_page_unit_record
				(
					page_id,
					product_id,
					unit_id
				)
			SELECT DISTINCT
					page_content.page_id,
					product_unit.product_id,
					product_unit.unit_id
			FROM
				page_content
			JOIN
				product_unit
			ON
				(
					page_content.value_int = product_unit.product_id AND
					page_content.group_name = 'product' AND
					page_content.field_name = 'product'

				)
 			LEFT JOIN
				product_unit_option
			USING
				(unit_id)
 			JOIN
				(
					SELECT
						page_id,
						value_string AS option_value
					FROM
						page_content
					WHERE
						group_name = 'product'
					AND
						field_name = 'option'
					AND
						data_name = 'value'
				) AS content_option_value
			ON
				(
					product_unit_option.option_value = content_option_value.option_value AND
					page_content.page_id = content_option_value.page_id
				)
			JOIN
				(
					SELECT
						page_id,
						value_string AS option_name
					FROM
						page_content
					WHERE
						group_name = 'product'
					AND
						field_name = 'option'
					AND
						data_name = 'name'
				) AS content_option_name
			ON
				(
					product_unit_option.option_name = content_option_name.option_name AND
					page_content.page_id = content_option_name.page_id
				)
		");

		// Port units without options assigned
		$this->run("
			REPLACE INTO
				product_page_unit_record
				(
					page_id,
					product_id,
					unit_id
				)
			SELECT DISTINCT
				page_content.page_id,
				product_unit.product_id,
				product_unit.unit_id
			FROM
				page_content
			JOIN
				product_unit
			ON
				(
					page_content.value_int = product_unit.product_id AND
					page_content.group_name = 'product' AND
					page_content.field_name = 'product'

				)
			LEFT JOIN
				(
					SELECT
						page_id,
						value_string AS option_name
					FROM
						page_content
					WHERE
						group_name = 'product'
					AND
						field_name = 'option'
					AND
						data_name = 'name'
				) AS options
			USING
				(page_id)
			WHERE
				option_name IS NULL
			OR
				option_name = ''

		");
	}

	public function down()
	{
		$this->run("TRUNCATE TABLE product_page_unit_record");
	}
}