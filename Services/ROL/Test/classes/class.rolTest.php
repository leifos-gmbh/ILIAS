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

		$this->settings = new ilSetting("raiffrol");
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

		// build certificate
		include_once("./Services/ROL/Certificate/classes/class.rolCertificate.php");
		$path = rolCertificate::buildNewCertificate($a_test, $a_user_id);

		// send mail
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
		//$to = "rvs.arbeitssicherheit@raiffeisen.it";
		$to = $this->settings->get("cert_email");
		if ($to != "")
		{
			$this->log->debug("Sending Mail to: " . $to);
			$mail->sendMail(    /* Email to Client */
					$to, // to
					"", // cc
					"", // bcc
					"Mitteilung Prüfungsergebnis und Zusendung Teilnahmebestätigung", // subject
					$message3, // message
					count($file_names) > 0 ? $file_names : array(), // attachments
					array('normal') // type
			);
			$this->log->debug("Mail sent.");
		}

		if(count($file_names))
		{
			$fd->unlinkFiles($file_names);
			unset($fd);
		}

	}

	/**
	 * Get all certificate tests
	 *
	 * @return array all certificate tests "ref_id", "obj_id"
	 */
	static function getAllCertificateTests()
	{
		global $ilDB;

		$set = $ilDB->query("SELECT r.obj_id, r.ref_id FROM tst_tests t JOIN object_reference r ON (t.obj_fi = r.obj_id)".
			" WHERE t.is_certificate_test = ".$ilDB->quote(1, "integer")
			);
		$tests = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$tests[] = $rec;
		}
		return $tests;
	}

}

?>