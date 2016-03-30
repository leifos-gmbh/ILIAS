<#1>
<?php
if(!$ilDB->tableColumnExists('crs_settings', 'min_onlinetime'))
{
	$ilDB->addTableColumn('crs_settings', 'min_onlinetime', array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0)
	);
}
?>
