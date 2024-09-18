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

namespace ILIAS\MetaData\Vocabularies\Copyright;

use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Vocabularies\Factory;
use ILIAS\MetaData\Copyright\RepositoryInterface as CopyrightRepository;
use ILIAS\MetaData\Settings\SettingsInterface;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as IdentifierHandler;

class Bridge implements BridgeInterface
{
    protected Factory $factory;
    protected PathFactory $path_factory;
    protected SettingsInterface $settings;
    protected CopyrightRepository $copyright_repository;
    protected IdentifierHandler $identifier_handler;

    public function __construct(
        Factory $factory,
        PathFactory $path_factory,
        SettingsInterface $settings,
        CopyrightRepository $copyright_repository,
        IdentifierHandler $identifier_handler
    ) {
        $this->factory = $factory;
        $this->path_factory = $path_factory;
        $this->settings = $settings;
        $this->copyright_repository = $copyright_repository;
        $this->identifier_handler = $identifier_handler;
    }

    public function vocabularyForElement(
        BaseElementInterface $element
    ): ?VocabularyInterface {
        $path = $this->path_factory->toElement($element);
        if (
            !$this->settings->isCopyrightSelectionActive() ||
            $path->toString() !== $this->copyrightPath()->toString()
        ) {
            return null;
        }

        $values = [];
        foreach ($this->copyright_repository->getActiveEntries() as $copyright) {
            $values[] = $this->identifier_handler->buildIdentifierFromEntryID($copyright->id());
        }

        if (empty($values)) {
            return null;
        }
        return $this->factory->copyright(...$values)->get();
    }

    public function labelForValue(string $value): string
    {
        if (
            !$this->settings->isCopyrightSelectionActive() ||
            !$this->identifier_handler->isIdentifierValid($value)
        ) {
            return '';
        }

        return $this->copyright_repository->getEntry(
            $this->identifier_handler->parseEntryIDFromIdentifier($value)
        )->title();
    }

    protected function copyrightPath(): PathInterface
    {
        return $this->path_factory
                    ->custom()
                    ->withNextStep('rights')
                    ->withNextStep('description')
                    ->withNextStep('string')
                    ->get();
    }
}
