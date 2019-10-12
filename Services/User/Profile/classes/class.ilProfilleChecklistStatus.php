<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author killing@leifos.de
 */
class ilProfileChecklistStatus
{
    const STEP_PROFILE_DATA = 0;
    const STEP_PUBLISH_OPTIONS = 1;
    const STEP_VISIBILITY_OPTIONS = 2;

    const STATUS_NOT_STARTED = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_SUCCESSFUL = 2;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct($lng = null)
    {
        global $DIC;

        $this->lng = is_null($lng)
            ? $DIC->language()
            : $lng;
    }

    /**
     * Get steps
     *
     * @param
     * @return
     */
    public function getSteps()
    {
        $lng = $this->lng;

        return [
            self::STEP_PROFILE_DATA => $lng->txt("personal_data"),
            self::STEP_PUBLISH_OPTIONS => $lng->txt("user_publish_options"),
            self::STEP_VISIBILITY_OPTIONS => $lng->txt("user_visibility_settings")
        ];
    }


    /**
     * Get status of step
     *
     * @param int
     * @return int
     */
    protected function getStatus(int $step)
    {
        return self::STATUS_NOT_STARTED;
    }


}