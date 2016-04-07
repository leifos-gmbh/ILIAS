<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  Patch class for certificates
 *
 * @author Alex Killing <killing@leifos.de>
 */
class rolCertificate
{
	/**
	 * certificate id
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Constructor
	 *
	 * @param int $a_id Test ID
	 */
	function __construct($a_id = 0)
	{
		global $ilDB, $tree;

		$this->settings = new ilSetting("raiffrol");
		$this->log = ilLoggerFactory::getLogger('raiffrol');
		$this->tree = $tree;
		$this->id = $a_id;
		$this->db = $ilDB;
		if ($this->id > 0)
		{
			$this->read();
		}
	}

	/**
	 * Set id
	 *
	 * @param int $a_val id
	 */
	function setId($a_val)
	{
		$this->id = $a_val;
	}

	/**
	 * Get id
	 *
	 * @return int id
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 * Set user id
	 *
	 * @param int $a_val user id
	 */
	function setUserId($a_val)
	{
		$this->user_id = $a_val;
	}

	/**
	 * Get user id
	 *
	 * @return int user id
	 */
	function getUserId()
	{
		return $this->user_id;
	}

	/**
	 * Set test object id
	 *
	 * @param int $a_val test obj id	
	 */
	function setTestObjId($a_val)
	{
		$this->test_obj_id = $a_val;
	}
	
	/**
	 * Get test object id
	 *
	 * @return int test obj id
	 */
	function getTestObjId()
	{
		return $this->test_obj_id;
	}
	
	/**
	 * Set created date
	 *
	 * @param string $a_val created date	
	 */
	function setCreated($a_val)
	{
		$this->created = $a_val;
	}
	
	/**
	 * Get created date
	 *
	 * @return string created date
	 */
	function getCreated()
	{
		return $this->created;
	}
	
	/**
	 * Set verified
	 *
	 * @param string $a_val verification date	
	 */
	function setVerified($a_val)
	{
		$this->verified = $a_val;
	}
	
	/**
	 * Get verified
	 *
	 * @return string verification date
	 */
	function getVerified()
	{
		return $this->verified;
	}
	
	/**
	 * Read
	 */
	function read()
	{
		$set = $this->db->query($q = "SELECT * FROM rol_certificate ".
			" WHERE id  = ".$this->db->quote($this->id, "integer")
		);
		$rec = $this->db->fetchAssoc($set);
		$this->setUserId($rec["user_id"]);
		$this->setTestObjId($rec["test_obj_id"]);
		$this->setCreated($rec["created"]);
		$this->setVerified($rec["verified"]);
	}

	/**
	 * Create
	 */
	function create()
	{
		$this->setId($this->db->nextId("rol_certificate"));
		$this->db->manipulate("INSERT INTO rol_certificate ".
			"(id, user_id, test_obj_id, created, verified) VALUES (".
			$this->db->quote($this->getId(), "integer").",".
			$this->db->quote($this->getUserId(), "integer").",".
			$this->db->quote($this->getTestObjId(), "integer").",".
			$this->db->quote($this->getCreated(), "timestamp").",".
			$this->db->quote($this->getVerified(), "timestamp").
			")");
	}


	/**
	 * Update
	 */
	function update()
	{
		$this->db->manipulate($q = "UPDATE rol_certificate SET ".
			" user_id = ".$this->db->quote($this->getUserId(), "integer").
			", test_obj_id = ".$this->db->quote($this->getUserId(), "integer").
			", created = ".$this->db->quote($this->getUserId(), "timestamp").
			", verified = ".$this->db->quote($this->getUserId(), "timestamp").
			" WHERE id = ".$this->db->quote($this->id, "integer")
		);
	}

