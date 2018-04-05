<#1>
<?php
if (!$ilDB->tableExists('like_data'))
{
	$ilDB->createTable('like_data', array(
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'obj_type' => array(
			'type' => 'text',
			'length' => 40,
			'notnull' => true
		),
		'sub_obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_obj_type' => array(
			'type' => 'text',
			'length' => 40,
			'notnull' => true
		),
		'news_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'like_type' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('like_data',array('user_id','obj_id','obj_type','sub_obj_id','sub_obj_type','news_id','like_type'));

	$ilDB->addIndex('like_data',array('obj_id'),'i1');
}
?>
