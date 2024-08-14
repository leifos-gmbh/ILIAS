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

namespace ILIAS\Exercise\Submission;

use ILIAS\Exercise\InternalRepoService;
use ILIAS\Exercise\InternalDomainService;

class SubmissionManager
{
    protected SubmissionRepositoryInterface $repo;
    protected \ilExAssignment $assignment;

    public function __construct(
        InternalRepoService $repo,
        protected InternalDomainService $domain,
        protected int $ass_id
    )
    {
        $this->assignment = $domain->assignment()->getAssignment($ass_id);
        $this->repo = $repo->submission();
    }

    public function recalculateLateSubmissions(): void
    {
        // see JF, 2015-05-11
        $assigment = $this->assignment;

        $ext_deadline = $assigment->getExtendedDeadline();

        foreach ($this->getAllAssignmentFiles() as $file) {
            $id = $file["returned_id"];
            $uploaded = new \ilDateTime($file["ts"], IL_CAL_DATETIME);
            $uploaded = $uploaded->get(IL_CAL_UNIX);

            $deadline = $assigment->getPersonalDeadline($file["user_id"]);
            $last_deadline = max($deadline, $assigment->getExtendedDeadline());

            $late = null;

            // upload is not late anymore
            if ($file["late"] &&
                (!$last_deadline ||
                    !$ext_deadline ||
                    $uploaded < $deadline)) {
                $late = false;
            }
            // upload is now late
            elseif (!$file["late"] &&
                $ext_deadline &&
                $deadline &&
                $uploaded > $deadline) {
                $late = true;
            }

            if ($late !== null) {
                $this->repo->updateLate(
                    $id,
                    $late
                );
            }
        }
    }

    /**
     * @throws \ilExcUnknownAssignmentTypeException
     */
    public function getAllAssignmentFiles(): array
    {
        $assignment = $this->assignment;

        $storage = new \ilFSStorageExercise(
            $assignment->getExerciseId(),
            $assignment->getId()
        );
        $path = $storage->getAbsoluteSubmissionPath();

        $ass_type = $assignment->getAssignmentType();

        $delivered = [];
        foreach ($this->repo->getAllEntriesOfAssignment($assignment->getId()) as $row) {
            if ($ass_type->isSubmissionAssignedToTeam()) {
                $storage_id = $row["team_id"];
            } else {
                $storage_id = $row["user_id"];
            }

            $row["timestamp"] = $row["ts"];
            $row["filename"] = $path . "/" . $storage_id . "/" . basename($row["filename"] ?? "");
            $delivered[] = $row;
        }

        return $delivered;
    }

}
