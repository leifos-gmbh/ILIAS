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

if ($ilDB->tableExists('il_exc_team_log') && $ilDB->tableExists('rbac_log_old'))
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

if(! $ilDB->indexExistsByFields('lo_access', array('usr_id')) )
{
	$ilDB->dropIndexByFields('lo_access', array('usr_id'));
}

if($ilDB->tableExists('lo_access'))
{
	$ilDB->addPrimaryKey('lo_access', array('usr_id', 'lm_id'));
}
?>
