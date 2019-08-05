<#1>
<?php

if(!$ilDB->tableColumnExists('object_reference', 'deleted_by') )
{
	$ilDB->addTableColumn('object_reference', 'deleted_by',
		[
			'type' => 'integer',
			'notnull' => false,
			'length' => 4,
			'default' => 0
		]
	);
}
?>