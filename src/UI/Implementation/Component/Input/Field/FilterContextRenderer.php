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

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Input\Field as F;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Implementation\Component\Input\Container\Filter\ProxyFilterField;
use LogicException;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Container\Filter\FilterInput;

/**
 * Class FilterContextRenderer
 * @package ILIAS\UI\Implementation\Component\Input
 */
class FilterContextRenderer extends AbstractRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        /**
         * @var $component FilterInput
         */
        $this->checkComponent($component);

        if (!$component instanceof F\Group) {
            $component = $this->setSignals($component);
        }

        switch (true) {
            case ($component instanceof F\Duration):
                return $this->renderDurationField($component, $default_renderer);

            case ($component instanceof F\Group):
                return $this->renderFieldGroups($component, $default_renderer);

            case ($component instanceof F\Text):
                return $this->renderTextField($component, $default_renderer);

            case ($component instanceof F\Numeric):
                return $this->renderNumericField($component, $default_renderer);

            case ($component instanceof F\Select):
                return $this->renderSelectField($component, $default_renderer);

            case ($component instanceof F\MultiSelect):
                return $this->renderMultiSelectField($component, $default_renderer);

            case ($component instanceof F\DateTime):
                return $this->renderDateTimeField($component, $default_renderer);

            default:
                throw new LogicException("Cannot render '" . get_class($component) . "'");
        }
    }

    protected function renderFieldGroups(Group $group, RendererInterface $default_renderer): string
    {
        $inputs = "";
        $input_labels = array();
        foreach ($group->getInputs() as $input) {
            $inputs .= $default_renderer->render($input);
            $input_labels[] = $input->getLabel();
        }
        $inputs .= $this->renderAddField($input_labels, $default_renderer);

        return $inputs;
    }

    protected function renderAddField(array $input_labels, RendererInterface $default_renderer): string
    {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.context_filter.html", true, true);
        $add_tpl = $this->getTemplate("tpl.filter_add_list.html", true, true);

        $links = array();
        foreach ($input_labels as $label) {
            $links[] = $f->button()->shy($label, "")->withAdditionalOnLoadCode(fn($id) => "$('#$id').on('click', function(event) {
						il.UI.filter.onAddClick(event, '$id');
						return false; // stop event propagation
				});");
        }
        $add_tpl->setVariable("LIST", $default_renderer->render($f->listing()->unordered($links)));
        $list = $f->legacy($add_tpl->get());
        $popover = $f->popover()->standard($list)->withVerticalPosition();
        $tpl->setVariable("POPOVER", $default_renderer->render($popover));
        $add = $f->button()->bulky($f->symbol()->glyph()->add(), "", "")->withOnClick($popover->getShowSignal());

        $tpl->setCurrentBlock("filter_field");
        $tpl->setVariable("FILTER_FIELD", $default_renderer->render($add));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    protected function maybeDisable(FormInput $component, Template $tpl): void
    {
        // Filter Inputs should not be deactivatable
    }

    protected function wrapInContext(
        FormInput $component,
        string $input_html,
        RendererInterface $default_renderer,
        string $id_pointing_to_input = '',
        string $dependant_group_html = '',
        bool $bind_label_with_for = true
    ): string {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.context_filter.html", true, true);

        /**
         * @var $remove_glyph Component\Symbol\Glyph\Glyph
         */
        $remove_glyph = $f->symbol()->glyph()->remove("")->withAdditionalOnLoadCode(fn($id) => "$('#$id').on('click', function(event) {
							il.UI.filter.onRemoveClick(event, '$id');
							return false; // stop event propagation
					});");

        $tpl->setCurrentBlock("addon_left");
        $tpl->setVariable("LABEL", $component->getLabel());
        if ($id_pointing_to_input && $bind_label_with_for) {
            $tpl->setCurrentBlock("for");
            $tpl->setVariable("ID", $id_pointing_to_input);
            $tpl->parseCurrentBlock();
        }
        $tpl->parseCurrentBlock();
        $tpl->setCurrentBlock("filter_field");
        if ($component->isComplex()) {
            $tpl->setVariable("FILTER_FIELD", $this->renderProxyField($input_html, $default_renderer));
        } else {
            $tpl->setVariable("FILTER_FIELD", $input_html);
        }
        $tpl->parseCurrentBlock();
        $tpl->setCurrentBlock("addon_right");
        $tpl->setVariable("DELETE", $default_renderer->render($remove_glyph));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    protected function renderProxyField(
        string $input_html,
        RendererInterface $default_renderer
    ): string {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.filter_field.html", true, true);

        $popover = $f->popover()->standard($f->legacy($input_html))->withVerticalPosition();
        $tpl->setVariable("POPOVER", $default_renderer->render($popover));

        $prox = new ProxyFilterField();
        $prox = $prox->withOnClick($popover->getShowSignal());
        $tpl->touchBlock("tabindex");

        $this->bindJSandApplyId($prox, $tpl);
        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return [
            Component\Input\Field\Text::class,
            Component\Input\Field\Numeric::class,
            Component\Input\Field\Group::class,
            Component\Input\Field\Select::class,
            Component\Input\Field\MultiSelect::class,
            Component\Input\Field\DateTime::class,
            Component\Input\Field\Duration::class
        ];
    }
}
