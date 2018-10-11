<#1>
<?php
if(!$ilDB->tableColumnExists("il_object_def", "offline_handling"))
{
	$def = array(
		'type'    => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	);
	$ilDB->addTableColumn("il_object_def", "offline_handling", $def);
}
?>
<#2>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#3>
<?php
if(!$ilDB->tableColumnExists('object_data', 'offline'))
{
	$def = [
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	];
	$ilDB->addTableColumn('object_data', 'offline', $def);
}
?>

<#4>
<?php

// migration of course offline status
$query = 'update object_data od set offline = '.
	'(select if( activation_type = 0,1,0) from crs_settings '.
	'where obj_id = od.obj_id) where type = '.$ilDB->quote('crs','text');
$ilDB->manipulate($query);
?>

<#5>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#6>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#7>
<?php

// migration of lm offline status
$query = 'update object_data od set offline = '.
	'(select if( is_online = '.$ilDB->quote('n','text').',1,0) from content_object '.
	'where id = od.obj_id) where type = '.$ilDB->quote('lm','text');
$ilDB->manipulate($query);

?>
<#8>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#9>
<?php

// migration of lm offline status
$query = 'update object_data od set offline = '.
	'(select if( is_online = '.$ilDB->quote('n','text').',1,0) from file_based_lm '.
	'where id = od.obj_id) where type = '.$ilDB->quote('htlm','text');
$ilDB->manipulate($query);

?>

<#10>
<?php

// migration of svy offline status
$query = 'update object_data od set offline = '.
	'(select if( status = 0,1,0) from svy_svy '.
	'where obj_fi = od.obj_id) where type = '.$ilDB->quote('svy','text');
$ilDB->manipulate($query);
?>

<#11>
<?php

// migration of svy offline status
$query = 'update object_data od set offline = '.
	'(select if( online_status = 0,1,0) from tst_tests '.
	'where obj_fi = od.obj_id) where type = '.$ilDB->quote('tst','text');
$ilDB->manipulate($query);
?>


<#12>
<?php
$ilCtrlStructureReader->getStructure();
?>

