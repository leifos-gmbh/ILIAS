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

use ILIAS\Data\ObjectId;
use ILIAS\Data\ReferenceId;
use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicLegacyHandler as ilBasicLegacyExportOption;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\DI\Container;
use ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface as ilExportHandlerFileInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface as ilExportHandlerConsumerFileIdentifierCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\HandlerInterface as ilExportHandlerConsumerFileIdentifierInterface;

class ilTestExportOptionARC extends ilBasicLegacyExportOption
{
    protected ilLanguage $lng;

    public function init(
        Container $DIC
    ): void {
        $this->lng = $DIC->language();
        parent::init($DIC);
    }

    public function getExportType(): string
    {
        return 'arc';
    }

    public function getExportOptionId(): string
    {
        return 'ilTestExportOptionARC';
    }

    public function getSupportedRepositoryObjectTypes(): array
    {
        return ['tst'];
    }

    public function getLabel(): string
    {
        return $this->lng->txt('ass_create_export_test_archive');
    }

    public function onExportOptionSelected(
        ilExportHandlerConsumerContextInterface $context
    ): void {
        $context->exportGUIObject()->createTestArchiveExport();
    }

    protected function getExportFiles(
        string $directory
    ): array {
        $file = [];
        try {
            $h_dir = dir($directory);
            while ($entry = $h_dir->read()) {
                if (
                    $entry !== "." &&
                    $entry !== ".." &&
                    (
                        substr($entry, -4) === ".csv" or
                        substr($entry, -5) === ".xlsx"
                    )
                ) {
                    $ts = substr($entry, 0, strpos($entry, "__"));
                    $file[$entry . $this->getExportType()] = [
                        "type" => $this->getExportType(),
                        "file" => $entry,
                        "size" => (int) filesize($directory . "/" . $entry),
                        "timestamp" => (int) $ts
                    ];
                }
            }
        } catch (Exception $e) {

        }
        return $file;
    }

    protected function getDirectory(
        ObjectId $object_id,
        string $export_object_type
    ): string {
        $dir = ilExport::_getExportDirectory(
            $object_id->toInt(),
            "",
            $export_object_type
        );
        $dir = substr($dir, 0, strlen($dir) - strlen("export") - 1);
        return $dir;
    }
}
