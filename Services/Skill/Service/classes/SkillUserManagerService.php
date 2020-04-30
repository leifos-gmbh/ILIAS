<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Service;

/**
 * Skill user manager service
 * @author famula@leifos.de
 */
class SkillUserManagerService
{
    /**
     * @var SkillInternalRepoService
     */
    protected $repo_service;

    /**
     * Constructor
     */
    public function __construct(SkillInternalRepoService $repo_service)
    {
        $this->repo_service = $repo_service;
    }

    /**
     * @return
     */
    public function writeSkillLevel(
        int $a_level_id,
        int $a_user_id,
        int $a_trigger_ref_id,
        int $a_tref_id,
        bool $a_self_eval,
        string $a_unique_identifier
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
                $a_user_id, $skill_id, $a_tref_id, $trigger_ref_id)) {
            $status_date = $this->repo_service->getUserLevelRepo()->hasRecentSelfEvaluation($trigger_obj_id, $a_user_id,
                $skill_id, $a_tref_id, $trigger_ref_id);
            if ($status_date != "") {
                $update = true;
            }
        }

        $this->repo_service->getUserLevelRepo()->writeUserSkillLevelStatus($skill_id, $trigger_ref_id, $trigger_obj_id,
            $trigger_title, $trigger_type, $update, $status_date, $a_level_id, $a_user_id, $a_tref_id, $a_self_eval,
            $a_unique_identifier);
    }

}