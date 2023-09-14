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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseMailNotification extends ilMailNotification
{
    public const TYPE_FEEDBACK_FILE_ADDED = 20;
    public const TYPE_SUBMISSION_UPLOAD = 30;
    public const TYPE_FEEDBACK_TEXT_ADDED = 40;
    public const TYPE_MESSAGE_FROM_PF_GIVER = 50;
    public const TYPE_MESSAGE_FROM_PF_RECIPIENT = 60;
    protected string $additional_text = "";
    protected int $peer_id = 0;
    protected \ILIAS\Exercise\PermanentLink\PermanentLinkManager $permanent_link;

    protected ilObjUser $user;
    protected int $ass_id;

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->permanent_link = $DIC->exercise()->internal()->gui()->permanentLink();
        parent::__construct();
    }

    public function setAssignmentId(int $a_val): void
    {
        $this->ass_id = $a_val;
    }

    public function getAssignmentId(): int
    {
        return $this->ass_id;
    }

    public function setPeerId(int $a_val): void
    {
        $this->peer_id = $a_val;
    }

    public function getPeerId(): int
    {
        return $this->peer_id;
    }

    public function setAdditionalText(string $a_val): void
    {
        $this->additional_text = $a_val;
    }

    public function getAdditionalText() : string
    {
        return $this->additional_text;
    }

    public function send(): bool
    {
        $ilUser = $this->user;
        $perma = $this->permanent_link;
        // parent::send();

        switch ($this->getType()) {
            case self::TYPE_FEEDBACK_FILE_ADDED:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf(
                            $this->getLanguageText('exc_msg_new_feedback_file_uploaded'),
                            $this->getObjectTitle(true)
                        )
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_msg_new_feedback_file_uploaded2')
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('obj_exc') . ": " . $this->getObjectTitle(true)
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_assignment') . ": " .
                        ilExAssignment::lookupTitle($this->getAssignmentId())
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('exc_mail_permanent_link'));
                    $this->appendBody("\n");
                    $this->appendBody($this->createPermanentLink([], $perma->getDefaultAppend($this->getAssignmentId())));
                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_SUBMISSION_UPLOAD:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf(
                            $this->getLanguageText('exc_submission_notification_subject'),
                            $this->getObjectTitle(true)
                        )
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        sprintf($this->getLanguageText('exc_submission_notification_body'), $this->getObjectTitle(true))
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_assignment') . ": " .
                        ilExAssignment::lookupTitle($this->getAssignmentId())
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('user') . ": " .
                        $ilUser->getFullname()
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody(sprintf(
                        $this->getLanguageText('exc_submission_notification_link'),
                        $this->createPermanentLink()
                    ));

                    if (ilExAssignment::lookupType($this->getAssignmentId()) == ilExAssignment::TYPE_UPLOAD) {
                        $this->appendBody("\n\n");

                        //new files uploaded
                        //$assignment = new ilExAssignment($this->getAssignmentId());
                        //$submission = new ilExSubmission($assignment, $ilUser->getId());

                        // since mails are sent immediately after upload the files should always be new
                        //if($submission->lookupNewFiles($submission->getTutor()))
                        //{
                        $this->appendBody(sprintf(
                            $this->getLanguageText('exc_submission_downloads_notification_link'),
                            $this->createPermanentLink([], $perma->getDownloadSubmissionAppend($this->getAssignmentId(), $ilUser->getId()))
                        ));
                        //}
                        //else
                        //{
                        //	$this->appendBody(sprintf($this->getLanguageText('exc_submission_downloads_notification_link'),
                        //		$this->getLanguageText("exc_submission_no_new_files")));
                        //}
                    }

                    $this->appendBody("\n\n");
                    $this->appendBody(sprintf(
                        $this->getLanguageText('exc_submission_and_grades_notification_link'),
                        $this->createPermanentLink([], $perma->getGradesAppend($this->getAssignmentId()))
                    ));

                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_FEEDBACK_TEXT_ADDED:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->initMail();
                    $this->setSubject(
                        sprintf(
                            $this->getLanguageText('exc_msg_new_feedback_text_uploaded'),
                            $this->getObjectTitle(true)
                        )
                    );
                    $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                    $this->appendBody("\n\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_msg_new_feedback_text_uploaded2')
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('obj_exc') . ": " . $this->getObjectTitle(true)
                    );
                    $this->appendBody("\n");
                    $this->appendBody(
                        $this->getLanguageText('exc_assignment') . ": " .
                        ilExAssignment::lookupTitle($this->getAssignmentId())
                    );
                    $this->appendBody("\n\n");
                    $this->appendBody($this->getLanguageText('exc_mail_permanent_link'));
                    $this->appendBody("\n");
                    $this->appendBody($this->createPermanentLink(array(), $perma->getDefaultAppend($this->getAssignmentId())));
                    $this->getMail()->appendInstallationSignature(true);

                    $this->sendMail(array($rcp));
                }
                break;

            case self::TYPE_MESSAGE_FROM_PF_GIVER:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->sendExerciseNotification(
                        $rcp,
                        sprintf(
                            $this->getLanguageText('exc_msg_new_message_from_pf_giver'),
                            $this->getObjectTitle(true)
                        ),
                        $this->getLanguageText('exc_msg_new_message_from_pf_giver2') .
                        "\n\n" . $this->getAdditionalText(),
                        $perma->getReceivedFeedbackAppend($this->getAssignmentId())
                    );
                }
                break;

            case self::TYPE_MESSAGE_FROM_PF_RECIPIENT:

                foreach ($this->getRecipients() as $rcp) {
                    $this->initLanguage($rcp);
                    $this->sendExerciseNotification(
                        $rcp,
                        sprintf(
                            $this->getLanguageText('exc_msg_new_message_from_pf_recipient'),
                            $this->getObjectTitle(true)
                        ),
                        $this->getLanguageText('exc_msg_new_message_from_pf_recipient2') .
                        "\n\n" . $this->getAdditionalText(),
                        $perma->getGivenFeedbackAppend($this->getAssignmentId(), $this->getPeerId())
                    );
                }
                break;
        }
        return true;
    }

    protected function sendExerciseNotification(
        int $rcp,
        string $subject,
        string $text,
        string $link_append = "") : void
    {
        $this->initLanguage($rcp);
        $this->initMail();
        $this->setSubject($subject);
        $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
        $this->appendBody("\n\n" . $text);
        $this->appendBody(
            "\n\n" . $this->getLanguageText('obj_exc') . ": " . $this->getObjectTitle(true)
        );
        $this->appendBody("\n");
        if ($this->getAssignmentId() > 0) {
            $this->appendBody(
                $this->getLanguageText('exc_assignment') . ": " .
                ilExAssignment::lookupTitle($this->getAssignmentId())
            );
        }
        $this->appendBody("\n\n");
        $this->appendBody($this->getLanguageText('exc_mail_permanent_link'));
        $this->appendBody("\n");
        $this->appendBody($this->createPermanentLink(array(), $link_append));
        $this->getMail()->appendInstallationSignature(true);
        $this->sendMail(array($rcp));
    }

    /**
     * Add language module exc
     */
    protected function initLanguage(int $a_usr_id): void
    {
        parent::initLanguage($a_usr_id);
        $this->getLanguage()->loadLanguageModule('exc');
    }
}
