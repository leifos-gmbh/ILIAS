<#1>
<?php
//step 1/4 ecs_part_settings search for dublicates and store it in ecs_part_settings_tmp

if ($ilDB->tableExists('ecs_part_settings'))
{
	$res = $ilDB->query("
		SELECT sid, mid
		FROM ecs_part_settings
		GROUP BY sid, mid
		HAVING COUNT(sid) > 1
	");

	if($ilDB->numRows($res))
	{
		if(!$ilDB->tableExists('ecs_part_settings_tmp'))
		{
			$ilDB->createTable('ecs_part_settings_tmp', array(
				'sid' => array(
					'type'  => 'integer',
					'length'=> 8,
					'notnull' => true,
					'default' => 0
				),
				'mid' => array(
					'type'  => 'integer',
					'length'=> 8,
					'notnull' => true,
					'default' => 0
				)
			));
			$ilDB->addPrimaryKey('ecs_part_settings_tmp', array('sid','mid'));
		}

		while($row = $ilDB->fetchAssoc($res))
		{
			$ilDB->replace('ecs_part_settings_tmp', array(), array(
				'sid' => array('integer', $row['sid']),
				'mid' => array('integer', $row['mid'])
			));
		}
	}
}
?>
<#2>
<?php
//step 2/4 ecs_part_settings deletes dublicates stored in ecs_part_settings_tmp

if ($ilDB->tableExists('ecs_part_settings_tmp'))
{
	$res = $ilDB->query("
	SELECT sid, mid
	FROM ecs_part_settings_tmp
");

	while($row = $ilDB->fetchAssoc($res))
	{
		$res_data = $ilDB->query("
			SELECT *
			FROM ecs_part_settings
			WHERE
			sid = ".$ilDB->quote($row['sid'] ,'integer')." AND
			mid = ".$ilDB->quote($row['mid'] ,'integer')
		);
		$data = $ilDB->fetchAssoc($res_data);

		$ilDB->manipulate("DELETE FROM ecs_part_settings WHERE".
			" sid = " . $ilDB->quote($row['sid'] ,'integer').
			" AND mid = " . $ilDB->quote($row['mid'] ,'integer')
		);

		$ilDB->manipulate("INSERT INTO ecs_part_settings (sid, mid, export, import, import_type, title, cname, token, export_types, import_types, dtoken) ".
			"VALUES ( ".
			$ilDB->quote($data['sid'] ,'integer').', '.
			$ilDB->quote($data['mid'] ,'integer').', '.
			$ilDB->quote($data['export'] ,'integer').', '.
			$ilDB->quote($data['import'] ,'integer').', '.
			$ilDB->quote($data['import_type'] ,'integer').', '.
			$ilDB->quote($data['title'] ,'text').', '.
			$ilDB->quote($data['cname'] ,'text').', '.
			$ilDB->quote($data['token'] ,'integer').', '.
			$ilDB->quote($data['export_types'] ,'text').', '.
			$ilDB->quote($data['import_types'] ,'text').', '.
			$ilDB->quote($data['dtoken'] ,'integer').
			")");

		$ilDB->manipulate("DELETE FROM ecs_part_settings_tmp WHERE".
			" sid = " . $ilDB->quote($row['sid'] ,'integer').
			" AND mid = " . $ilDB->quote($row['mid'] ,'integer')
		);
	}
}
?>
<#3>
<?php
//step 3/4 ecs_part_settings adding primary key

if($ilDB->tableExists('ecs_part_settings'))
{
	$ilDB->addPrimaryKey('ecs_part_settings', array('sid', 'mid'));
}
?>
<#4>
<?php
//step 4/4 ecs_part_settings removes temp table

if ($ilDB->tableExists('ecs_part_settings_tmp'))
{
	$ilDB->dropTable('ecs_part_settings_tmp');
}
?>
<#5>
<?php
//step 1/1 feedback_results removes table

if ($ilDB->tableExists('feedback_results'))
{
	$ilDB->dropTable('feedback_results');
}
if ($ilDB->tableExists('feedback_items'))
{
	$ilDB->dropTable('feedback_items');
}
?>
<#6>
<?php
//step 1/4 il_exc_team_log renames old table

if ($ilDB->tableExists('il_exc_team_log') && !$ilDB->tableExists('exc_team_log_old'))
{
	$ilDB->renameTable("il_exc_team_log", "exc_team_log_old");
}
?>
<#7>
<?php
//step 2/4 il_exc_team_log creates new table with unique id and sequenz

if (!$ilDB->tableExists('il_exc_team_log'))
{
	$ilDB->createTable('il_exc_team_log',array(
		'log_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'team_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'details' => array(
			'type' => 'text',
			'length' => 500,
			'notnull' => false
		),
		'action' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true
		),
		'tstamp' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		)
	));
	$ilDB->addPrimaryKey('il_exc_team_log', array('log_id'));
	$ilDB->addIndex('il_exc_team_log',array('team_id'),'i1');
	$ilDB->createSequence('il_exc_team_log');
}
?>
<#8>
<?php
//step 3/4 il_exc_team_log moves all data to new table

if ($ilDB->tableExists('il_exc_team_log') && $ilDB->tableExists('exc_team_log_old'))
{
	$res = $ilDB->query("
		SELECT *
		FROM exc_team_log_old
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$id = $ilDB->nextId('il_exc_team_log');

		$ilDB->manipulate("INSERT INTO il_exc_team_log (log_id, team_id, user_id, details, action, tstamp)".
			" VALUES (".
			$ilDB->quote($id, "integer").
			",".$ilDB->quote($row['team_id'], "integer").
			",".$ilDB->quote($row['user_id'], "integer").
			",".$ilDB->quote($row['details'], "text").
			",".$ilDB->quote($row['action'], "integer").
			",".$ilDB->quote($row['tstamp'], "integer").
			")"
		);

		$ilDB->manipulateF(
			"DELETE FROM exc_team_log_old WHERE team_id = %s AND user_id = %s AND action = %s AND tstamp = %s",
			array('integer', 'integer', 'integer', 'integer'),
			array($row['team_id'], $row['user_id'], $row['action'], $row['tstamp'])
		);
	}
}
?>
<#9>
<?php
//step 4/4 il_exc_team_log removes old table

if ($ilDB->tableExists('exc_team_log_old'))
{
	$ilDB->dropTable('exc_team_log_old');
}
?>
<#10>
<?php
//step 1/1 il_log removes old table

if ($ilDB->tableExists('il_log'))
{
	$ilDB->dropTable('il_log');
}
?>
<#11>
<?php
//step 1/5 il_verification removes dublicates

