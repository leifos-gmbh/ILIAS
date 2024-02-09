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

namespace ILIAS\AdvancedMetaData\Repository\TypeSpecificData\Select;

use PHPUnit\Framework\TestCase;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\OptionTranslation;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\SelectSpecificData;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\NullSelectSpecificData;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\Option;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\NullOption;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\NullOptionTranslation;

class DatabaseGatewayTest extends TestCase
{
    protected array $original_data = [
        ['field_id' => 1, 'lang_code' => 'de', 'idx' => 0, 'value' => '1val0de', 'position' => 1],
        ['field_id' => 1, 'lang_code' => 'en', 'idx' => 0, 'value' => '1val0en', 'position' => 1],
        ['field_id' => 1, 'lang_code' => 'de', 'idx' => 1, 'value' => '1val1de', 'position' => 0],
        ['field_id' => 1, 'lang_code' => 'en', 'idx' => 1, 'value' => '1val1en', 'position' => 0],
        ['field_id' => 1, 'lang_code' => 'de', 'idx' => 2, 'value' => '1val2de', 'position' => 2],
        ['field_id' => 1, 'lang_code' => 'en', 'idx' => 2, 'value' => '1val2en', 'position' => 2],
        ['field_id' => 2, 'lang_code' => 'de', 'idx' => 0, 'value' => '2val0de', 'position' => 1],
        ['field_id' => 2, 'lang_code' => 'en', 'idx' => 0, 'value' => '2val0en', 'position' => 1],
        ['field_id' => 2, 'lang_code' => 'de', 'idx' => 1, 'value' => '2val1de', 'position' => 0],
        ['field_id' => 2, 'lang_code' => 'en', 'idx' => 1, 'value' => '2val1en', 'position' => 0],
        ['field_id' => 2, 'lang_code' => 'de', 'idx' => 2, 'value' => '2val2de', 'position' => 2],
        ['field_id' => 2, 'lang_code' => 'en', 'idx' => 2, 'value' => '2val2en', 'position' => 2]
    ];

    protected function getDBGateway()
    {
        return new class ($this->original_data) extends DatabaseGatewayImplementation {
            public function __construct(protected array $data)
            {
            }

            public function exposeData(): array
            {
                return $this->data;
            }

            protected function deleteOptionsExcept(int $field_id, int ...$keep_option_ids): void
            {
                foreach ($this->data as $key => $datum) {
                    if ($datum['field_id'] !== $field_id) {
                        continue;
                    }
                    if (!in_array($datum['idx'], $keep_option_ids)) {
                        unset($this->data[$key]);
                    }
                }
            }

            protected function deleteTranslationsOfOptionExcept(
                int $field_id,
                int $option_id,
                string ...$keep_languages
            ): void {
                foreach ($this->data as $key => $datum) {
                    if ($datum['idx'] !== $option_id || $datum['field_id'] !== $field_id) {
                        continue;
                    }
                    if (!in_array($datum['lang_code'], $keep_languages)) {
                        unset($this->data[$key]);
                    }
                }
            }

            protected function createTranslation(
                int $field_id,
                int $option_id,
                int $position,
                OptionTranslation $translation
            ): void {
                $this->data[] = [
                    'field_id' => $field_id,
                    'lang_code' => $translation->language(),
                    'idx' => $option_id,
                    'value' => $translation->getValue(),
                    'position' => $position
                ];
            }

            protected function updateTranslation(
                int $field_id,
                int $option_id,
                int $position,
                OptionTranslation $translation
            ): void {
                foreach ($this->data as $key => $datum) {
                    if (
                        $datum['idx'] !== $option_id ||
                        $datum['field_id'] !== $field_id ||
                        $datum['lang_code'] !== $translation->language()
                    ) {
                        continue;
                    }
                    $this->data[$key]['value'] = $translation->getValue();
                    $this->data[$key]['position'] = $position;
                }
            }

            protected function getNextOptionIDInField(int $field_id): int
            {
                $max_id = 0;
                foreach ($this->data as $datum) {
                    if ($datum['field_id'] !== $field_id) {
                        continue;
                    }
                    $max_id = max($max_id, $datum['idx']);
                }
                return $max_id + 1;
            }
        };
    }

    protected function getData(
        ?int $field_id,
        bool $contains_changes,
        Option ...$options
    ): SelectSpecificData {
        return new class ($field_id, $contains_changes, $options) extends NullSelectSpecificData {
            public function __construct(
                protected ?int $field_id,
                protected bool $contains_changes,
                protected array $options
            ) {
            }

            public function isPersisted(): bool
            {
                return !is_null($this->field_id);
            }

            public function containsChanges(): bool
            {
                return $this->contains_changes;
            }

            public function fieldID(): ?int
            {
                return $this->field_id;
            }

            public function getOptions(): \Generator
            {
                yield from $this->options;
            }
        };
    }

    protected function getOption(
        ?int $option_id,
        bool $contains_changes,
        int $position,
        OptionTranslation ...$translations
    ): Option {
        return new class ($option_id, $position, $contains_changes, $translations) extends NullOption {
            public function __construct(
                protected ?int $option_id,
                protected int $position,
                protected bool $contains_changes,
                protected array $translations
            ) {
            }

            public function isPersisted(): bool
            {
                return !is_null($this->option_id);
            }

            public function containsChanges(): bool
            {
                return $this->contains_changes;
            }

            public function optionID(): ?int
            {
                return $this->option_id;
            }

            public function getPosition(): int
            {
                return $this->position;
            }

            public function getTranslations(): \Generator
            {
                yield from $this->translations;
            }
        };
    }

