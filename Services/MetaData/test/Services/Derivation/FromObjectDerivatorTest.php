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
use ILIAS\MetaData\Repository\NullRepository;

class FromObjectDerivatorTest extends TestCase
{
    protected function getFromObjectDerivator(
        int $from_obj_id,
        int $from_sub_id,
        string $from_type
    ): FromObjectDerivatorInterface {
        $repo = new class () extends NullRepository {
            public array $copied_md = [];

            public function copyMD(
                int $from_obj_id,
                int $from_sub_id,
                string $from_type,
                int $to_obj_id,
                int $to_sub_id,
                string $to_type
            ): void {
                $this->copied_md[] = [
                    'from_obj_id' => $from_obj_id,
                    'from_sub_id' => $from_sub_id,
                    'from_type' => $from_type,
                    'to_obj_id' => $to_obj_id,
                    'to_sub_id' => $to_sub_id,
                    'to_type' => $to_type
                ];
            }
        };
        return new class ($from_obj_id, $from_sub_id, $from_type, $repo) extends FromObjectDerivator {
            public function exposeRepository(): RepositoryInterface
            {
                return $this->repository;
            }
        };

    }

    public function testForObject(): void
    {
        $from_object_derivator = $this->getFromObjectDerivator(
            9,
            39,
            'from_type'
        );
        $from_object_derivator->forObject(78, 5, 'to_type');

        $this->assertCount(1, $from_object_derivator->exposeRepository()->copied_md);
        $this->assertSame(
            [
                'from_obj_id' => 9,
                'from_sub_id' => 39,
                'from_type' => 'from_type',
                'to_obj_id' => 78,
                'to_sub_id' => 5,
                'to_type' => 'to_type'
            ],
            $from_object_derivator->exposeRepository()->copied_md[0]
        );
    }

    public function testForObjectWithSubIDZero(): void
    {
        $from_object_derivator = $this->getFromObjectDerivator(
            9,
            39,
            'from_type'
        );
        $from_object_derivator->forObject(78, 0, 'to_type');

        $this->assertCount(1, $from_object_derivator->exposeRepository()->copied_md);
        $this->assertSame(
            [
                'from_obj_id' => 9,
                'from_sub_id' => 39,
                'from_type' => 'from_type',
                'to_obj_id' => 78,
                'to_sub_id' => 78,
                'to_type' => 'to_type'
            ],
            $from_object_derivator->exposeRepository()->copied_md[0]
        );
    }

    public function testForXML(): void
    {
        // TODO test after implementation
    }
}
