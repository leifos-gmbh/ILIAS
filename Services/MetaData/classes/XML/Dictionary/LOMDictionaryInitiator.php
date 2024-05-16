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

namespace ILIAS\MetaData\XML\Dictionary;

use ILIAS\MetaData\Structure\Dictionaries\DictionaryInitiator as BaseDictionaryInitiator;
use ILIAS\MetaData\XML\Version;
use ILIAS\MetaData\XML\SpecialCase;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;

class LOMDictionaryInitiator extends BaseDictionaryInitiator
{
    protected PathFactoryInterface $path_factory;

    public function __construct(
        TagFactoryInterface $tag_factory,
        PathFactoryInterface $path_factory,
        StructureSetInterface $structure
    ) {
        $this->path_factory = $path_factory;
        parent::__construct($path_factory, $structure);
    }

    public function get(): DictionaryInterface
    {
        $this->initDictionary();
        return new LOMDictionary($this->path_factory, ...$this->getTagAssignments());
    }

    protected function initDictionary(): void
    {
        $structure = $this->getStructure();

        $this->addTagsToCopyright($structure);
        $this->addTagsToOtherLangStrings($structure);
    }

    protected function addTagsToCopyright(StructureSetInterface $structure): void
    {
        $tag_10 = new Tag(
            Version::V10_0,
            SpecialCase::COPYRIGHT
        );
        $tag_4 = new Tag(
            Version::V4_1_0,
            SpecialCase::COPYRIGHT
        );

        $copyright = $structure->getRoot()
                               ->getSubElement('rights')
                               ->getSubElement('description')
                               ->getSubElement('string');

        $this->addTagToElement($tag_10, $copyright);
        $this->addTagToElement($tag_4, $copyright);
    }

    protected function addTagsToOtherLangStrings(StructureSetInterface $structure): void
    {
        $root = $structure->getRoot();

        $general = $root->getSubElement('general');
        $this->addTagsToLangString($general->getSubElement('title'));
        $this->addTagsToLangString($general->getSubElement('description'));
        $this->addTagsToLangString($general->getSubElement('keyword'));
        $this->addTagsToLangString($general->getSubElement('coverage'));

        $lifecycle = $root->getSubElement('lifeCycle');
        $this->addTagsToLangString($lifecycle->getSubElement('version'));
        $this->addTagsToLangString(
            $lifecycle->getSubElement('contribute')
                      ->getSubElement('date')
                      ->getSubElement('description')
        );

        $metametadata = $root->getSubElement('metaMetadata');
        $this->addTagsToLangString(
            $metametadata->getSubElement('contribute')
                         ->getSubElement('date')
                         ->getSubElement('description')
        );

        $technical = $root->getSubElement('technical');
        $this->addTagsToLangString($technical->getSubElement('installationRemarks'));
        $this->addTagsToLangString($technical->getSubElement('otherPlatformRequirements'));
        $this->addTagsToLangString(
            $technical->getSubElement('duration')
                      ->getSubElement('description')
        );

        $educational = $root->getSubElement('educational');
        $this->addTagsToLangString($educational->getSubElement('typicalAgeRange'));
        $this->addTagsToLangString($educational->getSubElement('description'));
        $this->addTagsToLangString(
            $educational->getSubElement('typicalLearningTime')
                        ->getSubElement('description')
        );

        $rights = $root->getSubElement('rights');
        $this->addTagsToLangString($rights->getSubElement('description'));

        $relation = $root->getSubElement('relation');
        $this->addTagsToLangString(
            $relation->getSubElement('resource')
                     ->getSubElement('description')
        );

        $annotation = $root->getSubElement('annotation');
        $this->addTagsToLangString(
            $annotation->getSubElement('date')
                     ->getSubElement('description')
        );
        $this->addTagsToLangString($annotation->getSubElement('description'));

        $classification = $root->getSubElement('classification');
        $taxon_path = $classification->getSubElement('taxonPath');
        $this->addTagsToLangString($taxon_path->getSubElement('source'));
        $this->addTagsToLangString(
            $taxon_path->getSubElement('taxon')
                       ->getSubElement('entry')
        );
        $this->addTagsToLangString($classification->getSubElement('description'));
        $this->addTagsToLangString($classification->getSubElement('keyword'));
    }

    protected function addTagsToLangString(StructureElementInterface $element): void
    {
        $tag_10 = new Tag(
            Version::V10_0,
            SpecialCase::LANGSTRING
        );
        $tag_4 = new Tag(
            Version::V4_1_0,
            SpecialCase::LANGSTRING
        );

        $this->addTagToElement($tag_10, $element);
        $this->addTagToElement($tag_4, $element);
    }
}