if ($ilDB->tableExists('il_verification'))
{
	$res = $ilDB->query("
		SELECT id
		FROM il_verification
		GROUP BY id
		HAVING COUNT(id) > 1
	");

	if($ilDB->numRows($res))
	{
		if(!$ilDB->tableExists('il_verification_tmp'))
		{
			$ilDB->createTable('il_verification_tmp', array(
				'id' => array(
					'type'  => 'integer',
					'length'=> 8,
					'notnull' => true,
					'default' => 0
				)
			));
			$ilDB->addPrimaryKey('il_verification_tmp', array('id'));
		}

		while($row = $ilDB->fetchAssoc($res))
		{
			$ilDB->replace('il_verification_tmp', array(), array(
				'id' => array('integer', $row['id'])
			));
		}
	}
}
?>
<#12>
<?php
//step 2/5 il_verification deletes dublicates stored in il_verification_tmp

if ($ilDB->tableExists('il_verification_tmp'))
{
	$res = $ilDB->query("
		SELECT id
		FROM il_verification_tmp
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$res_data = $ilDB->query("
			SELECT *
			FROM il_verification
			WHERE
			id = ".$ilDB->quote($row['id'] ,'integer')
		);
		$data = $ilDB->fetchAssoc($res_data);

		$ilDB->manipulate("DELETE FROM il_verification WHERE".
			" id = " . $ilDB->quote($row['id'] ,'integer')
		);

		$ilDB->manipulate("INSERT INTO il_verification (id, type, parameters, raw_data) ".
			"VALUES ( ".
			$ilDB->quote($data['id'] ,'integer').', '.
			$ilDB->quote($data['type'] ,'text').', '.
			$ilDB->quote($data['parameters'] ,'text').', '.
			$ilDB->quote($data['raw_data'] ,'text').
			")");

		$ilDB->manipulate("DELETE FROM il_verification_tmp WHERE".
			" id = " . $ilDB->quote($row['id'] ,'integer')
		);
	}
}
?>
<#13>
<?php
//step 3/5 il_verification drops not used indexes

if( $ilDB->indexExistsByFields('il_verification', array('id')) )
{
	$ilDB->dropIndexByFields('il_verification', array('id'));
}
?>
<#14>
<?php
//step 4/5 il_verification adding primary key

if($ilDB->tableExists('il_verification'))
{
	$ilDB->addPrimaryKey('il_verification', array('id'));
}
?>
<#15>
<?php
//step 5/5 il_verification removes temp table

if ($ilDB->tableExists('il_verification_tmp'))
{
	$ilDB->dropTable('il_verification_tmp');
}
?>
<#16>
<?php
//step 1/4 il_wiki_imp_pages removes dublicates

if ($ilDB->tableExists('il_wiki_imp_pages'))
{
	$res = $ilDB->query("
		SELECT wiki_id, page_id
		FROM il_wiki_imp_pages
		GROUP BY wiki_id, page_id
		HAVING COUNT(wiki_id) > 1
	");

	if($ilDB->numRows($res))
	{
		if(!$ilDB->tableExists('wiki_imp_pages_tmp'))
		{
			$ilDB->createTable('wiki_imp_pages_tmp', array(
				'wiki_id' => array(
					'type'  => 'integer',
					'length'=> 8,
					'notnull' => true,
					'default' => 0
				),
				'page_id' => array(
					'type'  => 'integer',
					'length'=> 8,
					'notnull' => true,
					'default' => 0
				)
			));
			$ilDB->addPrimaryKey('wiki_imp_pages_tmp', array('wiki_id','page_id'));
		}

		while($row = $ilDB->fetchAssoc($res))
		{
			$ilDB->replace('wiki_imp_pages_tmp', array(), array(
				'wiki_id' => array('integer', $row['wiki_id']),
				'page_id' => array('integer', $row['page_id'])
			));
		}
	}
}
?>
<#17>
<?php
//step 2/4 il_wiki_imp_pages deletes dublicates stored in wiki_imp_pages_tmp

if ($ilDB->tableExists('wiki_imp_pages_tmp'))
{
	$res = $ilDB->query("
		SELECT wiki_id, page_id
		FROM wiki_imp_pages_tmp
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$res_data = $ilDB->query("
			SELECT *
			FROM il_wiki_imp_pages
			WHERE
			wiki_id = ".$ilDB->quote($row['wiki_id'] ,'integer')." AND
			page_id = ".$ilDB->quote($row['page_id'] ,'integer')
		);
		$data = $ilDB->fetchAssoc($res_data);

		$ilDB->manipulate("DELETE FROM il_wiki_imp_pages WHERE".
			" wiki_id = " . $ilDB->quote($row['wiki_id'] ,'integer').
			" AND page_id = " . $ilDB->quote($row['page_id'] ,'integer')
		);

		$ilDB->manipulate("INSERT INTO il_wiki_imp_pages (wiki_id, ord, indent, page_id) ".
			"VALUES ( ".
			$ilDB->quote($data['wiki_id'] ,'integer').', '.
			$ilDB->quote($data['ord'] ,'integer').', '.
			$ilDB->quote($data['indent'] ,'integer').', '.
			$ilDB->quote($data['page_id'] ,'integer').
			")");

		$ilDB->manipulate("DELETE FROM wiki_imp_pages_tmp WHERE".
			" wiki_id = " . $ilDB->quote($row['wiki_id'] ,'integer').
			" AND page_id = " . $ilDB->quote($row['page_id'] ,'integer')
		);
	}
}
?>
<#18>
<?php
//step 3/4 il_wiki_imp_pages adding primary key

if($ilDB->tableExists('il_wiki_imp_pages'))
{
	$ilDB->addPrimaryKey('il_wiki_imp_pages', array('wiki_id', 'page_id'));
}
?>
<#19>
<?php
//step 4/4 il_wiki_imp_pages removes temp table

if ($ilDB->tableExists('wiki_imp_pages_tmp'))
{
	$ilDB->dropTable('wiki_imp_pages_tmp');
}
?>
<#20>
<?php
//step 1/3 il_wiki_missing_page removes dublicates

if ($ilDB->tableExists('il_wiki_missing_page'))
{
	$res = $ilDB->query("
		SELECT wiki_id, source_id, target_name
		FROM il_wiki_missing_page
		GROUP BY wiki_id, source_id, target_name
		HAVING COUNT(wiki_id) > 1
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$ilDB->manipulate("DELETE FROM il_wiki_missing_page WHERE".
			" wiki_id = " . $ilDB->quote($row['wiki_id'] ,'integer').
			" AND source_id = " . $ilDB->quote($row['source_id'] ,'integer').
			" AND target_name = " . $ilDB->quote($row['target_name'] ,'text')
		);

		$ilDB->manipulate("INSERT INTO il_wiki_missing_page (wiki_id, source_id, target_name) ".
			"VALUES ( ".
			$ilDB->quote($row['wiki_id'] ,'integer').', '.
			$ilDB->quote($row['source_id'] ,'integer').', '.
			$ilDB->quote($row['target_name'] ,'text').
			")");
	}
}
?>
<#21>
<?php
//step 2/3 il_wiki_missing_page drops not used indexes

if( $ilDB->indexExistsByFields('il_wiki_missing_page', array('wiki_id')) )
{
	$ilDB->dropIndexByFields('il_wiki_missing_page', array('wiki_id'));
}
?>
<#22>
<?php
//step 3/3 il_wiki_missing_page adding primary key and removing index
if(! $ilDB->indexExistsByFields('il_wiki_missing_page', array('wiki_id', 'target_name')) )
{
	$ilDB->addIndex('il_wiki_missing_page', array('wiki_id', 'target_name'), 'i1');
}

if($ilDB->tableExists('il_wiki_missing_page'))
{
	$ilDB->addPrimaryKey('il_wiki_missing_page', array('wiki_id', 'source_id', 'target_name'));
}
?>
<#23>
<?php
//step 1/2 lo_access search for dublicates and remove them

if ($ilDB->tableExists('lo_access'))
{
	$res = $ilDB->query("
		SELECT first.timestamp ts, first.usr_id ui, first.lm_id li, first.obj_id oi, first.lm_title lt
		FROM lo_access first
		WHERE EXISTS (
			SELECT second.usr_id, second.lm_id
			FROM lo_access second
			WHERE first.usr_id = second.usr_id AND first.lm_id = second.lm_id
			GROUP BY second.usr_id, second.lm_id
			HAVING COUNT(second.lm_id) > 1
		)
	");
	$data = array();

	while($row = $ilDB->fetchAssoc($res))
	{
		$data[$row['ui'] . '_' . $row['li']][] = $row;
	}


	foreach($data as $rows) {
		$newest = null;

		foreach ($rows as $row) {

			if($newest && ($newest['ts'] == $row['ts'] && $newest['oi'] == $row['oi']))
			{
				$ilDB->manipulate("DELETE FROM lo_access WHERE" .
					" usr_id = " . $ilDB->quote($newest['ui'], 'integer') .
					" AND lm_id = " . $ilDB->quote($newest['li'], 'integer') .
					" AND timestamp = " . $ilDB->quote($newest['ts'], 'date') .
					" AND obj_id = " . $ilDB->quote($newest['oi'], 'integer')
				);

				$ilDB->manipulate("INSERT INTO lo_access (usr_id, lm_id, timestamp, obj_id) ".
					"VALUES ( ".
					$ilDB->quote($row['ui'] ,'integer').', '.
					$ilDB->quote($row['li'] ,'integer').', '.
					$ilDB->quote($row['ts'] ,'date').', '.
					$ilDB->quote($row['oi'] ,'integer').
					")");
			}

			if (!$newest || new DateTime($row["ts"]) > new DateTime($newest["ts"])) {
				$newest = $row;
			}
		}

		$ilDB->manipulate("DELETE FROM lo_access WHERE" .
			" usr_id = " . $ilDB->quote($newest['ui'], 'integer') .
			" AND lm_id = " . $ilDB->quote($newest['li'], 'integer') .
			" AND (timestamp != " . $ilDB->quote($newest['ts'], 'date') .
			" XOR obj_id != " . $ilDB->quote($newest['oi'], 'integer') . ")"
		);
	}
}
?>
<#24>
<?php
//step 2/2 lo_access adding primary key and removing indexes

if( $ilDB->indexExistsByFields('lo_access', array('usr_id')) )
{
	$ilDB->dropIndexByFields('lo_access', array('usr_id'));
}

if($ilDB->tableExists('lo_access'))
{
	$ilDB->addPrimaryKey('lo_access', array('usr_id', 'lm_id'));
}
?>
<#25>
<?php
//step 1/4 obj_stat search for dublicates and store it in obj_stat_tmp

if ($ilDB->tableExists('obj_stat'))
{
	$res = $ilDB->query("
		SELECT obj_id, yyyy, mm, dd, hh
		FROM obj_stat
		GROUP BY obj_id, yyyy, mm, dd, hh
		HAVING COUNT(obj_id) > 1
	");

	if($ilDB->numRows($res))
	{
		if(!$ilDB->tableExists('obj_stat_tmpd'))
		{
			$ilDB->createTable('obj_stat_tmpd', array(
				'obj_id' => array(
					'type'  => 'integer',
					'length'=> 8,
					'notnull' => true,
					'default' => 0
				),
				'yyyy' => array(
					'type'  => 'integer',
					'length'=> 8,
					'notnull' => true,
					'default' => 0
				),
				'mm' => array(
					'type'  => 'integer',
					'length'=> 8,
					'notnull' => true,
					'default' => 0
				),
				'dd' => array(
					'type'  => 'integer',
					'length'=> 8,
					'notnull' => true,
					'default' => 0
				),
				'hh' => array(
					'type'  => 'integer',
					'length'=> 8,
					'notnull' => true,
					'default' => 0
				)
			));
			$ilDB->addPrimaryKey('obj_stat_tmpd', array('obj_id','yyyy','mm','dd','hh'));
		}

		while($row = $ilDB->fetchAssoc($res))
		{
			$ilDB->replace('obj_stat_tmpd', array(), array(
				'obj_id' => array('integer', $row['obj_id']),
				'yyyy' => array('integer', $row['yyyy']),
				'mm' => array('integer', $row['mm']),
				'dd' => array('integer', $row['dd']),
				'hh' => array('integer', $row['hh'])
			));
		}
	}
}
?>
<#26>
<?php
//step 2/4 obj_stat deletes dublicates stored in obj_stat_tmpd

if ($ilDB->tableExists('obj_stat_tmpd'))
{
	$res = $ilDB->query("
		SELECT obj_id, yyyy, mm, dd, hh
		FROM obj_stat_tmpd
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$res_data = $ilDB->query("
			SELECT *
			FROM obj_stat
			WHERE
			obj_id = ".$ilDB->quote($row['obj_id'] ,'integer')." AND
			yyyy = ".$ilDB->quote($row['yyyy'] ,'integer')." AND
			mm = ".$ilDB->quote($row['mm'] ,'integer')." AND
			dd = ".$ilDB->quote($row['dd'] ,'integer')." AND
			hh = ".$ilDB->quote($row['hh'] ,'integer')
		);
		$data = $ilDB->fetchAssoc($res_data);

		$ilDB->manipulate("
			DELETE FROM obj_stat WHERE
			obj_id = ".$ilDB->quote($row['obj_id'] ,'integer')." AND
			yyyy = ".$ilDB->quote($row['yyyy'] ,'integer')." AND
			mm = ".$ilDB->quote($row['mm'] ,'integer')." AND
			dd = ".$ilDB->quote($row['dd'] ,'integer')." AND
			hh = ".$ilDB->quote($row['hh'] ,'integer')
		);

		$ilDB->manipulate("INSERT INTO obj_stat ".
			"(obj_id, obj_type,  yyyy, mm, dd, hh, read_count, childs_read_count, spent_seconds, childs_spent_seconds) ".
			"VALUES ( ".
			$ilDB->quote($data['obj_id'] ,'integer').', '.
			$ilDB->quote($data['obj_type'] ,'text').', '.
			$ilDB->quote($data['yyyy'] ,'integer').', '.
			$ilDB->quote($data['mm'] ,'integer').', '.
			$ilDB->quote($data['dd'] ,'integer').', '.
			$ilDB->quote($data['hh'] ,'integer').', '.
			$ilDB->quote($data['read_count'] ,'integer').', '.
			$ilDB->quote($data['childs_read_count'] ,'integer').', '.
			$ilDB->quote($data['spent_seconds'] ,'integer').', '.
			$ilDB->quote($data['childs_spent_seconds'] ,'integer').
			")");

		$ilDB->manipulate("
			DELETE FROM obj_stat_tmpd WHERE
			obj_id = ".$ilDB->quote($row['obj_id'] ,'integer')." AND
			yyyy = ".$ilDB->quote($row['yyyy'] ,'integer')." AND
			mm = ".$ilDB->quote($row['mm'] ,'integer')." AND
			dd = ".$ilDB->quote($row['dd'] ,'integer')." AND
			hh = ".$ilDB->quote($row['hh'] ,'integer')
		);
	}
}
?>
<#27>
<?php
//step 3/4 obj_stat adding primary key
if( $ilDB->indexExistsByFields('obj_stat', array('obj_id','yyyy','mm')) )
{
	$ilDB->dropIndexByFields('obj_stat', array('obj_id','yyyy','mm'));
}

if( $ilDB->indexExistsByFields('obj_stat', array('obj_id')) )
{
	$ilDB->dropIndexByFields('obj_stat', array('obj_id'));
}

if($ilDB->tableExists('obj_stat'))
{
	$ilDB->addPrimaryKey('obj_stat',  array('obj_id','yyyy','mm','dd','hh'));
}
?>
<#28>
<?php
//step 4/4 obj_stat removes temp table

if ($ilDB->tableExists('obj_stat_tmpd'))
{
	$ilDB->dropTable('obj_stat_tmpd');
}
?>
<#29>
<?php
//step 1/4 obj_stat_log renames old table

if ($ilDB->tableExists('obj_stat_log') && !$ilDB->tableExists('obj_stat_log_old'))
{
	$ilDB->renameTable("obj_stat_log", "obj_stat_log_old");
}
?>
<#30>
<?php
//step 2/4 obj_stat_log creates new table with unique id and sequenz

if (!$ilDB->tableExists('obj_stat_log'))
{
	$ilDB->createTable('obj_stat_log',array(
		'log_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'obj_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true
		),
		'tstamp' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
		'yyyy' => array(
			'type' => 'integer',
			'length' => 2,
			'notnull' => false
		),
		'mm' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		),
		'dd' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		),
		'hh' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		),
		'read_count' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
		'childs_read_count' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
		'spent_seconds' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
		'childs_spent_seconds' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
	));
	$ilDB->addPrimaryKey('obj_stat_log', array('log_id'));
	$ilDB->addIndex('obj_stat_log',array('tstamp'),'i1');
	$ilDB->createSequence('obj_stat_log');
}
?>
<#31>
<?php
//step 3/4 obj_stat_log moves all data to new table

