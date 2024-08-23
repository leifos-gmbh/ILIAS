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
        protected \ilExcSubmissionStakeholder $stakeholder,
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

    public function getMaxAmountOfSubmittedFiles(
        int $user_id = 0): int
    {
        $ass = $this->assignment;
        return $this->repo->getMaxAmountOfSubmittedFiles(
            $ass->getExerciseId(),
            $ass->getId(),
            $user_id
        );
    }

    /**
     * Get all user ids, that have submitted something
     * @return int[]
     */
    public function getUsersWithSubmission(): array
    {
        return $this->repo->getUsersWithSubmission(
            $this->ass_id
        );
    }

    public function addLocalFile(
        int $user_id,
        string $file,
        string $filename = ""
    ): bool {

        if ($filename === "") {
            $filename = basename($file);
        }
        $submission = new \ilExSubmission(
            $this->assignment,
            $user_id
        );

        if (!$submission->canAddFile()) {
            return false;
        }

        if ($this->assignment->getAssignmentType()->isSubmissionAssignedToTeam()) {
            $team_id = $submission->getTeam()->getId();
            $user_id = 0;
            if ($team_id === 0) {
                return false;
            }
        } else {
            $team_id = 0;
        }
        $success = $this->repo->addLocalFile(
            $this->assignment->getExerciseId(),
            $this->ass_id,
            $user_id,
            $team_id,
            $file,
            $filename,
            $submission->isLate(),
            $this->stakeholder
        );

        if ($success && $team_id > 0) {
            $this->domain->team()->writeLog(
                $team_id,
                (string) \ilExAssignmentTeam::TEAM_LOG_ADD_FILE,
                $filename
            );
        }

        return $success;
    }

    public function deliverFile(
        int $user_id,
        string $rid,
        string $filetitle = ""
    ): void
    {
        $this->repo->deliverFile(
            $this->ass_id,
            $user_id,
            $rid,
            $filetitle
        );
    }

    public function copyRidToWebDir(
        string $rid, string $internal_file_path
    ): ?string {
        global $DIC;

        $web_filesystem = $DIC->filesystem()->web();

        $internal_dirs = dirname($internal_file_path);
        $zip_file = basename($internal_file_path);

        if ($rid !== "") {
            //$this->log->debug("internal file path: " . $internal_file_path);
            if ($web_filesystem->hasDir($internal_dirs)) {
                $web_filesystem->deleteDir($internal_dirs);
            }
            $web_filesystem->createDir($internal_dirs);

            if ($web_filesystem->has($internal_file_path)) {
                $web_filesystem->delete($internal_file_path);
            }
            if (!$web_filesystem->has($internal_file_path)) {
                //$this->log->debug("writing: " . $internal_file_path);
                $stream = $this->repo->getStream(
                    $this->ass_id,
                    $rid
                );
                if (!is_null($stream)) {
                    $web_filesystem->writeStream($internal_file_path, $stream);

                    return ILIAS_ABSOLUTE_PATH .
                        DIRECTORY_SEPARATOR .
                        ILIAS_WEB_DIR .
                        DIRECTORY_SEPARATOR .
                        CLIENT_ID .
                        DIRECTORY_SEPARATOR .
                        $internal_dirs .
                        DIRECTORY_SEPARATOR .
                        $zip_file;
                }
            }
        }

        return null;
    }

}
