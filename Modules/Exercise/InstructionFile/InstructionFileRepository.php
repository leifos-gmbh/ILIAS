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

namespace ILIAS\Exercise\InstructionFile;

use ILIAS\Exercise\IRSS\CollectionWrapper;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

class InstructionFileRepository
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

    public function createCollection(int $ass_id) : void
    {
        $new_id = $this->wrapper->getNewCollectionIdAsString();
        $this->db->update("exc_assignment", [
            "if_rcid" => ["text", $new_id]
        ], [    // where
                "id" => ["integer", $ass_id]
            ]
        );
    }

    public function getCollection(int $ass_id) : ?ResourceCollection
    {
        $set = $this->db->queryF("SELECT if_rcid FROM exc_assignment " .
            " WHERE id = %s ",
            ["integer"],
            [$ass_id]
        );
        $rec = $this->db->fetchAssoc($set);
        $rcid = ($rec["if_rcid"] ?? "");
        if ($rcid !== "") {
            return $this->wrapper->getCollectionForIdString($rcid);
        }
        return null;
    }

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
    }

}