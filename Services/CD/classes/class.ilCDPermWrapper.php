<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Permission wrapper for CD
 *
 * @author alex killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesCD
 */
class ilCDPermWrapper
{
    /**
     * CD global Roles
     *
     * @return	array	global roles
     */
    public static function getGlobalCDRoles()
    {
        return array(
                "CDC-SysAdmin" => "y",
                "CDC-MA-Master-Chief" => "y",
                "CDC-MA-GF" => "n",
                "CDC-Admin" => "y",
                "Kunde-TN" => "n",
                "Kunde-PER" => "n",
                "Kunde-PER-Master" => "n",
                "User" => "n");
    }

    /**
     * CD local Roles
     *
     * @return	array	local roles
     */
    public static function getLocalCDRoles()
    {
        return array(
                "CDC-MA" => "y",
                "CDC-MA-Master" => "y",
                "Trainer" => "n",
                "Trainer-Master" => "n",
                "Kunde" => "n");
    }

    /**
     * Is a CD role?
     *
     * @param	string		role name
     * @return	boolean		cd role?
     */
    public static function isCDRole($a_role)
    {
        $gl = ilCDPermWrapper::getGlobalCDRoles();
        if (isset($gl[$a_role])) {
            return true;
        }
        $lc = ilCDPermWrapper::getLocalCDRoles();
        if (isset($lc[$a_role])) {
            return true;
        }
        return false;
    }

