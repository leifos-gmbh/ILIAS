<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Repository for skill profile completion
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilSkillProfileCompletionRepository
{
    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var int
     */
    protected $profile_id;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * Constructor
     *
     * @param int $a_profile_id
     * @param int $a_user_id
     */
    public function __construct(int $a_profile_id, int $a_user_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->setProfileId($a_profile_id);
        $this->setUserId($a_user_id);
    }

    /**
     * Set profile id
     *
     * @param int $a_val profile id
     */
    public function setProfileId(int $a_val)
    {
        $this->profile_id = $a_val;
    }

    /**
     * Get profile id
     *
     * @return int profile id
     */
    public function getProfileId()
    {
        return $this->profile_id;
    }

    /**
     * Set user id
     *
     * @param int $a_val user id
     */
    public function setUserId(int $a_val)
    {
        $this->user_id = $a_val;
    }

    /**
     * Get user id
     *
     * @return int user id
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Get profile completion entries for given user-profile-combination
     *
     * @return array
     */
    public function getEntries()
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE profile_id = " . $ilDB->quote($this->getProfileId(), "integer") .
            " AND user_id = " . $ilDB->quote($this->getUserId(), "integer")
        );
        $entries = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entries[] = array(
                "profile_id" => $rec["profile_id"],
                "user_id" => $rec["user_id"],
                "date" => $rec["date"],
                "fulfilled" => $rec["fulfilled"]
            );
        }

        return $entries;
    }

    /**
     * Add profile fulfilment entry to given user-profile-combination
     */
    public function addFulfilmentEntry()
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE profile_id = " . $ilDB->quote($this->getProfileId(), "integer") .
            " AND user_id = " . $ilDB->quote($this->getUserId(), "integer") .
            " ORDER BY date DESC" .
            " LIMIT 1"
        );

        $entry = null;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entry = $rec["fulfilled"];
        }

        if (is_null($entry) || $entry == 0) {
            $now = ilUtil::now();
            $ilDB->manipulate("INSERT INTO skl_profile_completion " .
                "(profile_id, user_id, date, fulfilled) VALUES (" .
                $ilDB->quote($this->getProfileId(), "integer") . "," .
                $ilDB->quote($this->getUserId(), "integer") . "," .
                $ilDB->quote($now, "timestamp") . "," .
                $ilDB->quote(1, "integer") .
                ")");
        }
    }

    /**
     * Add profile non-fulfilment entry to given user-profile-combination
     */
    public function addNonFulfilmentEntry()
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE profile_id = " . $ilDB->quote($this->getProfileId(), "integer") .
            " AND user_id = " . $ilDB->quote($this->getUserId(), "integer") .
            " ORDER BY date DESC" .
            " LIMIT 1"
        );

        $entry = null;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entry = $rec["fulfilled"];
        }

        if (is_null($entry) || $entry == 1) {
            $now = ilUtil::now();
            $ilDB->manipulate("INSERT INTO skl_profile_completion " .
                "(profile_id, user_id, date, fulfilled) VALUES (" .
                $ilDB->quote($this->getProfileId(), "integer") . "," .
                $ilDB->quote($this->getUserId(), "integer") . "," .
                $ilDB->quote($now, "timestamp") . "," .
                $ilDB->quote(0, "integer") .
                ")");
        }
    }

    /**
     * Remove all profile completion entries for given user-profile-combination
     */
    public function removeEntries()
    {
        $ilDB = $this->db;

        $ilDB->manipulate("DELETE FROM skl_profile_completion WHERE "
            . " profile_id = " . $ilDB->quote($this->getProfileId(), "integer")
            . " AND user_id = " . $ilDB->quote($this->getUserId(), "integer")
        );
    }

    /**
     * Remove all profile completion entries
     */
    public function removeAllEntries()
    {
        $ilDB = $this->db;

        $ilDB->manipulate("DELETE FROM skl_profile_completion");
    }

    /**
     * Get all profile completion entries for a user
     *
     * @return array
     */
    public static function getFulfilledEntriesForUser(int $a_user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND fulfilled = 1"
        );
        $entries = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entries[] = array(
                "profile_id" => $rec["profile_id"],
                "user_id" => $rec["user_id"],
                "date" => $rec["date"],
                "fulfilled" => $rec["fulfilled"]
            );
        }

        return $entries;
    }

    /**
     * Get all profile completion entries for a user
     *
     * @return array
     */
    public static function getAllEntriesForUser(int $a_user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE user_id = " . $ilDB->quote($a_user_id, "integer")
        );
        $entries = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entries[] = array(
                "profile_id" => $rec["profile_id"],
                "user_id" => $rec["user_id"],
                "date" => $rec["date"]
            );
        }

        return $entries;
    }

    /**
     * Get all completion entries for a single profile
     *
     * @param int $a_profile_id
     *
     * @return array
     */
    public static function getAllEntriesForProfile(int $a_profile_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE profile_id = " . $ilDB->quote($a_profile_id, "integer")
        );
        $entries = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entries[] = array(
                "profile_id" => $rec["profile_id"],
                "user_id" => $rec["user_id"],
                "date" => $rec["date"]
            );
        }

        return $entries;
    }
}
