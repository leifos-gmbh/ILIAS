<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  User utility class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class ilCDUserUtil
{
    /**
     * Modify user company of a user
     *
     * @param int $a_user_id user id
     * @param int $a_company_id company id
     */
    public static function setUserCompany($a_user_id, $a_company_id)
    {
        // get user object
        if (ilObject::_lookupType((int) $a_user_id) != "usr") {
            return;
        }
        $a_user = new ilObjUser((int) $a_user_id);


        include_once("./Services/CD/classes/class.ilCDPermWrapper.php");
        $a_company_id = (int) $a_company_id;
        $old_company_id = $a_user->getCompanyId();

        // check, if current user can perform the operation
        // the user must either be an admin or both companies must be assigned
        // to centers administrated by the user
        $user_admin_centers = ilCDPermWrapper::getAdminCenters();
        $new_center = cdCompany::lookupCenterId($a_company_id);
        $old_center = cdCompany::lookupCenterId($old_company_id);
        if (!ilCDPermWrapper::isAdmin()) {
            if (!in_array($new_center, $user_admin_centers) ||
                !in_array($old_center, $user_admin_centers)) {
                return;
            }
        }

        if ($a_company_id > 0 && $a_company_id != $old_company_id) {
            include_once("./Services/CD/classes/class.ilCDPermWrapper.php");
            ilCDPermWrapper::initCompanyUserRoles($a_user->getId(), $a_company_id);
        }
        $a_user->setCompanyId($a_company_id);
        $a_user->setCenterId(0);
        $a_user->update();
    }
}
