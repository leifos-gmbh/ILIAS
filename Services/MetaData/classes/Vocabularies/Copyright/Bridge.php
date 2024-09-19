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
use ILIAS\MetaData\Copyright\RepositoryInterface as CopyrightRepository;
use ILIAS\MetaData\Settings\SettingsInterface;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as IdentifierHandler;
use ILIAS\MetaData\Vocabularies\Dispatch\LabelledValueInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\LabelledValue;
use ILIAS\MetaData\Vocabularies\FactoryInterface;

class Bridge implements BridgeInterface
{
    protected FactoryInterface $factory;
    protected PathFactory $path_factory;
    protected SettingsInterface $settings;
    protected CopyrightRepository $copyright_repository;
    protected IdentifierHandler $identifier_handler;

    public function __construct(
        FactoryInterface $factory,
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
        PathInterface $path_to_element
    ): ?VocabularyInterface {
        if (
            !$this->settings->isCopyrightSelectionActive() ||
            $path_to_element->toString() !== $this->copyrightPath()->toString()
        ) {
            return null;
        }

        $values = [];
        foreach ($this->copyright_repository->getAllEntries() as $copyright) {
            $values[] = $this->identifier_handler->buildIdentifierFromEntryID($copyright->id());
        }

        if (empty($values)) {
            return null;
        }
        return $this->factory->copyright($this->copyrightPath(), ...$values)->get();
    }

    /**
     * @return LabelledValueInterface[]
     */
    public function labelsForValues(
        PathInterface $path_to_element,
        string ...$values
    ): \Generator {
        $labelled_values = [];
        if (
            $this->settings->isCopyrightSelectionActive() &&
            $path_to_element->toString() === $this->copyrightPath()->toString()
        ) {
            foreach ($this->copyright_repository->getAllEntries() as $copyright) {
                $identifier = $this->identifier_handler->buildIdentifierFromEntryID($copyright->id());
                if (!in_array($identifier, $values)) {
                    continue;
                }
                $labelled_values[$identifier] = new LabelledValue(
                    $identifier,
                    $copyright->title(),
                    true
                );
            }
        }

        foreach ($values as $value) {
            if (array_key_exists($value, $labelled_values)) {
                yield $labelled_values[$value];
                continue;
            }
            yield new LabelledValue($value, '', false);
        }
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
