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

use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledVocabsRepository;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;

class Importer
{
    protected const PATH_TO_SCHEMA = __DIR__ . '/../../../../VocabValidation/controlled_vocabulary.xsd';

    protected PathFactory $path_factory;
    protected ControlledVocabsRepository $vocab_repo;

    public function __construct(
        PathFactory $path_factory,
        ControlledVocabsRepository $vocab_repo
    ) {
        $this->path_factory = $path_factory;
        $this->vocab_repo = $vocab_repo;
    }

    public function import(string $xml_string): Result
    {
        $errors_or_xml = $this->loadXML($xml_string);
        if (is_array($errors_or_xml)) {
            return new Result(...$errors_or_xml);
        }
        $xml_path = new \DOMXPath($errors_or_xml);
        $errors = [];

        try {
            $path_to_element = $this->extractPathToElement($xml_path);
        } catch (\ilMDPathException $e) {
            $errors[] = $e->getMessage();
        }
        $path_to_condition = null;
        try {
            $path_to_condition = $this->extractPathToCondition($xml_path);
        } catch (\ilMDPathException $e) {
            $errors[] = $e->getMessage();
        }

        $duplicates = $this->findDuplicateValues($xml_path);
        if (!empty($duplicates)) {
            $errors[] = 'The following values are not unique: ' . implode(', ', $duplicates);
        }
        if (isset($path_to_element)) {
            $already_exist = $this->findAlreadyExistingValues($xml_path, $path_to_element);
            if (!empty($already_exist)) {
                $errors[] = 'The following values already exist in other vocabularies of the element: ' .
                    implode(', ', $already_exist);
            }
        }

        if (
            empty($errors) &&
            isset($path_to_element)
        ) {
            try {
                $vocab_id = $this->createVocabulary($xml_path, $path_to_element, $path_to_condition);
            } catch (\ilMDVocabulariesException $e) {
                $errors[] = $e->getMessage();
            }
        }
        if (empty($errors) && isset($vocab_id)) {
            $this->addValuesToVocabulary($xml_path, $vocab_id);
        }

        return new Result(...$errors);
    }

    /**
     * Returns the xml or errors
     * @return \DOMDocument|string[]
     */
    protected function loadXML(string $xml_string): \DOMDocument|array
    {
        $use_internal_errors = libxml_use_internal_errors(true);

        $xml = new \DOMDocument('1.0', 'utf-8');
        $xml->loadXML($xml_string);

        if (!$xml->schemaValidate(self::PATH_TO_SCHEMA)) {
            $errors = [];
            foreach (libxml_get_errors() as $error) {
                $errors[] = $error->message;
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($use_internal_errors);

        return empty($errors) ? $xml : $errors;
    }

    protected function extractPathToElement(\DOMXPath $xml_path): PathInterface
    {
        $node = $xml_path->query('//vocabulary/appliesTo/pathToElement')->item(0);
        return $this->writeToPath($node);
    }

    protected function extractPathToCondition(\DOMXPath $xml_path): ?PathInterface
    {
        $node = $xml_path->query('//vocabulary/appliesTo/condition/pathToElement')->item(0);
        return is_null($node) ? null : $this->writeToPath($node);
    }

    protected function extractConditionValue(\DOMXPath $xml_path): ?string
    {
        $node = $xml_path->query('//vocabulary/appliesTo/condition/@value')->item(0);
        return $node?->nodeValue;
    }

    protected function extractSource(\DOMXPath $xml_path): string
    {
        $node = $xml_path->query('//vocabulary/source')->item(0);
        return (string) $node?->nodeValue;
    }

    /**
     * Yields value => label as strings, label can be null.
     */
    protected function extractValuesAndLabels(\DOMXPath $xml_path): \Generator
    {
        $nodes = $xml_path->query('//vocabulary/values/value');
        foreach ($nodes as $node) {
            $label = $node->hasAttribute('label') ? $node->getAttribute('label') : null;
            $value = $node->nodeValue;
            yield $value => $label;
        }
    }

    /**
     * @return string[]
     */
    protected function findDuplicateValues(\DOMXPath $xml_path): array
    {
        $values = [];
        $duplicates = [];
        foreach ($this->extractValuesAndLabels($xml_path) as $value => $label) {
            if (in_array($value, $values) && !in_array($value, $duplicates)) {
                $duplicates[] = $value;
            }
            $values[] = $value;
        }

        return $duplicates;
    }

    /**
     * @return string[]
     */
    protected function findAlreadyExistingValues(
        \DOMXPath $xml_path,
        PathInterface $path_to_element
    ): array {
        $values = [];
        foreach ($this->extractValuesAndLabels($xml_path) as $value => $label) {
            $values[] = $value;
        }
        return iterator_to_array($this->vocab_repo->findAlreadyExistingValues(
            $path_to_element,
            ...$values
        ));
    }

    /**
     * Returns vocab ID
     */
    protected function createVocabulary(
        \DOMXPath $xml_path,
        PathInterface $path_to_element,
        ?PathInterface $path_to_condition
    ): string {
        return $this->vocab_repo->create(
            $path_to_element,
            $path_to_condition,
            $this->extractConditionValue($xml_path),
            $this->extractSource($xml_path)
        );
    }

    protected function addValuesToVocabulary(
        \DOMXPath $xml_path,
        string $vocab_id
    ): void {
        foreach ($this->extractValuesAndLabels($xml_path) as $value => $label) {
            $this->vocab_repo->addValueToVocabulary(
                $vocab_id,
                $value,
                $label
            );
        }
    }

    protected function writeToPath(\DOMElement $path_in_xml): PathInterface
    {
        $builder = $this->path_factory->custom();
        foreach ($path_in_xml->childNodes as $step) {
            if (!($step instanceof \DOMElement)) {
                continue;
            }
            $builder = $builder->withNextStep($step->nodeValue);
        }
        return $builder->get();
    }
}
