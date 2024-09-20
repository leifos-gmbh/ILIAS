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

namespace ILIAS\MetaData\Vocabularies\Dispatch\Presentation;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;
use ILIAS\MetaData\Presentation\NullUtilities;
use ILIAS\MetaData\Vocabularies\Type;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\NullVocabulary;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Vocabularies\Copyright\BridgeInterface as CopyrightBridge;
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledRepository;
use ILIAS\MetaData\Vocabularies\Standard\RepositoryInterface as StandardRepository;
use ILIAS\MetaData\Vocabularies\Copyright\NullBridge as NullCopyrightBridge;
use ILIAS\MetaData\Vocabularies\Controlled\NullRepository as NullControlledRepository;
use ILIAS\MetaData\Vocabularies\Standard\NullRepository as NullStandardRepository;
use ILIAS\MetaData\Paths\Path;

class PresentationTest extends TestCase
{
    public function getPath(string $path_string): PathInterface
    {
        return new class ($path_string) extends NullPath {
            public function __construct(protected string $path_string)
            {
            }

            public function toString(): string
            {
                return $this->path_string;
            }
        };
    }

    public function getVocabulary(
        Type $type,
        string $applicable_to,
        string ...$values
    ): VocabularyInterface {
        $applicable_to_path = $this->getPath($applicable_to);

        return new class ($type, $applicable_to_path, $values) extends NullVocabulary {
            public function __construct(
                protected Type $type,
                protected PathInterface $applicable_to,
                protected array $values
            ) {
            }

            public function type(): Type
            {
                return $this->type;
            }

            public function applicableTo(): PathInterface
            {
                return $this->applicable_to;
            }

            public function values(): \Generator
            {
                yield from $this->values;
            }
        };
    }

    public function getPresentationUtilities(): PresentationUtilities
    {
        return new class () extends NullUtilities {
            public function txt(string $key): string
            {
                return 'translated suffix';
            }
        };
    }

    public function getCopyrightBridge(string ...$values_from_vocab): CopyrightBridge
    {
        return new class ($values_from_vocab) extends NullCopyrightBridge {
            public function __construct(protected array $values_from_vocab)
            {
            }

            public function labelsForValues(
                PathInterface $path_to_element,
                string ...$values
            ): \Generator {
                foreach ($values as $value) {
                    if ($path_to_element->toString() !== 'valid path') {
                        return;
                    }
                    if (!in_array($value, $this->values_from_vocab)) {
                        continue;
                    }
                    yield new class ($value) extends NullLabelledValue {
                        public function __construct(protected string $value)
                        {
                        }

                        public function value(): string
                        {
                            return $this->value;
                        }

                        public function label(): string
                        {
                            return 'copyright label for ' . $this->value;
                        }
                    };
                }
            }
        };
    }

    public function getControlledRepository(string ...$values_from_vocab): ControlledRepository
    {
        return new class ($values_from_vocab) extends NullControlledRepository {
            public function __construct(protected array $values_from_vocab)
            {
            }

            public function getLabelsForValues(
                PathInterface $path_to_element,
                string ...$values
            ): \Generator {
                if ($path_to_element->toString() !== 'valid path') {
                    return;
                }
                foreach ($values as $value) {
                    if (!in_array($value, $this->values_from_vocab)) {
                        continue;
                    }
                    yield new class ($value) extends NullLabelledValue {
                        public function __construct(protected string $value)
                        {
                        }

                        public function value(): string
                        {
                            return $this->value;
                        }

                        public function label(): string
                        {
                            return 'controlled label for ' . $this->value;
                        }
                    };
                }
            }
        };
    }

    public function getStandardRepository(string ...$values_from_vocab): StandardRepository
    {
        return new class ($values_from_vocab) extends NullStandardRepository {
            public function __construct(protected array $values_from_vocab)
            {
            }

            public function getLabelsForValues(
                PresentationUtilities $presentation_utilities,
                PathInterface $path_to_element,
                string ...$values
            ): \Generator {
                if ($path_to_element->toString() !== 'valid path') {
                    return;
                }
                foreach ($values as $value) {
                    if (!in_array($value, $this->values_from_vocab)) {
                        continue;
                    }
                    yield new class ($value) extends NullLabelledValue {
                        public function __construct(protected string $value)
                        {
                        }

                        public function value(): string
                        {
                            return $this->value;
                        }

                        public function label(): string
                        {
                            return 'standard label for ' . $this->value;
                        }
                    };
                }
            }
        };
    }

