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

namespace ILIAS\MediaPool\Search;

use ILIAS\Search\Presentation\Result\Subitem\PropertiesReader;
use ILIAS\DI\Container;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;
use ILIAS\Search\Presentation\Result\Subitem\PropertiesFactory;
use ILIAS\StaticURL\Services as StaticURL;
use ilMediaPoolItem;
use ilObjMediaPool;
use ilMediaPoolPresentationGUI;
use ilCtrlInterface;
use Generator;

class SubitemPropertiesReader implements PropertiesReader
{
    protected ilLanguage $lng;
    protected DataFactory $data_factory;
    protected StaticURL $static_url;
    protected ilCtrlInterface $ctrl;

    public static function type(): string
    {
        return 'mep';
    }

    public function init(Container $dic): void
    {
        $this->lng = $dic->language();
        $this->data_factory = new DataFactory();
        $this->static_url = $dic['static_url'];
        $this->ctrl = $dic->ctrl();
    }

    public function getSubitemProperties(
        PropertiesFactory $factory,
        int $parent_ref_id,
        string ...$subitem_ids
    ): Generator {
        foreach ($subitem_ids as $subitem_id) {
            $type = ilMediaPoolItem::lookupType((int) $subitem_id);
            switch ($type) {
                case 'fold':
                    $link = $this->static_url->builder()->build(
                        'mep',
                        $this->data_factory->refId($parent_ref_id),
                        [$subitem_id]
                    );
                    break;

                case 'mob':
                    $this->ctrl->setParameterByClass(ilMediaPoolPresentationGUI::class, 'ref_id', $parent_ref_id);
                    $this->ctrl->setParameterByClass(ilMediaPoolPresentationGUI::class, 'force_filter', $subitem_id);
                    $link = $this->data_factory->uri(rtrim(ILIAS_HTTP_PATH, '/') . '/' .
                        $this->ctrl->getLinkTargetByClass(ilMediaPoolPresentationGUI::class, 'allMedia'));
                    $this->ctrl->clearParameterByClass(ilMediaPoolPresentationGUI::class, 'force_filter');
                    $this->ctrl->clearParameterByClass(ilMediaPoolPresentationGUI::class, 'ref_id');
                    break;

                case 'pg':
                    $pool = new ilObjMediaPool($parent_ref_id);
                    $parent_id = $pool->getParentId((int) $subitem_id);
                    $link = $this->static_url->builder()->build(
                        'mep',
                        $this->data_factory->refId($parent_ref_id),
                        $parent_id === null ? [] : [$parent_id]
                    );
                    break;

                default:
                    continue 2;
            }
            yield $factory->get(
                $subitem_id,
                ilMediaPoolItem::lookupTitle((int) $subitem_id),
                $link,
                false,
                $this->lng->txt('obj_' . $type)
            );
        }
    }
}
