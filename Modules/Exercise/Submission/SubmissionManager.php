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
use ILIAS\FileUpload\DTO\UploadResult;

class SubmissionManager
{
    protected \ilExAssignmentTypeInterface $type;
    protected \ILIAS\Exercise\Team\TeamManager $team;
    protected \ilLogger $log;
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
        $this->type = $this->assignment->getAssignmentType();
        $this->repo = $repo->submission();
        $this->log = $this->domain->logger()->exc();
        $this->team = $domain->team();
    }

    /**
     * Note: this includes submissions of other team members, if user is in a team
     */
    public function getSubmissionsOfUser(
        int $user_id,
        ?array $submit_ids = null,
        bool $only_valid = false,
        string $min_timestamp = null,
        bool $print_versions = false
    ) : array
    {
        $type_uses_print_versions = in_array($this->assignment->getType(), [
            \ilExAssignment::TYPE_BLOG,
            \ilExAssignment::TYPE_PORTFOLIO,
            \ilExAssignment::TYPE_WIKI_TEAM
        ], true);
        $type_uses_uploads = $this->type->usesFileUpload();
        if ($this->type->isSubmissionAssignedToTeam()) {
            $team_id = $this->team->getTeamForMember($this->ass_id, $user_id);
            if (((int) $team_id) === 0) {
                return [];
            }
            return $this->repo->getSubmissionsOfTeam(
                $this->ass_id,
                $type_uses_uploads,
                $type_uses_print_versions,
                $team_id,
                $submit_ids,
                $only_valid,
                $min_timestamp,
                $print_versions
            );
        } else {
            $user_ids = $this->team->getTeamMemberIdsOrUserId(
                $this->ass_id,
                $user_id
            );
            return $this->repo->getSubmissionsOfUsers(
                $this->ass_id,
                $type_uses_uploads,
                $type_uses_print_versions,
                $user_ids,
                $submit_ids,
                $only_valid,
                $min_timestamp,
                $print_versions
            );
        }
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

        $delivered = [];
        foreach ($this->repo->getAllEntriesOfAssignment($assignment->getId()) as $row) {
            if ($this->type->isSubmissionAssignedToTeam()) {
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

        if ($this->type->isSubmissionAssignedToTeam()) {
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

    public function addUpload(
        int $user_id,
        UploadResult $result,
        string $filename = ""
    ): bool {

        if ($filename === "") {
            $filename = $result->getName();
        }
        $submission = new \ilExSubmission(
            $this->assignment,
            $user_id
        );

        if (!$submission->canAddFile()) {
            return false;
        }

        if ($this->type->isSubmissionAssignedToTeam()) {
            $team_id = $submission->getTeam()->getId();
            $user_id = 0;
            if ($team_id === 0) {
                return false;
            }
        } else {
            $team_id = 0;
        }
        $success = $this->repo->addUpload(
            $this->assignment->getExerciseId(),
            $this->ass_id,
            $user_id,
            $team_id,
            $result,
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

    public function addZipUploads(
        int $user_id,
        UploadResult $result
    ): bool {
        $submission = new \ilExSubmission(
            $this->assignment,
            $user_id
        );
        if (!$submission->canAddFile()) {
            return false;
        }
        if ($this->type->isSubmissionAssignedToTeam()) {
            $team_id = $submission->getTeam()->getId();
            $user_id = 0;
            if ($team_id === 0) {
                return false;
            }
        } else {
            $team_id = 0;
        }
        $filenames = $this->repo->addZipUpload(
            $this->assignment->getExerciseId(),
            $this->ass_id,
            $user_id,
            $team_id,
            $result,
            $submission->isLate(),
            $this->stakeholder
        );
        $this->log->debug("99");
        if ($team_id > 0) {
            foreach ($filenames as $filename) {
                $this->domain->team()->writeLog(
                    $team_id,
                    (string) \ilExAssignmentTeam::TEAM_LOG_ADD_FILE,
                    $filename
                );
            }
        }

        return count($filenames) > 0;
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

    public function getTableUserWhere(
        bool $a_team_mode = false
    ): string {
        $ilDB = $this->db;

        if ($this->type->isSubmissionAssignedToTeam()) {
            $team_id = $this->getTeam()->getId();
            $where = " team_id = " . $ilDB->quote($team_id, "integer") . " ";
        } else {
            $where = " " . $ilDB->in("user_id", $this->getUserIds(), "", "integer") . " ";
        }
        return $where;
    }

    /**
     * Check if user can delete submissions of others
     */
    public function deleteSubmissions(int $user_id, array $ids): void {

        if (count($ids) == 0) {
            return;
        }

        if ($this->type->isSubmissionAssignedToTeam()) {
            $team_id = $this->team->getTeamForMember($this->ass_id, $user_id);
            if (is_null($team_id)) {
                return;
            }
        } else {
            $team_id = $this->team->getTeamForMember($this->ass_id, $user_id);
            if (!is_null($team_id)) {
                $user_ids = $this->team->getMemberIds($team_id);
            } else {
                $user_ids = [$user_id];
            }
        }

        $ilDB = $this->db;

        $where = " AND " . $this->getTableUserWhere(true);

        $result = $ilDB->query("SELECT * FROM exc_returned" .
            " WHERE " . $ilDB->in("returned_id", $file_id_array, false, "integer") .
            $where);

        if ($ilDB->numRows($result)) {
            $result_array = array();
            while ($row = $ilDB->fetchAssoc($result)) {
                $row["timestamp"] = $row["ts"];
                $result_array[] = $row;
            }

            // delete the entries in the database
            $ilDB->manipulate("DELETE FROM exc_returned" .
                " WHERE " . $ilDB->in("returned_id", $file_id_array, false, "integer") .
                $where);

            // delete the files
            $path = $this->initStorage()->getAbsoluteSubmissionPath();
            foreach ($result_array as $value) {
                if ($value["filename"]) {
                    if ($this->team) {
                        $this->team->writeLog(
                            ilExAssignmentTeam::TEAM_LOG_REMOVE_FILE,
                            $value["filetitle"]
                        );
                    }

                    if ($this->getAssignment()->getAssignmentType()->isSubmissionAssignedToTeam()) {
                        $storage_id = $value["team_id"];
                    } else {
                        $storage_id = $value["user_id"];
                    }

                    $filename = $path . "/" . $storage_id . "/" . basename($value["filename"]);
                    if (file_exists($filename)) {
                        unlink($filename);
                    }
                }
            }
        }
    }


}