    protected function assertLabelledValueMatches(
        LabelledValueInterface $labelled_value,
        string $expected_value,
        string $expected_label
    ): void {
        $this->assertSame(
            $expected_value,
            $labelled_value->value(),
            'Value of Labelled value ' . $labelled_value->value() . ' should be ' . $expected_value
        );
        $this->assertSame(
            $expected_label,
            $labelled_value->label(),
            'Label of Labelled value ' . $labelled_value->label() . ' should be ' . $expected_label
        );
    }

    /**
     * @param LabelledValueInterface $labelled_values
     * @param array $expected with each expected labelledValue as ['value' => $value, 'label' => $label]
     */
    protected function assertLabelledValuesMatchInOrder(
        \Generator $labelled_values,
        array $expected
    ): void {
        $as_array = iterator_to_array($labelled_values);
        $this->assertCount(
            count($expected),
            $as_array,
            'There should be ' . count($expected) . ' labelled values, not ' . count($as_array)
        );
        $i = 0;
        foreach ($as_array as $labelled_value) {
            $this->assertLabelledValueMatches(
                $labelled_value,
                $expected[$i]['value'],
                $expected[$i]['label']
            );
            $i++;
        }
    }

    public function singleValueProvider(): array
    {
        return [
            ['v1', true, true, true, 'copyright label for v1'],
            ['v2', true, false, true, 'copyright label for v2'],
            ['v3', true, true, false, 'copyright label for v3'],
            ['v4', true, false, false, 'copyright label for v4'],
            ['v5', false, true, true, 'controlled label for v5'],
            ['v6', false, false, true, 'standard label for v6'],
            ['v7', false, true, false, 'controlled label for v7'],
            ['v8', false, false, false, 'v8']
        ];
    }

    /**
     * @dataProvider singleValueProvider
     */
    public function testPresentableLabels(
        string $value,
        bool $is_in_copyright,
        bool $is_in_controlled,
        bool $is_in_standard,
        string $expected_label
    ): void {
        $presentation = new Presentation(
            $this->getCopyrightBridge(...($is_in_copyright ? [$value] : [])),
            $this->getControlledRepository(...($is_in_controlled ? [$value] : [])),
            $this->getStandardRepository(...($is_in_standard ? [$value] : []))
        );

        $labels = $presentation->presentableLabels(
            $this->getPresentationUtilities(),
            $this->getPath('valid path'),
            false,
            $value
        );

        $this->assertLabelledValuesMatchInOrder(
            $labels,
            [['value' => $value, 'label' => $expected_label]]
        );
    }

    /**
     * @dataProvider singleValueProvider
     */
    public function testPresentableLabelsWithUnknownVocab(
        string $value,
        bool $is_in_copyright,
        bool $is_in_controlled,
        bool $is_in_standard,
        string $expected_label_without_suffix
    ): void {
        $presentation = new Presentation(
            $this->getCopyrightBridge(...($is_in_copyright ? [$value] : [])),
            $this->getControlledRepository(...($is_in_controlled ? [$value] : [])),
            $this->getStandardRepository(...($is_in_standard ? [$value] : []))
        );

        $labels = $presentation->presentableLabels(
            $this->getPresentationUtilities(),
            $this->getPath('valid path'),
            true,
            $value
        );

        $suffix = (!$is_in_standard && !$is_in_controlled && !$is_in_copyright) ? ' translated suffix' : '';
        $this->assertLabelledValuesMatchInOrder(
            $labels,
            [['value' => $value, 'label' => $expected_label_without_suffix . $suffix]]
        );
    }

