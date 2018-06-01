<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Cron for booking manager notification
 * 
 * @author Alex Killing <killing@leifos.com>
 * @ingroup ModulesBookingManager
 */
class ilBookCronNotification extends ilCronJob
{			
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * Constructor
	 */
	function __construct()
	{
		global $DIC;

		$this->lng = $DIC->language();
		if (isset($DIC["ilAccess"]))
		{
			$this->access = $DIC->access();
		}

	}

	public function getId()
	{
		return "book_notification";
	}
	
	public function getTitle()
	{
		$lng = $this->lng;
		
		$lng->loadLanguageModule("book");
		return $lng->txt("book_notification");
	}
	
	public function getDescription()
	{
		$lng = $this->lng;
		
		$lng->loadLanguageModule("book");
		return $lng->txt("book_notification_info");
	}
	
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_DAILY;
	}
	
	public function getDefaultScheduleValue()
	{
		return;
	}
	
	public function hasAutoActivation()
	{
		return false;
	}
	
	public function hasFlexibleSchedule()
	{
		return false;
	}
	
	public function run()
	{
		$status = ilCronJobResult::STATUS_NO_ACTION;

		$count = $this->sendNotifications();

		if ($count > 0)
		{
			$status = ilCronJobResult::STATUS_OK;
		}
		
		$result = new ilCronJobResult();
		$result->setStatus($status);
		
		return $result;
	}

	/**
	 * Send notifications
	 *
	 * @param
	 * @return int
	 */
	protected function sendNotifications()
	{
		// get all booking pools with notification setting
		/*
		 * pool id 123 > 2 days, ...
		 */
		foreach (ilObjBookingPool::getPoolsWithReminders() as $p)
		{
			// determine reservations from max(next day $last_to_ts) up to "rmd_day" days + 1
			// per pool id
			$next_day_ts = mktime(0, 0, 0, date('n'), date('j') + 1);
			$last_reminder_to_ts = $p["last_remind_ts"];
			$from_ts = max($next_day_ts, $last_reminder_to_ts);
			$to_ts = mktime(0, 0, 0, date('n'), date('j') + $p["reminder_day"] + 1);
			$res = [];
			if ($to_ts > $from_ts)
			{
				$res = ilBookingReservation::getListByDate(true, null, [
					"from" => $from_ts,
					"to" => $to_ts
				], [$p["booking_pool_id"]]);
			}
//var_dump($res); exit;

			// get subscriber of pool id
			$user_ids = ilNotification::getNotificationsForObject(ilNotification::TYPE_BOOK, $p["booking_pool_id"]);

			// group by user, type, pool
			$notifications = [];
			foreach ($res as $r)
			{

				if (in_array($r["user_id"], $user_ids))
				{
					// check pool read permission
					$notifications[$r["user_id"]]["personal"][$r["pool_id"]][] = $r;
				}

				// admins
				foreach ($user_ids as $uid)
				{
					if ($this->checkAccess("write", $uid, $p["booking_pool_id"]))
					{
						// check pool read permission
						$notifications[$uid]["admin"][$r["pool_id"]][] = $r;
					}
				}
			}
			ilObjBookingPool::writeLastReminderTimestamp($p["booking_pool_id"], $to_ts);
		}

		// send mails
		$this->sendMails($notifications);

		return count($notifications);
	}

	/**
	 * Send mails
	 *
	 * @param
	 */
	protected function sendMails($notifications)
	{
		foreach ($notifications as $uid => $n)
		{
			include_once "./Services/Notification/classes/class.ilSystemNotification.php";
			$ntf = new ilSystemNotification();
			$lng = $ntf->getUserLanguage($uid);
			$lng->loadLanguageModule("book");

			$txt = "";
			if (is_array($n["personal"]))
			{
				$txt.= "\n".$lng->txt("book_your_reservations")."\n";
				$txt.= "-----------------------------------------\n";
				foreach ($n["personal"] as $obj_id => $reservs)
				{
					$txt.= ilObject::_lookupTitle($obj_id).":\n";
					foreach ($reservs as $r)
					{
						$txt.= "- ".$r["title"]." (".$r["counter"]."), ".
							ilDatePresentation::formatDate(new ilDate($r["date"], IL_CAL_DATE)).", ".
							$r["slot"]."\n";
					}
				}
			}

			if (is_array($n["admin"]))
			{
				$txt.= "\n".$lng->txt("book_reservation_overview")."\n";
				$txt.= "-----------------------------------------\n";
				foreach ($n["admin"] as $obj_id => $reservs)
				{
					$txt.= ilObject::_lookupTitle($obj_id).":\n";
					foreach ($reservs as $r)
					{
						$txt.= "- ".$r["title"]." (".$r["counter"]."), ".$r["user_name"].", ".
							ilDatePresentation::formatDate(new ilDate($r["date"], IL_CAL_DATE)).", ".
							$r["slot"]."\n";
					}
				}
			}

			$ntf->setLangModules(array("book"));
			$ntf->setSubjectLangId("book_booking_reminders");
			$ntf->setIntroductionLangId("book_rem_intro");
			$ntf->addAdditionalInfo("", $txt);
			$ntf->setReasonLangId("book_rem_reason");
			$ntf->sendMail(array($uid));
		}
	}


	/**
	 * check access on obj id
	 *
	 * @param
	 * @return
	 */
	protected function checkAccess($perm, $uid, $obj_id)
	{
		$access = $this->access;
		foreach (ilObject::_getAllReferences($obj_id) as $ref_id)
		{
			if ($access->checkAccessOfUser($uid, $perm, "", $ref_id))
			{
				return true;
			}
		}
		return false;
	}


	/**
	 * Send user notifications
	 *
	 * @return int
	 */
	protected function sendUserNotifications($res)
	{
		/*
		 * Your reservations for tomorrow
		 *
		 * Pool Title
		 * Pool Link
		 * - Object (cnt), From - To
		 * - ...
		 *
		 * Reservations for tomorrow
		 *
		 * Pool Title
		 * Pool Link
		 * - Object (cnt), From - To
		 * - ...
		 *
		 */
	}

	/**
	 * Send admin notifications
	 *
	 * @return int
	 */
	protected function sendAdminNotifications($res)
	{

	}

}

?>