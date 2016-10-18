<?php
chdir(dirname(__FILE__));
$ilias_main_directory = './';
while(!file_exists($ilias_main_directory . 'ilias.ini.php'))
{
	$ilias_main_directory .= '../';
}
chdir($ilias_main_directory);

include_once 'Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_CRON);

include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);

$_COOKIE['ilClientId'] = $_SERVER['argv'][3];
$_POST['username'] = $_SERVER['argv'][1];
$_POST['password'] = $_SERVER['argv'][2];
$hotrun = isset($_SERVER['argv'][4]) ? $_SERVER['argv'][4] == 'hotrun' : false;

if($_SERVER['argc'] < 4)
{
	die('Usage: '. basename(__FILE__) .' username password client');
}

require_once 'include/inc.header.php';

/**
 * @var $ilDB ilDB
 * @var $tree ilTree
 * @var $ilObjDataCache ilObjectDataCache
 * @var $ilLog ilLog
 */
global $ilDB, $tree, $ilObjDataCache, $ilLog;

$query = '
	SELECT ud1.usr_id, ud1.login, ud1.ext_account, ud1.create_date
	FROM usr_data ud1
	INNER JOIN (
		SELECT ext_account FROM usr_data WHERE ext_account IS NOT NULL GROUP BY ext_account HAVING COUNT(ext_account) > 1
	) ud2 ON ud2.ext_account = ud1.ext_account
	ORDER BY ud1.login, ud1.create_date
';

$res = $ilDB->query($query);

$last_handled_row   = null;
$rows_to_delete     = null;

while((($row = $ilDB->fetchAssoc($res)) && is_array($row)) || is_array($rows_to_delete))
{
	if($last_handled_row !== null && ($last_handled_row['ext_account'] != $row['ext_account'] || !$row && count($rows_to_delete) > 0))
	{
		if(is_array($rows_to_delete));
		{
			foreach($rows_to_delete as $row_to_delete)
			{
				if($hotrun)
				{
					$user = new ilObjUser($row_to_delete['usr_id']);
					$user->delete();
					$ilLog->write(sprintf("Deleted duplicate user with login '%s' (ext_account: %s|id: %s)", $row_to_delete['login'], $row_to_delete['ext_account'], $row_to_delete['usr_id']));
					echo sprintf("Deleted duplicate user with login '%s' (ext_account: %s|id: %s)", $row_to_delete['login'], $row_to_delete['ext_account'], $row_to_delete['usr_id']);
				}
				else
				{
					$ilLog->write(sprintf("Would delete duplicate user with login '%s' (ext_account: %s|id: %s)", $row_to_delete['login'], $row_to_delete['ext_account'], $row_to_delete['usr_id']));
					echo sprintf("Would delete duplicate user with login '%s' (ext_account: %s|id: %s)", $row_to_delete['login'], $row_to_delete['ext_account'], $row_to_delete['usr_id']);
				}
			}
		}

		$last_handled_row = null;
		$rows_to_delete   = null;
	}

	if(is_array($row))
	{
		/* Wenn Login relevant: 
		 * if($row['login'] != $row['ext_account'])
		{
			$rows_to_delete[] = $row;
		}*/
		
		// Wenn Create-Datum relevant:
		if($last_handled_row)
		{
			$rows_to_delete[] = $row;
		}

		$last_handled_row = $row;
	}
	else
	{
		break;
	}
}

echo "Finished script!";