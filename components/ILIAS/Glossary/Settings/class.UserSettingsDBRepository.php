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

use ilDBInterface;
use ILIAS\Glossary\InternalDataService;

class UserSettingsDBRepository
{
    public function __construct(
        protected ilDBInterface $db,
        protected InternalDataService $data
    ) {
    }

    public function create(UserSettings $settings): void
    {
        $this->db->insert(
            "glo_user_settings",
            [
                "user_id" => ["integer", $settings->getUserId()],
                "glo_id" => ["integer", $settings->getGlossaryId()],
                "flash_mode" => ["text", $settings->getFlashcardMode()],
            ]
        );
    }

    public function update(UserSettings $settings): void
    {
        $this->db->update(
            "glo_user_settings",
            [
                "flash_mode" => ["text", $settings->getFlashcardMode()],
            ],
            [
                "user_id" => ["integer", $settings->getUserId()],
                "glo_id" => ["integer", $settings->getGlossaryId()],
            ]
        );
    }

    public function getByIds(int $user_id, int $glossary_id): ?UserSettings
    {
        $set = $this->db->queryF(
            "SELECT * FROM glo_user_settings WHERE user_id = %s AND glo_id = %s",
            ["integer", "integer"],
            [$user_id, $glossary_id]
        );

        $record = $this->db->fetchAssoc($set);
        if ($record) {
            return $this->getSettingsFromRecord($record);
        }

        return null;
    }

    protected function getSettingsFromRecord(array $record): UserSettings
    {
        $flash_mode = ($record["flash_mode"] == "")
            ? "term"
            : (string) $record["flash_mode"];

        return $this->data->userSettings(
            (int) $record["user_id"],
            (int) $record["glo_id"],
            $flash_mode
        );
    }
}
