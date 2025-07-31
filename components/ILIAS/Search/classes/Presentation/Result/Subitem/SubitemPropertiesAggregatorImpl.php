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

namespace ILIAS\Search\Presentation\Result\Subitem;

use ILIAS\Data\URI;
use ILIAS\Search\Setup\BuildSubitemPresentationReadersObjective;
use ILIAS\DI\Container;

class SubitemPropertiesAggregatorImpl implements SubitemPropertiesAggregator
{
    public function __construct(
        protected Container $dic
    ) {
    }

    /**
     * @var PropertiesReader[]
     */
    protected array $readers_by_type = [];

    protected function getReader(string $parent_type): ?PropertiesReader
    {
        if (isset($this->readers_by_type[$parent_type])) {
            return $this->readers_by_type[$parent_type];
        }

        $class_name = (include BuildSubitemPresentationReadersObjective::PATH())[$parent_type] ?? null;
        if ($class_name === null || !class_exists((string) $class_name)) {
            return null;
        }
        $reader = new $class_name();
        if (!$reader instanceof PropertiesReader) {
            return null;
        }
        $reader->init($this->dic);
        return $this->readers_by_type[$parent_type] = $reader;
    }

    public function getTitle(int $parent_ref_id, string $parent_type, int $id): string
    {
        return $this->getReader($parent_type)?->getSubitemTitle($parent_ref_id, $id) ?? '';
    }

    public function getLink(int $parent_ref_id, string $parent_type, int $id): ?URI
    {
        return $this->getReader($parent_type)?->getLinkToSubitem($parent_ref_id, $id) ?? null;
    }

    public function openLinkInNewViewport(int $parent_ref_id, string $parent_type, int $id): bool
    {
        return $this->getReader($parent_type)?->openLinkInNewViewport($parent_ref_id, $id) ?? false;
    }

    public function makeTypePresentable(int $parent_ref_id, string $parent_type, int $id): string
    {
        return $this->getReader($parent_type)?->getPresentableSubitemType($parent_ref_id, $id) ?? '';
    }
}
