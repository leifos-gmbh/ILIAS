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

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Vocabularies\Type;
use ILIAS\MetaData\Vocabularies\Dispatch\Info\InfosInterface;
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledRepo;
use ILIAS\MetaData\Vocabularies\Controlled\NullRepository as NullControlledRepo;
use ILIAS\MetaData\Vocabularies\Standard\RepositoryInterface as StandardRepo;
use ILIAS\MetaData\Vocabularies\Dispatch\Info\NullInfos;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Standard\NullRepository as NullStandardRepo;
use ILIAS\MetaData\Vocabularies\NullVocabulary;
use ILIAS\MetaData\Vocabularies\Slots\Conditions\ConditionInterface;
use ILIAS\MetaData\Vocabularies\Slots\Conditions\NullCondition;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;

class PropertiesTest extends TestCase
{
    public function getVocabulary(
        Type $type,
        string $id,
        SlotIdentifier $slot = SlotIdentifier::NULL
    ): VocabularyInterface {
        return new class ($type, $id, $slot) extends NullVocabulary {
            public function __construct(
                protected Type $type,
                protected string $id,
                protected SlotIdentifier $slot
            ) {
            }

            public function type(): Type
            {
                return $this->type;
            }

            public function id(): string
            {
                return $this->id;
            }

            public function slot(): SlotIdentifier
            {
                return $this->slot;
            }
        };
    }

    public function getInfos(
        bool $is_deactivatable = true,
        bool $can_disallow_custom_input = true,
        bool $is_custom_input_applicable = true
    ): InfosInterface {
        return new class (
            $is_deactivatable,
            $can_disallow_custom_input,
            $is_custom_input_applicable
        ) extends NullInfos {
            public function __construct(
                protected bool $is_deactivatable,
                protected bool $can_disallow_custom_input,
                protected bool $is_custom_input_applicable
            ) {
            }

            public function isDeactivatable(VocabularyInterface $vocabulary): bool
            {
                return $this->is_deactivatable;
            }

            public function canDisallowCustomInput(VocabularyInterface $vocabulary): bool
            {
                return $this->can_disallow_custom_input;
            }

            public function isCustomInputApplicable(VocabularyInterface $vocabulary): bool
            {
                return $this->is_custom_input_applicable;
            }
        };
    }

    public function getControlledRepo(): ControlledRepo
    {
        return new class () extends NullControlledRepo {
            public array $changes_to_active = [];
            public array $changes_to_custom_input = [];

            public function setActiveForVocabulary(
                string $vocab_id,
                bool $active
            ): void {
                $this->changes_to_active[] = ['id' => $vocab_id, 'active' => $active];
            }

            public function setCustomInputsAllowedForVocabulary(
                string $vocab_id,
                bool $custom_inputs
            ): void {
                $this->changes_to_custom_input[] = ['id' => $vocab_id, 'custom_inputs' => $custom_inputs];
            }
        };
    }

    public function getStandardRepo(): StandardRepo
    {
        return new class () extends NullStandardRepo {
            public array $changes_to_active = [];

            public function activateVocabulary(SlotIdentifier $slot): void
            {
                $this->changes_to_active[] = [
                    'slot' => $slot,
                    'active' => true
                ];
            }

            public function deactivateVocabulary(SlotIdentifier $slot): void
            {
                $this->changes_to_active[] = [
                    'slot' => $slot,
                    'active' => false
                ];
            }
        };
    }

