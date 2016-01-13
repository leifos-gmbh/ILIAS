<#1>
<?php

if(!$ilDB->tableColumnExists('booking_settings','rsv_filter_period'))
{
	$ilDB->addTableColumn('booking_settings', 'rsv_filter_period', array(
		'type' => 'integer',
		'length' => 2,
		'notnull' => false,
		'default' => null
	));
}

?>

