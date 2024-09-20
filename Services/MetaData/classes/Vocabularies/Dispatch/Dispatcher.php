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

namespace ILIAS\MetaData\Vocabularies\Dispatch;

use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Vocabularies\Type;
use ILIAS\MetaData\Vocabularies\Dispatch\Info\InfosInterface;
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledRepo;
use ILIAS\MetaData\Vocabularies\Standard\RepositoryInterface as StandardRepo;
use ILIAS\MetaData\Vocabularies\Copyright\BridgeInterface as CopyrightBridge;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;

class Dispatcher implements DispatcherInterface
{
    protected InfosInterface $infos;
    protected PathFactory $path_factory;
    protected CopyrightBridge $copyright_bridge;
    protected ControlledRepo $controlled_repo;
    protected StandardRepo $standard_repo;

    public function __construct(
        InfosInterface $infos,
        PathFactory $path_factory,
        CopyrightBridge $copyright_bridge,
        ControlledRepo $controlled_repo,
        StandardRepo $standard_repo
    ) {
        $this->infos = $infos;
        $this->path_factory = $path_factory;
        $this->copyright_bridge = $copyright_bridge;
        $this->controlled_repo = $controlled_repo;
        $this->standard_repo = $standard_repo;
    }

    /**
     * @return VocabularyInterface[]
     */
    public function vocabulariesForElement(
        BaseElementInterface $element
    ): \Generator {
        $path_to_element = $this->path_factory->toElement($element);

        $from_copyright = $this->copyright_bridge->vocabularyForElement($path_to_element);
        if (!is_null($from_copyright)) {
            yield $from_copyright;
        }
        yield from $this->controlled_repo->getVocabulariesForElement($path_to_element);
        yield from $this->standard_repo->getVocabularies($path_to_element);
    }

    /**
     * @return VocabularyInterface[]
     */
    public function activeVocabulariesForElement(
        BaseElementInterface $element
    ): \Generator {
        $path_to_element = $this->path_factory->toElement($element);

        $from_copyright = $this->copyright_bridge->vocabularyForElement($path_to_element);
        if (!is_null($from_copyright) && $from_copyright->isActive()) {
            yield $from_copyright;
        }
        yield from $this->controlled_repo->getActiveVocabulariesForElement($path_to_element);
        yield from $this->standard_repo->getActiveVocabularies($path_to_element);
    }

    public function delete(VocabularyInterface $vocabulary): void
    {
        if (!$this->infos->canBeDeleted($vocabulary)) {
            throw new \ilMDVocabulariesException('Vocabulary cannot be deleted.');
        }

        switch ($vocabulary->type()) {
            case Type::CONTROLLED_STRING:
            case Type::CONTROLLED_VOCAB_VALUE:
                $this->controlled_repo->deleteVocabulary($vocabulary->id());
                break;

            default:
            case Type::STANDARD:
            case Type::COPYRIGHT:
                break;
        }
    }
}
