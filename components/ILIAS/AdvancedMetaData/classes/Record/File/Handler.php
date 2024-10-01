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

namespace ILIAS\AdvancedMetaData\Record\File;

use ILIAS\AdvancedMetaData\Record\File\I\HandlerInterface as ilAMDRecordFileInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\CollectionInterface as ilAMDRecordFileRepositoryElementCollectionInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\HandlerInterface as ilAMDRecordFileRepositoryElementInterface;
use ILIAS\Data\ObjectId;
use ILIAS\AdvancedMetaData\Record\File\I\FactoryInterface as ilAMDRecordFileFactoryInterface;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Services as ilResourceStorageServices;

class Handler implements ilAMDRecordFileInterface
{
    protected ilAMDRecordFileFactoryInterface $amd_record_file_factory;
    protected ilResourceStorageServices $irss;

    public function __construct(
        ilAMDRecordFileFactoryInterface $amd_record_file_factory,
        ilResourceStorageServices $irss
    ) {
        $this->amd_record_file_factory = $amd_record_file_factory;
        $this->irss = $irss;
    }

    public function getFilesByObjectId(
        ObjectId $object_id
    ): ilAMDRecordFileRepositoryElementCollectionInterface {
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withObjectId($object_id);
        return $this->amd_record_file_factory->repository()->handler()->getElements($key);
    }

    public function getFileByObjectIdAndResourceId(
        ObjectId $object_id,
        string $resource_id_serialized
    ): ilAMDRecordFileRepositoryElementInterface|null {
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withObjectId($object_id)
            ->withResourceIdSerialized($resource_id_serialized);
        $elements = $this->amd_record_file_factory->repository()->handler()->getElements($key);
        $elements->rewind();
        return $elements->count() === 0 ? null : $elements->current();
    }

    public function getGlobalFiles(): ilAMDRecordFileRepositoryElementCollectionInterface
    {
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withIsGlobal(true);
        return $this->amd_record_file_factory->repository()->handler()->getElements($key);
    }

    public function addFile(
        ObjectId $object_id,
        int $user_id,
        string $file_name,
        FileStream $content
    ): void {
        $stakeholder = $this->amd_record_file_factory->repository()->stakeholder()->handler()
            ->withOwnerId($user_id);
        $rid = $this->irss->manage()->stream($content, $stakeholder);
        $this->irss->manage()->getResource($rid)->getCurrentRevision()->setTitle();
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withObjectId($object_id)
            ->withIsGlobal(false)
            ->withResourceIdSerialized($rid->serialize());
        $values = $this->amd_record_file_factory->repository()->values()->handler();
        $this->amd_record_file_factory->repository()->handler()->store($key, $values);
    }

    public function addGlobalFile(
        int $user_id,
        string $file_name,
        FileStream $content
    ): void {
        $stakeholder = $this->amd_record_file_factory->repository()->stakeholder()->handler()
            ->withOwnerId($user_id);
        $rid = $this->irss->manage()->stream($content, $stakeholder);
        $this->irss->manage()->getResource($rid)->getCurrentRevision()->setTitle();
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withObjectId(new ObjectId(0))
            ->withIsGlobal(true)
            ->withResourceIdSerialized($rid->serialize());
        $values = $this->amd_record_file_factory->repository()->values()->handler();
        $this->amd_record_file_factory->repository()->handler()->store($key, $values);
    }

    public function download(
        ObjectId $object_id,
        string $resource_id_serialized,
        string|null $filename_overwrite
    ): void {
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withIsGlobal(false)
            ->withObjectId($object_id)
            ->withResourceIdSerialized($resource_id_serialized);
        $elements = $this->amd_record_file_factory->repository()->handler()->getElements($key);
        if ($elements->count() === 0) {
            return;
        }
        $elements->rewind();
        $elements->current()->getIRSS()->download($filename_overwrite);
    }

    public function downloadGlobal(
        string $resource_id_serialized,
        string|null $filename_overwrite
    ): void {
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withIsGlobal(true)
            ->withObjectId(new ObjectId(0))
            ->withResourceIdSerialized($resource_id_serialized);
        $elements = $this->amd_record_file_factory->repository()->handler()->getElements($key);
        if ($elements->count() === 0) {
            return;
        }
        $elements->rewind();
        $elements->current()->getIRSS()->download($filename_overwrite);
    }

    public function delete(
        ObjectId $object_id,
        int $user_id,
        string $resource_id_serialized
    ): void {
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withIsGlobal(false)
            ->withObjectId($object_id)
            ->withResourceIdSerialized($resource_id_serialized);
        $elements = $this->amd_record_file_factory->repository()->handler()->getElements($key);
        if ($elements->count() === 0) {
            return;
        }
        $elements->rewind();
        $element = $elements->current();
        $this->amd_record_file_factory->repository()->handler()->delete($key);
    }

    public function deleteGlobal(
        int $user_id,
        string $resource_id_serialized
    ): void {
        $key = $this->amd_record_file_factory->repository()->key()->handler()
            ->withIsGlobal(true)
            ->withObjectId(new ObjectId(0))
            ->withResourceIdSerialized($resource_id_serialized);
        $elements = $this->amd_record_file_factory->repository()->handler()->getElements($key);
        if ($elements->count() === 0) {
            return;
        }
        $elements->rewind();
        $element = $elements->current();

        $this->amd_record_file_factory->repository()->handler()->delete($key);
    }
}
