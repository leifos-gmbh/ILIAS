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

namespace ILIAS\MetaData\XML\Reader;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Elements\Scaffolds\NullScaffoldFactory;
use ILIAS\MetaData\Elements\Structure\NullStructureSet;
use ILIAS\MetaData\XML\Dictionary\NullDictionary;
use ILIAS\MetaData\XML\Copyright\NullCopyrightHandler;
use ILIAS\MetaData\Elements\Markers\NullMarkerFactory;
use ILIAS\MetaData\Manipulator\ScaffoldProvider\NullScaffoldProvider;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Elements\NullSet;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;
use ILIAS\MetaData\Elements\Structure\NullStructureElement;
use ILIAS\MetaData\Structure\Definitions\NullDefinition;
use ILIAS\MetaData\Elements\NullElement;
use ILIAS\MetaData\Manipulator\ScaffoldProvider\ScaffoldProviderInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Markers\Action;
use ILIAS\MetaData\Elements\Markers\MarkerFactoryInterface;
use ILIAS\MetaData\Elements\Data\Type;
use SimpleXMLElement;
use ILIAS\MetaData\XML\Version;

class StandardTest extends TestCase
{
    /**
     * Element data types and tag information are contained hyphen-separated in
     * their names in the xml:
     * name = actual name - data type
     *
     * Using the name 'failme' will lead to a refusal to add the element as scaffold.
     *
     * In the actual reader this info is derived from the LOM structure via
     * the scaffold provider, but that is cumbersome to mock here.
     */
    protected function getElement(): ElementInterface
    {
        return new class () extends NullElement {
            public array $exposed_data;
            protected ElementInterface $super_element;
            protected array $sub_elements;

            public function getDefinition(): DefinitionInterface
            {
                return new class ($this->exposed_data) extends NullDefinition {
                    public function __construct(public array $exposed_data)
                    {
                    }

                    public function name(): string
                    {
                        return $this->exposed_data['name'];
                    }

                    public function dataType(): Type
                    {
                        return $this->exposed_data['type'];
                    }
                };
            }

            public function addScaffoldToSubElements(
                ScaffoldProviderInterface $scaffold_provider,
                string $name
            ): ?ElementInterface {
                if ($name === 'failme') {
                    return null;
                }

                $scaffold = clone $this;
                $this->sub_elements[] = $scaffold;

                $scaffold->sub_elements = [];
                $scaffold->super_element = $this;
                $scaffold->exposed_data = [];

                $info_from_name = explode('-', $name);

                $scaffold->exposed_data['name'] = $name;
                $scaffold->exposed_data['type'] = Type::from($info_from_name[1]);

                return $scaffold;
            }

            public function mark(
                MarkerFactoryInterface $factory,
                Action $action,
                string $data_value = ''
            ): void {
                $this->exposed_data['marker_action'] = $action;
                $this->exposed_data['marker_value'] = $data_value;
                if (isset($this->super_element)) {
                    $this->super_element->mark($factory, $action);
                }
            }

            public function exposeData(): array
            {
                $data = $this->exposed_data;
                $data['subs'] = [];
                foreach ($this->sub_elements as $sub_element) {
                    $data['subs'][] = $sub_element->exposeData();
                }
                return $data;
            }
        };
    }

    protected function getReader(): Standard
    {
        $root = $this->getElement();
        $root->exposed_data = ['name' => 'correctroot', 'type' => Type::NULL];

        $scaffold_provider = new class ($root) extends NullScaffoldProvider {
            public function __construct(protected ElementInterface $root)
            {
            }

            public function set(): SetInterface
            {
                return new class ($this->root) extends NullSet {
                    public function __construct(protected ElementInterface $root)
                    {
                    }

                    public function getRoot(): ElementInterface
                    {
                        return $this->root;
                    }
                };
            }
        };

        $dictionary = new class () extends NullDictionary {
        };

        $copyright_handler = new class () extends NullCopyrightHandler {
            public function copyrightFromExport(string $copyright): string
            {
                return '~parsed:' . $copyright . '~';
            }
        };

        return new Standard(
            new NullMarkerFactory(),
            $scaffold_provider,
            $dictionary,
            $copyright_handler
        );
    }

