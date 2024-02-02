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

namespace ILIAS\AdvancedMetaData\FieldDefinition\GenericData;

use ILIAS\AdvancedMetaData\FieldDefinition\Type;

interface GenericData
{
    public function id(): ?int;

    public function type(): Type;

    /**
     * Is this data is already persisted under an ID?
     */
    public function isPersisted(): bool;

    /**
     * Was the contained data altered with respect to what is persisted?
     * Returns true if not persisted.
     */
    public function containsChanges(): bool;

    public function getRecordID(): int;

    public function setRecordID(int $id): void;

    public function getImportID(): string;

    public function setImportID(string $id): void;

    public function getTitle(): string;

    public function setTitle(string $title): void;

    public function getDescription(): string;

    public function setDescription(string $description): void;

    public function getPosition(): int;

    public function setPosition(int $position): void;

    public function isSearchable(): bool;

    public function setSearchable(bool $searchable): void;

    public function isRequired(): bool;

    public function setRequired(bool $required): void;

    public function getFieldValues(): array;

    public function setFieldValues(array $values): void;
}
