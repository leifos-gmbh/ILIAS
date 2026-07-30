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

use ILIAS\Data\ReferenceId;
use ILIAS\AdvancedMetaData\APIDraft\Exception;

interface ActivationManager
{
    public function updateGlobalActivation(
        RecordID $id,
        bool $active
    ): void;

    /**
     * @throws Exception Scopes can not be used for local records.
     */
    public function addScope(
        RecordID $id,
        ReferenceId $ref_id
    ): void;

    public function removeScope(
        RecordID $id,
        ReferenceId $ref_id
    ): void;

    public function updateActivationByObjectType(
        RecordID $id,
        string $type,
        string $sub_type,
        ActivationStatus $status
    ): void;
}
