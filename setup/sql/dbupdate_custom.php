<#1>
<?php
if(!$ilDB->tableColumnExists('skl_tree_node','description'))
{
	$ilDB->addTableColumn(
		'skl_tree_node',
		'description',
		array(
			'type' 		=> 'clob',
			'notnull'	=> false
		)
	);
}
?>
<#2>
<?php
if(!$ilDB->tableExists('skl_profile_role'))
{
	$fields = array (
		'profile_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'role_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true)
	);
	$ilDB->createTable('skl_profile_role', $fields);
	$ilDB->addPrimaryKey('skl_profile_role', array('profile_id', 'role_id'));
}
?>