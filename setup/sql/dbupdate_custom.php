<#1>
<?php
if(!$ilDB->tableColumnExists('exc_assignment', 'fb_date_custom'))
{
	$ilDB->addTableColumn(
		'exc_assignment',
		'fb_date_custom',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		));
}
?>