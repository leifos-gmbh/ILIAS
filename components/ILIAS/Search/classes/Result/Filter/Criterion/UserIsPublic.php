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

namespace ILIAS\Search\Result\Filter\criterion;

use ILIAS\User\Settings\Settings as UserPreferences;
use ilObjUser;

class UserIsPublic implements Criterion
{
    public function __construct(
        protected UserPreferences $user_preferences
    ) {
    }

    public function doesFulfill(int $id): bool
    {
        if ($id === ANONYMOUS_USER_ID) {
            return false;
        }
        $public_profile = $this->user_preferences->getSettingValueFor($id, 'public_profile') ?? '';
        if ($public_profile !== 'y' && $public_profile !== 'g') {
            return false;
        }
        $user = new ilObjUser($id);
        return $user->getActive() && $user->checkTimeLimit();
    }
}