    public function testPresentableLabelsMultipleValues(): void
    {
        $presentation = new Presentation(
            $this->getCopyrightBridge('cp 1', 'cp 2'),
            $this->getControlledRepository('contr 1', 'contr 2', 'contr 3'),
            $this->getStandardRepository('stand 1', 'stand 2')
        );

        $labels = $presentation->presentableLabels(
            $this->getPresentationUtilities(),
            $this->getPath('valid path'),
            false,
            'contr 2',
            'cp 1',
            'something else',
            'stand 2',
            'contr 3'
        );

        $this->assertLabelledValuesMatchInOrder(
            $labels,
            [
                ['value' => 'contr 2', 'label' => 'controlled label for contr 2'],
                ['value' => 'cp 1', 'label' => 'copyright label for cp 1'],
                ['value' => 'something else', 'label' => 'something else'],
                ['value' => 'stand 2', 'label' => 'standard label for stand 2'],
                ['value' => 'contr 3', 'label' => 'controlled label for contr 3']
            ]
        );
    }

    public function testLabelsForVocabularyStandard(): void
    {
        $presentation = new Presentation(
            $this->getCopyrightBridge('v1', 'v2', 'v3'),
            $this->getControlledRepository('v1', 'v2', 'v3'),
            $this->getStandardRepository('v1', 'v2', 'v3')
        );

        $labels = $presentation->labelsForVocabulary(
            $this->getPresentationUtilities(),
            $this->getVocabulary(
                Type::STANDARD,
                'valid path',
                'v1',
                'v2',
                'v3'
            )
        );

        $this->assertLabelledValuesMatchInOrder(
            $labels,
            [
                ['value' => 'v1', 'label' => 'standard label for v1'],
                ['value' => 'v2', 'label' => 'standard label for v2'],
                ['value' => 'v3', 'label' => 'standard label for v3']
            ]
        );
    }

    public function testLabelsForVocabularyControlledString(): void
    {
        $presentation = new Presentation(
            $this->getCopyrightBridge('v1', 'v2', 'v3'),
            $this->getControlledRepository('v1', 'v2', 'v3'),
            $this->getStandardRepository('v1', 'v2', 'v3')
        );

        $labels = $presentation->labelsForVocabulary(
            $this->getPresentationUtilities(),
            $this->getVocabulary(
                Type::CONTROLLED_STRING,
                'valid path',
                'v1',
                'v2',
                'v3'
            )
        );

        $this->assertLabelledValuesMatchInOrder(
            $labels,
            [
                ['value' => 'v1', 'label' => 'controlled label for v1'],
                ['value' => 'v2', 'label' => 'controlled label for v2'],
                ['value' => 'v3', 'label' => 'controlled label for v3']
            ]
        );
    }

    public function testLabelsForVocabularyControlledVocabValue(): void
    {
        $presentation = new Presentation(
            $this->getCopyrightBridge('v1', 'v2', 'v3'),
            $this->getControlledRepository('v1', 'v2', 'v3'),
            $this->getStandardRepository('v1', 'v2', 'v3')
        );

        $labels = $presentation->labelsForVocabulary(
            $this->getPresentationUtilities(),
            $this->getVocabulary(
                Type::CONTROLLED_VOCAB_VALUE,
                'valid path',
                'v1',
                'v2',
                'v3'
            )
        );

        $this->assertLabelledValuesMatchInOrder(
            $labels,
            [
                ['value' => 'v1', 'label' => 'controlled label for v1'],
                ['value' => 'v2', 'label' => 'controlled label for v2'],
                ['value' => 'v3', 'label' => 'controlled label for v3']
            ]
        );
    }

    public function testLabelsForVocabularyCopyright(): void
    {
        $presentation = new Presentation(
            $this->getCopyrightBridge('v1', 'v2', 'v3'),
            $this->getControlledRepository('v1', 'v2', 'v3'),
            $this->getStandardRepository('v1', 'v2', 'v3')
        );

        $labels = $presentation->labelsForVocabulary(
            $this->getPresentationUtilities(),
            $this->getVocabulary(
                Type::COPYRIGHT,
                'valid path',
                'v1',
                'v2',
                'v3'
            )
        );

        $this->assertLabelledValuesMatchInOrder(
            $labels,
            [
                ['value' => 'v1', 'label' => 'copyright label for v1'],
                ['value' => 'v2', 'label' => 'copyright label for v2'],
                ['value' => 'v3', 'label' => 'copyright label for v3']
            ]
        );
    }
}
