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