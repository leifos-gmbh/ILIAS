<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
 * Class ilObjCourseListGUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * $Id$
 *
 * @ingroup ModulesCourse
 */
class ilObjCourseListGUI extends ilObjectListGUI
{

    /**
     * @var \ilCertificateObjectsForUserPreloader
     */
    private $certificatePreloader;

    // cdpatch start

    protected static $pl_loaded = false;
    protected static $pl = null;

    function __construct($a_context = self::CONTEXT_REPOSITORY)
    {
        parent::__construct($a_context);

        if (!self::$pl_loaded) {
            global $ilPluginAdmin;
            $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
            $plugin_html = false;
            foreach ($pl_names as $pl) {
                if ($pl == "CD") {
                    self::$pl = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
                }
            }
            self::$pl_loaded = true;
        }
    }
    // cdpatch end

    /**
    * initialisation
    */
    public function init()
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->type = "crs";
        $this->gui_class_name = "ilobjcoursegui";
        
        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }

        // general commands array
        $this->commands = ilObjCourseAccess::_getCommands();
    }
    
    /**
     * @inheritdoc
     */
    public function initItem($a_ref_id, $a_obj_id, $type, $a_title = "", $a_description = "")
    {
        parent::initItem($a_ref_id, $a_obj_id, $type, $a_title, $a_description);

        $this->conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($a_ref_id, $this->obj_id);
    }

    /**
     * @return \ilCertificateObjectsForUserPreloader
     */
    protected function getCertificatePreloader() : \ilCertificateObjectsForUserPreloader
    {
        if (null === $this->certificatePreloader) {
            $repository = new ilUserCertificateRepository();
            $this->certificatePreloader = new ilCertificateObjectsForUserPreloader($repository);
        }
        
        return $this->certificatePreloader;
    }

    /**
    * Get item properties
    *
    * @return	array		array of property arrays:
    *						"alert" (boolean) => display as an alert property (usually in red)
    *						"property" (string) => property name
    *						"value" (string) => property value
    */
    public function getProperties()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];

        $props = parent::getProperties();
        
        // check activation
        if (
            !ilObjCourseAccess::_isActivated($this->obj_id) &&
            !ilObject::lookupOfflineStatus($this->obj_id)
        ) {
            $showRegistrationInfo = false;
            $props[] = array(
                "alert" => true,
                "property" => $lng->txt("status"),
                "value" => $lng->txt("offline")
            );
        }

        // blocked
        include_once 'Modules/Course/classes/class.ilCourseParticipant.php';
        $members = ilCourseParticipant::_getInstanceByObjId($this->obj_id, $ilUser->getId());
        if ($members->isBlocked($ilUser->getId()) and $members->isAssigned($ilUser->getId())) {
            $props[] = array("alert" => true, "property" => $lng->txt("member_status"),
                "value" => $lng->txt("crs_status_blocked"));
        }

        // pending subscription
        include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
        if (ilCourseParticipants::_isSubscriber($this->obj_id, $ilUser->getId())) {
            $props[] = array("alert" => true, "property" => $lng->txt("member_status"),
                "value" => $lng->txt("crs_status_pending"));
        }
        
        include_once './Modules/Course/classes/class.ilObjCourseAccess.php';
        $info = ilObjCourseAccess::lookupRegistrationInfo($this->obj_id);
        if ($info['reg_info_list_prop']) {
            $props[] = array(
                'alert' => false,
                'newline' => true,
                'property' => $info['reg_info_list_prop']['property'],
                'value' => $info['reg_info_list_prop']['value']
            );
        }
        if ($info['reg_info_list_prop_limit']) {
            $props[] = array(
                'alert' => false,
                'newline' => false,
                'property' => $info['reg_info_list_prop_limit']['property'],
                'propertyNameVisible' => strlen($info['reg_info_list_prop_limit']['property']) ? true : false,
                'value' => $info['reg_info_list_prop_limit']['value']
            );
        }
        
        // waiting list
        include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
        if (ilCourseWaitingList::_isOnList($ilUser->getId(), $this->obj_id)) {
            $props[] = array(
                "alert" => true,
                "property" => $lng->txt('member_status'),
                "value" => $lng->txt('on_waiting_list')
            );
        }
        
        // course period
        $info = ilObjCourseAccess::lookupPeriodInfo($this->obj_id);
        if (is_array($info)) {
            $props[] = array(
                'alert' => false,
                'newline' => true,
                'property' => $info['property'],
                'value' => $info['value']
            );
        }
        
        // check for certificates
        $hasCertificate = $this->getCertificatePreloader()->isPreloaded($ilUser->getId(), $this->obj_id);
        if (true === $hasCertificate) {
            $lng->loadLanguageModule('certificate');
            $cmd_link = "ilias.php?baseClass=ilRepositoryGUI&ref_id=" . $this->ref_id . "&cmd=deliverCertificate";
            $props[] = [
                'alert' => false,
                'property' => $lng->txt('certificate'),
                'value' => $DIC->ui()->renderer()->render(
                    $DIC->ui()->factory()->link()->standard($lng->txt('download_certificate'), $cmd_link)
                )
            ];
        }


        // booking information
        $repo = ilObjCourseAccess::getBookingInfoRepo();
        $book_info = new ilBookingInfoListItemPropertiesAdapter($repo);
        $props = $book_info->appendProperties($this->obj_id, $props);

        // cdpatch start
        $cd_prop = ilObjCourseAccess::_getCDProperties($this->obj_id);
        if ($cd_prop["course_nr"] != "") {
            $props[] = array(
                "property" => self::$pl->txt('course_nr'),
                "value" => $cd_prop["course_nr"]
            );
        }
        if ($cd_prop["course_type"] > 0) {
            self::$pl->includeClass("class.cdCourseType.php");
            $props[] = array(
                "property" => self::$pl->txt('course_type'),
                "value" => cdCourseType::lookupTitle($cd_prop["course_type"])
            );
        }
        if ($cd_prop["course_level"] > "") {
            $props[] = array(
                "property" => self::$pl->txt('course_level'),
                "value" => $cd_prop["course_level"]
            );
        }

        $part = ilCourseParticipants::_getInstanceByObjId($this->obj_id);
        $tuts = $part->getTutors();
        if (is_array($tuts) && count($tuts) > 0) {
            $tut_arr = array();
            foreach ($tuts as $t) {
                $n = ilObjUser::_lookupName($t);
                $tut_arr[] = $n["firstname"] . " " . $n["lastname"];
            }
            $props[] = array(
                "property" => $lng->txt('tutors'),
                "value" => implode(", ", $tut_arr)
            );
        }
        // cdpatch end

        return $props;
    }
    
    
    /**
     * Workaround for course titles (linked if join or read permission is granted)
     * @param type $a_permission
     * @param type $a_cmd
     * @param type $a_ref_id
     * @param type $a_type
     * @param type $a_obj_id
     * @return type
     */
    public function checkCommandAccess($a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id = "")
    {
        // Only check cmd access for cmd 'register' and 'unregister'
        if ($a_cmd != 'view' and $a_cmd != 'leave' and $a_cmd != 'join') {
            $a_cmd = '';
        }

        if ($a_permission == 'crs_linked') {
            return
                parent::checkCommandAccess('read', $a_cmd, $a_ref_id, $a_type, $a_obj_id) ||
                parent::checkCommandAccess('join', $a_cmd, $a_ref_id, $a_type, $a_obj_id);
        }
        return parent::checkCommandAccess($a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id);
    }
} // END class.ilObjCategoryGUI
