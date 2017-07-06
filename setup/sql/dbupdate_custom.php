<#1>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#2>
<?php
if(!$ilDB->tableColumnExists('lm_data','short_title'))
{
	$ilDB->addTableColumn(
		'lm_data',
		'short_title',
		array(
			'type' => 'text',
			'length' => 200,
			'default' => ''
		));
}
?>
<#3>
<?php
if(!$ilDB->tableColumnExists('lm_data_transl','short_title'))
{
	$ilDB->addTableColumn(
		'lm_data_transl',
		'short_title',
		array(
			'type' => 'text',
			'length' => 200,
			'default' => ''
		));
}
?>
