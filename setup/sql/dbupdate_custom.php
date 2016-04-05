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
<#2>
<?php
if(!$ilDB->tableColumnExists('tst_tests', 'is_certificate_test'))
{
	$ilDB->addTableColumn('tst_tests', 'is_certificate_test', array(
					'type'    => 'integer',
					'length'  => 1,
					'notnull' => true,
					'default' => 0)
	);
}
?>
<#3>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4>
<?php
if (!$ilDB->tableExists('user_course_email'))
{
	$ilDB->createTable('user_course_email', array(
			'user_id' => array(
					'type' => 'integer',
					'length' => 4,
					'notnull' => true,
					'default' => 0
			),
			'course_id' => array(
					'type' => 'integer',
					'length' => 4,
					'notnull' => true,
					'default' => 0
			)
	));
	$ilDB->addPrimaryKey("user_course_email", array("user_id", "course_id"));
}
?>