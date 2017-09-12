<?php
chdir(dirname(__FILE__));
$ilias_main_directory = './';
while(!file_exists($ilias_main_directory . 'ilias.ini.php'))
{
	$ilias_main_directory .= '../';
}
chdir($ilias_main_directory);

include_once './Services/Cron/classes/class.ilCronStartUp.php';

if($_SERVER['argc'] < 5)
{
	echo('Usage: '. basename(__FILE__) .' username password client ref_id');
	exit(1);
}

$cat_ref_id = $_SERVER['argv'][4];

$cron = new ilCronStartUp($_SERVER['argv'][3], $_SERVER['argv'][1], $_SERVER['argv'][2]);

$executionChain = function() use ($cat_ref_id) {
	/**
	 * @var $ilDB ilDB
	 * @var $tree ilTree
	 * @var $ilObjDataCache ilObjectDataCache
	 */
	global $ilDB, $tree, $ilObjDataCache;

	$cat_ref_id = trim($cat_ref_id);
	if(!$cat_ref_id)
	{
		exit();
	}

	$obj_id_cat = $ilObjDataCache->lookupObjId($cat_ref_id);
	if(!$obj_id_cat)
	{
		echo 'obj_id for ref_id '.$cat_ref_id.' could not be determined. Script execution aborted!' . "\n";
		exit();
	}
	else
	{
		echo 'Determined obj_id ' . $obj_id_cat . ' for ref_id '.$cat_ref_id  . "\n" ;
	}

	$title = $ilObjDataCache->lookupTitle($obj_id_cat);
	if('cat' != $ilObjDataCache->lookupType($obj_id_cat))
	{
		echo 'Object (title:'.$title.'|ref_id:'.$cat_ref_id.') is not of type "cat". Script execution aborted!' . "\n";
		exit();
	}

	if($tree->isDeleted($cat_ref_id))
	{
		echo 'Category (title:'.$title.'|ref_id:'.$cat_ref_id.') is in trash. Script execution aborted!' . "\n";
		exit();
	}

	$node = $tree->getNodeData($cat_ref_id);

	if(!is_object($GLOBALS['ilSetting']) or $GLOBALS['ilSetting']->getModule() != 'common')
	{
		include_once './Services/Administration/classes/class.ilSetting.php';
		$setting = new ilSetting('common');
	}
	else
	{
		$setting = $GLOBALS['ilSetting'];
	}

	$is_ns = $setting->get('main_tree_impl','ns') == 'ns';
	if($is_ns)
	{
		$query = '
        SELECT od.obj_id
        FROM object_data od
        INNER JOIN object_reference objr ON objr.obj_id = od.obj_id AND objr.deleted IS NULL
        INNER JOIN tree t ON t.child = objr.ref_id AND t.tree = %s AND (t.lft BETWEEN %s AND %s)
        WHERE od.type = %s
    ';
	}
	else
	{
		$query = '
        SELECT od.obj_id
        FROM object_data od
        INNER JOIN object_reference objr ON objr.obj_id = od.obj_id AND objr.deleted IS NULL
        INNER JOIN tree t ON t.child = objr.ref_id AND t.tree = %s AND (t.path BETWEEN %s AND %s)
        WHERE od.type = %s
    ';
	}
	$query .= ' AND ' . str_replace('LIKE', 'NOT LIKE', $ilDB->like('od.title', 'text', '[SEMESTER]%%'));

	$manipulation = "
    UPDATE object_data od
    INNER JOIN ($query) odinner ON odinner.obj_id = od.obj_id
    SET od.title = " . $ilDB->concat(array(array("'[SEMESTER] '", 'text'), array('od.title', '')));

	$affected_rows = $ilDB->manipulateF(
		$manipulation,
		array('integer', $is_ns ? 'integer' : 'text', $is_ns ? 'integer' : 'text', 'text'),
		array(1, $is_ns ? $node['lft'] : $node['path'], $is_ns ? $node['rgt'] : $node['path'] . 'Z', 'crs')
	);

	echo ((int)$affected_rows) . ' course title(s) changed' . "\n";
};

try {
	$cron->initIlias();
	$cron->authenticate();

	if(!defined("ILIAS_HTTP_PATH"))
	{
		define("ILIAS_HTTP_PATH", ilUtil::_getHttpPath());
	}

	$executionChain();
}
catch(Exception $e)
{
	echo $e->getMessage()."\n";
	exit(1);
}