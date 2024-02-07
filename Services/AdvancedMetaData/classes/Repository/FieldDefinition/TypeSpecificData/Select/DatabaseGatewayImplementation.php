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

namespace ILIAS\AdvancedMetaData\Repository\TypeSpecificData\Select;

use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\SelectSpecificData;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\OptionTranslationImplementation;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\OptionImplementation;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\SelectSpecificDataImplementation;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\OptionTranslation;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\Option;

class DatabaseGatewayImplementation implements Gateway
{
    public function __construct(
        protected \ilDBInterface $db
    ) {
    }

    public function create(int $field_id, SelectSpecificData $data): void
    {
        $option_id = 0;
        foreach ($data->getOptions() as $option) {
            foreach ($option->getTranslations() as $translation) {
                $this->createTranslation($field_id, $option_id, $translation);
            }
            $option_id++;
        }
    }

    public function readByID(int $field_id): ?SelectSpecificData
    {
        $query = 'SELECT * FROM adv_mdf_enum WHERE field_id = ' .
            $this->db->quote($field_id, \ilDBConstants::T_INTEGER) .
            ' ORDER BY idx';

        $res = $this->db->query($query);
        $rows = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $rows[] = $row;
        }

        if (!empty($rows)) {
            return $this->dataFromRows($field_id, $rows);
        }
        return null;
    }

    /**
     * @return SelectSpecificData[]
     */
    public function readByIDs(int ...$field_ids): \Generator
    {
        if (empty($field_ids)) {
            return;
        }

        $query = 'SELECT * FROM adv_mdf_enum WHERE ' .
            $this->db->in('field_id', $field_ids, false, \ilDBConstants::T_INTEGER) .
            ' ORDER BY idx';

        $res = $this->db->query($query);
        $rows_by_field_id = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $rows_by_field_id[(int) $row['field_id']][] = $row;
        }

        foreach ($rows_by_field_id as $field_id => $rows) {
            yield $this->dataFromRows($field_id, $rows);
        }
    }

    public function update(SelectSpecificData $data): void
    {
        if (!$data->isPersisted() || !$data->containsChanges()) {
            return;
        }

        $option_ids = [];
        foreach ($data->getOptions() as $option) {
            $option_ids[] = $option->optionID();
            $this->createOrUpdateOption(
                $data->fieldID(),
                $option
            );
        }
        $this->deleteOptionsExcept(...$option_ids);
    }

    protected function createOrUpdateOption(int $field_id, Option $option): void
    {
        if (!$option->containsChanges()) {
            return;
        }

        $option_id = $option->isPersisted() ?
            $option->optionID() :
            $this->getNextOptionIDInField($field_id);

        $translation_langs = [];
        foreach ($option->getTranslations() as $translation) {
            $translation_langs[] = $translation->language();

            if (!$translation->containsChanges()) {
                continue;
            }

            if ($translation->isPersisted()) {
                $this->updateTranslation(
                    $field_id,
                    $option_id,
                    $translation
                );
            } else {
                $this->createTranslation(
                    $field_id,
                    $option_id,
                    $translation
                );
            }
        }

        if ($option->isPersisted()) {
            $this->deleteTranslationsOfOptionExcept($option_id, ...$translation_langs);
        }
    }

    protected function deleteOptionsExcept(int ...$keep_option_ids): void
    {
        $query = 'DELETE FROM adv_mdf_enum WHERE ' .
            $this->db->in('option_id', $keep_option_ids, true, \ilDBConstants::T_INTEGER);

        $this->db->manipulate($query);
    }

    protected function deleteTranslationsOfOptionExcept(
        int $option_id,
        string ...$keep_languages
    ): void {
        $query = 'DELETE FROM adv_mdf_enum WHERE option_id = ' .
            $this->db->quote($option_id, \ilDBConstants::T_INTEGER) . ' AND ' .
            $this->db->in('lang_code', $keep_languages, true, \ilDBConstants::T_TEXT);

        $this->db->manipulate($query);
    }

    public function delete(int ...$field_ids): void
    {
        if (empty($field_ids)) {
            return;
        }

        $query = 'DELETE FROM adv_mdf_enum WHERE ' .
            $this->db->in('field_id', $field_ids, false, \ilDBConstants::T_INTEGER);

        $this->db->manipulate($query);
    }

    protected function dataFromRows(int $field_id, array $rows): ?SelectSpecificData
    {
        $translations_by_option_id = [];
        foreach ($rows as $row) {
            $translations_by_option_id[(int) $row['option_id']][] = new OptionTranslationImplementation(
                (string) $row['lang_code'],
                (int) $row['idx'],
                (string) $row['value'],
                true
            );
        }

        $options = [];
        foreach ($translations_by_option_id as $option_id => $translations) {
            $options[] = new OptionImplementation(
                $option_id,
                ...$translations
            );
        }

        return new SelectSpecificDataImplementation($field_id, ...$options);
    }

    protected function createTranslation(
        int $field_id,
        int $option_id,
        OptionTranslation $translation
    ): void {
        $this->db->insert(
            'adv_mdf_enum',
            [
                'field_id' => [\ilDBConstants::T_INTEGER, $field_id],
                'option_id' => [\ilDBConstants::T_INTEGER, $option_id],
                'lang_code' => [\ilDBConstants::T_TEXT, $translation->language()],
                'idx' => [\ilDBConstants::T_INTEGER, $translation->getPosition()],
                'value' => [\ilDBConstants::T_TEXT, trim($translation->getValue())]
            ]
        );
    }

    protected function updateTranslation(
        int $field_id,
        int $option_id,
        OptionTranslation $translation
    ): void {
        $this->db->update(
            'adv_mdf_enum',
            [
                'idx' => [\ilDBConstants::T_INTEGER, $translation->getPosition()],
                'value' => [\ilDBConstants::T_TEXT, trim($translation->getValue())]
            ],
            [
                'field_id' => [\ilDBConstants::T_INTEGER, $field_id],
                'option_id' => [\ilDBConstants::T_INTEGER, $option_id],
                'lang_code' => [\ilDBConstants::T_TEXT, $translation->language()]
            ]
        );
    }

    protected function getNextOptionIDInField(int $field_id): int
    {
        $query = 'SELECT MAX(option_id) max_id FROM adv_mdf_enum WHERE field_id = ' .
            $this->db->quote($field_id, \ilDBConstants::T_INTEGER);

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            return $row['max_id'] + 1;
        }
        return 0;
    }
}