if ($ilDB->tableExists('obj_stat_log') && $ilDB->tableExists('obj_stat_log_old'))
{
	$res = $ilDB->query("
		SELECT *
		FROM obj_stat_log_old
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$id = $ilDB->nextId('obj_stat_log');

		$ilDB->manipulate("INSERT INTO obj_stat_log ".
						  "(log_id, obj_id, obj_type, tstamp,  yyyy, mm, dd, hh, read_count, childs_read_count, spent_seconds, childs_spent_seconds) ".
						  "VALUES ( ".
						  $ilDB->quote($id ,'integer').', '.
						  $ilDB->quote($row['obj_id'] ,'integer').', '.
						  $ilDB->quote($row['obj_type'] ,'text').', '.
						  $ilDB->quote($row['tstamp'] ,'integer').', '.
						  $ilDB->quote($row['yyyy'] ,'integer').', '.
						  $ilDB->quote($row['mm'] ,'integer').', '.
						  $ilDB->quote($row['dd'] ,'integer').', '.
						  $ilDB->quote($row['hh'] ,'integer').', '.
						  $ilDB->quote($row['read_count'] ,'integer').', '.
						  $ilDB->quote($row['childs_read_count'] ,'integer').', '.
						  $ilDB->quote($row['spent_seconds'] ,'integer').', '.
						  $ilDB->quote($row['childs_spent_seconds'] ,'integer').
						  ")"
		);

		$ilDB->manipulate("
			DELETE FROM obj_stat_log_old WHERE
			obj_id = ".$ilDB->quote($row['obj_id'] ,'integer')." AND
			obj_type = ".$ilDB->quote($row['obj_type'] ,'integer')." AND
			tstamp = ".$ilDB->quote($row['tstamp'] ,'integer')." AND
			yyyy = ".$ilDB->quote($row['yyyy'] ,'integer')." AND
			mm = ".$ilDB->quote($row['mm'] ,'integer')." AND
			dd = ".$ilDB->quote($row['dd'] ,'integer')." AND
			hh = ".$ilDB->quote($row['hh'] ,'integer')." AND
			read_count = ".$ilDB->quote($row['read_count'] ,'integer')." AND
			childs_read_count = ".$ilDB->quote($row['childs_read_count'] ,'integer')." AND
			spent_seconds = ".$ilDB->quote($row['spent_seconds'] ,'integer')." AND
			childs_spent_seconds = ".$ilDB->quote($row['childs_spent_seconds'] ,'integer')
		);
	}
}
?>
<#32>
<?php
//step 4/4 obj_stat_log removes old table

