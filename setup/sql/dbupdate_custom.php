<#1>
<?php

// get tst type id
$row = $ilDB->fetchAssoc($ilDB->queryF(
	"SELECT obj_id FROM object_data WHERE type = %s AND title = %s",
	array('text', 'text'), array('typ', 'pdts')
));
$pdts_id = $row['obj_id'];

// register new 'object' rbac operation for tst
$op_id = $ilDB->nextId('rbac_operations');
$ilDB->insert('rbac_operations', array(
	'ops_id' => array('integer', $op_id),
	'operation' => array('text', 'change_presentation'),
	'description' => array('text', 'change presentation of a view'),
	'class' => array('text', 'object'),
	'op_order' => array('integer', 200)
));
$ilDB->insert('rbac_ta', array(
	'typ_id' => array('integer', $pdts_id),
	'ops_id' => array('integer', $op_id)
));

?>