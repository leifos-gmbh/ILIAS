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

use ILIAS\AdvancedMetaData\Data\FieldDefinition\GenericData\GenericData;
use ILIAS\AdvancedMetaData\Repository\FieldDefinition\TypeSpecificData\Select\Gateway;
use ILIAS\AdvancedMetaData\Repository\FieldDefinition\TypeSpecificData\Select\DatabaseGatewayImplementation;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\SelectSpecificData;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\SelectSpecificDataImplementation;

/**
 * AMD field type select
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionSelect extends ilAdvancedMDFieldDefinition
{
    public const REMOVE_ACTION_ID = "-iladvmdrm-";

    protected array $confirm_objects = [];
    protected array $confirm_objects_values = [];
    protected ?array $confirmed_objects = null;

    protected ?array $old_options_array = null;
    protected SelectSpecificData $options;

    protected string $default_language;

    private \ilGlobalTemplateInterface $main_tpl;

    private Gateway $db_gateway;

    public function __construct(GenericData $generic_data, string $language = '')
    {
        global $DIC;

        parent::__construct($generic_data, $language);

        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->db_gateway = new DatabaseGatewayImplementation($DIC->database());

        $this->readOptions();
    }

    public function getType(): int
    {
        return self::TYPE_SELECT;
    }

    public function getSearchQueryParserValue(ilADTSearchBridge $a_adt_search): string
    {
        return (string) $a_adt_search->getADT()->getSelection();
    }

    protected function initADTDefinition(): ilADTDefinition
    {
        $def = ilADTFactory::getInstance()->getDefinitionInstanceByType("Enum");
        $def->setNumeric(false);

        $def->setOptions($this->getOptionsInLanguageAsArray($this->language));
        return $def;
    }

    protected function options(): SelectSpecificData
    {
        return $this->options;
    }

    public function getOptionsInDefaultLanguageAsArray(): ?array
    {
        $default_language_values = [];
        foreach ($this->options()->getOptions() as $option) {
            if ($translation = $option->getTranslationInLanguage($this->default_language)) {
                $default_language_values[$option->optionID()] = $translation->getValue();
            }
        }
        return $default_language_values;
    }

    protected function getOptionsInLanguageAsArray(
        string $language,
        bool $default_as_fallback = true
    ): ?array {
        $current_language_values = [];
        foreach ($this->options()->getOptions() as $option) {
            if ($translation = $option->getTranslationInLanguage($language)) {
                $current_language_values[$option->optionID()] = $translation->getValue();
            } elseif (
                $default_as_fallback &&
                $translation = $option->getTranslationInLanguage($this->default_language)
            ) {
                $current_language_values[$option->optionID()] = $translation->getValue();
            }
        }
        return $current_language_values;
    }

    protected function importFieldDefinition(array $a_def): void
    {
    }

    protected function getFieldDefinition(): array
    {
        return [];
    }

    public function getFieldDefinitionForTableGUI(string $content_language): array
    {
        if (strlen($content_language)) {
            $options = $this->getOptionsInLanguageAsArray($content_language);
        } else {
            $options = $this->getOptionsInDefaultLanguageAsArray();
        }
        return [
            $this->lng->txt("meta_advmd_select_options") => implode(",", $options)
        ];
    }

    protected function addCustomFieldToDefinitionForm(
        ilPropertyFormGUI $a_form,
        bool $a_disabled = false,
        string $language = ''
    ): void {
        if (!$this->useDefaultLanguageMode($language)) {
            $this->addCustomFieldToDefinitionFormInTranslationMode($a_form, $a_disabled, $language);
            return;
        }

        // if not the default language is chosen => no add/remove; no sorting
        $field = new ilTextInputGUI($this->lng->txt("meta_advmd_select_options"), "opts");
        $field->setRequired(true);
        $field->setMulti(true, true);
        $field->setMaxLength(255); // :TODO:
        $a_form->addItem($field);

        $options = $this->getOptionsInDefaultLanguageAsArray();
        if ($options) {
            $field->setMultiValues($options);
            $field->setValue(array_shift($options));
        }

        if ($a_disabled) {
            $field->setDisabled(true);
        }
    }

    protected function addCustomFieldToDefinitionFormInTranslationMode(
        ilPropertyFormGUI $form,
        bool $disabled,
        string $language = ''
    ): void {
        $default_language = ilAdvancedMDRecord::_getInstanceByRecordId($this->getRecordId())->getDefaultLanguage();

        $first = true;
        foreach ($this->options()->getOptions() as $option) {
            $title = '';
            if ($first) {
                $title = $this->lng->txt("meta_advmd_select_options");
            }
            $text = new ilTextInputGUI(
                $title,
                'opts__' . $language . '__' . $option->optionID()
            );

            if ($option->hasTranslationInLanguage($language)) {
                $text->setValue($option->getTranslationInLanguage($language)->getValue());
            }

            $default_value = '';
            if ($option->hasTranslationInLanguage($default_language)) {
                $default_value = $option->getTranslationInLanguage($default_language)->getValue();
            }

            $text->setInfo($default_language . ': ' . $default_value);
            $text->setMaxLength(255);
            $text->setRequired(true);

            $first = false;
            $form->addItem($text);
        }
    }

    /**
     * Process custom post values from definition form
     */
    protected function buildConfirmedObjects(ilPropertyFormGUI $a_form): ?array
    {
        // #15719
        $recipes = $a_form->getInput("conf_det");
        if (is_array($recipes[$this->getFieldId()] ?? null)) {
            $recipes = $recipes[$this->getFieldId()];
            $sum = $a_form->getInput("conf_det_act");
            $sum = $sum[$this->getFieldId()];
            $sgl = $a_form->getInput("conf");
            $sgl = $sgl[$this->getFieldId()];

            $res = array();
            foreach ($recipes as $old_option => $recipe) {
                $sum_act = $sum[$old_option];
                $sgl_act = $sgl[$old_option];

                if ($recipe == "sum") {
                    // #18885
                    if (!$sum_act) {
                        return null;
                    }

                    foreach (array_keys($sgl_act) as $obj_idx) {
                        if ($sum_act == self::REMOVE_ACTION_ID) {
                            $sum_act = "";
                        }
                        if (substr($sum_act, 0, 4) == 'idx_') {
                            $parts = explode('_', $sum_act);
                            $sum_act = $parts[1];
                        }
                        $res[$old_option][$obj_idx] = $sum_act;
                    }
                } else {
                    // #18885
                    foreach ($sgl_act as $sgl_index => $sgl_item) {
                        if (!$sgl_item) {
                            return null;
                        } elseif ($sgl_item == self::REMOVE_ACTION_ID) {
                            $sgl_act[$sgl_index] = "";
                        }
                        if (substr($sgl_item, 0, 4) == 'idx_') {
                            $parts = explode('_', $sgl_item);
                            $sgl_act[$sgl_index] = $parts[1];
                        }
                    }

                    $res[$old_option] = $sgl_act;
                }
            }
            return $res;
        }
        return null;
    }

    public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form, string $language = ''): void
    {
        $this->importNewSelectOptions(true, $a_form, $language);
    }

    protected function importNewSelectOptions(
        bool $multi,
        ilPropertyFormGUI $a_form,
        string $language = ''
    ): void {
        if (!$this->useDefaultLanguageMode($language)) {
            $this->importTranslatedFormPostValues($a_form, $language);
            return;
        }

        $search = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance(
            $this->getADTDefinition(),
            false,
            $multi
        );

        if (!strlen($language)) {
            $language = $this->default_language;
        }

        $this->old_options_array = $this->getOptionsInLanguageAsArray($language);

        $unmapped_new_values = $a_form->getInput('opts');
        $unmapped_old_options = [];
        $removed_old_options = [];

        // update position for unchanged values
        foreach ($this->options()->getOptions() as $option) {
            $old_value = $option->getTranslationInLanguage($language)->getValue();

            if (in_array($old_value, $unmapped_new_values)) {
                $new_position = array_search($old_value, $unmapped_new_values);
                $option->setPosition((int) $new_position);
                unset($unmapped_new_values[$new_position]);
                continue;
            }

            $unmapped_old_options[$option->getPosition()] = $option;
        }

        // if all leftover options have values in their old position, use those values
        if (count(array_intersect_key($unmapped_old_options, $unmapped_new_values)) === count($unmapped_old_options)) {
            foreach ($unmapped_old_options as $position => $option) {
                $option->getTranslationInLanguage($language)->setValue(
                    trim($unmapped_new_values[$position])
                );
                unset($unmapped_new_values[$position]);
                unset($unmapped_old_options[$position]);
            }
        }

        // remove leftover options
        foreach ($unmapped_old_options as $option) {
            $this->options()->removeOption($option->optionID());
            $removed_old_options[] = $option;
        }

        // create leftover values as new options
        foreach ($unmapped_new_values as $position => $value) {
            $new_option = $this->options()->addOption();
            $new_option->setPosition((int) $position);
            $new_translation = $new_option->addTranslation($language);
            $new_translation->setValue(trim($value));
        }

        if (count($removed_old_options)) {
            $this->confirmed_objects = $this->buildConfirmedObjects($a_form);
            $already_confirmed = is_array($this->confirmed_objects);

            foreach ($removed_old_options as $option) {
                $in_use = $this->findBySingleValue($search, $option->optionID());
                if (is_array($in_use)) {
                    foreach ($in_use as $item) {
                        if (!$already_confirmed) {
                            $old_option_value = $option->getTranslationInLanguage($language)?->getValue() ?? '';
                            $this->confirm_objects[$option->optionID()][] = $item;
                            $this->confirm_objects_values[$option->optionID()] = $old_option_value;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param ilADTEnumSearchBridgeMulti $a_search
     * @param                            $a_value
     * @return array
     * @todo fix $a_value type
     */
    protected function findBySingleValue(ilADTSearchBridge $a_search, $a_value): array
    {
        $res = array();
        $a_search->getADT()->setSelections((array) $a_value);
        $condition = $a_search->getSQLCondition('value_index');

        $in_use = ilADTActiveRecordByType::find(
            "adv_md_values",
            "Enum",
            $this->getFieldId(),
            $condition
        );
        if ($in_use) {
            foreach ($in_use as $item) {
                $res[] = array($item["obj_id"], $item["sub_type"], $item["sub_id"], $item["value_index"]);
            }
        }
        return $res;
    }

    protected function importTranslatedFormPostValues(ilPropertyFormGUI $form, string $language): void
    {
        foreach ($this->options()->getOptions() as $option) {
            $value = $form->getInput('opts__' . $language . '__' . $option->optionID());
            $value = trim($value);

            if ($option->hasTranslationInLanguage($language)) {
                $option->getTranslationInLanguage($language)->setValue($value);
                continue;
            }
            $new_translation = $option->addTranslation($language);
            $new_translation->setValue($value);
        }
    }

    public function importDefinitionFormPostValuesNeedsConfirmation(): bool
    {
        return is_array($this->confirm_objects) && count($this->confirm_objects) > 0;
    }
    public function prepareCustomDefinitionFormConfirmation(ilPropertyFormGUI $a_form): void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $objDefinition = $DIC['objDefinition'];

        $post_conf_det = (array) ($this->http->request()->getParsedBody()['conf_det'] ?? []);
        $post_conf = (array) ($this->http->request()->getParsedBody()['conf'] ?? []);

        $a_form->getItemByPostVar("opts")->setDisabled(true);
        if (is_array($this->confirm_objects) && count($this->confirm_objects) > 0) {
            $sec = new ilFormSectionHeaderGUI();
            $sec->setTitle($lng->txt("md_adv_confirm_definition_select_section"));
            $a_form->addItem($sec);

            foreach ($this->confirm_objects as $old_option => $items) {
                $old_option_value = $this->confirm_objects_values[$old_option];
                $details = new ilRadioGroupInputGUI(
                    $lng->txt("md_adv_confirm_definition_select_option") . ': "' . $old_option_value . '"',
                    "conf_det[" . $this->getFieldId() . "][" . $old_option . "]"
                );
                $details->setRequired(true);
                $details->setValue("sum");
                $a_form->addItem($details);

                // automatic reload does not work
                if (isset($post_conf_det[$this->getFieldId()][$old_option])) {
                    $details->setValue($post_conf_det[$this->getFieldId()][$old_option]);
                }

                $sum = new ilRadioOption($lng->txt("md_adv_confirm_definition_select_option_all"), "sum");
                $details->addOption($sum);

                $sel = new ilSelectInputGUI(
                    $lng->txt("md_adv_confirm_definition_select_option_all_action"),
                    "conf_det_act[" . $this->getFieldId() . "][" . $old_option . "]"
                );
                $sel->setRequired(true);
                $options = array(
                    "" => $lng->txt("please_select"),
                    self::REMOVE_ACTION_ID => $lng->txt("md_adv_confirm_definition_select_option_remove")
                );
                foreach ($this->options()->getOptions() as $new_option) {
                    $new_id = $new_option->optionID();
                    $new_value = $new_option->getTranslationInLanguage($this->default_language)->getValue();
                    $options['idx_' . $new_id] = $lng->txt("md_adv_confirm_definition_select_option_overwrite") . ': "' . $new_value . '"';
                }
                $sel->setOptions($options);
                $sum->addSubItem($sel);

                // automatic reload does not work
                if (isset($post_conf_det[$this->getFieldId()][$old_option])) {
                    if ($post_conf_det[$this->getFieldId()][$old_option]) {
                        $sel->setValue($post_conf_det[$this->getFieldId()][$old_option]);
                    } elseif ($post_conf_det[$this->getFieldId()][$old_option] == "sum") {
                        $sel->setAlert($lng->txt("msg_input_is_required"));
                        $this->main_tpl->setOnScreenMessage('failure', $lng->txt("form_input_not_valid"));
                    }
                }
                $single = new ilRadioOption($lng->txt("md_adv_confirm_definition_select_option_single"), "sgl");
                $details->addOption($single);
                foreach ($items as $item) {
                    $obj_id = (int) $item[0];
                    $sub_type = (string) $item[1];
                    $sub_id = (int) $item[2];

                    /*
                     * media objects are saved in adv_md_values with obj_id=0, and their actual obj_id
                     * as sub_id.
                     */
                    if ($sub_type === 'mob') {
                        $obj_id = $sub_id;
                        $sub_id = 0;
                    }

                    $item_id = $obj_id . "_" . $sub_type . "_" . $sub_id;

                    $type = ilObject::_lookupType($obj_id);
                    $type_title = $lng->txt("obj_" . $type);
                    $title = ' "' . ilObject::_lookupTitle($obj_id) . '"';

                    if ($sub_id) {
                        $class = "ilObj" . $objDefinition->getClassName($type);
                        $class_path = $objDefinition->getLocation($type);
                        $ints = class_implements($class);
                        if (isset($ints["ilAdvancedMetaDataSubItems"])) {
                            /** @noinspection PhpUndefinedMethodInspection */
                            $sub_title = $class::getAdvMDSubItemTitle($obj_id, $sub_type, $sub_id);
                            if ($sub_title) {
                                $title .= ' (' . $sub_title . ')';
                            }
                        }
                    }

                    $sel = new ilSelectInputGUI(
                        $type_title . ' ' . $title,
                        "conf[" . $this->getFieldId() . "][" . $old_option . "][" . $item_id . "]"
                    );
                    $sel->setRequired(true);
                    $options = array(
                        "" => $lng->txt("please_select"),
                        self::REMOVE_ACTION_ID => $lng->txt("md_adv_confirm_definition_select_option_remove")
                    );
                    foreach ($this->options()->getOptions() as $new_option) {
                        $new_id = $new_option->optionID();
                        $new_value = $new_option->getTranslationInLanguage($this->default_language)->getValue();
                        $options['idx_' . $new_id] = $lng->txt("md_adv_confirm_definition_select_option_overwrite") . ': "' . $new_value . '"';
                    }
                    $sel->setOptions($options);

                    // automatic reload does not work
                    if (isset($post_conf[$this->getFieldId()][$old_option][$item_id])) {
                        if ($post_conf[$this->getFieldId()][$old_option][$item_id]) {
                            $sel->setValue($post_conf[$this->getFieldId()][$old_option][$item_id]);
                        } elseif ($post_conf_det[$this->getFieldId()][$old_option] == "sgl") {
                            $sel->setAlert($lng->txt("msg_input_is_required"));
                            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("form_input_not_valid"));
                        }
                    }

                    $single->addSubItem($sel);
                }
            }
        }
    }

    public function delete(): void
    {
        $this->deleteOptions();
        parent::delete();
    }

    public function save(bool $a_keep_pos = false): void
    {
        parent::save($a_keep_pos);
        $this->saveOptions();
    }

    protected function deleteOptions(): void
    {
        $this->db_gateway->delete($this->getFieldId());
    }

    protected function updateOptions(): void
    {
        $this->db_gateway->update($this->options());
        $this->options = $this->db_gateway->readByID($this->getFieldId());
    }

    protected function saveOptions(): void
    {
        $this->db_gateway->create($this->getFieldId(), $this->options());
        $this->options = $this->db_gateway->readByID($this->getFieldId());
    }

    public function update(): void
    {
        if (is_array($this->confirmed_objects) && count($this->confirmed_objects) > 0) {
            // we need the "old" options for the search
            $def = $this->getADTDefinition();
            $def = clone($def);
            $def->setOptions($this->old_options_array);
            $search = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance($def, false, true);
            ilADTFactory::initActiveRecordByType();

            $page_list_mappings = [];

            foreach ($this->confirmed_objects as $old_option => $item_ids) {
                // get complete old values
                $old_values = [];
                foreach ($this->findBySingleValue($search, $old_option) as $item) {
                    $old_values[$item[0] . "_" . $item[1] . "_" . $item[2]] = $item[3];
                }

                foreach ($item_ids as $item => $new_option) {
                    $parts = explode("_", $item);
                    $obj_id = (int) $parts[0];
                    $sub_type = $parts[1];
                    $sub_id = (int) $parts[2];

                    // update existing value (with changed option)
                    if (isset($old_values[$item])) {
                        $old_id = $old_values[$item];

                        $primary = array(
                            "obj_id" => array("integer", $obj_id),
                            "sub_type" => array("text", $sub_type),
                            "sub_id" => array("integer", $sub_id),
                            "field_id" => array("integer", $this->getFieldId())
                        );

                        $id_old = array_merge(
                            $primary,
                            [
                                'value_index' => [ilDBConstants::T_INTEGER, $old_id]
                            ]
                        );
                        $id_new = array_merge(
                            $primary,
                            [
                                'value_index' => [ilDBConstants::T_INTEGER, $new_option]
                            ]
                        );
                        ilADTActiveRecordByType::deleteByPrimary('adv_md_values', $id_old, 'MultiEnum');

                        if (is_numeric($new_option)) {
                            ilADTActiveRecordByType::deleteByPrimary('adv_md_values', $id_new, 'MultiEnum');
                            ilADTActiveRecordByType::create('adv_md_values', $id_new, 'MultiEnum');
                        }
                    }

                    if ($sub_type == "wpg") {
                        // #15763 - adapt advmd page lists
                        $page_list_mappings[(string) $old_option] = (string) $new_option;
                    }
                }
            }

            if (!empty($page_list_mappings)) {
                ilPCAMDPageList::migrateField(
                    $this->getFieldId(),
                    $page_list_mappings
                );
            }

            $this->confirmed_objects = array();
        }

        parent::update();
        $this->updateOptions();
    }

    protected function addPropertiesToXML(ilXmlWriter $a_writer): void
    {
        foreach ($this->options()->getOptions() as $option) {
            foreach ($option->getTranslations() as $translation) {
                $a_writer->xmlElement(
                    'FieldValue',
                    ['id' => $translation->language()],
                    $translation->getValue()
                );
            }
        }
    }

    /**
     * Since the XML import only allows for a key-value pair, we also rely on
     * the order of properties to sort translations into options.
     */
    public function importXMLProperty(string $a_key, string $a_value): void
    {
        $language = $a_key;

        $associated_option = null;
        $max_position = -1;
        foreach ($this->options()->getOptions() as $option) {
            if (
                !$option->hasTranslationInLanguage($a_key) &&
                !isset($associated_option)
            ) {
                $associated_option = $option;
            }
            $max_position = max($max_position, $option->getPosition());
        }
        if (!isset($associated_option)) {
            $associated_option = $this->options()->addOption();
            $associated_option->setPosition($max_position + 1);
        }

        $new_translation = $associated_option->addTranslation($language);
        $new_translation->setValue($a_value);
    }

    public function getValueForXML(ilADT $element): string
    {
        return $element->getSelection();
    }

    public function importValueFromXML(string $a_cdata): void
    {
        $this->getADT()->setSelection($a_cdata);
    }

    public function prepareElementForEditor(ilADTFormBridge $a_bridge): void
    {
        assert($a_bridge instanceof ilADTEnumFormBridge);

        $a_bridge->setAutoSort(false);
    }

    protected function readOptions(): void
    {
        if ($this->getFieldId()) {
            $this->options = $this->db_gateway->readByID($this->getFieldId());
        }
        if (!isset($this->options)) {
            $this->options = new SelectSpecificDataImplementation();
        }

        $record = ilAdvancedMDRecord::_getInstanceByRecordId($this->getRecordId());
        $this->default_language = $record->getDefaultLanguage();
    }

    public function _clone(int $a_new_record_id): self
    {
        /** @var ilAdvancedMDFieldDefinitionSelect $obj */
        $obj = parent::_clone($a_new_record_id);
        $this->db_gateway->delete($obj->getFieldId());
        $this->db_gateway->create($obj->getFieldId(), $this->options());
        $obj->update();
        return $obj;
    }
}
