<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\Repository\Form;

use ILIAS\UI\Component\Input\Container\Form;
use ILIAS\UI\Component\Input\Field\FormInput;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class FormAdapterGUI
{
    protected const DEFAULT_SECTION = "@internal_default_section";
    protected const ASYNC_NONE = 0;
    protected const ASYNC_MODAL = 1;
    protected \ILIAS\Data\Factory $data;
    protected \ilObjUser $user;
    protected \ilLanguage $lng;
    protected string $last_key = "";
    protected \ILIAS\Refinery\Factory $refinery;

    protected string $title = "";


    /**
     * @var mixed|null
     */
    protected $raw_data = null;
    protected \ILIAS\HTTP\Services $http;
    protected \ilCtrlInterface $ctrl;
    protected \ILIAS\DI\UIServices $ui;
    protected array $fields = [];
    protected array $sections = [self::DEFAULT_SECTION => ["title" => "", "description" => ""]];
    protected string $current_section = self::DEFAULT_SECTION;
    protected array $section_of_field = [];
    protected $class_path;
    protected string $cmd = self::DEFAULT_SECTION;
    protected ?Form\Standard $form = null;
    protected array $upload_handler = [];
    protected int $async_mode = self::ASYNC_NONE;
    protected \ilGlobalTemplateInterface $main_tpl;
    protected ?array $current_switch = null;
    protected ?array $current_group = null;
    protected static bool $initialised = false;

    /**
     * @param string|array $class_path
     */
    public function __construct(
        $class_path,
        string $cmd
    ) {
        global $DIC;
        $this->class_path = $class_path;
        $this->cmd = $cmd;
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->lng = $DIC->language();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->data = new \ILIAS\Data\Factory();
        self::initJavascript();
    }

    public static function getOnLoadCode() : string
    {
        return "il.repository.ui.init()";
    }

    public static function initJavascript() : void
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();
        if (!self::$initialised) {
            $main_tpl = $DIC->ui()->mainTemplate();
            $main_tpl->addJavaScript("./Services/Repository/js/repository.js");
            $main_tpl->addOnLoadCode(self::getOnLoadCode());

            // render dummy components to load the necessary .js needed for async processing
            $d = [];
            $d[] = $f->input()->field()->text("");
            $r->render($d);
            self::$initialised = true;
        }
    }

    public function asyncModal() : self
    {
        $this->async_mode = self::ASYNC_MODAL;
        return $this;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function section(
        string $key,
        string $title,
        string $description = ""
    ) : self {
        if ($this->title == "") {
            $this->title = $title;
        }

        $this->sections[$key] = [
            "title" => $title,
            "description" => $description
        ];
        $this->current_section = $key;
        return $this;
    }

    public function text(
        string $key,
        string $title,
        string $description = "",
        ?string $value = null
    ) : self {
        $field = $this->ui->factory()->input()->field()->text($title, $description);
        if (!is_null($value)) {
            $field = $field->withValue($value);
        }
        $this->addField($key, $field);
        return $this;
    }

    public function textarea(
        string $key,
        string $title,
        string $description = "",
        ?string $value = null
    ) : self {
        $field = $this->ui->factory()->input()->field()->textarea($title, $description);
        if (!is_null($value)) {
            $field = $field->withValue($value);
        }
        $this->addField($key, $field);
        return $this;
    }

    public function number(
        string $key,
        string $title,
        string $description = "",
        ?int $value = null,
        ?int $min_value = null,
        ?int $max_value = null
    ) : self {
        $trans = [];
        if (!is_null($min_value)) {
            $trans[] = $this->refinery->int()->isGreaterThanOrEqual($min_value);
        }
        if (!is_null($max_value)) {
            $trans[] = $this->refinery->int()->isLessThanOrEqual($max_value);
        }
        $field = $this->ui->factory()->input()->field()->numeric($title, $description);
        if (count($trans) > 0) {
            $field = $field->withAdditionalTransformation($this->refinery->logical()->parallel($trans));
        }
        if (!is_null($value)) {
            $field = $field->withValue($value);
        }
        $this->addField($key, $field);
        return $this;
    }

    public function date(
        string $key,
        string $title,
        string $description = "",
        ?\ilDate $value = null
    ) : self {
        $field = $this->ui->factory()->input()->field()->dateTime($title, $description);

        switch ((int) $this->user->getDateFormat()) {
            case \ilCalendarSettings::DATE_FORMAT_DMY:
                $format = $this->data->dateFormat()->germanShort();
                $dt_format = "d.m.Y";
                break;
            case \ilCalendarSettings::DATE_FORMAT_MDY:
                $format = $this->data->dateFormat()->custom()->month()->slash()->day()->slash()->year();
                $dt_format = "m/d/Y";
                break;
            default:
                $format = $this->data->dateFormat()->standard();
                $dt_format = "Y-m-d";
                break;
        }

        $field = $field->withFormat($format);
        if (!is_null($value)) {
            $field = $field->withValue(
                (new \DateTime($value->get(IL_CAL_DATE)))->format($dt_format)
            );
        }
        $this->addField($key, $field);
        return $this;
    }

    public function select(
        string $key,
        string $title,
        array $options,
        string $description = "",
        ?string $value = null
    ) : self {
        $field = $this->ui->factory()->input()->field()->select($title, $options, $description);
        if (!is_null($value)) {
            $field = $field->withValue($value);
        }
        $this->addField(
            $key,
            $field
        );
        return $this;
    }

    public function radio(
        string $key,
        string $title,
        string $description = "",
        ?string $value = null
    ) : self {
        $field = $this->ui->factory()->input()->field()->radio($title, $description);
        if (!is_null($value)) {
            $field = $field->withOption($value, "");    // dummy to prevent exception, will be overwritten by radioOption
            $field = $field->withValue($value);
        }
        $this->addField(
            $key,
            $field
        );
        return $this;
    }

    public function radioOption(string $value, string $title, string $description = "") : self
    {
        if ($field = $this->getLastField()) {
            $field = $field->withOption($value, $title, $description);
            $this->replaceLastField($field);
        }
        return $this;
    }

    public function switch(
        string $key,
        string $title,
        string $description = "",
        ?string $value = null
    ) : self {
        $this->current_switch = [
            "key" => $key,
            "title" => $title,
            "description" => $description,
            "value" => $value,
            "groups" => []
        ];
        return $this;
    }

    public function group(string $key, string $title, string $description = "") : self
    {
        $this->endCurrentGroup();
        $this->current_group = [
            "key" => $key,
            "title" => $title,
            "description" => $description,
            "fields" => []
        ];
        return $this;
    }

    protected function endCurrentGroup() : void
    {
        if (!is_null($this->current_group)) {
            if (!is_null($this->current_switch)) {
                $this->current_switch["groups"][$this->current_group["key"]] =
                    $this->ui->factory()->input()->field()->group(
                        $this->current_group["fields"],
                        $this->current_group["title"]
                    )->withByline($this->current_group["description"]);
            }
        }
        $this->current_group = null;
    }

    public function end() : self
    {
        $this->endCurrentGroup();
        if (!is_null($this->current_switch)) {
            $field = $this->ui->factory()->input()->field()->switchableGroup(
                $this->current_switch["groups"],
                $this->current_switch["title"],
                $this->current_switch["description"]
            );
            if (!is_null($this->current_switch["value"])) {
                $field = $field->withValue($this->current_switch["value"]);
            }
            $this->addField($this->current_switch["key"], $field);
            $this->current_switch = null;
        }
        return $this;
    }

    public function file(
        string $key,
        string $title,
        \Closure $result_handler,
        string $id_parameter,
        int $max_files = 1,
        array $mime_types = []
    ) : self {
        $this->upload_handler[$key] = new \ilRepoStandardUploadHandlerGUI(
            $result_handler,
            $id_parameter
        );

        $field = $this->ui->factory()->input()->field()->file(
            $this->upload_handler[$key],
            $title
        )
            ->withMaxFileSize((int) \ilFileUtils::getUploadSizeLimitBytes())
            ->withMaxFiles($max_files);
        if (count($mime_types) > 0) {
            $field = $field->withAcceptedMimeTypes($mime_types);
        }

        $this->addField(
            $key,
            $field
        );
        return $this;
    }

    public function getRepoStandardUploadHandlerGUI(string $key) : \ilRepoStandardUploadHandlerGUI
    {
        if (!isset($this->upload_handler[$key])) {
            throw new \ilException("Unknown file upload field: " . $key);
        }
        return $this->upload_handler[$key];
    }


    protected function addField(string $key, FormInput $field) : void
    {
        if (isset($this->section_of_field[$key])) {
            throw new \ilException("Duplicate Input Key: " . $key);
        }
        if ($key === "") {
            throw new \ilException("Missing Input Key: " . $key);
        }
        if (!is_null($this->current_group)) {
            $this->current_group["fields"][$key] = $field;
            if (!is_null($this->current_switch)) {
                $this->section_of_field[$key] = [
                    $this->current_section,
                    $this->current_switch["key"],
                    $this->current_group["key"]
                ];
            }
        } else {
            $this->section_of_field[$key] = $this->current_section;
            $this->fields[$this->current_section][$key] = $field;
        }
        $this->last_key = $key;
        $this->form = null;
    }

    protected function getLastField() : ?FormInput
    {
        return $this->fields[$this->current_section][$this->last_key] ?? null;
    }

    protected function replaceLastField(FormInput $field) : void
    {
        if ($this->last_key !== "") {
            $this->fields[$this->current_section][$this->last_key] = $field;
        }
    }

    protected function getForm() : Form\Standard
    {
        $ctrl = $this->ctrl;

        if (is_null($this->form)) {
            $async = ($this->async_mode !== self::ASYNC_NONE);
            $action = $ctrl->getLinkTargetByClass($this->class_path, $this->cmd, "", $async);
            $inputs = [];
            foreach ($this->sections as $sec_key => $section) {
                if ($sec_key === self::DEFAULT_SECTION) {
                    if (isset($this->fields[$sec_key])) {
                        foreach ($this->fields[$sec_key] as $f_key => $field) {
                            $inputs[$f_key] = $field;
                        }
                    }
                } else {
                    if (isset($this->fields[$sec_key]) && count($this->fields[$sec_key]) > 0) {
                        $inputs[$sec_key] = $this->ui->factory()->input()->field()->section(
                            $this->fields[$sec_key],
                            $section["title"],
                            $section["description"]
                        );
                    }
                }
            }
            $this->form = $this->ui->factory()->input()->container()->form()->standard(
                $action,
                $inputs
            );
        }
        return $this->form;
    }

    public function getSubmitCaption() : string
    {
        return $this->getForm()->getSubmitCaption() ?? $this->lng->txt("save");
    }

    protected function _getData() : void
    {
        if (is_null($this->raw_data)) {
            $request = $this->http->request();
            $this->form = $this->getForm()->withRequest($request);
            $this->raw_data = $this->form->getData();
        }
    }

    public function isValid() : bool
    {
        $this->_getData();
        return !(is_null($this->raw_data));
    }

    /**
     * @return mixed
     */
    public function getData(string $key)
    {
        $this->_getData();

        if (!isset($this->section_of_field[$key])) {
            throw new \ilException("Unknown Key: " . $key);
        }

        if (!is_array($this->section_of_field[$key])) {
            $section_data = ($this->section_of_field[$key] === self::DEFAULT_SECTION)
                ? $this->raw_data
                : $this->raw_data[$this->section_of_field[$key]] ?? null;
        } else {
            $sec = $this->section_of_field[$key];
            $section_data = $this->raw_data[$sec[0]][$sec[1]][$sec[2]];
        }

        return $section_data[$key] ?? null;
    }

    public function render() : string
    {
        if ($this->async_mode === self::ASYNC_NONE && !$this->ctrl->isAsynch()) {
            $html = $this->ui->renderer()->render($this->getForm());
        } else {
            $html = $this->ui->renderer()->renderAsync($this->getForm()) . "<script>" . $this->getOnLoadCode() . "</script>";
        }
        switch ($this->async_mode) {
            case self::ASYNC_MODAL:
                $html = str_replace("<form ", "<form data-rep-form-async='modal' ", $html);
                break;
        }
        return $html;
    }
}
