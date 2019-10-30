<#1>
<?php

/**
 * @var $ilDB \ilDBInterface
 */

if(!$ilDB->tableColumnExists('crs_settings', 'period_start')) {

	$ilDB->addTableColumn(
		'crs_settings',
		'period_start',
		[
			'type' => \ilDBConstants::T_TIMESTAMP,
			'notnull' => false,
			'default' => null
		]
	);
	$ilDB->addTableColumn(
		'crs_settings',
		'period_end',
		[
			'type' => \ilDBConstants::T_TIMESTAMP,
			'notnull' => false,
			'default' => null
		]
	);
}
?>

<#2>
<?php

$query = 'select obj_id, crs_start, crs_end from crs_settings where crs_start IS NOT NULL or crs_end IS NOT NULL';
$res = $ilDB->query($query);
while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {

	$dtstart = $dtend = null;
	if($row->crs_start != null) {
		$start = new DateTime();
		$start->setTimezone(new DateTimeZone('UTC'));
		$start->setTimestamp((int) $row->crs_start);
		$dtstart = $start->format('Y-m-d');
	}
	if($row->crs_end != null) {
		$end = new DateTime();
		$end->setTimezone(new DateTimeZone('UTC'));
		$end->setTimestamp((int) $row->crs_end);
		$dtend = $end->format('Y-m-d');
	}

	$query = 'update crs_settings set ' .
		'period_start = ' . $ilDB->quote($dtstart, \ilDBConstants::T_TIMESTAMP) . ', ' .
		'period_end = ' . $ilDB->quote($dtend, \ilDBConstants::T_TIMESTAMP) . ' ' .
		'where obj_id = ' . $ilDB->quote($row->obj_id, \ilDBConstants::T_INTEGER);
	$ilDB->manipulate($query);

}
?>

<#3>
<?php
if(!$ilDB->tableColumnExists('crs_settings', 'period_time_indication')) {

	$ilDB->addTableColumn(
		'crs_settings',
		'period_time_indication',
		[
			'type' => \ilDBConstants::T_INTEGER,
			'notnull' => true,
			'default' => 0
		]
	);
}
?>

<#4>
<?php

/**
 * @var $ilDB \ilDBInterface
 */

if(!$ilDB->tableColumnExists('grp_settings', 'period_start')) {

	$ilDB->addTableColumn(
		'grp_settings',
		'period_start',
		[
			'type' => \ilDBConstants::T_TIMESTAMP,
			'notnull' => false,
			'default' => null
		]
	);
	$ilDB->addTableColumn(
		'grp_settings',
		'period_end',
		[
			'type' => \ilDBConstants::T_TIMESTAMP,
			'notnull' => false,
			'default' => null
		]
	);
}
?>

<#5>
<?php

$query = 'select obj_id, grp_start, grp_end from grp_settings where grp_start IS NOT NULL or grp_end IS NOT NULL';
$res = $ilDB->query($query);
while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {

	$dtstart = $dtend = null;
	if($row->grp_start != null) {
		$start = new DateTime();
		$start->setTimezone(new DateTimeZone('UTC'));
		$start->setTimestamp((int) $row->grp_start);
		$dtstart = $start->format('Y-m-d');
	}
	if($row->grp_end != null) {
		$end = new DateTime();
		$end->setTimezone(new DateTimeZone('UTC'));
		$end->setTimestamp((int) $row->grp_end);
		$dtend = $end->format('Y-m-d');
	}

	$query = 'update grp_settings set ' .
		'period_start = ' . $ilDB->quote($dtstart, \ilDBConstants::T_TIMESTAMP) . ', ' .
		'period_end = ' . $ilDB->quote($dtend, \ilDBConstants::T_TIMESTAMP) . ' ' .
		'where obj_id = ' . $ilDB->quote($row->obj_id, \ilDBConstants::T_INTEGER);
	$ilDB->manipulate($query);

}
?>
<#6>
<?php
if(!$ilDB->tableColumnExists('grp_settings', 'period_time_indication')) {

	$ilDB->addTableColumn(
		'grp_settings',
		'period_time_indication',
		[
			'type' => \ilDBConstants::T_INTEGER,
			'notnull' => true,
			'default' => 0
		]
	);
}
?>