if ($ilDB->tableExists('obj_stat_log_old'))
{
	$ilDB->dropTable('obj_stat_log_old');
}
?>
<#33>
<?php
//step 1/4 obj_stat_tmp renames old table

if ($ilDB->tableExists('obj_stat_tmp') && !$ilDB->tableExists('obj_stat_tmp_old'))
{
	$ilDB->renameTable("obj_stat_tmp", "obj_stat_tmp_old");
}
?>
<#34>
<?php
//step 2/4 obj_stat_tmp creates new table with unique id

if (!$ilDB->tableExists('obj_stat_tmp'))
{
	$ilDB->createTable('obj_stat_tmp',array(
		'log_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'obj_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true
		),
		'tstamp' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
		'yyyy' => array(
			'type' => 'integer',
			'length' => 2,
			'notnull' => false
		),
		'mm' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		),
		'dd' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		),
		'hh' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		),
		'read_count' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
		'childs_read_count' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
		'spent_seconds' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
		'childs_spent_seconds' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
	));
	$ilDB->addPrimaryKey('obj_stat_tmp', array('log_id'));
	$ilDB->addIndex('obj_stat_tmp',array('obj_id, obj_type, yyyy, mm, dd, hh'),'i1');
}
?>
<#35>
<?php
//step 3/4 obj_stat_tmp moves all data to new table