    public function testRead(): void
    {
        $xml_string = /** @lang text */ <<<XML
            <?xml version="1.0"?>
            <correctroot>
                <el1-string>val1</el1-string>
                <el2-none>
                    <el2.1-non_neg_int>val2.1</el2.1-non_neg_int>
                    <el2.2-duration>val2.2</el2.2-duration>
                </el2-none>
            </correctroot>
            XML;
        $xml = new SimpleXMLElement($xml_string);

        $expected_data = [
            'name' => 'correctroot',
            'type' => Type::NULL,
            'marker_action' => Action::CREATE_OR_UPDATE,
            'marker_value' => '',
            'subs' => [
                [
                    'name' => 'el1-string',
                    'type' => Type::STRING,
                    'marker_action' => Action::CREATE_OR_UPDATE,
                    'marker_value' => 'val1',
                    'subs' => []
                ],
                [
                    'name' => 'el2-none',
                    'type' => Type::NULL,
                    'marker_action' => Action::CREATE_OR_UPDATE,
                    'marker_value' => '',
                    'subs' => [
                        [
                            'name' => 'el2.1-non_neg_int',
                            'type' => Type::NON_NEG_INT,
                            'marker_action' => Action::CREATE_OR_UPDATE,
                            'marker_value' => 'val2.1',
                            'subs' => []
                        ],
                        [
                            'name' => 'el2.2-duration',
                            'type' => Type::DURATION,
                            'marker_action' => Action::CREATE_OR_UPDATE,
                            'marker_value' => 'val2.2',
                            'subs' => []
                        ]
                    ]
                ]
            ]
        ];

        $reader = $this->getReader();
        $result_set = $reader->read($xml, Version::V10_0);

        $this->assertEquals(
            $expected_data,
            $result_set->getRoot()->exposeData()
        );
    }

    public function testReadWrongStructureException(): void
    {
        $xml_string = /** @lang text */ <<<XML
            <?xml version="1.0"?>
            <correctroot>
                <el1-string>val1</el1-string>
                <el2-none>
                    <failme>val2.1</failme>
                    <el2.2-duration>val2.2</el2.2-duration>
                </el2-none>
            </correctroot>
            XML;
        $xml = new SimpleXMLElement($xml_string);

        $reader = $this->getReader();

        $this->expectException(\ilMDXMLException::class);
        $result_set = $reader->read($xml, Version::V10_0);
    }

    public function testReadInvalidRootException(): void
    {
        $xml_string = /** @lang text */ <<<XML
            <?xml version="1.0"?>
            <incorrectroot>
                <el1-string>val1</el1-string>
                <el2-none>
                    <el2.1-non_neg_int>val2.1</el2.1-non_neg_int>
                    <el2.2-duration>val2.2</el2.2-duration>
                </el2-none>
            </incorrectroot>
            XML;
        $xml = new SimpleXMLElement($xml_string);

        $reader = $this->getReader();

        $this->expectException(\ilMDXMLException::class);
        $result_set = $reader->read($xml, Version::V10_0);
    }

    public function testReadWithLanguageNone(): void
    {
        $xml_string = /** @lang text */ <<<XML
            <?xml version="1.0"?>
            <correctroot>
                <el-lang>none</el-lang>
            </correctroot>
            XML;
        $xml = new SimpleXMLElement($xml_string);

        $expected_data = [
            'name' => 'correctroot',
            'type' => Type::NULL,
            'marker_action' => Action::CREATE_OR_UPDATE,
            'marker_value' => '',
            'subs' => [
                [
                    'name' => 'el-lang',
                    'type' => Type::LANG,
                    'marker_action' => Action::CREATE_OR_UPDATE,
                    'marker_value' => 'xx',
                    'subs' => []
                ]
            ]
        ];

        $reader = $this->getReader();
        $result_set = $reader->read($xml, Version::V10_0);

        $this->assertEquals(
            $expected_data,
            $result_set->getRoot()->exposeData()
        );
    }

    public function testReadWithLangstring(): void
    {
    }

    public function testReadWithLangstringLanguageNone(): void
    {
    }

    public function testReadWithLangstringNoLanguage(): void
    {
    }

    public function testReadWithLangstringNoString(): void
    {
    }

    public function testReadWithOmittedDataCarryingElement(): void
    {
    }

    public function testReadWithOmittedContainerElement(): void
    {
    }
}