	/**
	 * Build new certificate
	 *
	 * @param ilObjTest $a_test
	 * @param $a_user_id
	 * @return string
	 */
	static public function buildNewCertificate(ilObjTest $a_test, $a_user_id)
	{
		global $lng;

		$log = ilLoggerFactory::getLogger('raiffrol');
		$log->debug("Creating PDF");

		$rol_certificate = new rolCertificate();
		$rol_certificate->setTestObjId($a_test->getId());
		$rol_certificate->setUserId($a_user_id);
		$rol_certificate->setCreated(ilUtil::now());
		$rol_certificate->create();


		////
		//// This is the "classic" way, the original patch created certificate files
		////

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
		$log->debug("Got certificate: ".strlen($certificate));

		// save pdf file
		$full_path = "";
		if($certificate)
		{
			// we need the object id for storing the certificate file
			$newObj->create();

			$path = ilObjTestVerification::initStorage($newObj->getId(), "certificate");

			// put file into /<data_dir>/51rol/ilVerification/2/vrfc_273/certificate/tst_269_270_12.pdf

			$file_name = "tst_".$a_test->getId()."_".$a_user_id."_".$active_id.".pdf";
			if(file_put_contents($path.$file_name, $certificate))
			{
				$newObj->setProperty("file", $file_name);
				$newObj->update();

				$log->debug("Returning: ".$path.$file_name);
				$full_path = $path.$file_name;
			}
			else
			{
				// file creation failed, so remove to object, too
				$newObj->delete();
				$full_path = "";
			}
		}
		else
		{
			$log->debug("Nothing to return - no certificate.");
		}

		if ($full_path != "")
		{
			// new: move certificate to interface directory
			$rol_certificate->handoverToExternalDirectory($full_path, $a_test);
		}

		$rol_certificate->verify($a_test);

		return $full_path;
	}

	/**
	 * Get xml filename
	 *
	 * @return string filename
	 */
	function getXmlFilename()
	{
		return $this->getId().".xml";
	}

	/**
	 * Get pdf filename
	 *
	 * @return string filename
	 */
	function getPdfFilename()
	{
		return $this->getId().".pdf";
	}


	/**
	 * Handover certificate to external directory
	 *
	 * @param string $path original certificate file
	 */
	function handoverToExternalDirectory($path, $a_test)
	{
		$this->log->debug("Start Handover");

		// user
		$user = new ilObjUser($this->getUserId());
		$udf_id = $this->settings->get("abi_udf");
		$user_abi = "";
		if ($udf_id > 0)
		{
			include_once("./Services/User/classes/class.ilUserDefinedData.php");
			$d = ilUserDefinedData::lookupData(array($this->getUserId()), array($udf_id));
			$user_abi = $d[$this->getUserId()][$udf_id];
		}

		// course
		$course_ref_id = $this->tree->checkForParentType((int) $a_test->getRefId(), "crs");
		$course_obj_id = ilObject::_lookupObjId($course_ref_id);
		$course_title = ilObject::_lookupTitle($course_obj_id);
		include_once("./Services/ROL/Course/classes/class.rolCourse.php");
		$rol_course = new rolCourse($course_obj_id);

		// time
		$hours = floor($rol_course->getMinOnlinetime()/60);
		$minutes = $rol_course->getMinOnlinetime() % 60;
		$time = $hours.":".str_pad($minutes, 2, "0", STR_PAD_LEFT);

		$cert_dir = $this->settings->get("cert_dir");
		$this->log->debug("Cert Directory: ".$cert_dir);
		if ($this->settings->get("cert_dir") != "")
		{
			$ext_file = $this->getPdfFilename();
			$this->log->debug("Copy ".$path." to ".$cert_dir."/".$ext_file.".");
			copy ($path, $cert_dir."/".$ext_file);

			// write xml
			$xml_file = $this->getXmlFilename();
			include_once "./Services/Xml/classes/class.ilXmlWriter.php";
			$writer = new ilXmlWriter();
			$writer->xmlHeader();
			$writer->xmlStartTag("certificate", array());

			$writer->xmlElement("certificate_date", array(), substr($this->getCreated(), 0, strpos($this->getCreated(), " ")));
			$writer->xmlElement("certificate_filename", array(), $ext_file);
			$writer->xmlElement("course_name_de", array(), $course_title);
			$writer->xmlElement("course_name_it", array(), "");
			$writer->xmlElement("course_content_de", array(), "");
			$writer->xmlElement("course_content_it", array(), "");
			$writer->xmlElement("course_hours", array(), $time);
			$writer->xmlElement("asix_qualification", array(), $rol_course->getAsixKey());
			$writer->xmlElement("firstname", array(), $user->getFirstname());
			$writer->xmlElement("lastname", array(), $user->getLastname());
			$writer->xmlElement("client_key", array(), $user_abi);
			$writer->xmlElement("language", array(), $user->getLanguage());
			$writer->xmlElement("gender", array(), $user->getGender());
			$writer->xmlElement("date_of_birth", array(), $user->getBirthday());
			$writer->xmlElement("tax_number", array(), $user->getDepartment());
			$writer->xmlElement("email", array(), $user->getEmail());
			$writer->xmlElement("institution", array(), $user->getInstitution());
			$writer->xmlElement("department", array(), $user->getDepartment());
			$writer->xmlElement("adress", array(), $user->getStreet());
			$writer->xmlElement("postal_code", array(), $user->getZipcode());
			$writer->xmlElement("city", array(), $user->getCity());
			$writer->xmlElement("comment", array(), "");

			$writer->xmlEndTag("certificate");
			$this->log->debug("Create XMl File at ".$cert_dir."/".$xml_file.".");
			$writer->xmlDumpFile($cert_dir."/".$xml_file, false);
		}
	}

