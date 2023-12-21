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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Component\Input\Container\Filter\FilterInput;

/**
 * Class DateTimeContextRenderer
 * @package ILIAS\UI\Implementation\Component\Input
 */
class DateTimeFilterContextRenderer extends FilterContextRenderer
{
    protected function wrapInFilterContext(
        FilterInput $component,
        string $input_html,
        RendererInterface $default_renderer,
        string $id_pointing_to_input = ""
    ): string {
        $tpl = $this->getTemplate("tpl.context_form.html", true, true);

        $tpl->setVariable("INPUT", $input_html);

        if ($id_pointing_to_input) {
            $tpl->setCurrentBlock("for");
            $tpl->setVariable("ID", $id_pointing_to_input);
            $tpl->parseCurrentBlock();
        }

        $label = $component->getLabel();
        $tpl->setVariable("LABEL", $label);

        return $tpl->get();
    }
}
