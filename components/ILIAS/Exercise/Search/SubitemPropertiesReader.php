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

namespace ILIAS\Exercise\Search;

use ILIAS\Search\Presentation\Result\Subitem\PropertiesReader;
use ILIAS\DI\Container;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;
use ILIAS\Search\Presentation\Result\Subitem\PropertiesFactory;
use ilCtrlInterface;
use Generator;
use ilExAssignment;
use ilExerciseHandlerGUI;
use ilAccess;

class SubitemPropertiesReader implements PropertiesReader
{
    protected ilLanguage $lng;
    protected DataFactory $data_factory;
    protected ilCtrlInterface $ctrl;
    protected ilAccess $access;

    public static function type(): string
    {
        return 'exc';
    }

    public function init(Container $dic): void
    {
        $this->lng = $dic->language();
        $this->lng->loadLanguageModule('exc');
        $this->data_factory = new DataFactory();
        $this->ctrl = $dic->ctrl();
        $this->access = $dic->access();
    }

    public function getSubitemProperties(
        PropertiesFactory $factory,
        int $parent_ref_id,
        string ...$subitem_ids
    ): Generator {
        foreach ($subitem_ids as $subitem_id) {
            if (!$this->isAssignmentVisible($parent_ref_id, (int) $subitem_id)) {
                continue;
            }
            $this->ctrl->setParameterByClass(ilExerciseHandlerGUI::class, 'ref_id', $parent_ref_id);
            $this->ctrl->setParameterByClass(ilExerciseHandlerGUI::class, 'ass_id', $subitem_id);
            $link = $this->data_factory->uri(rtrim(ILIAS_HTTP_PATH, '/') . '/' .
                $this->ctrl->getLinkTargetByClass([ilExerciseHandlerGUI::class, \ilObjExerciseGUI::class, \ilAssignmentPresentationGUI::class], ''));
            $this->ctrl->clearParameterByClass(ilExerciseHandlerGUI::class, 'ass_id');
            $this->ctrl->clearParameterByClass(ilExerciseHandlerGUI::class, 'ref_id');
            yield $factory->get(
                $subitem_id,
                ilExAssignment::lookupTitle((int) $subitem_id),
                $link,
                false,
                $this->lng->txt('exc_assignment')
            );
        }
    }
    protected function isAssignmentVisible(
        int $ref_id,
        int $subitem_id
    ): bool {
        if ($this->access->checkAccess('write', '', $ref_id)) {
            return true;
        }
        return ilExAssignment::lookupAssignmentOnline($subitem_id);
    }

}
