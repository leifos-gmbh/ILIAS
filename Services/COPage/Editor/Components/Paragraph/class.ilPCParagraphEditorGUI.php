<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCParagraphEditorGUI implements \ILIAS\COPage\Editor\Components\PageComponentEditor
{
    /**
     * @inheritDoc
     */
    function getEditorElements(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, string $page_type, ilPageConfig $cfg, int $style_id): array {

        $menu = ilPageObjectGUI::getTinyMenu(
            $page_type,
            $cfg->getEnableInternalLinks(),
            $cfg->getEnableWikiLinks(),
            $cfg->getEnableKeywords(),
            $style_id,
            true,
            true,
            $cfg->getEnableAnchors(),
            true,
            $cfg->getEnableUserLinks(),
            $ui_wrapper
        );

        return [
            "menu" => $menu
        ];
    }

}