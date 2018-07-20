<#1>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#2>
<?php

if (!$ilDB->tableColumnExists('media_item', 'upload_hash'))
{
	$ilDB->addTableColumn('media_item', 'upload_hash', array(
		"type" => "text",
		"length" => 100
	));
}

?>
