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
<#6>
<?php
//
?>
<#7>
<?php
//
?>
<#8>
<?php
//
?>
<#9>
<?php
//
?>
<#10>
<?php
if(!$ilDB->tableColumnExists('il_news_item', 'content_html')) {
	$ilDB->addTableColumn('il_news_item', 'content_html',
		array(
			"type"    => "integer",
			"notnull" => true,
			"length"  => 1,
			"default" => 0
		)
	);
}
?>
<#11>
<?php
if(!$ilDB->tableColumnExists('il_news_item', 'update_user_id')) {
	$ilDB->addTableColumn('il_news_item', 'update_user_id',
		array(
			"type"    => "integer",
			"notnull" => true,
			"length"  => 4,
			"default" => 0
		)
	);
}
?>
<#12>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('crs');
if($type_id)
{
	$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("news_add_news");
	if($ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($type_id, $ops_id);
	}
}
?>