    protected function getTranslation(
        string $language,
        string $value,
        bool $is_persisted,
        bool $contains_changes
    ): OptionTranslation {
        return new class ($language, $value, $is_persisted, $contains_changes) extends NullOptionTranslation {
            public function __construct(
                protected string $language,
                protected string $value,
                protected bool $is_persisted,
                protected bool $contains_changes
            ) {
            }

            public function isPersisted(): bool
            {
                return $this->is_persisted;
            }

            public function containsChanges(): bool
            {
                return $this->contains_changes;
            }

            public function language(): string
            {
                return $this->language;
            }

            public function getValue(): string
            {
                return $this->value;
            }
        };
    }

    public function testCreate(): void
    {
        $gateway = $this->getDBGateway();
        $new_data_array = [
            ['field_id' => 3, 'lang_code' => 'de', 'idx' => 1, 'value' => '3val0de', 'position' => 1],
            ['field_id' => 3, 'lang_code' => 'en', 'idx' => 1, 'value' => '3val0en', 'position' => 1],
            ['field_id' => 3, 'lang_code' => 'de', 'idx' => 2, 'value' => '3val1de', 'position' => 0]
        ];
        $translations = [];
        foreach ($new_data_array as $item) {
            $translations[] = $this->getTranslation(
                $item['lang_code'],
                $item['value'],
                false,
                true
            );
        }
        $new_data = $this->getData(
            null,
            true,
            $this->getOption(
                null,
                true,
                $new_data_array[0]['position'],
                $translations[0],
                $translations[1]
            ),
            $this->getOption(
                null,
                true,
                $new_data_array[2]['position'],
                $translations[2]
            )
        );

        $gateway->create($new_data_array[0]['field_id'], $new_data);
        $this->assertSame(
            array_values(array_merge($this->original_data, $new_data_array)),
            array_values($gateway->exposeData())
        );
    }

    public function testUpdateRemoveOption(): void
    {
        $gateway = $this->getDBGateway();
        $new_data_array = array_slice($this->original_data, 2);
        $translations = [];
        foreach ($new_data_array as $item) {
            $translations[] = $this->getTranslation(
                $item['lang_code'],
                $item['value'],
                true,
                false
            );
        }
        $new_data = $this->getData(
            1,
            true,
            $this->getOption(
                $new_data_array[0]['idx'],
                false,
                $new_data_array[0]['position'],
                $translations[0],
                $translations[1]
            ),
            $this->getOption(
                $new_data_array[2]['idx'],
                false,
                $new_data_array[2]['position'],
                $translations[2],
                $translations[3]
            )
        );

        $gateway->update($new_data);
        $this->assertSame(
            array_values($new_data_array),
            array_values($gateway->exposeData())
        );
    }

    public function testUpdateAddOption(): void
    {
        $gateway = $this->getDBGateway();
        $added_data_array = [
            ['field_id' => 1, 'lang_code' => 'de', 'idx' => 3, 'value' => '1val3de', 'position' => 3],
            ['field_id' => 1, 'lang_code' => 'en', 'idx' => 3, 'value' => '1val3en', 'position' => 3]
        ];
        $new_data_array = array_merge($this->original_data, $added_data_array);
        $translations = [];
        foreach ($new_data_array as $item) {
            if ($item['field_id'] !== 1) {
                continue;
            }
            $translations[] = $this->getTranslation(
                $item['lang_code'],
                $item['value'],
                in_array($item, $added_data_array) ? false : true,
                in_array($item, $added_data_array) ? true : false,
            );
        }
        $new_data = $this->getData(
            1,
            true,
            $this->getOption(
                $new_data_array[0]['idx'],
                false,
                $new_data_array[0]['position'],
                $translations[0],
                $translations[1]
            ),
            $this->getOption(
                $new_data_array[2]['idx'],
                false,
                $new_data_array[2]['position'],
                $translations[2],
                $translations[3]
            ),
            $this->getOption(
                $new_data_array[4]['idx'],
                false,
                $new_data_array[4]['position'],
                $translations[4],
                $translations[5]
            ),
            $this->getOption(
                null,
                true,
                $added_data_array[0]['position'],
                $translations[6],
                $translations[7]
            )
        );

        $gateway->update($new_data);
        $this->assertSame(
            array_values($new_data_array),
            array_values($gateway->exposeData())
        );
    }

    public function testUpdateChangeOptionPosition(): void
    {
        $gateway = $this->getDBGateway();

        $new_data_array = $this->original_data;
        $new_data_array[0]['position'] = 54;
        $new_data_array[1]['position'] = 54;
        $translations = [];
        foreach ($new_data_array as $item) {
            if ($item['field_id'] !== 1) {
                continue;
            }
            $translations[] = $this->getTranslation(
                $item['lang_code'],
                $item['value'],
                true,
                false,
            );
        }
        $new_data = $this->getData(
            1,
            true,
            $this->getOption(
                $new_data_array[0]['idx'],
                true,
                $new_data_array[0]['position'],
                $translations[0],
                $translations[1]
            ),
            $this->getOption(
                $new_data_array[2]['idx'],
                false,
                $new_data_array[2]['position'],
                $translations[2],
                $translations[3]
            ),
            $this->getOption(
                $new_data_array[4]['idx'],
                false,
                $new_data_array[4]['position'],
                $translations[4],
                $translations[5]
            )
        );

        $gateway->update($new_data);
        $this->assertSame(
            array_values($new_data_array),
            array_values($gateway->exposeData())
        );
    }
}
