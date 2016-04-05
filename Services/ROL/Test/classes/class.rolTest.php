<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  Patch class for test object
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class rolTest
{
	/**
	 * object id
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * @var bool
	 */
	protected $certificate_test = false;

	/**
	 * Constructor
	 *
	 * @param int $a_id Test ID
	 */
	function __construct($a_id)
	{
		global $ilDB, $lng;

		$this->log = ilLoggerFactory::getLogger('raiffrol');
		$this->lng = $lng;
		$this->id = $a_id;
		$this->db = $ilDB;
		if ($this->id > 0)
		{
			$this->read();
		}
	}

	/**
	 * Set certificate test
	 *
	 * @param bool $a_val is certificate test?	
	 */
	function setCertificateTest($a_val)
	{
		$this->certificate_test = $a_val;
	}
	
	/**
	 * Get certificate test
	 *
	 * @return bool is certificate test?
	 */
	function getCertificateTest()
	{
		return $this->certificate_test;
	}

	/**
	 * Read
	 */
	function read()
	{
		$set = $this->db->query($q = "SELECT * FROM tst_tests ".
			" WHERE obj_fi  = ".$this->db->quote($this->id, "integer")
		);
		$rec = $this->db->fetchAssoc($set);
//		echo $q;
//		var_dump($rec); exit;
		$this->setCertificateTest($rec["is_certificate_test"]);
	}


	/**
	 * Update
	 */
	function update()
	{
		$this->db->manipulate($q = "UPDATE tst_tests SET ".
			" is_certificate_test = ".$this->db->quote($this->getCertificateTest(), "integer").
			" WHERE obj_fi = ".$this->db->quote($this->id, "integer")
		);
	}

	/**
	 * Clone test
	 *
	 * @param int $a_new_test_id new test id
	 */
	function cloneTest($a_new_test_id)
	{
		$new_t = new rolTest($a_new_test_id);
		$new_t->setCertificateTest($this->getCertificateTest());
		$new_t->update();
	}


	/**
	 * Finish test processing
	 *
	 * @param
	 * @return
	 */
	function finishTestProcessing($active_id, $test_id, $a_test)
	{
		$this->log->debug("Active ID: ".$active_id);
		include_once("./Modules/Test/classes/class.ilTestParticipantData.php");
		$p = new ilTestParticipantData($this->db, $this->lng);
		$p->load($test_id);
		$a_user_id = $p->getUserIdByActiveId($active_id);
		$this->log->debug("User ID: ".$a_user_id);


		include_once("./Modules/Test/classes/class.ilObjTest.php");
		$test_obj_id = ilObjTest::_getObjectIDFromTestID($test_id);
		$is_passed = ilObjTestAccess::_isPassed($a_user_id, $test_obj_id);

		$this->log->debug("Passed: ".$is_passed);

		if(!$is_passed)
		{
			return;
		}

		include_once "./Services/Mail/classes/class.ilMail.php";
		$mail = new ilMail(ANONYMOUS_USER_ID);

		include_once './Services/User/classes/class.ilObjUser.php';
		$uname = ilObjUser::_lookupName($a_user_id);
		$usr_data = trim($uname["lastname"] . ", " . $uname["firstname"]);
		$gender = ilObjUser::_lookupGender($a_user_id);

		if($gender == 'm')
		{
			$title = 'geehrter Herr';
		}
		elseif($gender == 'f')
		{
			$title = 'geehrte Frau';
		}

		include_once "./Modules/Test/classes/class.ilObjTestVerification.php";
		$path = $this->createPdf($a_test, $a_user_id);
		include_once "./Services/Mail/classes/class.ilFileDataMail.php";
		$fd = new ilFileDataMail(ANONYMOUS_USER_ID);
		$fd->copyAttachmentFile($path, "Certificate.pdf");
		$file_names[] = "Certificate.pdf";

		/* Mail an Teilnehmer */
		$message2 = 'Sehr '.$title.' '.$usr_data.',

Ihre Prüfung im Training ist bewertet worden und Sie haben bestanden. Herzlichen Glückwunsch!
Im Anhang dieser E-Mail finden Sie Ihre persönliche Teilnahmebestätigung.

Mit freundlichen Grüßen,
Ihr Trainingsleiter';
		$client = new ilObjUser($a_user_id);
		$this->log->debug("Sending Mail to: ".$client->getEmail());
		$mail->sendMail(	/* Email to Client */
			$client->getEmail(), // to
			"", // cc
			"", // bcc
			"Mitteilung Prüfungsergebnis und Zusendung Teilnahmebestätigung", // subject
			$message2, // message
			count($file_names)>0 ? $file_names : array(), // attachments
			array('normal') // type
		);
		$this->log->debug("Mail sent.");

		/* Mail an Tutor */
		$message3 = 'Sehr '.$title.' '.$usr_data.',

Ihre Prüfung im Training ist bewertet worden und Sie haben bestanden. Herzlichen Glückwunsch!
Im Anhang dieser E-Mail finden Sie Ihre persönliche Teilnahmebestätigung.

Mit freundlichen Grüßen,
Ihr Trainingsleiter';
		$to = "rvs.arbeitssicherheit@raiffeisen.it";
		$to = "alex.killing@gmx.de";
		$this->log->debug("Sending Mail to: ".$to);
		$mail->sendMail(	/* Email to Client */
			$to, // to
			"", // cc
			"", // bcc
			"Mitteilung Prüfungsergebnis und Zusendung Teilnahmebestätigung", // subject
			$message3, // message
			count($file_names)>0 ? $file_names : array(), // attachments
			array('normal') // type
		);
		$this->log->debug("Mail sent.");

		if(count($file_names))
		{
			$fd->unlinkFiles($file_names);
			unset($fd);
			@unlink($file);
		}

	}

	/**
	 * @param ilObjTest $a_test
	 * @param $a_user_id
	 * @return string
	 */
	public function createPdf(ilObjTest $a_test, $a_user_id)
	{
		global $lng;

		$this->log->debug("Creating PDF");

		$lng->loadLanguageModule("wsp");

		include_once("./Modules/Test/classes/class.ilObjTestVerification.php");
		$newObj = new ilObjTestVerification();
		$newObj->setTitle($lng->txt("wsp_type_tstv")." \"".$a_test->getTitle()."\"");
		$newObj->setDescription($a_test->getDescription());

		$active_id = $a_test->getActiveIdOfUser($a_user_id);
		$pass = ilObjTest::_getResultPass($active_id);

		$date = $a_test->getPassFinishDate($active_id, $pass);
		$newObj->setProperty("issued_on", new ilDate($date, IL_CAL_UNIX));

		// create certificate
		include_once "Services/Certificate/classes/class.ilCertificate.php";
		include_once "Modules/Test/classes/class.ilTestCertificateAdapter.php";
		$certificate = new ilCertificate(new ilTestCertificateAdapter($a_test));
		$certificate = $certificate->outCertificate(array("active_id" => $active_id, "pass" => $pass), false);
		$this->log->debug("Got certificate: ".$certificate);

		// save pdf file
		if($certificate)
		{
			// we need the object id for storing the certificate file
			$newObj->create();

			$path = ilObjTestVerification::initStorage($newObj->getId(), "certificate");

			$file_name = "tst_".$a_test->getId()."_".$a_user_id."_".$active_id.".pdf";
			if(file_put_contents($path.$file_name, $certificate))
			{
				$newObj->setProperty("file", $file_name);
				$newObj->update();

				$this->log->debug("Returning: ".$path.$file_name);
				return $path.$file_name;
			}

			// file creation failed, so remove to object, too
			$newObj->delete();
		}
		$this->log->debug("Nothing to return - no certificate.");
		return "";
	}

}

?>