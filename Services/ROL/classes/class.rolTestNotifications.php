<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * ROL test notifications
 *
 * @author Alex Killing <killing@leifos.com>
 */
class rolTestNotifications extends ilCronJob
{
	/**
	 * @var ilComponentLogger
	 */
	protected $log;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * Constructor
	 */
	function __construct()
	{
		global $lng, $ilDB, $tree, $ilAccess;

		// init logger and language (language used for config gui only)
		$this->log = ilLoggerFactory::getLogger('raiffrol');
		$this->lng = $lng;
		$this->db = $ilDB;
		$this->tree = $tree;
		$this->access = $ilAccess;
	}

	/**
	 * Get id
	 *
	 * @return string
	 */
	public function getId()
	{
		return "rol_test_notifications";
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->lng->txt("rol_cron_test_notifications");
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->lng->txt("rol_cron_test_notifications_desc");
	}

	/**
	 * @return int
	 */
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_IN_MINUTES;
	}

	/**
	 *
	 */
	public function getDefaultScheduleValue()
	{
		return;
	}

	/**
	 * @return bool
	 */
	public function hasAutoActivation()
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function hasFlexibleSchedule()
	{
		return true;
	}

	/**
	 * Run job
	 *
	 * @return ilCronJobResult
	 * @throws ilBuddySystemException
	 */
	public function run()
	{
		$this->log->debug("start -----");
		$this->log->debug("display_errors: ".ini_get("display_errors").", ".
			"log_errors :".ini_get("log_errors").", ".
			"error_log :".ini_get("error_log").", php.ini path: ".php_ini_loaded_file());

		// init status and determine last run timestamp
		$status_details = null;
		$setting = new ilSetting("cron");
		$last_run = $setting->get(get_class($this));
		if(!$last_run)	// no last run
		{
			$status_details = "No previous run found - starting from yesterday.";
		}
		else if(strlen($last_run) == 10) // migration: used to be date-only value
		{
			$status_details = "Switched from daily runs to open schedule.";
		}

		$status = $this->sendMails();

		// return result cron job result
		$setting->set(get_class($this), date("Y-m-d H:i:s"));
		$result = new ilCronJobResult();
		$result->setStatus($status);
		if($status_details)
		{
			$result->setMessage($status_details);
		}

		$this->log->debug("end -------");

		return $result;
	}


	/**
	 * Send mails
	 *
	 * @return int status
	 */
	function sendMails()
	{
		$this->log->debug("start");

		$status = ilCronJobResult::STATUS_NO_ACTION;

//		      rolTestNotifications
//		      rolTestNotifications
//		class.rolTestNotifications.php
		// get all missing notifications
		include_once("./Services/ROL/Course/classes/class.rolCourse.php");
		$missing = rolCourse::getMissingUserNotifications();

		$this->log->debug("found ".count($missing)." missing notifications");
		
		// get all certificate tests
		include_once("./Services/ROL/Test/classes/class.rolTest.php");
		$ctests = rolTest::getAllCertificateTests();
		$this->log->debug("found ".count($ctests)." certificate tests");

		// for each test...
		foreach ($ctests as $test)
		{
			// get course
			$this->log->debug("getting course for test ref id: ".$test["ref_id"]);
			$course_ref_id = $this->tree->checkForParentType((int) $test["ref_id"], "crs");
			if ($course_ref_id > 0)
			{
				$course_id = ilObject::_lookupObjId($course_ref_id);

				// for each course get all missing notifications
				$this->log->debug("iterate missing notifications");
				if (is_array($missing[$course_id]["users"]))
				{
					$c = new rolCourse($course_id);

					foreach ($missing[$course_id]["users"] as $user_id)
					{
						$this->log->debug("check online time");
						// check online time
						if ($c->getMinOnlinetime() > 0)
						{
							$this->log->debug("lookup online time for user ".$user_id." and course ref id  ".$course_ref_id.".");
							$ot = (int) rolCourse::lookupOnlinetimeForObjectAndUser($user_id, $course_ref_id);
							if ($ot >= $c->getMinOnlinetime() * 60)
							{
								$this->log->debug("check permission");
								// check read permission (including preconditions) for test
								if ($this->access->checkAccess("read", "", $test["ref_id"]))
								{
									$this->log->debug("send mail");
									// send mail and add user_course_email entry
									$this->sendMail($user_id, $course_id);
									$status = ilCronJobResult::STATUS_OK;
								}
							}
						}
					}
				}
				else
				{
					$this->log->debug("no missing notifications for course with ref id: ".$course_ref_id);
				}
			}
			else
			{
				$this->log->debug("no course ref id found");
			}
		}
		$this->log->debug("return status ".$status);

		return $status;
	}

	/**
	 * Send email
	 *
	 * @param int $a_usr_id
	 * @param int $a_course_id
	 */
	public function sendMail($a_usr_id, $a_course_id)
	{
		global $ilDB;

		$name = ilObjUser::_lookupName($a_usr_id);
		$email = ilObjUser::_lookupEMail($a_usr_id);

		$message = 'Sehr geehrte/r Frau/Herr '.$name['firstname'].' '.$name['lastname'].',

ich möchte Sie herzlich zum erfolgreichen Abschluss des Trainings beglückwünschen.
Alle Zwischenprüfungen wurden erfolgreich bestanden und Sie können nun den abschließenden Zertifikatstest durchführen.

Nachfolgend erhalten Sie noch einmal Ihre persönlichen Zugangsdaten:
Benutzername: '.$name['login'].'
Passwort: persönliche Steuernummer (Achtung: alle Buchstaben müssen großgeschrieben werden)

Mit freundlichen Grüßen,
Ihr Trainingsleiter';

		include_once "./Services/Mail/classes/class.ilMail.php";
		$mail = new ilMail(ANONYMOUS_USER_ID);

		$mail->sendMail($email, // to
			"", // cc
			"", // bcc
			"Einladung zum Zertifikatstest", // subject
			$message,
			array(), // attachments
			array('normal') // type
		);

		$ilDB->manipulate("INSERT INTO user_course_email VALUES (".$ilDB->quote($a_usr_id, "text").",".$ilDB->quote($a_course_id, "text").")");
	}

	/**
	 * Lookup user name
	 *
	 * @param $a_user_id
	 * @return array
	 */
	function _lookupUserName($a_user_id)
	{
		if (!isset(self::$user_name[$a_user_id]))
		{
			self::$user_name[$a_user_id] = ilObjUser::_lookupName($a_user_id);
		}
		return self::$user_name[$a_user_id];
	}

	/**
	 * Has cron job any custom setting which can be edited?
	 *
	 * @return boolean
	 */
	public function hasCustomSettings()
	{
		return false;
	}

	/**
	 * Add custom settings to form
	 *
	 * @param ilPropertyFormGUI $a_form
	 */
	public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
	{
		/*include_once("./Services/News/NewsStream/classes/class.ilNewsStreamSettings.php");

		// report max items
		$ti = new ilNumberInputGUI($this->lng->txt("news_stream_report_news_items"), "news_stream_report_news_items");
		$ti->setMaxLength(3);
		$ti->setSize(3);
		$ti->setValue(ilNewsStreamSettings::getReportMaxNewsItems());
		$a_form->addItem($ti);

		// report max items
		$ti = new ilNumberInputGUI($this->lng->txt("news_stream_report_comments_items"), "news_stream_report_comments_items");
		$ti->setMaxLength(3);
		$ti->setSize(3);
		$ti->setValue(ilNewsStreamSettings::getReportMaxCommentsItems());
		$a_form->addItem($ti);*/

	}

	/**
	 * Save custom settings
	 *
	 * @param ilPropertyFormGUI $a_form
	 * @return boolean
	 */
	public function saveCustomSettings(ilPropertyFormGUI $a_form)
	{
		//ilNewsStreamSettings::setReportMaxNewsItems((int) $_POST["news_stream_report_news_items"]);
		//ilNewsStreamSettings::setReportMaxCommentsItems((int) $_POST["news_stream_report_comments_items"]);
		return true;
	}

}

?>