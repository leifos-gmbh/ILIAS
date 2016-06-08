<#1>
<?php
if(!$ilDB->tableExists('uzk_instance_stats'))
{
	$ilDB->createTable('uzk_instance_stats', array(
		'keyword' => array(
			'type' => 'text',
			'length' => '100',
			'notnull' => false,
			'default' => null
		),
		'value' => array(
			'type' => 'text',
			'length' => 4000,
			'notnull' => false,
			'default' => null
		)
	));
	$ilDB->addPrimaryKey('uzk_instance_stats', array('keyword'));
}
?>
<#2>
<?php
if(!$ilDB->indexExistsByFields('conditions', array('target_ref_id')))
{
	$ilDB->addIndex('conditions', array('target_ref_id'), 'c1');
}
?>
<#3>
<?php
	include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
	ilDBUpdateNewObjectType::addAdminNode('pdfg', 'PDFGeneration');
?>
<#4>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#5>
<?php

if(!$ilDB->tableExists('exc_idl'))
{
	$ilDB->createTable('exc_idl', array(
		'ass_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),	
		'member_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'is_team' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),	
		'tstamp' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('exc_idl', array('ass_id', 'member_id', 'is_team'));
}

?>