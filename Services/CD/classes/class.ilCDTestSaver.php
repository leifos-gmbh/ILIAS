<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * CDC Test Saver 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilCDTestSaver
{
	/**
	 * saveTestResults
	 *
	 * @param
	 * @return
	 */
	static function saveTestResults()
	{
		global $ilDB, $ilSetting;
		
		$sess_id  = $_POST['user_id'];
		$data = $_POST;

		// check if session id exists in table
		$set = $ilDB->query("SELECT * FROM cd_ext_test_sess WHERE ".
			" session_id = ".$ilDB->quote($sess_id, "text")
		);
		$rec = $ilDB->fetchAssoc($set);
		//$rec = array("user_id" => 253, "session_id" => "14af7e99b461aabe43aaac572624ef40");
		if ($rec)
		{
			$set2 = $ilDB->query("SELECT * FROM usr_session WHERE ".
				" session_id = ".$ilDB->quote($sess_id, "text")
			);
			$rec2 = $ilDB->fetchAssoc($set2);

			// we got a test session -> refresh ilias session
			$ilDB->replace("usr_session", array(
					"session_id" => array("text", $rec["session_id"])
				),array(
					"data" => array("clob", $rec["sess_data"]),
					"expires" => array("integer", time() + 3600),
					"ctime" => array("integer", time()),
					"user_id" => array("integer", $rec["user_id"]),
					"last_remind_ts" => array("integer", $rec2["last_remind_ts"]),
					"type" => array("integer", $rec2["type"]),
					"createtime" => array("integer", $rec2["createtime"]),
					"remote_addr" => array("text", $rec2["remote_addr"])
				));
	
			$type = explode("_",$data['test_id']);
			$duration = explode(":",$data['test_duration']);
			$duration = $duration[2] + (60 * $duration[1]) + (60 * 60 * $duration[0]);
	
			$nid = $ilDB->nextId("cd_ext_test");
			$ilDB->manipulate("INSERT INTO cd_ext_test ".
				"(id, user_id, ts, target_lang, in_lang, duration, finished, points, result_level".
				", result_grammar, result_voca, result_read, result_listen) VALUES (".
				$ilDB->quote($nid, "integer").",".
				$ilDB->quote($rec["user_id"], "integer").",".
				$ilDB->quote(ilUtil::now(), "timestamp").",".
				$ilDB->quote($type[1], "text").",".
				$ilDB->quote($type[2], "text").",".
				$ilDB->quote($duration, "integer").",".
				$ilDB->quote($data["test_finished"], "integer").",".
				$ilDB->quote($data["result_points"], "float").",".
				$ilDB->quote($data["result_level"], "text").",".
				$ilDB->quote($data["result_grammar"], "integer").",".
				$ilDB->quote($data["result_voca"], "integer").",".
				$ilDB->quote($data["result_read"], "integer").",".
				$ilDB->quote($data["result_listen"], "integer").
			")");
			
			// get user
			$set2 = $ilDB->query("SELECT * FROM usr_data ".
				" WHERE usr_id = ".$ilDB->quote($rec["user_id"], "integer"));
			$rec2 = $ilDB->fetchAssoc($set2);

			// get company
			$set3 = $ilDB->query("SELECT * FROM cd_company ".
				" WHERE id = ".$ilDB->quote($rec2["company_id"], "integer"));
			$rec3 = $ilDB->fetchAssoc($set3);

			// get creation user
			$set4 = $ilDB->query("SELECT * FROM usr_data ".
				" WHERE usr_id = ".$ilDB->quote($rec3["creation_user"], "integer"));
			$rec4 = $ilDB->fetchAssoc($set4);
			
			
			
			if ($rec4["email"] != "")
			{
				// send mail to owner of component
				include_once("./Services/Mail/classes/class.ilMimeMail.php");
				$m = new ilMimeMail();
				$m->From($ilSetting->get("admin_email"));
				$m->To($rec4["email"]);
				// über $m->Cc($rec4["email"]);
				$m->Subject("Carl Duisberg Teilnehmerportal: Neue Einstufung abgeschlossen.");	
				$message= "Sehr geehrte Damen und Herren,\n\nder folgende Benutzer hat eine Einstufung abgeschlossen:".
				"\n\nFirma: ".$rec3["title"].
				"\nName: ".$rec2["lastname"].
				"\nVorname: ".$rec2["firstname"].
				/// EINBAUEN ÜBER data array (points, duration, target_lang) /// Ergänzung Heller Sprache des TESTS - 14.09.2015
				"\nLevel: ".$data["result_level"].
				"\nTestID: ".$data["test_id"].
				"\nZielsprache: ".$type[1].
				"\n\nMit freundlichen Grüßen".
				"\n\n".ILIAS_HTTP_PATH;
				$m->Body($message);
				//$m->Priority(4) ;	// set the priority to Low 
				$m->Send();
			}
			
		}

	}
	
}

?>