if ($ilDB->tableExists('obj_stat_tmp') && $ilDB->tableExists('obj_stat_tmp_old'))
{
	$res = $ilDB->query("
		SELECT *
		FROM obj_stat_tmp_old
");

	while($row = $ilDB->fetchAssoc($res))
	{
		$id = $ilDB->nextId('obj_stat_tmp');

		$ilDB->manipulate("INSERT INTO obj_stat_tmp ".
						  "(log_id, obj_id, obj_type, tstamp,  yyyy, mm, dd, hh, read_count, childs_read_count, spent_seconds, childs_spent_seconds) ".
						  "VALUES ( ".
						  $ilDB->quote($id ,'integer').', '.
						  $ilDB->quote($data['obj_id'] ,'integer').', '.
						  $ilDB->quote($data['obj_type'] ,'text').', '.
						  $ilDB->quote($data['tstamp'] ,'integer').', '.
						  $ilDB->quote($data['yyyy'] ,'integer').', '.
						  $ilDB->quote($data['mm'] ,'integer').', '.
						  $ilDB->quote($data['dd'] ,'integer').', '.
						  $ilDB->quote($data['hh'] ,'integer').', '.
						  $ilDB->quote($data['read_count'] ,'integer').', '.
						  $ilDB->quote($data['childs_read_count'] ,'integer').', '.
						  $ilDB->quote($data['spent_seconds'] ,'integer').', '.
						  $ilDB->quote($data['childs_spent_seconds'] ,'integer').
						  ")"
		);

		$ilDB->manipulate("
			DELETE FROM obj_stat_tmp_old WHERE
			obj_id = ".$ilDB->quote($row['obj_id'] ,'integer')." AND
			yyyy = ".$ilDB->quote($row['yyyy'] ,'integer')." AND
			mm = ".$ilDB->quote($row['mm'] ,'integer')." AND
			dd = ".$ilDB->quote($row['dd'] ,'integer')." AND
			hh = ".$ilDB->quote($row['hh'] ,'integer')." AND
			read_count = ".$ilDB->quote($row['read_count'] ,'integer')." AND
			childs_read_count = ".$ilDB->quote($row['childs_read_count'] ,'integer')." AND
			spent_seconds = ".$ilDB->quote($row['spent_seconds'] ,'integer')." AND
			childs_spent_seconds = ".$ilDB->quote($row['childs_spent_seconds'] ,'integer')
		);
	}
}
?>
<#36>
<?php
//step 4/4 obj_stat_tmp_old removes old table

if ($ilDB->tableExists('obj_stat_tmp_old'))
{
	$ilDB->dropTable('obj_stat_tmp_old');
}
?>
<#37>
<?php
//page_question adding primary key

if($ilDB->tableExists('page_question'))
{
	$ilDB->addPrimaryKey('page_question', array('page_id', 'question_id'));
}
?>
<#38>
<?php
//step 1/4 page_style_usage renames old table

if ($ilDB->tableExists('page_style_usage') && !$ilDB->tableExists('page_style_usage_old'))
{
	$ilDB->renameTable("page_style_usage", "page_style_usage_old");
}
?>
<#39>
<?php
//step 2/4 page_style_usage creates new table with unique id and sequenz

if (!$ilDB->tableExists('page_style_usage'))
{
	$ilDB->createTable('page_style_usage',array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'page_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'page_type' => array(
			'type' => 'text',
			'length' => 10,
			'fixed' => true,
			'notnull' => true
		),
		'page_nr' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'template' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),
		'stype' => array(
			'type' => 'text',
			'length' => 30,
			'fixed' => false,
			'notnull' => false
		),
		'sname' => array(
			'type' => 'text',
			'length' => 30,
			'fixed' => true,
			'notnull' => false
		),
		'page_lang' => array(
			'type' => 'text',
			'length'  => 2,
			'notnull' => true,
			'default' => "-")
	));
	$ilDB->addPrimaryKey('page_style_usage', array('id'));
	$ilDB->createSequence('page_style_usage');
}
?>
<#40>
<?php
//step 3/4 page_style_usage moves all data to new table

if ($ilDB->tableExists('page_style_usage') && $ilDB->tableExists('page_style_usage_old'))
{
	$res = $ilDB->query("
		SELECT *
		FROM page_style_usage_old
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$id = $ilDB->nextId('page_style_usage');

		$ilDB->manipulate("INSERT INTO page_style_usage ".
						  "(id, page_id, page_type, page_lang, page_nr, template, stype, sname) VALUES (".
						  $ilDB->quote($id, "integer").",".
						  $ilDB->quote($row['page_id'], "integer").",".
						  $ilDB->quote($row['page_type'], "text").",".
						  $ilDB->quote($row['page_lang'], "text").",".
						  $ilDB->quote($row['page_nr'], "integer").",".
						  $ilDB->quote($row['template'], "integer").",".
						  $ilDB->quote($row['stype'], "text").",".
						  $ilDB->quote($row['sname'], "text").
						  ")");

		$ilDB->manipulateF(
			"DELETE FROM page_style_usage_old WHERE page_id = %s AND page_type = %s AND page_lang = %s AND page_nr = %s AND template = %s AND stype = %s AND sname = %s",
			array('integer', 'text', 'text', 'integer', 'integer', 'text', 'text'),
			array($row['page_id'], $row['page_type'], $row['page_lang'], $row['page_nr'], $row['template'], $row['stype'], $row['sname'])
		);
	}
}
?>
<#41>
<?php
//step 4/4 page_style_usage removes old table

if ($ilDB->tableExists('page_style_usage_old'))
{
	$ilDB->dropTable('page_style_usage_old');
}
?>
<#42>
<?php
//page_question adding primary key

if( $ilDB->indexExistsByFields('personal_pc_clipboard', array('user_id')) )
{
	$ilDB->dropIndexByFields('obj_stat', array('user_id'));
}

if($ilDB->tableExists('personal_pc_clipboard'))
{
	$ilDB->addPrimaryKey('personal_pc_clipboard', array('user_id', 'insert_time', 'order_nr'));
}
?>
<#43>
<?php
//step 1/4 ut_lp_collections search for dublicates and store it in ut_lp_collections_tmp

