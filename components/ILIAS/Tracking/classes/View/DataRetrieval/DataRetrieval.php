<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=0);

namespace ILIAS\Tracking\View\DataRetrieval;

use ilDateTime;
use ilDBConstants;
use ilDBInterface;
use ILIAS\Tracking\View\DataRetrieval\DataRetrievalInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\ViewInterface;
use ILIAS\Tracking\View\DataRetrieval\FilterInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\FactoryInterface as InfoFactoryInterface;
use ilLPMarks;
use ilLPObjSettings;
use ilObject;
use ilObjectLP;
use ilTrQuery;

class DataRetrieval implements DataRetrievalInterface
{
    protected const KEY_OBJ_ID = "obj_id";
    protected const KEY_USR_ID = "usr_id";
    protected const KEY_OBJ_TITLE = "title";
    protected const KEY_OBJ_DESCRIPTION = "description";
    protected const KEY_OBJ_TYPE = "type";
    protected const KEY_LP_STATUS = "lp_status";

    public function __construct(
        protected ilDBInterface $db,
        protected InfoFactoryInterface $info_factory
    ) {
    }

    public function retrieveViewInfo(
        FilterInterface $filter
    ): ViewInterface {
        $object_infos = [];
        $lp_infos = [];
        $combined_infos = [];

        # Remove obj_ids if type is set
        $object_ids = $filter->hasObjectTypes() ? [] : $filter->getObjectIds();
        $user_ids = $filter->getUserIds();

        # Get additional obj_ids with types
        if ($filter->hasObjectTypes()) {
            foreach ($filter->getObjectTypes() as $type) {
                $object_datas = ilObject::_getObjectsByType($type);
                $new_object_ids = array_map(function ($object_data) { return $object_data['obj_id']; }, $object_datas);
                $object_ids = array_merge($object_ids, $new_object_ids);
            }
        }

        # Get additional user_ids for object_ids if no user ids are supplied
        if (!$filter->hasUserIds()) {
            foreach ($object_ids as $obj_id) {
                $user_ids = array_merge($user_ids, ilLPMarks::_getAllUserIds($obj_id));
            }
            $user_ids = array_unique($user_ids);
        }
        $user_obj_id_mappings = [];

        foreach ($user_ids as $usr_id) {
            foreach ($object_ids as $obj_id) {
                $lp_mode = (int) ilLPObjSettings::_lookupDBMode($obj_id);
                $user_obj_id_mappings[$usr_id][$lp_mode][] = $obj_id;
            }
        }

        $data = [];
        foreach ($user_obj_id_mappings as $usr_id => $mode_mapping) {
            foreach ($mode_mapping as $lp_mode => $obj_ids) {
                $obj_ids = array_flip($obj_ids);
                $new_data = [];
                switch ($lp_mode) {
                    case ilLPObjSettings::LP_MODE_SCORM:
                        $new_data = ilTrQuery::getSCOsStatusForUser(
                            $usr_id,
                            0,
                            $obj_ids
                        );
                        break;
                    case ilLPObjSettings::LP_MODE_OBJECTIVES:
                        $new_data = ilTrQuery::getObjectivesStatusForUser(
                            $usr_id,
                            0,
                            $obj_ids
                        );
                        break;
                    case ilLPObjSettings::LP_MODE_COLLECTION_MANUAL:
                    case ilLPObjSettings::LP_MODE_COLLECTION_TLT:
                    case ilLPObjSettings::LP_MODE_COLLECTION_MOBS:
                        if ($usr_id) {
                            $data = ilTrQuery::getSubItemsStatusForUser(
                                $usr_id,
                                0,
                                $obj_ids
                            );
                        }
                        break;
                    case ilLPObjSettings::LP_MODE_UNDEFINED:
                    case ilLPObjSettings::LP_MODE_DEACTIVATED:
                        break;
                    default:
                        $new_data = ilTrQuery::getObjectsStatusForUser(
                            $usr_id,
                            $obj_ids
                        );
                        break;
                }
                foreach ($new_data as $new) {
                    $new["lp_mode"] = $lp_mode;
                    $new["usr_id"] = $usr_id;
                    $data[] = $new;
                }
            }
        }

        foreach ($data as $entry) {
            global $DIC;
            $DIC->logger()->root()->dump($entry);
            $obj_id = (int) $entry['obj_id'];
            $obj_title = (string) $entry['title'];
            $percentage = (int) $entry['percentage'];
            $obj_description = "...";
            $lp_status = (int) $entry['status'];
            $obj_type = (string) $entry['type'];
            $usr_id = (int) $entry['usr_id'];
            $lp_mode = (int) $entry['lp_mode'];
            $spent_seconds = (int) $entry['spent_seconds'];
            $status_changed = new ilDateTime($entry['status_changed'], IL_CAL_DATETIME);
            $visits = (int) $entry['visits'];
            $read_count = (int) $entry['read_count'];
            $lp_info = $this->info_factory->lp(
                $usr_id,
                $obj_id,
                $lp_status,
                $percentage,
                $lp_mode,
                $spent_seconds,
                $status_changed,
                $visits,
                $read_count
            );
            $object_info = $this->info_factory->objectData(
                $obj_id,
                $obj_title,
                $obj_description,
                $obj_type,
            );
            $lp_infos[] = $lp_info;
            $object_infos[] = $object_info;
            $combined_infos[] = $this->info_factory->combined(
                $lp_info,
                $object_info
            );
        }
        # ref_id -> welche benutzer sind relevant, not attempted z.B in Kursen benötigt die ref id
        # obj_id -> hilft nicht bei not attemptet

        return $this->info_factory->view(
            $this->info_factory->iterator()->objectData(...$object_infos),
            $this->info_factory->iterator()->lp(...$lp_infos),
            $this->info_factory->iterator()->combined(...$combined_infos)
        );
    }
}
