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

namespace ILIAS\LearningModule\Search;

use ILIAS\Search\Presentation\Result\Subitem\PropertiesReader;
use ILIAS\DI\Container;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;
use ilObject;
use ILIAS\Search\Presentation\Result\Subitem\PropertiesFactory;
use ilCtrlInterface;
use ILIAS\Search\Presentation\Result\Subitem\Properties;
use ilLMObject;
use ilLMPresentationGUI;
use ilLMPageGUI;

class SubitemPropertiesReader implements PropertiesReader
{
    protected ilLanguage $lng;
    protected DataFactory $data_factory;
    protected ilCtrlInterface $ctrl;

    public static function type(): string
    {
        return 'lm';
    }

    public function init(Container $dic): void
    {
        $this->lng = $dic->language();
        $this->data_factory = new DataFactory();
        $this->ctrl = $dic->ctrl();
    }

    public function getSubitemProperties(
        PropertiesFactory $factory,
        int $parent_ref_id,
        string ...$subitem_ids
    ): array {
        $obj_id = ilObject::_lookupObjId($parent_ref_id);
        $item_properties = [];
        foreach ($subitem_ids as $subitem_id) {
            switch (ilLMObject::_lookupType((int) $subitem_id, $obj_id)) {
                case 'pg':
                    $item_properties[] = $this->getPropertiesForLMObject(
                        $factory,
                        $parent_ref_id,
                        $subitem_id,
                        $this->lng->txt('obj_pg')
                    );
                    break;

                case 'st':
                    $item_properties[] = $this->getPropertiesForLMObject(
                        $factory,
                        $parent_ref_id,
                        $subitem_id,
                        $this->lng->txt('obj_st')
                    );
                    break;

                default:
                    if (!ilObject::_exists((int) $subitem_id)) {
                        break;
                    }
                    $item_properties[] = $this->getPropertiesForFile($factory, $parent_ref_id, $subitem_id);
            }
        }
        return $item_properties;
    }

    protected function getPropertiesForLMObject(
        PropertiesFactory $factory,
        int $parent_ref_id,
        string $subitem_id,
        string $presentable_type
    ): Properties {
        $this->ctrl->setParameterByClass(ilLMPresentationGUI::class, 'ref_id', $parent_ref_id);
        $this->ctrl->setParameterByClass(ilLMPresentationGUI::class, 'obj_id', $subitem_id);
        $link = rtrim(ILIAS_HTTP_PATH, '/') . '/' .
            $this->ctrl->getLinkTargetByClass(ilLMPresentationGUI::class, '');
        $this->ctrl->clearParameterByClass(ilLMPresentationGUI::class, 'obj_id');
        $this->ctrl->clearParameterByClass(ilLMPresentationGUI::class, 'ref_id');
        return $factory->get(
            $subitem_id,
            ilLMObject::_lookupTitle((int) $subitem_id),
            $this->data_factory->uri($link),
            false,
            $presentable_type
        );
    }

    protected function getPropertiesForFile(
        PropertiesFactory $factory,
        int $parent_ref_id,
        string $subitem_id
    ): Properties {
        $this->ctrl->setParameterByClass(ilLMPresentationGUI::class, 'ref_id', $parent_ref_id);
        $this->ctrl->setParameterByClass(ilLMPresentationGUI::class, 'file_id', 'il__file_' . $subitem_id);
        $link = rtrim(ILIAS_HTTP_PATH, '/') . '/' .
            $this->ctrl->getLinkTargetByClass(ilLMPresentationGUI::class, 'downloadFile');
        $this->ctrl->clearParameterByClass(ilLMPresentationGUI::class, 'file_id');
        $this->ctrl->clearParameterByClass(ilLMPresentationGUI::class, 'ref_id');
        return $factory->get(
            $subitem_id,
            ilObject::_lookupTitle((int) $subitem_id),
            $this->data_factory->uri($link),
            false,
            $this->lng->txt('obj_file')
        );
    }
}
