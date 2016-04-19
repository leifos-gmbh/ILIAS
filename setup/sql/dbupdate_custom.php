<#1>
<?php

if(!$ilDB->tableColumnExists("ldap_server_settings", "username_filter"))
{
	$ilDB->addTableColumn("ldap_server_settings", "username_filter", array(
		'type' => 'text',
		'length' => 255,
	));
}
?>
<#2>
<?php
$query = "SELECT max(server_id) id FROM ldap_server_settings";
$res = $ilDB->query($query);
$set = $res->fetchRow(DB_FETCHMODE_OBJECT);

if(!$set->id)
{
	$set->id = 1;
}

$query = "UPDATE ldap_role_assignments ".
	"SET server_id = ".$set->id.
	" WHERE server_id = 0";
$this->db->manipulate($query);
?>