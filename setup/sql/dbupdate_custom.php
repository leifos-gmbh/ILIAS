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
<#2>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#3>
<?php

if (!$ilDB->tableColumnExists('media_item', 'upload_hash'))
{
	$ilDB->addTableColumn('media_item', 'upload_hash', array(
		"type" => "text",
		"length" => 100
	));
}

?>