	/**
	 * Verify xml and pdf creation
	 */
	function verify($a_test)
	{
		$cert_dir = $this->settings->get("cert_dir");
		if (is_file($cert_dir."/".$this->getXmlFilename()) && is_file($cert_dir."/".$this->getPdfFilename()))
		{
			$this->log->debug("Set verified.");
			$this->setVerified(ilUtil::now());
			$this->update();
		}
		else
		{
			// user
			$user = new ilObjUser($this->getUserId());

			// course
			$course_ref_id = $this->tree->checkForParentType((int) $a_test->getRefId(), "crs");
			$course_obj_id = ilObject::_lookupObjId($course_ref_id);
			$course_title = ilObject::_lookupTitle($course_obj_id);

			// send mail
			include_once "./Services/Mail/classes/class.ilMail.php";
			$mail = new ilMail(ANONYMOUS_USER_ID);

			$this->log->error("Certificate XML Interface Error. Files not written '".$cert_dir."/".$this->getXmlFilename().
					"', '".$cert_dir."/".$this->getPdfFilename()."'. Course: ".$course_title.". User: ".$user->getFirstname()." ".$user->getLastname()." [".$user->getLogin()."]".".");

			/* Mail an Admin */
			$m = 'Fehler in der ROL Zertifikatsgenerierung:

die Dateien '.$cert_dir."/".$this->getXmlFilename().' und '.$cert_dir."/".$this->getPdfFilename().' konnten nicht geschrieben werden.
Kurs: '.$course_title.'
Benutzer: '.$user->getFirstname()." ".$user->getLastname()." [".$user->getLogin()."]";
			//$to = "rvs.arbeitssicherheit@raiffeisen.it";
			$to = $this->settings->get("cert_email");
			if ($to != "")
			{
				$this->log->debug("Sending Mail to: " . $to);
				$mail->sendMail(    /* Email to Client */
						$to, // to
						"", // cc
						"", // bcc
						"Fehler in der ROL Zertifikatsgenerierung", // subject
						$m, // message
						array(),
						array('normal') // type
				);
				$this->log->debug("Mail sent.");
			}

		}
	}
	

}

?>