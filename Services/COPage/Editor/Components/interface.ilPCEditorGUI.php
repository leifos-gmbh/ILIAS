<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Components;

use ILIAS\COPage\Editor\Server\UIWrapper;/**
 * @author Alexander Killing <killing@leifos.de>
 */
interface PageComponentEditor
{
    /**
     * Get rendered editor elements
     * @param UIWrapper $this
     * @param string       $page_type
     * @param \ilPageConfig $page_config
     * @param int          $style_id
     * @return array
     */
    function getEditorElements(UIWrapper $ui_wrapper, string $page_type, \ilPageConfig $page_config, int $style_id): array;

}