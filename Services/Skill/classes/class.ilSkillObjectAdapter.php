<?php

/**
 * Class ilBasicSkillObjectAdapter
 */
class ilSkillObjectAdapter implements ilSkillObjectAdapterInterface
{
    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * Get object id for reference id
     * @param int $a_ref_id
     * @return int
     */
    public function getObjIdForRefId(int $a_ref_id) : int
    {
        $trigger_obj_id = ($a_ref_id > 0)
            ? ilObject::_lookupObjId($a_ref_id)
            : 0;

        return $trigger_obj_id;
    }

    /**
     * Get object type for object id
     * @param int $a_obj_id
     * @return string
     */
    public function getTypeForObjId(int $a_obj_id) : string
    {
        return ilObject::_lookupType($a_obj_id);
    }

    /**
     * Get object title for object id
     * @param int $a_obj_id
     * @return string
     */
    public function getTitleForObjId(int $a_obj_id) : string
    {
        return ilObject::_lookupTitle($a_obj_id);
    }

}