    /**
     * Get admin centers
     *
     * @return	array	array of centers the user can administrate
     */
    public static function getAdminCenters($a_mode = "")
    {
        global $ilUser, $rbacreview, $tree, $ilDB;

        $centers = array();

        $roles = $rbacreview->assignedRoles($ilUser->getId());
        $all = false;
        foreach ($roles as $r) {
            $role_title = ilObject::_lookupTitle($r);
            if (!$all) {
                //include_once("./Services/AccessControl/classes/class.ilObjRole.php");
                //$role = new ilObjRole($r);
                $folds = $rbacreview->getFoldersAssignedToRole($r);
                foreach ($folds as $f) {
                    if ($f == ROLE_FOLDER_ID) {
                        if (in_array(
                            strtolower($role_title),
                            array("administrator", "cdc-ma-master-chief", "cdc-admin", "cdc-system-admin")
                        )) {
                            $all = true;
                        }
                    } else {
                        if (in_array(
                            strtolower($role_title),
                            array("cdc-ma", "cdc-ma-master")
                        )) {
                            // this is only set by fki
                            if ($a_mode == "train") {
                                $all = true;
                            } else {
                                // check whether parent is a cdc category
                                $parent = $tree->getParentId($f);
                                $parent = $f;
                                if (ilObject::_lookupType($parent, true) == "cat") {
                                    $set = $ilDB->query(
                                        "SELECT id FROM cd_center WHERE " .
                                            " category = " . $ilDB->quote($parent, "integer")
                                    );
                                    while ($rec = $ilDB->fetchAssoc($set)) {
                                        $centers[$rec["id"]] = $rec["id"];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($all) {
            $set = $ilDB->query("SELECT id FROM cd_center");
            while ($rec = $ilDB->fetchAssoc($set)) {
                $centers[$rec["id"]] = $rec["id"];
            }
        }

        return $centers;
    }

    /**
     * Is per role
     *
     * @param
     * @return
     */
    public static function isPerRole($a_only_master = false)
    {
        global $ilUser, $rbacreview, $tree, $ilDB;

        $target_roles = ($a_only_master)
            ? array("kunde-per-master")
            : array("kunde-per", "kunde-per-master");

        $roles = $rbacreview->assignedRoles($ilUser->getId());
        foreach ($roles as $r) {
            $role_title = ilObject::_lookupTitle($r);
            
            //include_once("./Services/AccessControl/classes/class.ilObjRole.php");
            //$role = new ilObjRole($r);
            $folds = $rbacreview->getFoldersAssignedToRole($r);
            foreach ($folds as $f) {
                if ($f == ROLE_FOLDER_ID) {
                    if (in_array(strtolower($role_title), $target_roles)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Init roles for new user
     *
     * @param
     * @return
     */
    public static function initCompanyUserRoles($a_user_id, $a_company_id)
    {
        global $rbacadmin, $ilPluginAdmin, $rbacreview, $tree;

        $roles = $rbacreview->assignedRoles($a_user_id);
        foreach ($roles as $r) {
            $rbacadmin->deassignUser($r, $a_user_id);
        }

        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
        $plugin_html = false;
        foreach ($pl_names as $pl) {
            if ($pl == "CD") {
                $pl = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
            }
        }

        // assign user to "User" role
        $rbacadmin->assignUser((int) 4, $a_user_id, true);

        // assign user to center category role
        $pl->includeClass("class.cdCompany.php");
        $center_id = cdCompany::lookupCenterId($a_company_id);

        $pl->includeClass("class.cdCenter.php");
        $center_id = cdCompany::lookupCenterId($a_company_id);

        $category_ref_id = cdCenter::lookupCategory($center_id);
        if ($tree->isInTree($category_ref_id) &&
            ilObject::_lookupType($category_ref_id, true) == "cat") {
            //echo "2-$category_ref_id-";
            //var_dump($rf); exit;
            //echo "<br>3-$rf-"; exit;
            $role_list = $rbacreview->getRoleListByObject($category_ref_id);
            //echo "4"; exit;
            foreach ($role_list as $r) {
                if (strtolower($r["title"]) == "kunde") {
                    $rbacadmin->assignUser((int) $r["obj_id"], $a_user_id, true);
                }
            }
            //var_dump($role_list);
        }
        //exit;
    }

    /**
     * Init roles for new employee
     *
     * @param
     * @return
     */
    public static function initEmployeeUserRoles($a_user_id, $a_center_id)
    {
        global $rbacadmin, $ilPluginAdmin, $rbacreview, $tree;

        $roles = $rbacreview->assignedRoles($a_user_id);
        foreach ($roles as $r) {
            //			$rbacadmin->deassignUser($r, $a_user_id);
        }

        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
        $plugin_html = false;
        foreach ($pl_names as $pl) {
            if ($pl == "CD") {
                $pl = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
            }
        }

        // assign user to "User" role
        //		$rbacadmin->assignUser((int) 4, $a_user_id, true);

        $pl->includeClass("class.cdCenter.php");
        $center_id = $a_center_id;

        $category_ref_id = cdCenter::lookupCategory($center_id);
        if ($tree->isInTree($category_ref_id) &&
            ilObject::_lookupType($category_ref_id, true) == "cat") {
            $role_list = $rbacreview->getRoleListByObject($category_ref_id);
            foreach ($role_list as $r) {
                if (strtolower($r["title"]) == "cdc-ma") {
                    $rbacadmin->assignUser((int) $r["obj_id"], $a_user_id, true);
                }
            }
        }
    }

    /**
     * Init roles for new trainer
     *
     * @param
     * @return
     */
    public static function initTrainerUserRoles($a_user_id, $a_center_id)
    {
        global $rbacadmin, $ilPluginAdmin, $rbacreview, $tree;

        $roles = $rbacreview->assignedRoles($a_user_id);
        foreach ($roles as $r) {
            $rbacadmin->deassignUser($r, $a_user_id);
        }

        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
        $plugin_html = false;
        foreach ($pl_names as $pl) {
            if ($pl == "CD") {
                $pl = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
            }
        }

        // assign user to "User" role
        $rbacadmin->assignUser((int) 4, $a_user_id, true);
        
        $pl->includeClass("class.cdCenter.php");
        $category_ref_id = cdCenter::lookupCategory($a_center_id);
        if ($tree->isInTree($category_ref_id) &&
            ilObject::_lookupType($category_ref_id, true) == "cat") {
            $role_list = $rbacreview->getRoleListByObject($category_ref_id);
            foreach ($role_list as $r) {
                if (strtolower($r["title"]) == "trainer") {
                    $rbacadmin->assignUser((int) $r["obj_id"], $a_user_id, true);
                }
            }
        }
    }

    /**
     * Check, whether user can be created for a company
     *
     * @param
     * @return
     */
    public static function checkUserCreationPossible($a_company_id)
    {
        global $rbacadmin, $ilPluginAdmin, $rbacreview, $tree;
        
        $possible = false;
        
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
        foreach ($pl_names as $pl) {
            if ($pl == "CD") {
                $pl = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
            }
        }

        $pl->includeClass("class.cdCenter.php");
        $center_id = cdCompany::lookupCenterId($a_company_id);

        $category_ref_id = cdCenter::lookupCategory($center_id);
        if ($tree->isInTree($category_ref_id) &&
            ilObject::_lookupType($category_ref_id, true) == "cat") {
            $role_list = $rbacreview->getRoleListByObject($category_ref_id);
            foreach ($role_list as $r) {
                if (strtolower($r["title"]) == "kunde") {
                    $possible = true;
                }
            }
        }
        return $possible;
    }
    
    
    /**
     * Init roles for new user
     *
     * @param
     * @return
     */
    public static function initCenterUserRoles($a_user_id, $a_center_id)
    {
        global $rbacadmin, $ilPluginAdmin, $rbacreview, $tree;

        $roles = $rbacreview->assignedRoles($a_user_id);
        foreach ($roles as $r) {
            $rbacadmin->deassignUser($r, $a_user_id);
        }

        // assign user to "User" role
        $rbacadmin->assignUser((int) 4, $a_user_id, true);
    }

    /**
     * Check whether current user is an admin
     */
    public static function isAdmin()
    {
        global $rbacsystem;

        if ($rbacsystem->checkAccess("visible,read", SYSTEM_FOLDER_ID)) {
            return true;
        }
        return false;
    }

    /**
     * Check if a admin/ma user can administrate a target user
     *
     * @param
     * @return
     */
    public static function canAdminUser($a_target_user, $a_pl)
    {
        if (self::isAdmin()) {
            return true;
        }
        $a_pl->includeClass("class.cdCompany.php");
        // get centers that the user can administrate
        $user_admin_centers = ilCDPermWrapper::getAdminCenters();
        $comp_id = ilObjUser::_lookupCompanyId($a_target_user);
        if ($comp_id > 0) {
            $center_id = cdCompany::lookupCenterId($comp_id);
            if (in_array($center_id, $user_admin_centers)) {
                return true;
            }
        }
        return false;
    }
}
