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

namespace ILIAS\MetaData\Settings\Vocabularies\Import;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledVocabsRepository;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Vocabularies\Controlled\NullRepository;
use ILIAS\MetaData\Paths\NullFactory;
use ILIAS\MetaData\Paths\BuilderInterface;
use ILIAS\MetaData\Paths\NullBuilder;
use ILIAS\MetaData\Paths\NullPath;
use DOMDocument;

class ImporterAndResultTest extends TestCase
{
    protected function getRepo(
        bool $config_error = false,
        string ...$already_existing_values
    ): ControlledVocabsRepository {
        return new class ($config_error, $already_existing_values) extends NullRepository {
            public array $created_vocabs = [];
            public array $created_values = [];

            public function __construct(
                protected bool $config_error,
                protected array $already_existing_values
            ) {
            }

            public function create(
                PathInterface $path_to_element,
                ?PathInterface $path_to_condition,
                ?string $condition_value,
                string $source
            ): string {
                if ($this->config_error) {
                    throw new \ilMDVocabulariesException('config error');
                }
                $new_id = 'new id ' . count($this->created_vocabs);
                $this->created_vocabs[] = [
                    'id' => $new_id,
                    'path' => $path_to_element->toString(),
                    'condition path' => $path_to_condition?->toString(),
                    'condition value' => $condition_value,
                    'source' => $source
                ];
                return $new_id;
            }

            public function findAlreadyExistingValues(
                PathInterface $path_to_element,
                string ...$values
            ): \Generator {
                yield from array_intersect($this->already_existing_values, $values);
            }

            public function addValueToVocabulary(
                string $vocab_id,
                string $value,
                ?string $label
            ): void {
                $this->created_values[] = [
                    'vocab id' => $vocab_id,
                    'value' => $value,
                    'label' => $label
                ];
            }
        };
    }

    protected function getPathFactory(): PathFactory
    {
        return new class () extends NullFactory {
            public function custom(): BuilderInterface
            {
                return new class () extends NullBuilder {
                    protected $path_string = '';

                    public function withNextStep(string $name, bool $add_as_first = false): BuilderInterface
                    {
                        $clone = clone $this;
                        $clone->path_string .= $name . ';';
                        return $clone;
                    }

                    public function get(): PathInterface
                    {
                        if (str_contains($this->path_string, 'INVALID')) {
                            throw new \ilMDPathException('path invalid');
                        }
                        return new class ($this->path_string) extends NullPath {
                            public function __construct(protected string $path_string)
                            {
                            }

                            public function toString(): string
                            {
                                return $this->path_string;
                            }
                        };
                    }
                };
            }
        };
    }

    public function testImportMalformedXMLError(): void
    {
        $repo = $this->getRepo();
        $importer = new Importer($this->getPathFactory(), $repo);

        $xml_string = 'asbduafduhsbdjfbsjfbjdbgfd532t7hubfjxd';

        $result = $importer->import($xml_string);

        $this->assertFalse($result->wasSuccessful());
        $this->assertNotEmpty($result->getErrors());
        $this->assertEmpty($repo->created_vocabs);
        $this->assertEmpty($repo->created_values);
    }

    public function testImportInvalidXMLStructureError(): void
    {
        $repo = $this->getRepo();
        $importer = new Importer($this->getPathFactory(), $repo);

        $xml_string = <<<XML
<?xml version="1.0"?>
<something>
    <else>value</else>
</something>
XML;

        $result = $importer->import($xml_string);

        $this->assertFalse($result->wasSuccessful());
        $this->assertNotEmpty($result->getErrors());
        $this->assertEmpty($repo->created_vocabs);
        $this->assertEmpty($repo->created_values);
    }

