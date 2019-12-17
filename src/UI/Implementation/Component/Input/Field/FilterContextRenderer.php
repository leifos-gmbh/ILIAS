<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\Container\Filter\ProxyFilterField;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Component;
use \ILIAS\UI\Implementation\Render\Template;
use ILIAS\Data\DateFormat as DateFormat;

/**
 * Class Renderer
 *
 * @package ILIAS\UI\Implementation\Component\Input
 */
class FilterContextRenderer extends AbstractComponentRenderer
{

    const DATEPICKER_MINMAX_FORMAT = 'Y/m/d';

    const DATEPICKER_FORMAT_MAPPING = [
        'd' => 'DD',
        'jS' => 'Do',
        'l' => 'dddd',
        'D' => 'dd',
        'S' => 'o',
        'W' => '',
        'm' => 'MM',
        'F' => 'MMMM',
        'M' => 'MMM',
        'Y' => 'YYYY',
        'y' => 'YY'
    ];

    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        /**
         * @var $component Input
         */
        $this->checkComponent($component);

        if ($component instanceof Component\Input\Field\Group) {
            /**
             * @var $component Group
             */
            return $this->renderFieldGroups($component, $default_renderer);
        }

        return $this->renderNoneGroupInput($component, $default_renderer);
    }


    /**
     * @param Input $input
     * @return Input|\ILIAS\UI\Implementation\Component\JavaScriptBindable
     */
    protected function setSignals(Input $input)
    {
        foreach ($input->getTriggeredSignals() as $s) {
            $signals[] = [
                "signal_id" => $s->getSignal()->getId(),
                "event" => $s->getEvent(),
                "options" => $s->getSignal()->getOptions()
            ];
        }
        if ($signals !== null) {
            $signals = json_encode($signals);


            $input = $input->withAdditionalOnLoadCode(function ($id) use ($signals) {
                $code = "il.UI.input.setSignalsForId('$id', $signals);";
                return $code;
            });

            $input = $input->withAdditionalOnLoadCode($input->getUpdateOnLoadCode());
        }
        return $input;
    }


    /**
     * @param Component\Input\Field\Input $input
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    protected function renderNoneGroupInput(Component\Input\Field\Input $input, RendererInterface $default_renderer)
    {
        $input_tpl = null;

        if ($input instanceof Component\Input\Field\Text) {
            $input_tpl = $this->getTemplate("tpl.text.html", true, true);
        } elseif ($input instanceof Component\Input\Field\Numeric) {
            $input_tpl = $this->getTemplate("tpl.numeric.html", true, true);
        } elseif ($input instanceof Component\Input\Field\Select) {
            $input_tpl = $this->getTemplate("tpl.select.html", true, true);
        } elseif ($input instanceof Component\Input\Field\MultiSelect) {
            $input_tpl = $this->getTemplate("tpl.multiselect.html", true, true);
        } elseif ($input instanceof Component\Input\Field\DateTime) {
            $input_tpl = $this->getTemplate("tpl.datetime.html", true, true);
        } else {
            throw new \LogicException("Cannot render '" . get_class($input) . "'");
        }

        return $this->renderProxyFieldWithContext($input_tpl, $input, $default_renderer);
    }


    /**
     * @param Group $group
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    protected function renderFieldGroups(Group $group, RendererInterface $default_renderer)
    {
        $inputs = "";
        $input_labels = array();
        foreach ($group->getInputs() as $input) {
            if ($input instanceof Duration) {
                $duration = $this->renderDurationInput($input, $default_renderer);
                $input_html = '';
                $inpt = array_shift($duration); //from
                $inputs .= $default_renderer->render($inpt);

                $inpt = array_shift($duration)->withAdditionalPickerconfig([ //until
                    'useCurrent' => false
                ]);
                $inputs .= $default_renderer->render($inpt);
                //$inputs .= $default_renderer->render($input_html);
            } else {
                $inputs .= $default_renderer->render($input);
                $input_labels[] = $input->getLabel();
            }
        }
        if (!$group->isDisabled()) {
            $inputs .= $this->renderAddField($input_labels, $default_renderer);
        }

        return $inputs;
    }


    /**
     * @param Template $input_tpl
     * @param Input $input
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    protected function renderProxyFieldWithContext(Template $input_tpl, Input $input, RendererInterface $default_renderer)
    {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.context_filter.html", true, true);

        if ($input->isDisabled()) {
            $remove_glyph = $f->symbol()->glyph()->remove()->withUnavailableAction();
        } else {
            $remove_glyph = $f->symbol()->glyph()->remove("")->withAdditionalOnLoadCode(function ($id) {
                $code = "$('#$id').on('click', function(event) {
							il.UI.filter.onRemoveClick(event, '$id');
							return false; // stop event propagation
					});";
                return $code;
            });
        }

        $tpl->setCurrentBlock("addon_left");
        $tpl->setVariable("LABEL", $input->getLabel());
        $tpl->parseCurrentBlock();
        $tpl->setCurrentBlock("filter_field");
        $tpl->setVariable("FILTER_FIELD", $this->renderProxyField($input_tpl, $input, $default_renderer));
        $tpl->parseCurrentBlock();
        $tpl->setCurrentBlock("addon_right");
        $tpl->setVariable("DELETE", $default_renderer->render($remove_glyph));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }


    /**
     * @param Template $input_tpl
     * @param Input $input
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    protected function renderProxyField(Template $input_tpl, Input $input, RendererInterface $default_renderer)
    {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.filter_field.html", true, true);

        $content = $this->renderInputField($input_tpl, $input);
        $popover = $f->popover()->standard($f->legacy($content))->withVerticalPosition();
        $tpl->setVariable("POPOVER", $default_renderer->render($popover));

        $prox = new ProxyFilterField();
        if (!$input->isDisabled()) {
            $prox = $prox->withOnClick($popover->getShowSignal());
            $tpl->touchBlock("tabindex");
        }

        $this->maybeRenderId($prox, $tpl);
        return $tpl->get();
    }

    /**
     * @param Template $tpl
     * @param Input $input
     * @return string
     */
    protected function renderInputField(Template $tpl, Input $input)
    {
        $id = null;
        $input = $this->setSignals($input);

        switch (true) {
            case ($input instanceof Text):
            case ($input instanceof Numeric):
                $tpl->setVariable("NAME", $input->getName());

                if ($input->getValue() !== null) {
                    $tpl->setCurrentBlock("value");
                    $tpl->setVariable("VALUE", $input->getValue());
                    $tpl->parseCurrentBlock();
                }
                if ($input->isDisabled()) {
                    $tpl->setCurrentBlock("disabled");
                    $tpl->setVariable("DISABLED", "disabled");
                    $tpl->parseCurrentBlock();
                }
                break;

            case ($input instanceof Select):
                $tpl->setVariable("NAME", $input->getName());
                $tpl = $this->renderSelectInput($tpl, $input);
                break;

            case ($input instanceof MultiSelect):
                $tpl = $this->renderMultiSelectInput($tpl, $input);
                break;
            case ($input instanceof DateTime):
                return $this->renderDateTimeInput($tpl, $input);
                break;
        }

        if ($id === null) {
            $this->maybeRenderId($input, $tpl);
        }

        return $tpl->get();
    }

    /**
     * @param Template $tpl
     * @param MultiSelect $input
     * @return Template
     */
    public function renderMultiSelectInput(Template $tpl, MultiSelect $input): Template
    {
        $value = $input->getValue();
        $name = $input->getName();

        foreach ($input->getOptions() as $opt_value => $opt_label) {
            $tpl->setCurrentBlock("option");
            $tpl->setVariable("NAME", $name);
            $tpl->setVariable("VALUE", $opt_value);
            $tpl->setVariable("LABEL", $opt_label);

            if ($value && in_array($opt_value, $value)) {
                $tpl->setVariable("CHECKED", 'checked="checked"');
            }
            if ($input->isDisabled()) {
                $tpl->setVariable("DISABLED", 'disabled="disabled"');
            }

            $tpl->parseCurrentBlock();
        }
        return $tpl;
    }

    /**
     * @param Template $tpl
     * @param Select $input
     * @return Template
     */
    public function renderSelectInput(Template $tpl, Select $input)
    {
        if ($input->isDisabled()) {
            $tpl->setCurrentBlock("disabled");
            $tpl->setVariable("DISABLED", "disabled");
            $tpl->parseCurrentBlock();
        }
        $value = $input->getValue();
        //disable first option if required.
        $tpl->setCurrentBlock("options");
        if (!$value) {
            $tpl->setVariable("SELECTED", "selected");
        }
        if ($input->isRequired()) {
            $tpl->setVariable("DISABLED_OPTION", "disabled");
            $tpl->setVariable("HIDDEN", "hidden");
        }
        $tpl->setVariable("VALUE", null);
        $tpl->setVariable("VALUE_STR", "-");
        $tpl->parseCurrentBlock();
        //rest of options.
        foreach ($input->getOptions() as $option_key => $option_value) {
            $tpl->setCurrentBlock("options");
            if ($value == $option_key) {
                $tpl->setVariable("SELECTED", "selected");
            }
            $tpl->setVariable("VALUE", $option_key);
            $tpl->setVariable("VALUE_STR", $option_value);
            $tpl->parseCurrentBlock();
        }

        return $tpl;
    }

    /**
     * Return the datetime format in a form fit for the JS-component of this input.
     * Currently, this means transforming the elements of DateFormat to momentjs.
     *
     * http://eonasdan.github.io/bootstrap-datetimepicker/Options/#format
     * http://momentjs.com/docs/#/displaying/format/
     */
    protected function getTransformedDateFormat(
        DateFormat\DateFormat $origin,
        array $mapping
    ): string
    {
        $ret = '';
        foreach ($origin->toArray() as $element) {
            if (array_key_exists($element, $mapping)) {
                $ret .= $mapping[$element];
            } else {
                $ret .= $element;
            }
        }
        return $ret;
    }

    /**
     * @param Template $tpl
     * @param DateTime $input
     *
     * @return string
     */
    protected function renderDateTimeInput(Template $tpl, DateTime $input): string
    {
        global $DIC;
        $f = $this->getUIFactory();
        $renderer = $DIC->ui()->renderer()->withAdditionalContext($input);
        if ($input->getTimeOnly() === true) {
            $cal_glyph = $f->symbol()->glyph()->time("#");
            $format = $input::TIME_FORMAT;
        } else {
            $cal_glyph = $f->symbol()->glyph()->calendar("#");

            $format = $this->getTransformedDateFormat(
                $input->getFormat(),
                self::DATEPICKER_FORMAT_MAPPING
            );

            if ($input->getUseTime() === true) {
                $format .= ' ' . $input::TIME_FORMAT;
            }
        }

        $tpl->setVariable("CALENDAR_GLYPH", $renderer->render($cal_glyph));

        $config = [
            'showClear' => true,
            'sideBySide' => true,
            'format' => $format,
        ];
        $config = array_merge($config, $input->getAdditionalPickerConfig());

        $min_date = $input->getMinValue();
        if (!is_null($min_date)) {
            $config['minDate'] = date_format($min_date, self::DATEPICKER_MINMAX_FORMAT);
        }
        $max_date = $input->getMaxValue();
        if (!is_null($max_date)) {
            $config['maxDate'] = date_format($max_date, self::DATEPICKER_MINMAX_FORMAT);
        }
        require_once("./Services/Calendar/classes/class.ilCalendarUtil.php");
        \ilCalendarUtil::initDateTimePicker();
        $input = $this->setSignals($input);
        $input = $input->withAdditionalOnLoadCode(function ($id) use ($config) {
            return '$("#' . $id . '").datetimepicker(' . json_encode($config) . ')';
        });
        $id = $this->bindJavaScript($input);
        $tpl->setVariable("ID", $id);

        $tpl->setVariable("NAME", $input->getName());
        $tpl->setVariable("PLACEHOLDER", $format);

        if ($input->getValue() !== null) {
            $tpl->setCurrentBlock("value");
            $tpl->setVariable("VALUE", $input->getValue());
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * @param Duration $input
     * @param RendererInterface $default_renderer
     * @return
     */
    protected function renderDurationInput(Duration $input, RendererInterface $default_renderer)
    {
        $tpl_duration = $this->getTemplate("tpl.duration.html", true, true);

        $input = $this->setSignals($input);
        $input = $input->withAdditionalOnLoadCode(
            function ($id) {
                return "$(document).ready(function() {
					il.UI.Input.duration.init('$id');
				});";
            }
        );
        $id = $this->bindJavaScript($input);
        $tpl_duration->setVariable("ID", $id);

        $input_html = '';
        $inputs = $input->getInputs();

        return $inputs;
    }

    /**
     * @param array $input_labels
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    protected function renderAddField(array $input_labels, RendererInterface $default_renderer)
    {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.context_filter.html", true, true);
        $add_tpl = $this->getTemplate("tpl.filter_add_list.html", true, true);

        $links = array();
        foreach ($input_labels as $label) {
            $links[] = $f->button()->shy($label, "")->withAdditionalOnLoadCode(function ($id) {
                $code = "$('#$id').on('click', function(event) {
						il.UI.filter.onAddClick(event, '$id');
						return false; // stop event propagation
				});";
                return $code;
            });
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


    /**
     * @param Component\JavascriptBindable $component
     * @param Template $tpl
     */
    protected function maybeRenderId(Component\JavascriptBindable $component, $tpl)
    {
        $id = $this->bindJavaScript($component);
        if ($id !== null) {
            $tpl->setCurrentBlock("id");
            $tpl->setVariable("ID", $id);
            $tpl->parseCurrentBlock();
        }
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Input/Container/filter.js');
        $registry->register('./src/UI/templates/js/Input/Field/input.js');
        $registry->register('./src/UI/templates/js/Input/Field/duration.js');
    }


    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
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
