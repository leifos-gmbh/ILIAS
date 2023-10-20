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

namespace ILIAS\Exercise\TutorFeedbackFile;

use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Exercise\IRSS\ResourceInformation;
use ILIAS\Exercise\InstructionFile\ilFSWebStorageExercise;
use ILIAS\Exercise\InstructionFile\InstructionFileRepository;
use ILIAS\Exercise\InternalRepoService;

class TutorFeedbackFileManager
{
    protected TutorFeedbackFileRepository $repo;

    public function __construct(
        int $ass_id,
        InternalRepoService $repo,
        \ilExcTutorFeedbackFileStakeholder $stakeholder)
    {
        global $DIC;

        $this->upload = $DIC->upload();

        $types = \ilExAssignmentTypes::getInstance();
        $type = $types->getById(\ilExAssignment::lookupType($ass_id));
        if ($type->usesTeams()) {
            $this->repo = $repo->tutorFeedbackFileTeam();
        } else {
            $this->repo = $repo->tutorFeedbackFile();
        }

        $this->ass_id = $ass_id;

        $this->stakeholder = $stakeholder;
    }

    public function getStakeholder() : ResourceStakeholder
    {
        return $this->stakeholder;
    }

    public function hasCollection(int $participant_id) : bool
    {
        return ($this->getCollectionIdString($participant_id) !== "");
    }

    public function getCollectionIdString(int $participant_id) : string
    {
        return $this->repo->getIdStringForAssIdAndUserId($this->ass_id, $participant_id);
    }

    public function createCollection(int $participant_id) : void
    {
        $this->repo->createCollection($this->ass_id, $participant_id);
    }

    /*
    public function importFromLegacyUpload(array $file_input) : void
    {
        $this->repo->importFromLegacyUpload(
            $this->ass_id,
            $file_input,
            $this->stakeholder
        );
    }*/

    public function deleteCollection(int $participant_id): void {
        $this->repo->deleteCollection(
            $this->ass_id,
            $participant_id,
            $this->stakeholder
        );
    }

    public function getFiles(int $participant_id): array
    {
        if ($this->repo->hasCollection($this->ass_id, $participant_id)) {
            return array_map(function (ResourceInformation $info): array {
                return [
                    'name' => $info->getTitle(),
                    'size' => $info->getSize(),
                    'ctime' => $info->getCreationTimestamp(),
                    'fullpath' => $info->getSrc(),
                    'mime' => $info->getMimeType(),
                    'order' => 0 // sorting is currently not supported
                ];
            }, iterator_to_array($this->repo->getCollectionResourcesInfo($this->ass_id, $participant_id)));
        } else {
            $this->log->debug("getting files from class.ilExAssignment using ilFSWebStorageExercise");
            /*
            $storage = new ilFSWebStorageExercise($this->getExerciseId(), $this->ass_id);
            return $storage->getFiles();*/
        }
    }

    public function deliver(int $participant_id, string $file) : void
    {
        if ($this->repo->hasCollection($this->ass_id, $participant_id)) {
            // IRSS
            $this->repo->deliverFile($this->ass_id, $participant_id, $file);
        } else {
            // LEGACY
            $assignment = new \ilExAssignment($this->ass_id);
            $submission = new \ilExSubmission($assignment, $participant_id);
            $exc_id = \ilExAssignment::lookupExerciseId($this->ass_id);
            $storage = new \ilFSStorageExercise($exc_id, $this->ass_id);
            $files = $storage->getFeedbackFiles($submission->getFeedbackId());
            $file_exist = false;
            foreach ($files as $fb_file) {
                if ($fb_file == $file) {
                    $file_exist = true;
                    break;
                }
            }
            if (!$file_exist) {
                return;
            }
            // check whether assignment has already started
            if (!$this->assignment->notStartedYet()) {
                // deliver file
                $p = $storage->getFeedbackFilePath($submission->getFeedbackId(), $file);
                \ilFileDelivery::deliverFileLegacy($p, $file);
            }
        }
    }

}