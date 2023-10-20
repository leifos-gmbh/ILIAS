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

namespace ILIAS\Exercise\Team;

class TeamMember
{
    public function __construct(
        int $team_id,
        int $assignment_id,
        int $user_id
    )
    {
        $this->team_id = $team_id;
        $this->assignment_id = $assignment_id;
        $this->user_id = $user_id;
    }

    public function getTeamId() : int
    {
        return $team_id;
    }

    public function getAssignmentId() : int
    {
        return $assignment_id;
    }

    public function getUserId() : int
    {
        return $user_id;
    }
}