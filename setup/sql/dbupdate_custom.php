<#1>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#2>
<?php
//
?>
<#3>
<?php
if(!$ilDB->tableColumnExists('itgr_data','behaviour'))
{
	$ilDB->addTableColumn(
		'itgr_data',
		'behaviour',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		)
	);
}
?>