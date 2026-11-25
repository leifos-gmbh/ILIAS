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

namespace ILIAS\COPage\PC\Definition\Legacy;

use ILIAS\COPage\PC\Definition\EnvironmentInterface;
use ILIAS\COPage\Editor\Components\PageComponentEditor;
use ILIAS\COPage\PC\Definition\GUI\PageContentGUIInterface;
use ILIAS\COPage\PC\Definition\GUI\GUIDefinitionInterface;
use ilLanguage;

class GUIDefinition implements GUIDefinitionInterface
{
    protected PageContentGUIInterface $gui_wrapper;

    public function __construct(
        protected EnvironmentInterface $environment,
        protected ilLanguage $lng,
        protected string $name,
        protected string $pc_type,
        protected bool $top_item
    ) {
    }

    public function presentableInsertActionLabel(): string
    {
        return $this->lng->txt('cont_ed_insert_' . $this->pc_type);
    }

    public function pageContentGUI(): PageContentGUIInterface
    {
        if (isset($this->gui_wrapper)) {
            return $this->gui_wrapper;
        }
        $class_name = 'ilPC' . $this->name . 'GUI';
        $class = new $class_name();
        return $this->gui_wrapper = new PageContentGUIWrapper($class, $this->top_item);
    }

    public function editor(): ?PageComponentEditor
    {
        $pc_class = 'ilPC' . $this->name . 'EditorGUI';
        if (class_exists($pc_class)) {
            return new $pc_class();
        }
        return null;
    }
}
