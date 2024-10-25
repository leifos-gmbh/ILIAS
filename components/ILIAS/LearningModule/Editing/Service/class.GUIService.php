<?php

declare(strict_types=1);

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

namespace ILIAS\LearningModule\Editing;

use ILIAS\LearningModule\InternalGUIService;
use ILIAS\LearningModule\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class GUIService
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui
    ) {
    }

    public function request(
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ): EditingGUIRequest {
        return new EditingGUIRequest(
            $this->gui->http(),
            $this->domain->refinery(),
            $passed_query_params,
            $passed_post_data
        );
    }

    public function subObjectTableGUI(
        int $lm_id,
        string $type,
        object $parent_gui
    ): \ILIAS\LearningModule\Table\TableAdapterGUI {
        $lng = $this->domain->lng();
        $table = new \ILIAS\LearningModule\Table\TableAdapterGUI(
            "subobj",
            ($type === "st")
                ? $lng->txt("cont_subchapters")
                : $lng->txt("cont_pages"),
            $this->domain->subObjectRetrieval(
                $lm_id,
                $type,
                $this->request()->getObjId()
            ),
            $parent_gui
        );
        $table = $table
            ->ordering("saveOrder")
            ->textColumn("title", $lng->txt("title"));

        if ($type === "st") {
            $acts = [
                [
                    "editChapter",
                    $lng->txt("edit"),
                    [\ilObjLearningModuleGUI::class, \ilStructureObjectGUI::class],
                    "view",
                    "obj_id"
                ],
                [
                    "insertChapter",
                    $lng->txt("cont_insert_chapter_after"),
                    [\ilObjLearningModuleGUI::class],
                    "insertChapter",
                    "obj_id"
                ],
            ];
        } else {
            $acts = [
                [
                    "editPage",
                    $lng->txt("edit"),
                    [\ilObjLearningModuleGUI::class, \ilLMPageObjectGUI::class],
                    "edit",
                    "obj_id"
                ]
            ];
        }
        foreach ($acts as $a) {
            $table = $table->singleAction($a[0], $a[1])
                           ->redirect($a[2], $a[3], $a[4]);
        }
        $table = $table
            ->standardAction(
                "delete",
                $lng->txt("delete")
            )
            ->standardAction(
                "cutItems",
                $lng->txt("cut")
            )
            ->standardAction(
                "copyItems",
                $lng->txt("copy")
            );
        return $table;
    }
}