if ($ilDB->tableExists('ut_lp_collections'))
{
	$res = $ilDB->query("
		SELECT obj_id, item_id
		FROM ut_lp_collections
		GROUP BY obj_id, item_id
		HAVING COUNT(obj_id) > 1
	");

	if($ilDB->numRows($res))
	{
		if(!$ilDB->tableExists('ut_lp_collections_tmp'))
		{
			$ilDB->createTable('ut_lp_collections_tmp', array(
				'obj_id' => array(
					'type'  => 'integer',
					'length'=> 8,
					'notnull' => true,
					'default' => 0
				),
				'item_id' => array(
					'type'  => 'integer',
					'length'=> 8,
					'notnull' => true,
					'default' => 0
				)
			));
			$ilDB->addPrimaryKey('ut_lp_collections_tmp', array('obj_id','item_id'));
		}

		while($row = $ilDB->fetchAssoc($res))
		{
			$ilDB->replace('ut_lp_collections_tmp', array(), array(
				'obj_id' => array('integer', $row['obj_id']),
				'item_id' => array('integer', $row['item_id'])
			));
		}
	}
}
?>
<#44>
<?php
//step 2/4 ut_lp_collections deletes dublicates stored in ut_lp_collections_tmp

if ($ilDB->tableExists('ut_lp_collections_tmp'))
{
	$res = $ilDB->query("
		SELECT obj_id, item_id
		FROM ut_lp_collections_tmp
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$res_data = $ilDB->query("
			SELECT *
			FROM ut_lp_collections
			WHERE
			obj_id = ".$ilDB->quote($row['obj_id'] ,'integer')." AND
			item_id = ".$ilDB->quote($row['item_id'] ,'integer')
		);
		$data = $ilDB->fetchAssoc($res_data);

		$ilDB->manipulate("DELETE FROM ut_lp_collections WHERE".
						  " obj_id = " . $ilDB->quote($row['obj_id'] ,'integer').
						  " AND item_id = " . $ilDB->quote($row['item_id'] ,'integer')
		);

		$ilDB->manipulate("INSERT INTO ut_lp_collections (obj_id, item_id, grouping_id, num_obligatory, active, lpmode) ".
						  "VALUES ( ".
						  $ilDB->quote($data['obj_id'] ,'integer').', '.
						  $ilDB->quote($data['item_id'] ,'integer').', '.
						  $ilDB->quote($data['grouping_id'] ,'integer').', '.
						  $ilDB->quote($data['num_obligatory'] ,'integer').', '.
						  $ilDB->quote($data['active'] ,'integer').', '.
						  $ilDB->quote($data['lpmode'] ,'text').
						  ")");

		$ilDB->manipulate("DELETE FROM ut_lp_collections_tmp WHERE".
						  " obj_id = " . $ilDB->quote($row['obj_id'] ,'integer').
						  " AND item_id = " . $ilDB->quote($row['item_id'] ,'integer')
		);
	}
}
?>
<#45>
<?php
//step 3/4 ut_lp_collections adding primary key and removing indexes

if( $ilDB->indexExistsByFields('ut_lp_collections', array('obj_id', 'item_id')) )
{
	$ilDB->dropIndexByFields('ut_lp_collections', array('obj_id', 'item_id'));
}

if($ilDB->tableExists('ut_lp_collections'))
{
	$ilDB->addPrimaryKey('ut_lp_collections', array('obj_id', 'item_id'));
}
?>
<#46>
<?php
//step 4/4 ut_lp_collections removes temp table

if ($ilDB->tableExists('ut_lp_collections_tmp'))
{
	$ilDB->dropTable('ut_lp_collections_tmp');
}
?>
<#47>
<?php
//usr_session_stats adding primary key

if($ilDB->tableExists('usr_session_stats'))
{
	$ilDB->addPrimaryKey('usr_session_stats', array('slot_begin'));
}
?>
<#48>
<?php
//step 1/2 usr_session_log search for dublicates and delete them

if ($ilDB->tableExists('usr_session_log'))
{
	$res = $ilDB->query("
		SELECT tstamp, maxval, user_id
		FROM usr_session_log
		GROUP BY tstamp, maxval, user_id
		HAVING COUNT(tstamp) > 1
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$ilDB->manipulate("DELETE FROM usr_session_log WHERE".
			  " tstamp = " . $ilDB->quote($row['tstamp'] ,'integer').
			  " AND maxval = " . $ilDB->quote($row['maxval'] ,'integer').
			  " AND user_id = " . $ilDB->quote($row['user_id'] ,'integer')
		);

		$ilDB->manipulate("INSERT INTO usr_session_log (tstamp, maxval, user_id) ".
			  "VALUES ( ".
			  $ilDB->quote($row['tstamp'] ,'integer').', '.
			  $ilDB->quote($row['maxval'] ,'integer').', '.
			  $ilDB->quote($row['user_id'] ,'integer').
		")");
	}
}
?>
<#49>
<?php
//step 2/2 usr_session_log adding primary key

if($ilDB->tableExists('usr_session_log'))
{
	$ilDB->addPrimaryKey('usr_session_log', array('tstamp', 'maxval', 'user_id'));
}
?>
<#50>
<?php
//step 1/2 style_template_class search for dublicates and delete them

if ($ilDB->tableExists('style_template_class'))
{


	$res = $ilDB->query("
		SELECT template_id, class_type
		FROM style_template_class
		GROUP BY template_id, class_type
		HAVING COUNT(template_id) > 1
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$res_data = $ilDB->query("
			SELECT *
			FROM style_template_class
			WHERE
			template_id = ".$ilDB->quote($row['template_id'] ,'integer')." AND
			class_type = ".$ilDB->quote($row['class_type'] ,'integer')
		);
		$data = $ilDB->fetchAssoc($res_data);

		$ilDB->manipulate("DELETE FROM style_template_class WHERE".
						  " template_id = " . $ilDB->quote($row['template_id'] ,'integer').
						  " AND class_type = " . $ilDB->quote($row['class_type'] ,'text')
		);

		$ilDB->manipulate("INSERT INTO style_template_class (template_id, class_type, class) ".
						  "VALUES ( ".
						  $ilDB->quote($row['template_id'] ,'integer').', '.
						  $ilDB->quote($row['class_type'] ,'text').', '.
						  $ilDB->quote($data['class'] ,'text').
						  ")");
	}
}
?>
<#51>
<?php
//step 2/2 style_template_class adding primary key

if($ilDB->tableExists('style_template_class'))
{
	$ilDB->addPrimaryKey('style_template_class', array('template_id', 'class_type', 'class'));
}
?>
<#52>
<?php
//step 1/2 style_folder_styles search for dublicates and delete them

if ($ilDB->tableExists('style_folder_styles'))
{
	$res = $ilDB->query("
		SELECT folder_id, style_id
		FROM style_folder_styles
		GROUP BY folder_id, style_id
		HAVING COUNT(folder_id) > 1
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$ilDB->manipulate("DELETE FROM style_folder_styles WHERE".
						  " folder_id = " . $ilDB->quote($row['folder_id'] ,'integer').
						  " AND style_id = " . $ilDB->quote($row['style_id'] ,'integer')
		);

		$ilDB->manipulate("INSERT INTO style_folder_styles (folder_id, style_id) ".
						  "VALUES ( ".
						  $ilDB->quote($row['folder_id'] ,'integer').', '.
						  $ilDB->quote($row['style_id'] ,'integer').
						  ")");
	}
}
?>
<#53>
<?php
//step 2/2 style_folder_styles adding primary key
if( $ilDB->indexExistsByFields('style_folder_styles', array('folder_id')) )
{
	$ilDB->dropIndexByFields('style_folder_styles', array('folder_id'));
}

if($ilDB->tableExists('style_folder_styles'))
{
	$ilDB->addPrimaryKey('style_folder_styles', array('folder_id', 'style_id'));
}
?>
<#54>
<?php
//step 1/4 mob_parameter search for dublicates and store it in mob_parameter_tmp

if ($ilDB->tableExists('mob_parameter'))
{
	$res = $ilDB->query("
		SELECT med_item_id, name
		FROM mob_parameter
		GROUP BY med_item_id, name
		HAVING COUNT(med_item_id) > 1
	");

	if($ilDB->numRows($res))
	{
		if(!$ilDB->tableExists('mob_parameter_tmp'))
		{
			$ilDB->createTable('mob_parameter_tmp', array(
				'med_item_id' => array(
					'type'  => 'integer',
					'length'=> 8,
					'notnull' => true,
					'default' => 0
				),
				'name' => array(
					'type'  => 'text',
					'length'=> 50,
					'notnull' => true,
				)
			));
			$ilDB->addPrimaryKey('mob_parameter_tmp', array('med_item_id','name'));
		}

		while($row = $ilDB->fetchAssoc($res))
		{
			$ilDB->replace('mob_parameter_tmp', array(), array(
				'med_item_id' => array('integer', $row['med_item_id']),
				'name' => array('text', $row['name'])
			));
		}
	}
}
?>
<#55>
<?php
//step 2/4 mob_parameter deletes dublicates stored in mob_parameter_tmp

if ($ilDB->tableExists('mob_parameter_tmp'))
{
	$res = $ilDB->query("
		SELECT med_item_id, name
		FROM mob_parameter_tmp
");

while($row = $ilDB->fetchAssoc($res))
{
	$res_data = $ilDB->query("
		SELECT *
		FROM mob_parameter
		WHERE
		med_item_id = ".$ilDB->quote($row['med_item_id'] ,'integer')." AND
		name = ".$ilDB->quote($row['name'] ,'text')
	);
	$data = $ilDB->fetchAssoc($res_data);

	$ilDB->manipulate("DELETE FROM mob_parameter WHERE".
					  " med_item_id = " . $ilDB->quote($row['med_item_id'] ,'integer').
					  " AND name = " . $ilDB->quote($row['name'] ,'integer')
	);

	$ilDB->manipulate("INSERT INTO mob_parameter (med_item_id, name, value) ".
					  "VALUES ( ".
					  $ilDB->quote($data['med_item_id'] ,'integer').', '.
					  $ilDB->quote($data['name'] ,'text').', '.
					  $ilDB->quote($data['value'] ,'text').
					  ")");

	$ilDB->manipulate("DELETE FROM mob_parameter_tmp WHERE".
					  " med_item_id = " . $ilDB->quote($row['med_item_id'] ,'integer').
					  " AND name = " . $ilDB->quote($row['name'] ,'text')
	);
}
}
?>
<#56>
<?php
//step 3/4 mob_parameter adding primary key
if( $ilDB->indexExistsByFields('mob_parameter', array('med_item_id')) )
{
	$ilDB->dropIndexByFields('mob_parameter', array('med_item_id'));
}

if($ilDB->tableExists('mob_parameter'))
{
	$ilDB->addPrimaryKey('mob_parameter', array('med_item_id', 'name'));
}
?>
<#57>
<?php
//step 4/4 mob_parameter removes temp table

if ($ilDB->tableExists('mob_parameter_tmp'))
{
	$ilDB->dropTable('mob_parameter_tmp');
}
?>
<#58>
<?php
//step 1/4 link_check renames old table

if ($ilDB->tableExists('link_check') && !$ilDB->tableExists('link_check_old'))
{
	$ilDB->renameTable("link_check", "link_check_old");
}
?>
<#59>
<?php
//step 2/4 link_check creates new table with unique id and sequenz

if (!$ilDB->tableExists('link_check'))
{
	$ilDB->createTable('link_check',array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'page_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'url' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false,
			'default' => null
		),
		'parent_type' => array(
			'type' => 'text',
			'length' => 8,
			'notnull' => false,
			'default' => null
		),
		'http_status_code' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'last_check' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		)
	));
	$ilDB->addPrimaryKey('link_check', array('id'));
	$ilDB->addIndex('link_check',array('obj_id'),'i1');
	$ilDB->createSequence('link_check');
}
?>
<#60>
<?php
//step 3/4 link_check moves all data to new table

