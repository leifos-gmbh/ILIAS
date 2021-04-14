<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Manages skill profile completion
 *
 * (business logic)
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilSkillProfileCompletionManager
{
    /**
     * @var int
     */
    protected $user_id;

    /**
     * Constructor
     *
     * @param int $a_user_id
     */
    public function __construct(int $a_user_id)
    {
        $this->setUserId($a_user_id);
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
     * Get actual max levels
     *
     * @param array|null $a_skills
     * @param string     $a_gap_mode
     * @param string     $a_gap_mode_type
     * @param int        $a_gap_mode_obj_id
     * @return array
     */
    public function getActualMaxLevels(
        array $a_skills = null,
        string $a_gap_mode = "",
        string $a_gap_mode_type = "",
        int $a_gap_mode_obj_id = 0
    ) {
        // get actual levels for gap analysis
        $actual_levels = array();
        if (is_array($a_skills)) {
            foreach ($a_skills as $sk) {
                $bs = new ilBasicSkill($sk["base_skill_id"]);
                if ($a_gap_mode == "max_per_type") {
                    $max = $bs->getMaxLevelPerType($sk["tref_id"], $a_gap_mode_type, $this->getUserId());
                    $actual_levels[$sk["base_skill_id"]][$sk["tref_id"]] = $max;
                } elseif ($a_gap_mode == "max_per_object") {
                    $max = $bs->getMaxLevelPerObject($sk["tref_id"], $a_gap_mode_obj_id, $this->getUserId());
                    $actual_levels[$sk["base_skill_id"]][$sk["tref_id"]] = $max;
                } else {
                    $max = $bs->getMaxLevel($sk["tref_id"], $this->getUserId());
                    $actual_levels[$sk["base_skill_id"]][$sk["tref_id"]] = $max;
                }
            }
        }

        return $actual_levels;
    }

    /**
     * Get actual last levels
     *
     * @param array  $a_skills
     * @param string $a_gap_mode
     * @param string $a_gap_mode_type
     * @param int    $a_gap_mode_obj_id
     */
    public function getActualLastLevels(
        array $a_skills,
        string $a_gap_mode = "",
        string $a_gap_mode_type = "",
        int $a_gap_mode_obj_id = 0
    ) {
        // todo for coming feature
    }

    /**
     * Get progress in percent for a profile
     *
     * @param int $a_profile_id
     * @return int
     */
    public function getProfileProgress(int $a_profile_id)
    {
        $profile = new ilSkillProfile($a_profile_id);
        $profile_levels = $profile->getSkillLevels();
        $skills = array();
        foreach ($profile_levels as $l) {
            $skills[] = array(
                "base_skill_id" => $l["base_skill_id"],
                "tref_id" => $l["tref_id"],
                "level_id" => $l["level_id"]
            );
        }
        $actual_levels = $this->getActualMaxLevels($skills);

        $profile_count = 0;
        $achieved_count = 0;
        foreach ($profile_levels as $profile) {
            if ($actual_levels[$profile["base_skill_id"]][$profile["tref_id"]] >= $profile["level_id"]) {
                $achieved_count++;
            }
            $profile_count++;
        }
        if ($profile_count == 0) {
            return 0;
        }
        $progress = $achieved_count / $profile_count * 100;

        return (int) $progress;
    }

    /**
     * Check if a profile is fulfilled (progress = 100%)
     *
     * @param int $a_profile_id
     * @return bool
     */
    public function isProfileFulfilled(int $a_profile_id)
    {
        if ($this->getProfileProgress($a_profile_id) == 100) {
            return true;
        }
        return false;
    }

    /**
     * Get all profiles of user which are fulfilled or non-fulfilled
     *
     * @return array
     */
    public function getAllProfileCompletionsForUser()
    {
        $user_profiles = ilSkillProfile::getProfilesOfUser($this->getUserId());
        $profile_comps = array();
        foreach ($user_profiles as $p) {
            if ($this->isProfileFulfilled($p["id"])) {
                $profile_comps[$p["id"]] = true;
            } else {
                $profile_comps[$p["id"]] = false;
            }
        }

        return $profile_comps;
    }

    /**
     * Write profile completion entries (fulfilled or non-fulfilled) of user for all profiles
     */
    public function writeCompletionEntryForAllProfiles()
    {
        $completions = $this->getAllProfileCompletionsForUser();
        foreach ($completions as $profile_id => $fulfilled) {
            $prof_comp_repo = new ilSkillProfileCompletionRepository($profile_id, $this->getUserId());
            if ($fulfilled) {
                $prof_comp_repo->addFulfilmentEntry();
            } else {
                $prof_comp_repo->addNonFulfilmentEntry();
            }
        }
    }

    /**
     * Write profile completion entry (fulfilled or non-fulfilled) of user for given profile
     *
     * @param int $a_profile_id
     */
    public function writeCompletionEntryForSingleProfile(int $a_profile_id)
    {
        $prof_comp_repo = new ilSkillProfileCompletionRepository($a_profile_id, $this->getUserId());

        if ($this->isProfileFulfilled($a_profile_id)) {
            $prof_comp_repo->addFulfilmentEntry();
        } else {
            $prof_comp_repo->addNonFulfilmentEntry();
        }
    }
}