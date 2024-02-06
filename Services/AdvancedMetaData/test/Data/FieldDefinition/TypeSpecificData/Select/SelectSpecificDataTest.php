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

namespace ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select;

use PHPUnit\Framework\TestCase;

class SelectSpecificDataTest extends TestCase
{
    public function testGetOption(): void
    {
        $option = new OptionImplementation(9, 13);
        $data = new SelectSpecificDataImplementation(1, $option);
        $this->assertSame(
            $option,
            $data->getOption(13)
        );
    }

    public function testAddOption(): void
    {
        $data = new SelectSpecificDataImplementation(1);
        $option = $data->addOption();
        $this->assertSame(
            $option,
            $data->getOptions()->current()
        );
    }

    public function testRemoveOption(): void
    {
        $option = new OptionImplementation(9, 13);
        $data = new SelectSpecificDataImplementation(1, $option);
        $data->removeOption(13);
        $this->assertNull($data->getOption(13));
    }

    public function testContainsChangesOptionRemoved(): void
    {
        $option = new OptionImplementation(9, 13);
        $data = new SelectSpecificDataImplementation(1, $option);
        $data->removeOption(13);
        $this->assertTrue($data->containsChanges());
    }
}
