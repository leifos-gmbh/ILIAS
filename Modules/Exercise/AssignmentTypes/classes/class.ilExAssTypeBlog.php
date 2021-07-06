<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Blog type
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilExAssTypeBlog implements ilExAssignmentTypeInterface
{
    protected const STR_IDENTIFIER = "blog";

    /**
     * @var ilSetting
     */
    protected $setting;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     *
     * @param ilSetting|null $a_setting
     * @param ilLanguage|null $a_lng
     */
    public function __construct(ilSetting $a_setting = null, ilLanguage $a_lng = null)
    {
        global $DIC;

        $this->setting = ($a_setting)
            ? $a_setting
            : $DIC["ilSetting"];

        $this->lng = ($a_lng)
            ? $a_lng
            : $DIC->language();
    }

    /**
     * @inheritdoc
     */
    public function isActive()
    {
        if ($this->setting->get('disable_wsp_blogs')) {
            return false;
        }
        return true;
    }


    /**
     * @inheritdoc
     */
    public function usesTeams()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function usesFileUpload()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        $lng = $this->lng;

        return $lng->txt("exc_type_blog");
    }

    /**
     * @inheritdoc
     */
    public function getSubmissionType()
    {
        return ilExSubmission::TYPE_OBJECT;
    }

    /**
     * @inheritdoc
     */
    public function isSubmissionAssignedToTeam()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function cloneSpecificProperties(ilExAssignment $source, ilExAssignment $target)
    {
    }

    /**
     *  @inheritdoc
     */
    public function supportsWebDirAccess() : bool
    {
        return true;
    }

    /**
     *  @inheritdoc
     */
    public function getStringIdentifier() : string
    {
        return self::STR_IDENTIFIER;
    }

    /**
     * @inheritDoc
     */
    public function getExportObjIdForResourceId(int $resource_id) : int
    {
        // in case of blogs the $resource id is the workspace id
        $tree = new ilWorkspaceTree(0);
        $owner = $tree->lookupOwner($resource_id);
        $tree = new ilWorkspaceTree($owner);
        return $tree->lookupObjectId($resource_id);
    }

}