    public function testImportInvalidPathToElementError(): void
    {
        $repo = $this->getRepo();
        $importer = new Importer($this->getPathFactory(), $repo);

        $xml_string = <<<XML
<?xml version="1.0"?>
<vocabulary>
    <appliesTo>
        <pathToElement>
            <step>step1</step>
            <step>INVALID</step>
            <step>step3</step>
        </pathToElement>
    </appliesTo>
    <source>some source</source>
    <values>
        <value>value</value>
        <value>different value</value>
        <value>third value</value>
    </values>
</vocabulary>
XML;

        $result = $importer->import($xml_string);

        $this->assertFalse($result->wasSuccessful());
        $this->assertNotEmpty($result->getErrors());
        $this->assertEmpty($repo->created_vocabs);
        $this->assertEmpty($repo->created_values);
    }

    public function testImportInvalidPathToConditionError(): void
    {
        $repo = $this->getRepo();
        $importer = new Importer($this->getPathFactory(), $repo);

        $xml_string = <<<XML
<?xml version="1.0"?>
<vocabulary>
    <appliesTo>
        <pathToElement>
            <step>step1</step>
            <step>step2</step>
        </pathToElement>
        <condition value="condition value">
            <pathToElement>
                <step>step1</step>
                <step>INVALID</step>
                <step>step 3</step>
            </pathToElement>
        </condition>
    </appliesTo>
    <source>some source</source>
    <values>
        <value>value</value>
        <value>different value</value>
        <value>third value</value>
    </values>
</vocabulary>
XML;

        $result = $importer->import($xml_string);

        $this->assertFalse($result->wasSuccessful());
        $this->assertNotEmpty($result->getErrors());
        $this->assertEmpty($repo->created_vocabs);
        $this->assertEmpty($repo->created_values);
    }

    public function testImportDuplicateValuesError(): void
    {
        $repo = $this->getRepo();
        $importer = new Importer($this->getPathFactory(), $repo);

        $xml_string = <<<XML
<?xml version="1.0"?>
<vocabulary>
    <appliesTo>
        <pathToElement>
            <step>step1</step>
            <step>step2</step>
        </pathToElement>
    </appliesTo>
    <source>some source</source>
    <values>
        <value>value</value>
        <value>duplicate value</value>
        <value>duplicate value</value>
    </values>
</vocabulary>
XML;

        $result = $importer->import($xml_string);

        $this->assertFalse($result->wasSuccessful());
        $this->assertNotEmpty($result->getErrors());
        $this->assertEmpty($repo->created_vocabs);
        $this->assertEmpty($repo->created_values);
    }

    public function testImportAlreadyExistingValuesError(): void
    {
        $repo = $this->getRepo(false, 'already exists', 'also already exists');
        $importer = new Importer($this->getPathFactory(), $repo);

        $xml_string = <<<XML
<?xml version="1.0"?>
<vocabulary>
    <appliesTo>
        <pathToElement>
            <step>step1</step>
            <step>step2</step>
        </pathToElement>
    </appliesTo>
    <source>some source</source>
    <values>
        <value>already exists</value>
        <value>value</value>
        <value>also already exists</value>
    </values>
</vocabulary>
XML;

        $result = $importer->import($xml_string);

        $this->assertFalse($result->wasSuccessful());
        $this->assertNotEmpty($result->getErrors());
        $this->assertEmpty($repo->created_vocabs);
        $this->assertEmpty($repo->created_values);
    }

    public function testImportInvalidVocabConfigurationError(): void
    {
        $repo = $this->getRepo(true);
        $importer = new Importer($this->getPathFactory(), $repo);

        $xml_string = <<<XML
<?xml version="1.0"?>
<vocabulary>
    <appliesTo>
        <pathToElement>
            <step>step1</step>
            <step>step2</step>
            <step>step3</step>
        </pathToElement>
    </appliesTo>
    <source>some source</source>
    <values>
        <value>value</value>
        <value>different value</value>
        <value>third value</value>
    </values>
</vocabulary>
XML;

        $result = $importer->import($xml_string);

        $this->assertFalse($result->wasSuccessful());
        $this->assertNotEmpty($result->getErrors());
        $this->assertEmpty($repo->created_vocabs);
        $this->assertEmpty($repo->created_values);
    }

