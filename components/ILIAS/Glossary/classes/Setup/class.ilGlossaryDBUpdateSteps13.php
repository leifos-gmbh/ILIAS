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

class ilGlossaryDBUpdateSteps13 implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableColumnExists("glossary", "flash_mode")) {
            $this->db->renameTableColumn("glossary", "flash_mode", "flash_mode_default");
        }
    }

    public function step_2(): void
    {
        if (!$this->db->tableColumnExists("glossary", "flash_mode_change")) {
            $this->db->addTableColumn("glossary", "flash_mode_change", [
                "type" => "text",
                "notnull" => true,
                "length" => 1,
                "default" => "n"
            ]);
        }
    }

    public function step_3(): void
    {
        $table = "glo_user_settings";
        if (!$this->db->tableExists($table)) {
            $this->db->createTable($table, [
                "user_id" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true,
                    "default" => 0
                ],
                "glo_id" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true,
                    "default" => 0
                ],
                "flash_mode" => [
                    "type" => "text",
                    "length" => 10,
                    "notnull" => true,
                    "default" => "term"
                ]
            ]);
            $this->db->addPrimaryKey($table, ["user_id", "glo_id"]);
        }
    }
}
