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

class ilMDControlledVocabsUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * Add a new table to store deactivated standard vocabularies.
     */
    public function step_1(): void
    {
        if (!$this->db->tableExists('il_md_vocab_inactive')) {
            $this->db->createTable(
                'il_md_vocab_inactive',
                [
                    'slot' => [
                        'type' => ilDBConstants::T_TEXT,
                        'notnull' => true,
                        'length' => 64
                    ]
                ]
            );
            $this->db->addPrimaryKey('il_md_vocab_inactive', ['slot']);
        }
    }

    /**
     * Add a new table to store controlled vocabularies.
     */
    public function step_2(): void
    {
        if (!$this->db->tableExists('il_md_vocab_contr')) {
            $this->db->createTable(
                'il_md_vocab_contr',
                [
                    'id' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'length' => 4
                    ],
                    'slot' => [
                        'type' => ilDBConstants::T_TEXT,
                        'notnull' => true,
                        'length' => 64
                    ],
                    'source' => [
                        'type' => ilDBConstants::T_TEXT,
                        'notnull' => true,
                        'length' => 64
                    ],
                    'active' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'length' => 1,
                        'default' => 1
                    ],
                    'custom_input' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'length' => 1,
                        'default' => 1
                    ],
                ]
            );
            $this->db->addPrimaryKey('il_md_vocab_contr', ['id']);
            $this->db->createSequence('il_md_vocab_contr');
        }
    }

    /**
     * Add a new table to store values and labels of controlled vocabularies.
     */
    public function step_3(): void
    {
        if (!$this->db->tableExists('il_md_vocab_contr_vals')) {
            $this->db->createTable(
                'il_md_vocab_contr_vals',
                [
                    'vocab_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'length' => 4
                    ],
                    'value' => [
                        'type' => ilDBConstants::T_TEXT,
                        'notnull' => true,
                        'length' => 300
                    ],
                    'label' => [
                        'type' => ilDBConstants::T_TEXT,
                        'notnull' => true,
                        'length' => 300
                    ]
                ]
            );
            $this->db->addPrimaryKey('il_md_vocab_contr_vals', ['vocab_id', 'value']);
        }
    }
}
