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

use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;

class ilMDVocabularyUploadHandlerGUI extends AbstractCtrlAwareUploadHandler
{
    protected function getUploadResult(): HandlerResult
    {
        // TODO: Implement getUploadResult() method.
    }

    protected function getRemoveResult(string $identifier): HandlerResult
    {
        // TODO: Implement getRemoveResult() method.
    }

    public function getInfoResult(string $identifier): ?FileInfoResult
    {
        // TODO: Implement getInfoResult() method.
    }

    public function getInfoForExistingFiles(array $file_ids): array
    {
        // TODO: Implement getInfoForExistingFiles() method.
    }
}
