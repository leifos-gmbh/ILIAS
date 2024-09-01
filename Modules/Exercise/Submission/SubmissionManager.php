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

    public function countSubmissionsOfUser(int $user_id) : int
    {
        return count(iterator_to_array(
            $this->getSubmissionsOfUser($user_id)
        ));
    }

    /**
     * Note: this includes submissions of other team members, if user is in a team
     * @return \Generator<Submission>
     */
    public function getSubmissionsOfUser(
        int $user_id,
        ?array $submit_ids = null,
        bool $only_valid = false,
        string $min_timestamp = null,
        bool $print_versions = false
    ) : \Generator
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
     * Delete submissions
     */
    public function deleteSubmissions(int $user_id, array $ids): void {

        if (count($ids) == 0) {
            return;
        }
        // this ensures, the ids belong to user submissions at all
        foreach ($this->getSubmissionsOfUser($user_id, $ids) as $s) {
            $this->repo->delete(
                $s->getId(),
                $this->stakeholder
            );
            $team_id = $this->team->getTeamForMember($this->ass_id, $user_id);
            if ($team_id && $team_id > 0) {
                $this->team->writeLog(
                    $team_id,
                    \ilExAssignmentTeam::TEAM_LOG_REMOVE_FILE,
                    $s->getTitle()
                );
            }
        }
    }

    public function deleteAllSubmissionsOfUser(int $user_id): void
    {
        $this->deleteSubmissions(
            $user_id,
            $this->repo->getAllSubmissionIdsOfOwner(
                $this->ass_id,
                $user_id
            )
        );
    }

    /**
     * Should be replaced by writing into a zip directly in the future
     */
    public function copySubmissionsToDir(
        array $user_ids,
        string $directory
    )
    {
        $members = [];
        foreach ($user_ids as $member_id) {
            $submission = new \ilExSubmission($this->assignment, $member_id);
            $submission->updateTutorDownloadTime();

            // get member object (ilObjUser)
            if ($this->domain->profile()->exists($member_id)) {
                // adding file metadata
                foreach ($this->getSubmissionsOfUser($member_id) as $sub) {
                    $members[$sub->getUserId()]["files"][$sub->getId()] = $sub;
                }

                $name = \ilObjUser::_lookupName($member_id);
                $members[$member_id]["name"] = $name["firstname"] . " " . $name["lastname"];
            }
        }
        $this->copySubmissionFilesToDir($members, $directory);
    }

    protected function copySubmissionFilesToDir(
        array $members,
        string $to_path
    ): void
    {
        global $DIC;

        $zip_archive = $DIC->archives()->zip([]);
        $ass = $this->assignment;

        $lng = $this->domain->lng();
        $log = $this->log;


        ksort($members);
        //$savepath = $storage->getAbsoluteSubmissionPath();
        //$cdir = getcwd();

        // important check: if the directory does not exist
        // ILIAS stays in the current directory (echoing only a warning)
        // and the zip command below archives the whole ILIAS directory
        // (including the data directory) and sends a mega file to the user :-o
//        if (!is_dir($savepath)) {
//            return;
//        }
        // Safe mode fix
        //		chdir($this->getExercisePath());

//        $tmpdir = $storage->getTempPath();
//        chdir($tmpdir);
//        $zip = PATH_TO_ZIP;

        //$ass_type = $a_ass->getType();

        // copy all member directories to the temporary folder
        // switch from id to member name and append the login if the member name is double
        // ensure that no illegal filenames will be created
        // remove timestamp from filename
        $team_map = null;
        $team_dirs = null;
        if ($ass->hasTeam()) {
            $team_dirs = array();
            $team_map = \ilExAssignmentTeam::getAssignmentTeamMap($ass->getId());
        }
        foreach ($members as $id => $item) {
            $user_files = $item["files"] ?? null;

            // group by teams
            $team_dir = "";
            if (is_array($team_map) &&
                array_key_exists($id, $team_map)) {
                $team_id = $team_map[$id];
                if (!array_key_exists($team_id, $team_dirs)) {
                    $team_dir = $lng->txt("exc_team") . " " . $team_id;
                    $team_dirs[$team_id] = $team_dir;
                }
                $team_dir = $team_dirs[$team_id] . DIRECTORY_SEPARATOR;
            }

            if ($ass->getAssignmentType()->isSubmissionAssignedToTeam()) {
                $targetdir = $team_dir . \ilFileUtils::getASCIIFilename(
                        $item["name"]
                    );
                if ($targetdir == "") {
                    continue;
                }
            } else {
                $targetdir = $this->getDirectoryNameFromUserData($id);
                if ($ass->getAssignmentType()->usesTeams()) {
                    $targetdir = $team_dir . $targetdir;
                }
            }

            $log->debug("Creation target directory: " . $targetdir);

            $duplicates = [];

            /** @var Submission $sub */
            foreach ($user_files as $sub) {
                $targetfile = $sub->getTitle();

                // rename repo objects
                if ($this->type->getSubmissionType() === \ilExSubmission::TYPE_REPO_OBJECT) {
                    $obj_id = \ilObject::_lookupObjId((int) $targetfile);
                    $obj_type = \ilObject::_lookupType($obj_id);
                    $targetfile = $obj_type . "_" . $obj_id . ".zip";
                }

                // handle duplicates
                if (array_key_exists($targetfile, $duplicates)) {
                    $suffix = strrpos($targetfile, ".");
                    $targetfile = substr($targetfile, 0, $suffix) .
                        " (" . (++$duplicates[$targetfile]) . ")" .
                        substr($targetfile, $suffix);
                } else {
                    $duplicates[$targetfile] = 1;
                }

                // add late submission info
                if ($sub->getLate()) {    // see #23900
                    $targetfile = $lng->txt("exc_late_submission") . " - " .
                        $targetfile;
                }

                // normalize and add directory
                $targetfile = \ilFileUtils::getASCIIFilename($targetfile);
                //$targetfile = $targetdir . DIRECTORY_SEPARATOR . $targetfile;

                /*
                $zip_archive->addStream(
                    $this->repo->getStream($ass->getId(), $sub->getRid()),
                    $targetfile
                );*/

                $stream = $this->repo->getStream($ass->getId(), $sub->getRid());

                // add file to directory (no zip see end of the function)
                $dir = $to_path . DIRECTORY_SEPARATOR . $targetdir;
                \ilFileUtils::makeDir($dir);
                $file = $dir . DIRECTORY_SEPARATOR . $targetfile;
                file_put_contents(
                    $file,
                    $stream->getContents()
                );

                // unzip blog/portfolio

            }
        }
    }

    /**
     * there is still a version in ilExSubmission that needs to be replaced
     */
    protected function getDirectoryNameFromUserData(int $user_id): string
    {
        $userName = \ilObjUser::_lookupName($user_id);
        return \ilFileUtils::getASCIIFilename(
            trim($userName["lastname"]) . "_" .
            trim($userName["firstname"]) . "_" .
            trim($userName["login"]) . "_" .
            $userName["user_id"]
        );
    }

}
