<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Manages skill resources
 *
 * (business logic)
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilSkillResourcesManager
{
    /**
     * @var ilSkillResources
     */
    protected $res;

    /**
     * @var int
     */
    protected $current_target_level = 0;

    /**
     * Constructor.
     *
     * @param int $a_base_skill
     * @param int $a_tref_id
     */
    public function __construct(int $a_base_skill = 0, int $a_tref_id = 0)
    {
        $this->res = new ilSkillResources($a_base_skill, $a_tref_id);
    }

    /**
     * @param array $a_levels
     * @param array $profile_levels
     * @param array $actual_levels
     *
     * @return bool
     */
    public function isLevelTooLow(array $a_levels, array $profile_levels, array $actual_levels)
    {
        $too_low = true;

        foreach ($a_levels as $k => $v) {
            foreach ($profile_levels as $pl) {
                if ($pl["level_id"] == $v["id"] &&
                    $pl["base_skill_id"] == $v["skill_id"]) {
                    $too_low = true;
                    $this->current_target_level = $v["id"];
                }
            }

            if ($actual_levels[$v["skill_id"]][$this->res->getTemplateRefId()] == $v["id"]) {
                $too_low = false;
            }
        }

        return $too_low;
    }

    /**
     * @return array
     */
    public function getSuggestedResourcesForProfile()
    {
        $resources = $this->res->getResources();
        $imp_resources = array();
        foreach ($resources as $level) {
            foreach ($level as $r) {
                if ($r["imparting"] == true &&
                    $this->current_target_level == $r["level_id"]) {
                    $imp_resources[] = $r;
                }
            }
        }

        return $imp_resources;
    }
}