    public function testImport(): void
    {
        $repo = $this->getRepo();
        $importer = new Importer($this->getPathFactory(), $repo);

        $xml_string = <<<XML
<?xml version="1.0"?>
<vocabulary>
    <appliesTo>
        <pathToElement>
            <step>step1</step>
            <step>step2</step>
            <step>step3</step>
        </pathToElement>
    </appliesTo>
    <source>some source</source>
    <values>
        <value>value</value>
        <value>different value</value>
        <value>third value</value>
    </values>
</vocabulary>
XML;

        $result = $importer->import($xml_string);

        $this->assertTrue($result->wasSuccessful());
        $this->assertEmpty($result->getErrors());
        $this->assertSame(
            [[
                'id' => 'new id 0',
                'path' => 'step1;step2;step3;',
                'condition path' => null,
                'condition value' => null,
                'source' => 'some source',
            ]],
            $repo->created_vocabs
        );
        $this->assertSame(
            [
                ['vocab id' => 'new id 0', 'value' => 'value', 'label' => null],
                ['vocab id' => 'new id 0', 'value' => 'different value', 'label' => null],
                ['vocab id' => 'new id 0', 'value' => 'third value', 'label' => null]
            ],
            $repo->created_values
        );
    }

    public function testImportWithLabels(): void
    {
        $repo = $this->getRepo();
        $importer = new Importer($this->getPathFactory(), $repo);

        $xml_string = <<<XML
<?xml version="1.0"?>
<vocabulary>
    <appliesTo>
        <pathToElement>
            <step>step1</step>
            <step>step2</step>
            <step>step3</step>
        </pathToElement>
    </appliesTo>
    <source>some source</source>
    <values>
        <value label="label">value</value>
        <value label="different label">different value</value>
        <value label="third label">third value</value>
    </values>
</vocabulary>
XML;

        $result = $importer->import($xml_string);

        $this->assertTrue($result->wasSuccessful());
        $this->assertEmpty($result->getErrors());
        $this->assertSame(
            [[
                 'id' => 'new id 0',
                 'path' => 'step1;step2;step3;',
                 'condition path' => null,
                 'condition value' => null,
                 'source' => 'some source',
             ]],
            $repo->created_vocabs
        );
        $this->assertSame(
            [
                ['vocab id' => 'new id 0', 'value' => 'value', 'label' => 'label'],
                ['vocab id' => 'new id 0', 'value' => 'different value', 'label' => 'different label'],
                ['vocab id' => 'new id 0', 'value' => 'third value', 'label' => 'third label']
            ],
            $repo->created_values
        );
    }

    public function testImportWithCondition(): void
    {
        $repo = $this->getRepo();
        $importer = new Importer($this->getPathFactory(), $repo);

        $xml_string = <<<XML
<?xml version="1.0"?>
<vocabulary>
    <appliesTo>
        <pathToElement>
            <step>step1</step>
            <step>step2</step>
            <step>step3</step>
        </pathToElement>
         <condition value="condition value">
            <pathToElement>
                <step>condstep1</step>
                <step>condstep2</step>
            </pathToElement>
        </condition>
    </appliesTo>
    <source>some source</source>
    <values>
        <value>value</value>
        <value>different value</value>
        <value>third value</value>
    </values>
</vocabulary>
XML;

        $result = $importer->import($xml_string);

        $this->assertTrue($result->wasSuccessful());
        $this->assertEmpty($result->getErrors());
        $this->assertSame(
            [[
                 'id' => 'new id 0',
                 'path' => 'step1;step2;step3;',
                 'condition path' => 'condstep1;condstep2;',
                 'condition value' => 'condition value',
                 'source' => 'some source',
             ]],
            $repo->created_vocabs
        );
        $this->assertSame(
            [
                ['vocab id' => 'new id 0', 'value' => 'value', 'label' => null],
                ['vocab id' => 'new id 0', 'value' => 'different value', 'label' => null],
                ['vocab id' => 'new id 0', 'value' => 'third value', 'label' => null]
            ],
            $repo->created_values
        );
    }
}
