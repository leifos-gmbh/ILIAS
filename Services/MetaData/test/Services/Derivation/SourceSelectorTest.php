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

namespace ILIAS\MetaData\Services\Derivation;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Repository\RepositoryInterface;

class SourceSelectorTest extends TestCase
{
    protected function getSourceSelector(): SourceSelector
    {
        return new class () extends SourceSelector {
            public function __construct()
            {
            }

            protected function getFromObjectDerivator(
                int $obj_id,
                int $sub_id,
                string $type
            ): FromObjectDerivatorInterface {
                return new class ($obj_id, $sub_id, $type) extends NullFromObjectDerivator {
                    public array $data;

                    public function __construct(int $obj_id, int $sub_id, string $type)
                    {
                        $this->data = [
                            'obj_id' => $obj_id,
                            'sub_id' => $sub_id,
                            'type' => $type,
                        ];
                    }
                };
            }
        };
    }

    public function testFromObject(): void
    {
        $source_selector = $this->getSourceSelector();
        $from_object_derivator = $source_selector->fromObject(7, 33, 'type');

        $this->assertSame(
            ['obj_id' => 7, 'sub_id' => 33, 'type' => 'type'],
            $from_object_derivator->data
        );
    }

    public function testFromObjectWithSubIDZero(): void
    {
        $source_selector = $this->getSourceSelector();
        $from_object_derivator = $source_selector->fromObject(67, 0, 'type');

        $this->assertSame(
            ['obj_id' => 67, 'sub_id' => 67, 'type' => 'type'],
            $from_object_derivator->data
        );
    }
}
