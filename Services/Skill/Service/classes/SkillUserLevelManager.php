<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Service;

/**
 * Skill user level manager
 * @author famula@leifos.de
 */
class SkillUserLevelManager
{
    /**
     * @var SkillInternalRepoService
     */
    protected $repo_service;

    /**
     * Constructor
     */
    public function __construct(SkillInternalRepoService $repo_service = null)
    {
        global $DIC;

        $this->repo_service = ($repo_service)
            ? $repo_service
            : $DIC->skills()->internal()->repo();
    }

    /**
     * @param int    $user_id
     * @param int    $a_level_id
     * @param int    $a_trigger_ref_id
     * @param int    $a_tref_id
     * @param bool   $a_self_eval
     * @param string $a_unique_identifier
     * @param float  $a_next_level_fulfilment
     */
    public function writeSkillLevel(
        int $user_id,
        int $a_level_id,
        int $a_trigger_ref_id,
        int $a_tref_id,
        bool $a_self_eval,
        string $a_unique_identifier,
        float $a_next_level_fulfilment
    ) {
        $skill_id = $this->repo_service->getLevelRepo()->lookupLevelSkillId($a_level_id);
        $trigger_ref_id = $a_trigger_ref_id;
        $obj_adapter = new \ilSkillObjectAdapter();
        $trigger_obj_id = $obj_adapter->getObjIdForRefId($trigger_ref_id);
        $trigger_title = $obj_adapter->getTitleForObjId($trigger_obj_id);
        $trigger_type = $obj_adapter->getTypeForObjId($trigger_obj_id);

        $update = false;

        // self evaluations will update, if the last self evaluation is on the same day
        if ($a_self_eval && $this->repo_service->getUserLevelRepo()->hasRecentSelfEvaluation($trigger_obj_id,
                $user_id, $skill_id, $a_tref_id, $trigger_ref_id)) {
            $status_date = $this->repo_service->getUserLevelRepo()->hasRecentSelfEvaluation($trigger_obj_id, $user_id,
                $skill_id, $a_tref_id, $trigger_ref_id);
            if ($status_date != "") {
                $update = true;
            }
        }

        //next level percentage fulfilment value must be >=0 and <1
        if (!($a_next_level_fulfilment >= 0) || !($a_next_level_fulfilment < 1)) {
            throw new \UnexpectedValueException(
                "Next level fulfilment must be equal to or greater than 0 and less than 1, '" .
                $a_next_level_fulfilment . "' given."
            );
        }

        $this->repo_service->getUserLevelRepo()->writeUserSkillLevelStatus($skill_id, $trigger_ref_id, $trigger_obj_id,
            $trigger_title, $trigger_type, $update, $status_date, $a_level_id, $user_id, $a_tref_id, $a_self_eval,
            $a_unique_identifier, $a_next_level_fulfilment);
    }
}