<#1>
<?php
	//
?>
<#2>
<?php
	//
?>
<#3>
<?php
	//
?>
<#4>
<?php
	//
?>
<#5>
<?php
	//
?>
<#6>
<?php
if(!$ilDB->tableColumnExists('obj_members','contact'))
{
	$ilDB->addTableColumn(
		'obj_members',
		'contact',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		));
}
?>