    public function testActivateStandard(): void
    {
        $properties = new Properties(
            $this->getInfos(true, false, false),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->activate($vocab);

        $this->assertSame(
            [['slot' => SlotIdentifier::LIFECYCLE_STATUS, 'active' => true]],
            $standard_repo->changes_to_active
        );
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testActivateControlledString(): void
    {
        $properties = new Properties(
            $this->getInfos(true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_STRING, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->activate($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertSame(
            [['id' => 'vocab id', 'active' => true]],
            $controlled_repo->changes_to_active
        );
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testActivateControlledVocabValue(): void
    {
        $properties = new Properties(
            $this->getInfos(true, false, false),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_VOCAB_VALUE, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->activate($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertSame(
            [['id' => 'vocab id', 'active' => true]],
            $controlled_repo->changes_to_active
        );
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testActivateCopyright(): void
    {
        $properties = new Properties(
            $this->getInfos(false, false, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::COPYRIGHT, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->activate($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testDeactivateNotDeactivatableException(): void
    {
        $properties = new Properties(
            $this->getInfos(false, false, false),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $this->expectException(\ilMDVocabulariesException::class);
        $properties->deactivate($vocab);
    }

    public function testDeactivateStandard(): void
    {
        $properties = new Properties(
            $this->getInfos(true, false, false),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->deactivate($vocab);

        $this->assertSame(
            [['slot' => SlotIdentifier::LIFECYCLE_STATUS, 'active' => false]],
            $standard_repo->changes_to_active
        );
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testDeactivateControlledString(): void
    {
        $properties = new Properties(
            $this->getInfos(true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_STRING, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->deactivate($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertSame(
            [['id' => 'vocab id', 'active' => false]],
            $controlled_repo->changes_to_active
        );
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testDeactivateControlledVocabValue(): void
    {
        $properties = new Properties(
            $this->getInfos(true, false, false),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_VOCAB_VALUE, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->deactivate($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertSame(
            [['id' => 'vocab id', 'active' => false]],
            $controlled_repo->changes_to_active
        );
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testDeactivateCopyright(): void
    {
        $properties = new Properties(
            $this->getInfos(true, false, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::COPYRIGHT, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->deactivate($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testAllowCustomInputNotApplicableException(): void
    {
        $properties = new Properties(
            $this->getInfos(true, false, false),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $this->expectException(\ilMDVocabulariesException::class);
        $properties->allowCustomInput($vocab);
    }

    public function testAllowCustomInputStandard(): void
    {
        $properties = new Properties(
            $this->getInfos(true, false, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->allowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testAllowCustomInputControlledString(): void
    {
        $properties = new Properties(
            $this->getInfos(true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_STRING, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->allowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertSame(
            [['id' => 'vocab id', 'custom_inputs' => true]],
            $controlled_repo->changes_to_custom_input
        );
    }

    public function testAllowCustomInputControlledVocabValue(): void
    {
        $properties = new Properties(
            $this->getInfos(true, false, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_VOCAB_VALUE, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->allowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testAllowCustomInputCopyright(): void
    {
        $properties = new Properties(
            $this->getInfos(false, false, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::COPYRIGHT, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->allowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testDisallowCustomInputNotApplicableException(): void
    {
        $properties = new Properties(
            $this->getInfos(true, true, false),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $this->expectException(\ilMDVocabulariesException::class);
        $properties->disallowCustomInput($vocab);
    }

    public function testDisallowCustomInputCannotDisallowException(): void
    {
        $properties = new Properties(
            $this->getInfos(true, false, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $this->expectException(\ilMDVocabulariesException::class);
        $properties->disallowCustomInput($vocab);
    }

    public function testDisallowCustomInputStandard(): void
    {
        $properties = new Properties(
            $this->getInfos(true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->disallowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testDisallowCustomInputControlledString(): void
    {
        $properties = new Properties(
            $this->getInfos(true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_STRING, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->disallowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertSame(
            [['id' => 'vocab id', 'custom_inputs' => false]],
            $controlled_repo->changes_to_custom_input
        );
    }

    public function testDisallowCustomInputControlledVocabValue(): void
    {
        $properties = new Properties(
            $this->getInfos(true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_VOCAB_VALUE, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->disallowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testDisallowCustomInputCopyright(): void
    {
        $properties = new Properties(
            $this->getInfos(false, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::COPYRIGHT, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $properties->disallowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }
}
