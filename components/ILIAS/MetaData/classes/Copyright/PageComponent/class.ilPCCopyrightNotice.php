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

use ILIAS\MetaData\Services\ServicesInterface as LOMServices;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;

class ilPCCopyrightNotice extends ilPageContent
{
    public const string BASE_NAME = 'CopyrightNotice';

    protected LOMServices $lom_services;
    protected UIRenderer $ui_renderer;
    protected Refinery $refinery;

    public function init(): void
    {
        global $DIC;

        $this->lom_services = $DIC->learningObjectMetadata();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();

        $this->setType('cpnotice');
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $node = $this->dom_doc->createElement('CopyrightNotice');
        $node = $this->getDomNode()->appendChild($node);
    }

    public function modifyPageContentPostXsl(
        string $a_output,
        string $a_mode,
        bool $a_abstract_only = false
    ): string {
        $obj_id = $this->getPage()->getRepoObjId();
        if (!$obj_id) {
            return $a_output;
        }
        $type = ilObject::_lookupType($obj_id);

        $copyright = $this->renderCopyrightNotice($obj_id, $type);
        $a_output = str_replace(
            '[[[COPYRIGHT_NOTICE]]]',
            '<div>' . $copyright . '</div>',
            $a_output
        );

        return $a_output;
    }

    public function renderCopyrightNotice(int $obj_id, string $type): string
    {
        $reader = $this->lom_services->read(
            $obj_id,
            $obj_id,
            $type,
            $this->lom_services->paths()->copyright()
        );

        $cp_helper = $this->lom_services->copyrightHelper();
        $copyright = '';
        if ($cp_helper->hasPresetCopyright($reader)) {
            return $this->ui_renderer->render(
                $cp_helper->readPresetCopyright($reader)->presentAsUIComponents()
            );
        } else {
            return nl2br($this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(
                $cp_helper->readCustomCopyright($reader)
            ));
        }
    }
}
