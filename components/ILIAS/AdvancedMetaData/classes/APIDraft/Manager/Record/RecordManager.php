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

namespace ILIAS\AdvancedMetaData\APIDraft\Manager\Record;

use ILIAS\Data\ObjectId;
use ILIAS\Data\LanguageTag;
use ILIAS\AdvancedMetaData\APIDraft\Exception;

interface RecordManager
{
    public function createGlobal(
        bool $active,
        LanguageTag $default_language,
        string $title,
        string $description
    ): RecordID;

    public function createLocal(
        ObjectId $parent_object,
        bool $active,
        LanguageTag $default_language,
        string $title,
        string $description
    ): RecordID;

    public function delete(RecordID $id): void;

    public function manageActivation(): ActivationManager;

    public function setTranslation(
        RecordID $id,
        LanguageTag $language,
        bool $is_default,
        string $title,
        string $description
    ): void;

    /**
     * @throws Exception The default language can not be removed.
     */
    public function removeTranslation(
        RecordID $id,
        LanguageTag $language
    ): void;
}
