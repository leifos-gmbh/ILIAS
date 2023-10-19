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

declare(strict_types=1);

namespace ILIAS\Exercise\TutorFeedback;

use ILIAS\Exercise\IRSS\CollectionWrapper;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

class TutorFeedbackFileRepository
{
    protected CollectionWrapper $collection;
    protected \ilDBInterface $db;

    public function __construct(
        CollectionWrapper $wrapper,
        \ilDBInterface $db
    )
    {
        $this->db = $db;
        $this->wrapper = $wrapper;
    }

    public function createCollection(int $ass_id, int $user_id) : void
    {
        $new_id = $this->wrapper->createEmptyCollection();
        $this->db->update("exc_mem_ass_status", [
            "feedback_rcid" => ["text", $new_id]
        ], [    // where
                "ass_id" => ["integer", $ass_id],
                "usr_id" => ["integer", $user_id]
            ]
        );
    }

    public function getIdStringForAssIdAndUserId(int $ass_id, int $user_id) : string
    {
        $set = $this->db->queryF("SELECT feedback_rcid FROM exc_mem_ass_status " .
            " WHERE ass_id = %s AND usr_id = %s",
            ["integer", "integer"],
            [$ass_id, $user_id]
        );
        $rec = $this->db->fetchAssoc($set);
        return ($rec["if_rcid"] ?? "");
    }

    public function hasCollection(int $ass_id, int $user_id) : bool
    {
        $rcid = $this->getIdStringForAssIdAndUserId($ass_id, $user_id);
        return ($rcid !== "");
    }

    public function getCollection(int $ass_id, int $user_id) : ?ResourceCollection
    {
        $rcid = $this->getIdStringForAssIdAndUserId($ass_id, $user_id);
        if ($rcid !== "") {
            return $this->wrapper->getCollectionForIdString($rcid);
        }
        return null;
    }

    /*
    public function importFromLegacyUpload(
        int $ass_id,
        array $file_input,
        ResourceStakeholder $stakeholder
    ) : void
    {
        $collection = $this->getCollection($ass_id);
        if ($collection) {
            $this->wrapper->importFilesFromLegacyUploadToCollection(
                $collection,
                $file_input,
                $stakeholder
            );
        }
    }*/

    public function getCollectionResourcesInfo(
        int $ass_id,
        int $user_id
    ) : \Generator
    {
        $collection = $this->getCollection($ass_id, $user_id);
        return $this->wrapper->getCollectionResourcesInfo($collection);
    }

    public function deleteCollection(
        int $ass_id,
        int $user_id,
        ResourceStakeholder $stakeholder
    ): void {
        $rcid = $this->getIdStringForAssIdAndUserId($ass_id, $user_id);
        if ($rcid === "") {
            return;
        }
        $this->wrapper->deleteCollectionForIdString(
            $rcid,
            $stakeholder
        );
    }
}