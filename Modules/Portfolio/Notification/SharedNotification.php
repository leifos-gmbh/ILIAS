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

namespace ILIAS\Portfolio\Notification;

class SharedNotification extends \ilMailNotification
{
    protected array $shared_to_obj_ids = [];
    protected \ilObjUser $user;

    public function __construct()
    {
        global $DIC;
        $this->user = $DIC->user();
        parent::__construct();
    }

    public function setSharedToObjectIds(array $a_val) : void
    {
        $this->shared_to_obj_ids = $a_val;
    }

    public function send(): bool
    {
        $rcp = $this->user->getId();

        $this->initLanguage($rcp);
        $this->initMail();
        $this->setSubject(
            sprintf(
                $this->getLanguageText('prtf_successfully_shared_prtf'),
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
        $this->appendBody($this->createPermanentLink(array(), '_' . $this->getAssignmentId()) .
            '#fb' . $this->getAssignmentId());
        $this->getMail()->appendInstallationSignature(true);

        $this->sendMail(array($rcp));

        return true;
    }

    protected function initLanguage(int $a_usr_id): void
    {
        parent::initLanguage($a_usr_id);
        $this->getLanguage()->loadLanguageModule('prtf');
    }
}
