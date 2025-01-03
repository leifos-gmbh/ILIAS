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

namespace ILIAS\Export\HTML;

use ilDBInterface;
use ILIAS\Repository\IRSS\IRSSWrapper;

class ExportFileDBRepository
{
    public function __construct(
        protected ilDBInterface $db,
        protected IRSSWrapper $irss,
        protected DataService $data
    )
    {
    }

    public function create(
        \ilExportHTMLStakeholder $stakeholder,
        int $object_id,
        string $type = ""
    ): string
    {
        $rid = $this->irss->createContainer(
            $stakeholder
        );
        $this->db->insert('export_files_html', [
            'object_id' => ['integer', $object_id],
            'rid' => ['text', $rid],
            'timestamp' => ['timestamp', \ilUtil::now()],
            'type' => ['text', $type]
        ]);
        return $rid;
    }

    public function addString(
        string $rid,
        string $content,
        string $path,
    ): void {
        $this->irss->addStringToContainer(
            $rid,
            $content,
            $path
        );
    }

    public function update(ExportFile $file): void
    {
        $this->db->update('export_files_html', [
            'timestamp' => ['timestamp', $file->getTimestamp()],
            'type' => ['text', $file->getType()]
        ], [
            'object_id' => ['integer', $file->getObjectId()],
            'rid' => ['text', $file->getRid()]
        ]);
    }

    public function delete(int $object_id, string $rid): void
    {
        $this->db->manipulateF(
            'DELETE FROM export_files_html WHERE object_id = %s AND rid = %s',
            ['integer', 'text'],
            [$object_id, $rid]
        );
    }

    public function getById(int $object_id, string $rid): ?ExportFile
    {
        $set = $this->db->queryF(
            'SELECT * FROM export_files_html WHERE object_id = %s AND rid = %s',
            ['integer', 'text'],
            [$object_id, $rid]
        );

        $record = $this->db->fetchAssoc($set);
        return $record ? $this->getExportFileFromRecord($record) : null;
    }

    /**
     * @return \Generator<ExportFile>
     */
    public function getAllOfObjectId(int $object_id): \Generator
    {
        $set = $this->db->queryF("SELECT * FROM export_files_html " .
            " WHERE object_id = %s ORDER BY timestamp DESC",
            ["integer"],
            [$object_id]
        );
        while ($record = $this->db->fetchAssoc($set)) {
            yield $this->getExportFileFromRecord($record);
        }
    }

    protected function getExportFileFromRecord(array $record): ExportFile
    {
        return $this->data->exportFile(
            (int) $record['object_id'],
            (string) $record['rid'],
            (string) $record['timestamp'],
            (string) $record['type']
        );
    }
}