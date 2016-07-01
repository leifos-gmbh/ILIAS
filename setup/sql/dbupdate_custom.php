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

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('grp');
if($type_id)
{
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('news_add_news', 'Add News', 'object', 2100);
	if($new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
	}
}
?>