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

use ilDBConstants;
use ilDBInterface;
use ILIAS\Tracking\View\DataRetrieval\DataRetrievalInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\ViewInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\View as ViewInfo;
use ILIAS\Tracking\View\DataRetrieval\Info\ObjectData as ObjectDataInfo;
use ILIAS\Tracking\View\DataRetrieval\Info\LP as LPInfo;
use ILIAS\Tracking\View\DataRetrieval\Info\Combined as CombinedInfo;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\Combined as CombinedIterator;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\ObjectData as ObjectDataIterator;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\LP as LPIterator;
use ILIAS\Tracking\View\DataRetrieval\Filter;
use ILIAS\Tracking\View\DataRetrieval\FilterInterface;

class DataRetrieval implements DataRetrievalInterface
{
    protected const KEY_OBJ_ID = "obj_id";
    protected const KEY_USR_ID = "usr_id";
    protected const KEY_OBJ_TITLE = "title";
    protected const KEY_OBJ_DESCRIPTION = "description";
    protected const KEY_OBJ_TYPE = "type";
    protected const KEY_LP_STATUS = "lp_status";

    public function __construct(
        protected ilDBInterface $db
    ) {
    }

    public function filter(): FilterInterface
    {
        return new Filter();
    }

    public function retrieveViewInfo(
        FilterInterface $filter
    ): ViewInterface {
        $query = "SELECT"
            . " object_data.title as obj_title,"
            . " object_data.type as obj_type,"
            . " object_data.description as obj_description,"
            . " object_data.obj_id as obj_id,"
            . " ut_lp_marks.usr_id as usr_id,"
            . " ut_lp_marks.status as lp_status,"
            . " ut_lp_marks.percentage as lp_percentage,"
            . " ut_lp_settings.u_mode as lp_mode"
            . " FROM object_data"
            . " JOIN ut_lp_marks ON object_data.obj_id = ut_lp_marks.obj_id"
            . " JOIN ut_lp_settings ON object_data.obj_id = ut_lp_settings.obj_id"
            . " " . $this->buildWhere($filter);
        $res = $this->db->query($query);
        $object_infos = [];
        $lp_infos = [];
        $combined_infos = [];
        while ($row = $res->fetchAssoc()) {
            $usr_id = (int) $row["usr_id"];
            $lp_status = (int) $row["lp_status"];
            $percentage = (int) $row["lp_percentage"];
            $obj_id = (int) $row["obj_id"];
            $obj_title = (string) $row["obj_title"];
            $obj_type = (string) $row["obj_type"];
            $obj_description = (string) $row["obj_description"];
            $lp_mode = (int) $row["lp_mode"];
            if (!$this->isValidMode($lp_mode)) {
                continue;
            }
            $lp_info = new LPInfo(
                $usr_id,
                $obj_id,
                $lp_status,
                $percentage,
                $lp_mode
            );
            $object_info = new ObjectDataInfo(
                $obj_id,
                $obj_title,
                $obj_description,
                $obj_type,
            );
            $combined_info = new CombinedInfo(
                $lp_info,
                $object_info
            );
            $lp_infos[$usr_id . ":" . $obj_id] = $lp_info;
            $object_infos[$obj_id] = $object_info;
            $combined_infos[] = $combined_info;
        }
        return new ViewInfo(
            new ObjectDataIterator(...$object_infos),
            new LPIterator(...$lp_infos),
            new CombinedIterator(...$combined_infos)
        );
    }

    protected function buildWhere(
        FilterInterface $filter
    ): string {
        $clauses = [];
        $empty_clause = "";
        $clauses[] = $filter->hasObjectTypes()
            ? $this->db->in("type", $filter->getObjectTypes(), false, ilDBConstants::T_TEXT)
            : $empty_clause;
        $clauses[] = $filter->hasUserIds()
            ? $this->db->in("usr_id", $filter->getUserIds(), false, ilDBConstants::T_INTEGER)
            : $empty_clause;
        $clauses[] = $filter->hasObjectIds()
            ? $this->db->in("obj_id", $filter->getObjectIds(), false, ilDBConstants::T_INTEGER)
            : $empty_clause;
        $where = "WHERE";
        foreach ($clauses as $clause) {
            if ($clause === $empty_clause) {
                continue;
            }
            if ($where !== "WHERE") {
                $where .= " AND";
            }
            $where .= " " . $clause;
        }
        return $where;
    }

    protected function isValidMode(int $lp_mode): bool
    {
        return true;
    }
}
