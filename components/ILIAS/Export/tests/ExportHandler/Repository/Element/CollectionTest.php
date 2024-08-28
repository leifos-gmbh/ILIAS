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

namespace ILIAS\Export\Test\ExportHandler\Repository\Element;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ILIAS\Export\ExportHandler\Repository\Element\Handler as ilExportHandlerRepositoryElement;
use ILIAS\Export\ExportHandler\Repository\Element\Collection as ilExportHandlerRepositoryElementCollection;

class CollectionTest extends TestCase
{
    public function testExportHandlerRepositoryElementCollection(): void
    {
        $date_time_1_mock = $this->createMock(DateTimeImmutable::class);
        $date_time_1_mock->method('getTimestamp')->willReturn(1500000);
        $date_time_2_mock = $this->createMock(DateTimeImmutable::class);
        $date_time_2_mock->method('getTimestamp')->willReturn(1000000);
        $date_time_3_mock = $this->createMock(DateTimeImmutable::class);
        $date_time_3_mock->method('getTimestamp')->willReturn(2000000);
        $date_time_4_mock = $this->createMock(DateTimeImmutable::class);
        $date_time_4_mock->method('getTimestamp')->willReturn(1700000);
        $element_1_mock = $this->createMock(ilExportHandlerRepositoryElement::class);
        $element_1_mock->method('getCreationDate')->willReturn($date_time_1_mock);
        $element_2_mock = $this->createMock(ilExportHandlerRepositoryElement::class);
        $element_2_mock->method('getCreationDate')->willReturn($date_time_2_mock);
        $element_3_mock = $this->createMock(ilExportHandlerRepositoryElement::class);
        $element_3_mock->method('getCreationDate')->willReturn($date_time_3_mock);
        $element_4_mock = $this->createMock(ilExportHandlerRepositoryElement::class);
        $element_4_mock->method('getCreationDate')->willReturn($date_time_4_mock);
        $element_array = [$element_1_mock, $element_2_mock, $element_3_mock];
        $empty_collection = new ilExportHandlerRepositoryElementCollection();
        $collection_with_3_elements = $empty_collection
            ->withElement($element_1_mock)
            ->withElement($element_2_mock)
            ->withElement($element_3_mock);
        $collection_with_4_elements = $collection_with_3_elements
            ->withElement($element_4_mock);
        $this->assertEquals($element_3_mock, $collection_with_3_elements->newest());
        $this->assertEquals($element_3_mock, $collection_with_4_elements->newest());
        $index = 0;
        $this->assertEquals(3, $collection_with_3_elements->count());
        $this->assertTrue($collection_with_3_elements->valid());
        foreach ($collection_with_3_elements as $element) {
            $this->assertEquals($element_array[$index++], $element);
        }
        $this->assertEquals(0, $empty_collection->count());
        $this->assertFalse($empty_collection->valid());
        $this->assertEquals(4, $collection_with_4_elements->count());
        $this->assertTrue($collection_with_4_elements->valid());
    }
}
