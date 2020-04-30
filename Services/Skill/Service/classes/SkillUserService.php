<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Service;

/**
 * Skill user service
 * @author famula@leifos.de
 */
class SkillUserService
{
    /**
     * @var SkillUserManagerService
     */
    protected $manager_service;

    /**
     * @var int $user_id
     */
    protected $user_id;

    /**
     * Constructor
     */
    public function __construct(int $user_id)
    {
        global $DIC;

        $this->user_id = $user_id;
        $this->manager_service = $DIC->skills()->internal()->manager();
    }

    /**
     * @param int    $a_level_id
     * @param int    $a_user_id
     * @param int    $a_trigger_ref_id
     * @param int    $a_tref_id
     * @param bool   $a_self_eval
     * @param string $a_unique_identifier
     */
    public function writeSkillLevel(
        int $a_level_id,
        int $a_user_id,
        int $a_trigger_ref_id,
        int $a_tref_id = 0,
        bool $a_self_eval = false,
        string $a_unique_identifier = ""
    ) {
        //TODO: next level percentage fulfilment value (value must be >=0 and <1)
        $this->manager_service->writeSkillLevel($a_level_id, $a_user_id,
            $a_trigger_ref_id, $a_tref_id, $a_self_eval, $a_unique_identifier);
    }

    public function getProfiles()
    {
        // repo for ilSkillProfile needed
    }
}