<?php declare(strict_types = 1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Container\Content;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ModeManager
{
    protected ModeSessionRepository $mode_repo;

    public function __construct(
        ModeSessionRepository $mode_repo
    ) {
        $this->mode_repo = $mode_repo;
    }

    public function setAdminMode() : void
    {
        $this->mode_repo->setAdminMode();
    }

    public function setContentMode() : void
    {
        $this->mode_repo->setContentMode();
    }

    public function isAdminMode() : bool
    {
        return $this->mode_repo->isAdminMode();
    }

    public function isContentMode() : bool
    {
        return $this->mode_repo->isContentMode();
    }
}
