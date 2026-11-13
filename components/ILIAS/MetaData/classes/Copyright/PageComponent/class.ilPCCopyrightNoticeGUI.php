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
 * @ilCtrl_isCalledBy ilPCCopyrightNoticeGUI: ilPageEditorGUI
 */
class ilPCCopyrightNoticeGUI extends ilPageContentGUI
{
    protected const string INSERT = 'insert';

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                switch ($cmd) {
                    case self::INSERT:
                        $this->insert();
                        break;

                    default:
                        $this->redirectToParent();
                        break;
                }
                break;
        }
    }

    public function insert(?ilPropertyFormGUI $a_form = null): void
    {
        $this->content_obj = new ilPCCopyrightNotice($this->getPage());
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
        $this->updated = $this->pg_obj->update();
        $this->redirectToParent();
    }
}
