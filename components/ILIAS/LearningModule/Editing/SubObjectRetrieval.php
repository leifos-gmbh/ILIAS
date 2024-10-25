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

namespace ILIAS\LearningModule\Table;

use ILIAS\Data\Range;
use ILIAS\Data\Order;

class SubObjectRetrieval implements RetrievalInterface
{
    protected ?array $childs = null;

    public function __construct(
        protected \ilLMTree $lm_tree,
        protected $type = "",
        protected $current_node = 0
    ) {
    }

    protected function getChilds(): array
    {
        $current_node = ($this->current_node > 0)
            ? $this->current_node
            : $this->lm_tree->readRootId();
        if (is_null($this->childs)) {
            $this->childs = $this->lm_tree->getChildsByType($current_node, $this->type);
        }
        return $this->childs;
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        foreach ($this->getChilds() as $child) {
            yield [
                "id" => $child["child"],
                "title" => $child["title"]
            ];
        }
    }

    public function count(
        array $filter = [],
        array $parameters = []
    ): int {
        return count($this->getChilds());
    }
}
