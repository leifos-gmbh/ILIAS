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

namespace ILIAS\Exercise\SampleSolution;

use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Exercise\IRSS\ResourceInformation;
use ILIAS\Exercise\InternalDomainService;

class SampleSolutionManager
{
    protected InternalDomainService $domain;
    protected SampleSolutionRepository $repo;

    public function __construct(
        int $ass_id,
        SampleSolutionRepository $repo,
        \ilExcSampleSolutionStakeholder $stakeholder,
        InternalDomainService $domain
    )
    {
        global $DIC;

        $this->upload = $DIC->upload();

        $this->repo = $repo;
        $this->ass_id = $ass_id;
        $this->stakeholder = $stakeholder;
        $this->domain = $domain;
    }

    public function getStakeholder() : ResourceStakeholder
    {
        return $this->stakeholder;
    }


    public function importFromLegacyUpload(array $file_input) : string
    {
        if (!isset($file_input["tmp_name"])) {
            return "";
        }
        return $this->repo->importFromLegacyUpload(
            $this->ass_id,
            $file_input,
            $this->stakeholder
        );
    }

    public function deliver() : void
    {
        if ($this->repo->hasFile($this->ass_id)) {
            $this->repo->deliverFile($this->ass_id);
        } else {
            $ass = $this->domain->assignment()->getAssignment($this->ass_id);
            \ilFileDelivery::deliverFileLegacy($ass->getGlobalFeedbackFilePath(),
                $ass->getFeedbackFile());
        }
    }

/*
    public function deleteCollection(): void {
        $this->repo->deleteCollection(
            $this->ass_id,
            $this->stakeholder
        );
    }

    public function getFiles(): array
    {
        if ($this->repo->hasCollection($this->ass_id)) {
            return array_map(function (ResourceInformation $info): array {
                return [
                    'name' => $info->getTitle(),
                    'size' => $info->getSize(),
                    'ctime' => $info->getCreationTimestamp(),
                    'fullpath' => $info->getSrc(),
                    'mime' => $info->getMimeType(), // this is additional to still use the image delivery in class.ilExAssignmentGUI.php:306
                    'order' => 0 // sorting is currently not supported
                ];
            }, iterator_to_array($this->repo->getCollectionResourcesInfo($this->ass_id)));
        } else {
            $this->log->debug("getting files from class.ilExAssignment using ilFSWebStorageExercise");
            $storage = new \ILIAS\Exercise\InstructionFile\ilFSWebStorageExercise($this->getExerciseId(), $this->ass_id);
            return $storage->getFiles();
        }
    }

    public function cloneTo(
        int $to_ass_id
    ) : void
    {
        $this->repo->clone($this->ass_id, $to_ass_id);
    }
*/
}