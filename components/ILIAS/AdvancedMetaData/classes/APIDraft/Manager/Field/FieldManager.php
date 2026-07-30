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

namespace ILIAS\AdvancedMetaData\APIDraft\Manager\Field;

use ILIAS\AdvancedMetaData\APIDraft\Manager\Record\RecordID;
use ILIAS\Data\LanguageTag;
use ILIAS\AdvancedMetaData\APIDraft\Exception;
use ILIAS\AdvancedMetaData\APIDraft\Manager\Field\Type\Configuration;

interface FieldManager
{
    public function create(
        RecordID $parent_record,
        string $title,
        string $description,
        Configuration $type_configuration,
        bool $searchable
    ): FieldID;

    public function delete(FieldID $id): void;

    /**
     * @throws Exception The type configuration must match the type of the field.
     */
    public function updateConfiguration(
        FieldID $id,
        Configuration $type_configuration
    ): void;

    public function updateSearchable(
        FieldID $id,
        bool $searchable
    ): void;

    public function setTranslation(
        FieldID $id,
        LanguageTag $language,
        string $title,
        string $description
    ): void;

    public function removeTranslation(
        FieldID $id,
        LanguageTag $language
    ): void;
}
