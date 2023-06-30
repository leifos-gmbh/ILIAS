<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * CDC Test Saver
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilCDTestSaver implements ilMailMimeSender
{
    /**
     * @return bool
     */
    public function hasReplyToAddress() : bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getReplyToAddress() : string
    {
        return "";
    }

    /**
     * @return string
     */
    public function getReplyToName() : string
    {
        return "";
    }

    /**
     * @return bool
     */
    public function hasEnvelopFromAddress() : bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getEnvelopFromAddress() : string
    {
        return "";
    }

    /**
     * @return string
     */
    public function getFromAddress() : string
    {
        global $ilSetting;
        return $ilSetting->get("admin_email");
    }

    /**
     * @return string
     */
    public function getFromName() : string
    {
        global $ilSetting;
        return $ilSetting->get("admin_email");
    }

    /**
     * saveTestResults
     *
     * @param
     * @return
     */
    public static function saveTestResults()
    {
        global $ilDB, $ilSetting, $DIC;
        
        $sess_id = $_POST['user_id'];
        $data = $_POST;
        // check if session id exists in table
        $set = $ilDB->query(
            "SELECT * FROM cd_ext_test_sess WHERE " .
            " session_id = " . $ilDB->quote($sess_id, "text")
        );
        $rec = $ilDB->fetchAssoc($set);
        //		$rec = array("user_id" => 253, "session_id" => "6349179172610e5e381f2e77dc038ab2");
        if ($rec) {
            $set2 = $ilDB->query(
                "SELECT * FROM usr_session WHERE " .
                " session_id = " . $ilDB->quote($sess_id, "text")
            );
            $rec2 = $ilDB->fetchAssoc($set2);
            // we got a test session -> refresh ilias session
            $ilDB->replace("usr_session", array(
                    "session_id" => array("text", $rec["session_id"])
                ), array(
                    "data" => array("clob", $rec["sess_data"]),
                    "expires" => array("integer", time() + 3600),
                    "ctime" => array("integer", time()),
                    "user_id" => array("integer", $rec["user_id"]),
                    "last_remind_ts" => array("integer", (int) $rec2["last_remind_ts"]),
                    "type" => array("integer", $rec2["type"]),
                    "createtime" => array("integer", $rec2["createtime"]),
                    "remote_addr" => array("text", $rec2["remote_addr"])
                ));
    
            $type = explode("_", $data['test_id']);
            $duration = explode(":", $data['test_duration']);
            $duration = $duration[2] + (60 * $duration[1]) + (60 * 60 * $duration[0]);
    
            $nid = $ilDB->nextId("cd_ext_test");
            $ilDB->manipulate("INSERT INTO cd_ext_test " .
                "(id, user_id, ts, target_lang, in_lang, duration, finished, points, result_level" .
                ", result_grammar, result_voca, result_read, result_listen) VALUES (" .
                $ilDB->quote($nid, "integer") . "," .
                $ilDB->quote($rec["user_id"], "integer") . "," .
                $ilDB->quote(ilUtil::now(), "timestamp") . "," .
                $ilDB->quote($type[1], "text") . "," .
                $ilDB->quote($type[2], "text") . "," .
                $ilDB->quote($duration, "integer") . "," .
                $ilDB->quote($data["test_finished"], "integer") . "," .
                $ilDB->quote($data["result_points"], "float") . "," .
                $ilDB->quote($data["result_level"], "text") . "," .
                $ilDB->quote($data["result_grammar"], "integer") . "," .
                $ilDB->quote($data["result_voca"], "integer") . "," .
                $ilDB->quote($data["result_read"], "integer") . "," .
                $ilDB->quote($data["result_listen"], "integer") .
            ")");
            // get user
            $set2 = $ilDB->query("SELECT * FROM usr_data " .
                " WHERE usr_id = " . $ilDB->quote($rec["user_id"], "integer"));
            $rec2 = $ilDB->fetchAssoc($set2);

            // get company
            $set3 = $ilDB->query("SELECT * FROM cd_company " .
                " WHERE id = " . $ilDB->quote($rec2["company_id"], "integer"));
            $rec3 = $ilDB->fetchAssoc($set3);

            // get creation user
            $set4 = $ilDB->query("SELECT * FROM usr_data " .
                " WHERE usr_id = " . $ilDB->quote($rec3["creation_user"], "integer"));
            $rec4 = $ilDB->fetchAssoc($set4);

//            $rec4["email"] = "alex.killing@gmx.de";
            
            if ($rec4["email"] != "") {
                // set dic user to root
                $set5 = $ilDB->query("SELECT * FROM usr_data " .
                    " WHERE login = " . $ilDB->quote("root", "text"));
                $rec5 = $ilDB->fetchAssoc($set5);
                $DIC["ilUser"] = new ilObjUser($rec5["usr_id"]);

                // send mail to owner of component
                include_once("./Services/Mail/classes/class.ilMimeMail.php");
                $m = new ilMimeMail();
                $m->From(new ilCDTestSaver());
                $m->To($rec4["email"]);
                // über $m->Cc($rec4["email"]);
                $m->Subject("Carl Duisberg Teilnehmerportal: Neue Einstufung abgeschlossen.");
                $message = "Sehr geehrte Damen und Herren,\n\nder folgende Benutzer hat eine Einstufung abgeschlossen:" .
                "\n\nFirma: " . $rec3["title"] .
                "\nName: " . $rec2["lastname"] .
                "\nVorname: " . $rec2["firstname"] .
                /// EINBAUEN ÜBER data array (points, duration, target_lang) /// Ergänzung Heller Sprache des TESTS - 14.09.2015
                /// Neue Daten in der Mail an Kundenbetreuer/Mitarbeiter der Centren --> Heller 26.06.2018
                "\n\nLevel: " . $data["result_level"] .
                "\nPunkte: " . $data["result_points"] .
                "\n\nGrammatik: " . $data["result_grammar"] .
                "\nVokabeln: " . $data["result_voca"] .
                "\nLesen: " . $data["result_read"] .
                "\nHören: " . $data["result_listen"] .
                "\n\nTestID: " . $data["test_id"] .
                "\nZielsprache: " . $type[1] .
                "\n\nMit freundlichen Grüßen" .
                "\n\n\nDas interne Punktesystem" .
                "\n0     -     1.4   A1" .
                "\n1.4   -     2.4   A2.1" .
                "\n2.5   -     3.4   A2.2" .
                "\n3.5   -     4.4   B1.1" .
                "\n4.5   -     5.4   B1.2" .
                "\n5.5   -     6.4   B2.1" .
                "\n6.5   -     7.4   B2.2" .
                "\n7.5   -     8.4   B2.3" .
                "\n8.5   -     9.4   C1.1" .
                "\n9.5   -     9.8   C1.2" .
                "\n9.9         +     C1.2" .
                "\n\n" . ILIAS_HTTP_PATH;
                $m->Body($message);
                //$m->Priority(4) ;	// set the priority to Low
                $m->Send();
            }
        }
    }
}
