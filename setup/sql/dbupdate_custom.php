<#1>
<?php

if(!$ilDB->tableColumnExists('svy_svy','confirmation_mail')) 
{
    $ilDB->addTableColumn(
        'svy_svy',
        'confirmation_mail',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => null
        ));
}

?>
<#2>
<?php

$ilDB->manipulate("UPDATE svy_svy".	
	" SET confirmation_mail = ".$ilDB->quote(1, "integer").
	" WHERE own_results_mail = ".$ilDB->quote(1, "integer").
	" AND confirmation_mail IS NULL");

?>
<#3>
<?php

if(!$ilDB->tableColumnExists('svy_svy','anon_user_list')) 
{
    $ilDB->addTableColumn(
        'svy_svy',
        'anon_user_list',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 0
        ));
}

?>