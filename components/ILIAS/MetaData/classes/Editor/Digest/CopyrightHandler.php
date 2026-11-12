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

namespace ILIAS\MetaData\Editor\Digest;

use ILIAS\MetaData\Copyright\RepositoryInterface as CopyrightRepository;
use ILIAS\MetaData\Copyright\EntryInterface;
use ILIAS\MetaData\Settings\SettingsInterface as Settings;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as CopyrightIDHandler;
use ILIAS\MetaData\OERHarvester\Settings\SettingsInterface as OERHarvesterSettings;
use ILIAS\MetaData\Copyright\Input\FactoryInterface as CopyrightInputFactory;
use ILIAS\MetaData\Copyright\Input\BuilderInterface as CopyrightInputBuilder;

class CopyrightHandler
{
    /**
     * @var EntryInterface[]
     */
    protected array $entries;

    public function __construct(
        protected CopyrightRepository $repository,
        protected Settings $settings,
        protected OERHarvesterSettings $harvester_settings,
        protected CopyrightIDHandler $copyright_id_handler,
        protected CopyrightInputFactory $copyright_input_factory
    ) {
    }

    public function isCPSelectionActive(): bool
    {
        return $this->settings->isCopyrightSelectionActive();
    }

    public function getCopyrightInputBuilder(): CopyrightInputBuilder
    {
        return $this->copyright_input_factory->buildInput()->withCustomInput()->withNonFeaturedEntries();
    }

    public function isCopyrightIdentifier(string $value): bool
    {
        return $this->copyright_id_handler->isIdentifierValid($value);
    }

    public function getCopyrightEntry(string $identifier): EntryInterface
    {
        return $this->repository->getEntry(
            $this->copyright_id_handler->parseEntryIDFromIdentifier($identifier)
        );
    }

    /**
     * @return string[]
     */
    public function getPublishedCopyrightEntryIdentifiers(): array
    {
        $res = [];
        foreach ($this->harvester_settings->getCopyrightEntryIDsSelectedForPublishing() as $id) {
            $res[] = $this->copyright_id_handler->buildIdentifierFromEntryID($id);
        }
        return $res;
    }

    public function isObjectTypePublished(string $type): bool
    {
        return $this->harvester_settings->isObjectTypeSelectedForPublishing($type);
    }
}
