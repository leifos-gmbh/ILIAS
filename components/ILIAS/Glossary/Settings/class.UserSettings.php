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

namespace ILIAS\Glossary\Settings;

class UserSettings
{
    public function __construct(
        protected int $user_id,
        protected int $glossary_id,
        protected string $flash_mode
    ) {
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getGlossaryId(): int
    {
        return $this->glossary_id;
    }

    public function getFlashcardMode(): string
    {
        return $this->flash_mode;
    }
}
