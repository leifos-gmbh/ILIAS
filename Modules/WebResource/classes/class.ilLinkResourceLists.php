<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilLinkResourceLists
 *
 * @author Thomas Famula <famula@leifos.com>
 */
class ilLinkResourceLists
{
    /**
     * @var int
     */
    protected $webr_id;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var int
     */
    protected $c_date;

    /**
     * @var int
     */
    protected $m_date;

    /**
     * ilLinkResourceLists constructor.
     * @param int $webr_id
     */
    public function __construct(int $webr_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->webr_id = $webr_id;
    }

    /**
     * @param int $a_webr_id
     * @param int $a_list_id
     * @return array
     */
    public static function lookupList(int $a_webr_id, int $a_list_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM webr_lists " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer') . " " .
            "AND list_id = " . $ilDB->quote($a_list_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $list['title']				= $row->title;
            $list['description']		= $row->description;
            $list['create_date']		= $row->create_date;
            $list['last_update']		= $row->last_update;
            $list['list_id']			= $row->list_id;
        }
        return $list ? $list : array();
    }

    /**
     * @param int $a_list_id
     * @param string $a_title
     * @return bool
     */
    public static function updateTitle(int $a_list_id, string $a_title)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'UPDATE webr_lists SET ' .
            'title = ' . $ilDB->quote($a_title, 'text') . ' ' .
            'WHERE list_id = ' . $ilDB->quote($a_list_id, 'integer');
        $ilDB->manipulate($query);
        return true;
    }


    public function setListResourceId(int $a_id)
    {
        $this->webr_id = $a_id;
    }
    public function getListResourceId()
    {
        return $this->webr_id;
    }
    public function setListId(int $a_id)
    {
        $this->id = $a_id;
    }
    public function getListId()
    {
        return $this->id;
    }
    public function setTitle(string $a_title)
    {
        $this->title = $a_title;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function setDescription(string $a_description)
    {
        $this->description = $a_description;
    }
    public function getDescription()
    {
        return $this->description;
    }
    protected function setCreateDate(int $a_date)
    {
        $this->c_date = $a_date;
    }
    public function getCreateDate()
    {
        return $this->c_date;
    }
    protected function setLastUpdateDate(int $a_date)
    {
        $this->m_date = $a_date;
    }
    public function getLastUpdateDate()
    {
        return $this->m_date;
    }


    /**
     * @param int $a_list_id
     * @param bool $a_update_history
     * @return bool
     */
    public function delete(int $a_list_id, bool $a_update_history = true)
    {
        $ilDB = $this->db;

        $list = $this->getList($a_list_id);

        $query = "DELETE FROM webr_lists " .
            "WHERE webr_id = " . $ilDB->quote($this->getListResourceId(), 'integer') . " " .
            "AND list_id = " . $ilDB->quote($a_list_id, 'integer');
        $res = $ilDB->manipulate($query);

        if ($a_update_history) {
            ilHistory::_createEntry(
                $this->getListResourceId(),
                "delete",
                $list['title']
            );
        }

        return true;
    }

    /**
     * @param bool $a_update_history
     * @return bool
     */
    public function update($a_update_history = true)
    {
        $ilDB = $this->db;

        if (!$this->getListResourceId()) {
            return false;
        }

        $this->setLastUpdateDate(time());
        $query = "UPDATE webr_lists " .
            "SET title = " . $ilDB->quote($this->getTitle(), 'text') . ", " .
            "description = " . $ilDB->quote($this->getDescription(), 'text') . ", " .
            "last_update = " . $ilDB->quote($this->getLastUpdateDate(), 'integer') . " " .
            "WHERE webr_id = " . $ilDB->quote($this->getListResourceId(), 'integer');
        $res = $ilDB->manipulate($query);

        if ($a_update_history) {
            ilHistory::_createEntry(
                $this->getListResourceId(),
                "update",
                $this->getTitle()
            );
        }

        return true;
    }

    /**
     * @param bool $a_update_history
     * @return int
     */
    public function add($a_update_history = true)
    {
        $ilDB = $this->db;

        $this->setLastUpdateDate(time());
        $this->setCreateDate(time());

        $next_id = $ilDB->nextId('webr_lists');
        $query = "INSERT INTO webr_lists (list_id,title,description,last_update,create_date,webr_id) " .
            "VALUES( " .
            $ilDB->quote($next_id, 'integer') . ", " .
            $ilDB->quote($this->getTitle(), 'text') . ", " .
            $ilDB->quote($this->getDescription(), 'text') . ", " .
            $ilDB->quote($this->getLastUpdateDate(), 'integer') . ", " .
            $ilDB->quote($this->getCreateDate(), 'integer') . ", " .
            $ilDB->quote($this->getListResourceId(), 'integer') . " " .
            ")";
        $res = $ilDB->manipulate($query);

        $list_id = $next_id;
        $this->setListId($list_id);

        if ($a_update_history) {
            ilHistory::_createEntry(
                $this->getListResourceId(),
                "add",
                $this->getTitle()
            );
        }

        return $list_id;
    }

    /**
     * @param int $a_list_id
     * @return bool
     */
    public function readList(int $a_list_id)
    {        $ilDB = $this->db;

        $query = "SELECT * FROM webr_lists " .
            "WHERE list_id = " . $ilDB->quote($a_list_id, 'integer');

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setTitle($row->title);
            $this->setDescription($row->description);
            $this->setCreateDate($row->create_date);
            $this->setLastUpdateDate($row->last_update);
            $this->setListId($row->list_id);
        }
        return true;
    }

    /**
     * @param int $a_list_id
     * @return array
     */
    public function getList(int $a_list_id)
    {
        $ilDB = $this->db;

        $query = "SELECT * FROM webr_lists " .
            "WHERE webr_id = " . $ilDB->quote($this->getListResourceId(), 'integer') . " " .
            "AND list_id = " . $ilDB->quote($a_list_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $list['title']				= $row->title;
            $list['description']		= $row->description;
            $list['create_date']		= $row->create_date;
            $list['last_update']		= $row->last_update;
            $list['list_id']			= $row->list_id;
        }
        return $list ? $list : array();
    }

    /**
     * @param int $a_webr_id
     * @return array
     */
    public static function getAllListIds(int $a_webr_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT list_id FROM webr_lists " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $list_ids[] = $row['list_id'];
        }
        return (array) $list_ids;
    }

    /**
     * @return array
     */
    public function getAllLists()
    {
        $ilDB = $this->db;

        $query = "SELECT * FROM webr_lists " .
            "WHERE webr_id = " . $ilDB->quote($this->getListResourceId(), 'integer');

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $lists[$row->list_id]['title']				= $row->title;
            $lists[$row->list_id]['description']		= $row->description;
            $lists[$row->list_id]['create_date']		= $row->create_date;
            $lists[$row->list_id]['last_update']		= $row->last_update;
            $lists[$row->list_id]['list_id']			= $row->list_id;
        }
        return $lists ? $lists : array();
    }

    /**
     * Check if a weblink list was already created or transformed from a single weblink
     * @return bool
     */
    public static function checkListStatus(int $a_obj_id)
    {
        $list_obj = new ilLinkResourceLists($a_obj_id);
        $lists = $list_obj->getAllLists();
        if (!empty($lists)) {
            return true;
        }
        return false;
    }

    /**
     * @param int $webr_id
     * @return bool
     */
    public static function _deleteAll(int $webr_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate("DELETE FROM webr_lists WHERE webr_id = " . $ilDB->quote($webr_id, 'integer'));

        return true;
    }
}