if ($ilDB->tableExists('link_check') && $ilDB->tableExists('link_check_old'))
{
	$res = $ilDB->query("
		SELECT *
		FROM link_check_old
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$id = $ilDB->nextId('link_check');

		$ilDB->manipulate("INSERT INTO link_check (id, obj_id, page_id, url, parent_type, http_status_code, last_check)".
						  " VALUES (".
						  $ilDB->quote($id, "integer").
						  ",".$ilDB->quote($row['obj_id'], "integer").
						  ",".$ilDB->quote($row['page_id'], "integer").
						  ",".$ilDB->quote($row['url'], "text").
						  ",".$ilDB->quote($row['parent_type'], "text").
						  ",".$ilDB->quote($row['http_status_code'], "integer").
						  ",".$ilDB->quote($row['last_check'], "integer").
						  ")"
		);

		$ilDB->manipulateF(
			"DELETE FROM link_check_old WHERE obj_id = %s AND page_id = %s AND url = %s AND parent_type = %s AND http_status_code = %s AND last_check = %s",
			array('integer', 'integer', 'text', 'text', 'integer', 'integer'),
			array($row['obj_id'], $row['page_id'], $row['url'], $row['parent_type'], $row['http_status_code'], $row['last_check'])
		);
	}
}
?>
<#61>
<?php
//step 4/4 link_check removes old table

if ($ilDB->tableExists('link_check_old'))
{
	$ilDB->dropTable('link_check_old');
}
?>
<#62>
<?php
$num_query = "
	SELECT COUNT(*) cnt
	FROM (
		SELECT tree, child
		FROM bookmark_tree
		GROUP BY tree, child
		HAVING COUNT(*) > 1
	) duplicateBookmarkTree
";
$res  = $ilDB->query($num_query);
$data = $ilDB->fetchAssoc($res);

if($data['cnt'] > 0)
{
	echo "<pre>

		Dear Administrator,

		DO NOT REFRESH THIS PAGE UNLESS YOU HAVE READ THE FOLLOWING INSTRUCTIONS

		The update process has been stopped due to a data consistency issue in table 'bookmark_tree'.
		The values in field 'tree' and 'child' should be unique together, but there are dublicated values in these fields.
		You have to review the data and apply manual fixes on your own risk. The duplicates can be determined with the following SQL string:

		SELECT *
		FROM bookmark_tree first
		WHERE EXISTS (
			SELECT second.tree, second.child
			FROM bookmark_tree second
			WHERE first.tree = second.tree AND first.child = second.child
			GROUP BY second.tree, second.child
			HAVING COUNT(second.tree) > 1
		);

		If you have fixed the Problem and try to rerun the update process, this warning will be skipped.

		Please ensure to backup your current database before fixing the database.
		Furthermore disable your client while fixing the database.

		For further questions use our <a href='http://mantis.ilias.de'>Bugtracker</a> or write a message to the responsible <a href='http://www.ilias.de/docu/goto_docu_pg_9985_42.html'>Maintainer</a>.

		Best regards,
		The Bookmark maintainer

	</pre>";

	exit();
}


if($ilDB->tableExists('bookmark_tree'))
{
	$ilDB->addPrimaryKey('bookmark_tree', array('tree', 'child'));
}

?>
<#63>
<?php
$num_query = "
	SELECT COUNT(*) cnt
	FROM (
	SELECT lm_id, child
	FROM lm_tree
	GROUP BY lm_id, child
	HAVING COUNT(*) > 1
	) duplicateLMTree
";
$res  = $ilDB->query($num_query);
$data = $ilDB->fetchAssoc($res);

