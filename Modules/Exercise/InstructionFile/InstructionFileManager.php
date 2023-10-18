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

class InstructionFileManager
{
    protected InstructionFileRepository $repo;

    public function __construct(
        int $ass_id,
        InstructionFileRepository $repo,
        \ilExcInstructionFilesStakeholder $stakeholder)
    {
        global $DIC;

        $this->upload = $DIC->upload();

        $this->repo = $repo;
        $this->ass_id = $ass_id;
        $this->stakeholder = $stakeholder;
    }

    public function createCollection() : void
    {
        $this->repo->createCollection($this->ass_id);
    }

    public function importFromLegacyUpload(array $file_input) : void
    {
        $this->repo->importFromLegacyUpload(
            $this->ass_id,
            $file_input,
            $this->stakeholder
        );
    }

    public function deleteCollection(): void {
        $this->repo->deleteCollection(
            $this->ass_id,
            $this->stakeholder
        );
    }

}