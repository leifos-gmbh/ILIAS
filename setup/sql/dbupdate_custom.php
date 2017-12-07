<#1>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('read_results', 'Access Results', 'object', 2500);
$type_id = ilDBUpdateNewObjectType::getObjectTypeId('svy');
if($type_id && $new_ops_id)
{
	ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
}
?>

<#2>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('read_results');
ilDBUpdateNewObjectType::cloneOperation('svy', $src_ops_id, $tgt_ops_id);
?>