if($data['cnt'] > 0)
{
	echo "<pre>

		Dear Administrator,

		DO NOT REFRESH THIS PAGE UNLESS YOU HAVE READ THE FOLLOWING INSTRUCTIONS

		The update process has been stopped due to a data consistency issue in table 'lm_tree'.
		The values in field 'lm_id' and 'child' should be unique together, but there are dublicated values in these fields.
		You have to review the data and apply manual fixes on your own risk. The duplicates can be determined with the following SQL string:

		SELECT *
		FROM lm_tree first
		WHERE EXISTS (
			SELECT second.lm_id, second.child
			FROM lm_tree second
			WHERE first.lm_id = second.lm_id AND first.child = second.child
			GROUP BY second.lm_id, second.child
			HAVING COUNT(second.lm_id) > 1
		);

		If you have fixed the Problem and try to rerun the update process, this warning will be skipped.

		Please ensure to backup your current database before fixing the database.
		Furthermore disable your client while fixing the database.

		For further questions use our <a href='http://mantis.ilias.de'>Bugtracker</a> or write a message to the responsible <a href='http://www.ilias.de/docu/goto_docu_pg_9985_42.html'>Maintainer</a>.

		Best regards,
		The Learning Modules maintainer

	</pre>";

	exit();
}


if($ilDB->tableExists('lm_tree'))
{
	$ilDB->addPrimaryKey('lm_tree', array('lm_id', 'child'));
}

?>
<#64>
<?php
$num_query = "
	SELECT COUNT(*) cnt
	FROM (
		SELECT mep_id, child
		FROM mep_tree
		GROUP BY mep_id, child
		HAVING COUNT(*) > 1
	) duplicateMEPTree
";
$res  = $ilDB->query($num_query);
$data = $ilDB->fetchAssoc($res);

if($data['cnt'] > 0)
{
	echo "<pre>

		Dear Administrator,

		DO NOT REFRESH THIS PAGE UNLESS YOU HAVE READ THE FOLLOWING INSTRUCTIONS

		The update process has been stopped due to a data consistency issue in table 'mep_tree'.
		The values in field 'mep_id' and 'child' should be unique together, but there are dublicated values in these fields.
		You have to review the data and apply manual fixes on your own risk. The duplicates can be determined with the following SQL string:

		SELECT *
		FROM mep_tree first
		WHERE EXISTS (
			SELECT second.mep_id, second.child
			FROM mep_tree second
			WHERE first.mep_id = second.mep_id AND first.child = second.child
			GROUP BY second.mep_id, second.child
			HAVING COUNT(second.mep_id) > 1
		);

		If you have fixed the Problem and try to rerun the update process, this warning will be skipped.

		Please ensure to backup your current database before fixing the database.
		Furthermore disable your client while fixing the database.

		For further questions use our <a href='http://mantis.ilias.de'>Bugtracker</a> or write a message to the responsible <a href='http://www.ilias.de/docu/goto_docu_pg_9985_42.html'>Maintainer</a>.

		Best regards,
		The Media Pool maintainer

	</pre>";

	exit();
}


if($ilDB->tableExists('mep_tree'))
{
	$ilDB->addPrimaryKey('mep_tree', array('mep_id', 'child'));
}

?>
<#65>
<?php
$num_query = "
	SELECT COUNT(*) cnt
	FROM (
	SELECT skl_tree_id, child
	FROM skl_tree
	GROUP BY skl_tree_id, child
	HAVING COUNT(*) > 1
	) duplicateSKLTree
";
$res  = $ilDB->query($num_query);
$data = $ilDB->fetchAssoc($res);

if($data['cnt'] > 0)
{
	echo "<pre>

		Dear Administrator,

		DO NOT REFRESH THIS PAGE UNLESS YOU HAVE READ THE FOLLOWING INSTRUCTIONS

		The update process has been stopped due to a data consistency issue in table 'skl_tree'.
		The values in field 'skl_tree_id' and 'child' should be unique together, but there are dublicated values in these fields.
		You have to review the data and apply manual fixes on your own risk. The duplicates can be determined with the following SQL string:

		SELECT *
		FROM skl_tree first
		WHERE EXISTS (
			SELECT second.skl_tree_id, second.child
			FROM skl_tree second
			WHERE first.skl_tree_id = second.skl_tree_id AND first.child = second.child
			GROUP BY second.skl_tree_id, second.child
			HAVING COUNT(second.skl_tree_id) > 1
		);

		If you have fixed the Problem and try to rerun the update process, this warning will be skipped.

		Please ensure to backup your current database before fixing the database.
		Furthermore disable your client while fixing the database.

		For further questions use our <a href='http://mantis.ilias.de'>Bugtracker</a> or write a message to the responsible <a href='http://www.ilias.de/docu/goto_docu_pg_9985_42.html'>Maintainer</a>.

		Best regards,
		The Competence Managment maintainer

	</pre>";

	exit();
}


if($ilDB->tableExists('skl_tree'))
{
	$ilDB->addPrimaryKey('skl_tree', array('skl_tree_id', 'child'));
}

?>
<#66>
<?php
//step 1/4 benchmark renames old table

if ($ilDB->tableExists('benchmark') && !$ilDB->tableExists('benchmark_old'))
{
	$ilDB->renameTable("benchmark", "benchmark_old");
}
?>
<#67>
<?php
//step 2/4 benchmark creates new table with unique id and sequenz

if (!$ilDB->tableExists('benchmark'))
{
	$ilDB->createTable('benchmark',array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		"cdate" => array (
			"notnull" => false,
			"type" => "timestamp"
		),
		"module" => array (
			"notnull" => false,
			"length" => 150,
			"fixed" => false,
			"type" => "text"
		),
		"benchmark" => array (
			"notnull" => false,
			"length" => 150,
			"fixed" => false,
			"type" => "text"
		),
		"duration" => array (
			"notnull" => false,
			"type" => "float"
		),
		"sql_stmt" => array (
			"notnull" => false,
			"type" => "clob"
		)
	));
	$ilDB->addPrimaryKey('benchmark', array('id'));
	$ilDB->addIndex('benchmark',array("module","benchmark"),'i1');
	$ilDB->createSequence('benchmark');
}
?>
<#68>
<?php
//step 3/4 benchmark moves all data to new table

if ($ilDB->tableExists('benchmark') && $ilDB->tableExists('benchmark_old'))
{
	$res = $ilDB->query("
		SELECT *
		FROM benchmark_old
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$id = $ilDB->nextId('benchmark');

		$ilDB->insert("benchmark", array(
			"id" => array("integer", $id),
			"cdate" => array("timestamp", $row['cdate']),
			"module" => array("text",$row['module']),
			"benchmark" => array("text", $row['benchmark']),
			"duration" => array("float", $row['duration']),
			"sql_stmt" => array("clob", $row['sql_stmt'])
		));

		$ilDB->manipulateF(
			"DELETE FROM benchmark_old WHERE cdate = %s AND module = %s AND benchmark = %s AND duration = %s ",
			array('timestamp', 'text', 'text', 'float'),
			array($row['cdate'], $row['module'], $row['benchmark'], $row['duration'])
		);
	}
}
?>
<#69>
<?php
//step 4/4 benchmark removes old table

if ($ilDB->tableExists('benchmark_old'))
{
	$ilDB->dropTable('benchmark_old');
}
?>
<#70>
<?php
//step skl_user_skill_level adding primary key
if($ilDB->tableExists('skl_user_skill_level'))
{
	$ilDB->addPrimaryKey('skl_user_skill_level', array('skill_id', 'tref_id', 'user_id', 'status_date', 'status', 'trigger_obj_id', 'self_eval'));
}

?>