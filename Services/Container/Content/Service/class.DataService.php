<?php declare(strict_types = 1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Container\Content;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DataService
{
    public function typeBlock(string $type) : TypeBlock
    {
        return new TypeBlock($type);
    }

    public function typeBlocks() : TypeBlocks
    {
        return new TypeBlocks();
    }

    public function otherBlock() : OtherBlock
    {
        return new OtherBlock();
    }

    public function objectiveBlock() : ObjectiveBlock
    {
        return new ObjectiveBlock();
    }

    public function sessionBlock() : SessionBlock
    {
        return new SessionBlock();
    }

    public function itemGroupBlock(int $ref_id) : itemGroupBlock
    {
        return new itemGroupBlock($ref_id);
    }

    public function itemGroupBlocks() : itemGroupBlocks
    {
        return new itemGroupBlocks();
    }

    /**
     * @param BlockSequencePart[] $parts
     */
    public function blockSequence(array $parts) : BlockSequence
    {
        return new BlockSequence($parts);
    }
}
