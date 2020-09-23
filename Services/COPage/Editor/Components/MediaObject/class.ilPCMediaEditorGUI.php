<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCMediaObjectEditorGUI implements \ILIAS\COPage\Editor\Components\PageComponentEditor
{
    /**
     * @inheritDoc
     */
    function getEditorElements(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, string $page_type, ilPageObjectGUI $page_gui, int $style_id): array {

        $form = "MEDIA FORM";

        return [
            "creation_form" => $form
        ];
    }

}