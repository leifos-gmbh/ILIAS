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