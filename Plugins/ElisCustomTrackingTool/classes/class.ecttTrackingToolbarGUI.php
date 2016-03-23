<?php

require_once('Services/Form/classes/class.ilSelectInputGUI.php');
require_once('Services/Form/classes/class.ilTextInputGUI.php');
require_once('Services/Form/classes/class.ilDateTimeInputGUI.php');
require_once(PLUGIN_PATH.'/classes/class.ecttDateDurationInputGUI.php');

class ecttTrackingToolbarGUI
{
	private $tplfile = 'tpl.ecttTrackingToolbar.html';
	private $tplpath = PLUGIN_PATH;

	private $link_target = '';

	private $view_cmd = 'view';
	private $export_cmd = 'export';

	const INPUT_OPTION_ALL_VALUE = 0;

	private $viewby_switch_enabled = false;
	private $viewby_option_selected = null;
	private $viewby_options = array();

	private $numbers_switch_enabled = false;
	private $numbers_option_selected = null;
	private $numbers_options = array();

	const FILTER_OBJECT				= 'object';
	const FILTER_COM_TYPE			= 'com_type';
	const FILTER_CONTENT_SEARCH		= 'text_search';
	const FILTER_USERNAME_SEARCH	= 'user_search';
	const FILTER_CLIENT				= 'client';
	const FILTER_PERIOD				= 'period';
	

	private $filters_available = array(
		self::FILTER_OBJECT => array(
			'options' => array(), 'selected' => null
		),
		self::FILTER_COM_TYPE => array(
			'options' => array(), 'selected' => null
		),
		self::FILTER_CLIENT => array(
			'options' => array(), 'selected' => null
		),
		self::FILTER_CONTENT_SEARCH => array(
			'value' => null
		),
		self::FILTER_USERNAME_SEARCH => array(
			'value' => null
		),
		self::FILTER_PERIOD => array('selected' => null)
	);

	private $filters_enabled = array();

	public function __construct($link_target)
	{
		$this->link_target = $link_target;
	}

	/**
	 * Filters
	 */

	private function checkFilterExists($filter)
	{
		if( !isset($this->filters_available[$filter]) )
			throw new ecttException('invalid filter ('.$filter.') given');
	}

	public function enableFilter($filter)
	{
		$this->checkFilterExists($filter);

		if( !isset($this->filters_enabled[$filter]) )
		{
			$this->filters_enabled[$filter] = $filter;
		}
	}

	public function disableFilter($filter)
	{
		$this->checkFilterExists($filter);

		if( isset($this->filters_enabled[$filter]) )
		{
			unset($this->filters_enabled[$filter]);
		}
	}

	public function addFilterOption($filter, $value, $label)
	{
		$this->checkFilterExists($filter);
		$this->filters_available[$filter]['options'][$value] = $label;
	}

	public function setFilterOptionSelected($filter, $selected)
	{
		$this->filters_available[$filter]['selected'] = $selected;
		return $this;
	}

	public function setFilterSearchtext($filter, $searchtext)
	{
		$this->filters_available[$filter]['value'] = $searchtext;
	}

	private function renderFilter($filter, $tpl)
	{
		global $lng;

		$input_label = $lng->txt('ectt_filter_'.$filter.'_label');
		$input_postvar = 'filter['.$filter.']';

		switch($filter)
		{
			case self::FILTER_OBJECT:

				$input = new ilSelectInputGUI('', $input_postvar);

				$options = $this->filters_available[$filter]['options'];
				$selected = $this->filters_available[$filter]['selected'];

				$form_options = array(
					self::INPUT_OPTION_ALL_VALUE => $lng->txt('ectt_filter_option_all')
				);

				foreach($options as $value => $label)
				{
					$form_options[$value] = $label;
				}

				$input->setOptions($form_options);
				$input->setValue($selected);

				$input_field = $input->getToolbarHTML();

				$tpl->setCurrentBlock('select_input_field');
				$tpl->setVariable('SELECT_INPUT_LABEL', $input_label);
				$tpl->setVariable('SELECT_INPUT_FIELD', $input_field);
				$tpl->parseCurrentBlock();

				break;

			case self::FILTER_CONTENT_SEARCH:

				$input = new ilTextInputGUI('', $input_postvar);

				$value = $this->filters_available[$filter]['value'];
				$input->setValue($value);

				$input_field = $input->getToolbarHTML();

				$tpl->setCurrentBlock('select_input_field');
				$tpl->setVariable('SELECT_INPUT_LABEL', $input_label);
				$tpl->setVariable('SELECT_INPUT_FIELD', $input_field);
				$tpl->parseCurrentBlock();

				break;

			case self::FILTER_USERNAME_SEARCH:

				$input = new ilTextInputGUI('', $input_postvar);

				$value = $this->filters_available[$filter]['value'];
				$input->setValue($value);

				$input_field = $input->getToolbarHTML();

				$tpl->setCurrentBlock('select_input_field');
				$tpl->setVariable('SELECT_INPUT_LABEL', $input_label);
				$tpl->setVariable('SELECT_INPUT_FIELD', $input_field);
				$tpl->parseCurrentBlock();

				break;

			case self::FILTER_COM_TYPE:

				$input = new ilSelectInputGUI('', $input_postvar);

				$options = $this->filters_available[$filter]['options'];
				$selected = $this->filters_available[$filter]['selected'];

				$form_options = array(
					self::INPUT_OPTION_ALL_VALUE => $lng->txt('ectt_filter_option_all')
				);

				foreach($options as $value => $label)
				{
					$form_options[$value] = $label;
				}

				$input->setOptions($form_options);
				$input->setValue($selected);

				$input_field = $input->getToolbarHTML();

				$tpl->setCurrentBlock('select_input_field');
				$tpl->setVariable('SELECT_INPUT_LABEL', $input_label);
				$tpl->setVariable('SELECT_INPUT_FIELD', $input_field);
				$tpl->parseCurrentBlock();

				break;

			case self::FILTER_CLIENT:

				$input = new ilSelectInputGUI('', $input_postvar);

				$options = $this->filters_available[$filter]['options'];
				$selected = $this->filters_available[$filter]['selected'];

				$form_options = array(
					self::INPUT_OPTION_ALL_VALUE => $lng->txt('ectt_filter_option_all')
				);

				foreach($options as $value => $label)
				{
					$form_options[$value] = $label;
				}

				$input->setOptions($form_options);
				$input->setValue($selected);

				$input_field = $input->getToolbarHTML();

				$tpl->setCurrentBlock('select_input_field');
				$tpl->setVariable('SELECT_INPUT_LABEL', $input_label);
				$tpl->setVariable('SELECT_INPUT_FIELD', $input_field);
				$tpl->parseCurrentBlock();

				break;

			case self::FILTER_PERIOD:

				$value = $this->filters_available[$filter]['selected'];

				$input = new ecttDateDurationInputGUI('', $input_postvar);
				$input->setValue($value);

				$input_field = $input->getToolbarHTML();

				$tpl->setCurrentBlock('duration_input_field');
				$tpl->setVariable('DURATION_INPUT', $input_field);
				$tpl->parseCurrentBlock();

				$tpl->setCurrentBlock('blubb');
				$tpl->touchBlock('blubb');
				$tpl->parseCurrentBlock();

				break;

			}

	}

	/**
	 * View By Switch
	 */

	public function enableViewBySwitch()
	{
		$this->viewby_switch_enabled = true;
		return $this;
	}

	public function disableViewBySwitch()
	{
		$this->viewby_switch_enabled = false;
		return $this;
	}

	public function setViewByOptions($viewby_options)
	{
		$this->viewby_options = $viewby_options;
		return $this;
	}

	public function setViewByOptionSelected($viewby_option)
	{
		$this->viewby_option_selected = $viewby_option;
		return $this;
	}

	private function renderViewBySwitch($tpl)
	{
		if(count($this->viewby_options) && $this->viewby_option_selected === null)
		{
			throw new ilException(
				'selected viewby option has not been set yet'
			);
		}

		if(count($this->viewby_options) && !in_array($this->viewby_option_selected, $this->viewby_options) )
		{
			throw new ilException(
				'selected viewby option does not exist in viewby options'
			);
		}

		global $lng;

		$input = new ilSelectInputGUI(
			$lng->txt('ectt_view_by_switch_label'), 'viewby'
		);

		$options = array();

		foreach($this->viewby_options as $option)
		{
			$options[$option] = $lng->txt('ectt_viewby_option_'.$option);
		}

		$input->setOptions($options);
		$input->setValue($this->viewby_option_selected);

		$tpl->setCurrentBlock('select_input_field');
		$tpl->setVariable('SELECT_INPUT_LABEL', $input->getTitle());
		$tpl->setVariable('SELECT_INPUT_FIELD', $input->getToolbarHTML());
		$tpl->parseCurrentBlock();

		return $this;
	}

	/**
	 * Numbers Switch
	 */

	public function enableNumbersSwitch()
	{
		$this->numbers_switch_enabled = true;
		return $this;
	}

	public function disableNumbersSwitch()
	{
		$this->numbers_switch_enabled = false;
		return $this;
	}

	public function setNumbersOptions($numbers_options)
	{
		$this->numbers_options = $numbers_options;
		return $this;
	}

	public function setNumbersOptionSelected($numbers_option)
	{
		$this->numbers_option_selected = $numbers_option;
		return $this;
	}

	private function renderNumbersSwitch($tpl)
	{
		if($this->numbers_option_selected === null)
		{
			throw new ilException(
				'selected numbers option has not been set yet'
			);
		}

		if( !in_array($this->numbers_option_selected, $this->numbers_options) )
		{
			throw new ilException(
				'selected numbers option does not exist in viewby options'
			);
		}

		global $lng;

		$input = new ilSelectInputGUI(
			$lng->txt('ectt_numbers_switch_label'), 'numbers'
		);

		$options = array();

		foreach($this->numbers_options as $option)
		{
			$options[$option] = $lng->txt('ectt_numbers_option_'.$option);
		}

		$input->setOptions($options);
		$input->setValue($this->numbers_option_selected);

		$tpl->setCurrentBlock('select_input_field');
		$tpl->setVariable('SELECT_INPUT_LABEL', $input->getTitle());
		$tpl->setVariable('SELECT_INPUT_FIELD', $input->getToolbarHTML());
		$tpl->parseCurrentBlock();

		return $this;
	}

	public function setViewCmd($cmd)
	{
		$this->view_cmd = $cmd;
		return $this;
	}

	public function setExportCmd($cmd)
	{
		$this->export_cmd = $cmd;
		return $this;
	}

	/**
	 * Toolbar
	 */
	public function getHTML()
	{
		global $lng;

		$tpl = new ilTemplate($this->tplfile, true, true, $this->tplpath);

		foreach($this->filters_enabled as $filter)
		{
			$this->renderFilter($filter, $tpl);
		}

		if($this->viewby_switch_enabled)
		{
			$this->renderViewBySwitch($tpl);
		}

		if($this->numbers_switch_enabled)
		{
			$this->renderNumbersSwitch($tpl);
		}

		$tpl->setCurrentBlock('select_input_fields_row');
		$tpl->parseCurrentBlock();


		global $cs;

		//View-Button

		$tpl->setCurrentBlock('btn_css_class_default');
		$tpl->touchBlock('btn_css_class_default');
		$tpl->parseCurrentBlock();
		

		$tpl->setCurrentBlock('toolbar_commands');
		$label = $lng->txt('ectt_toolbar_submit_create_report_screen');
		$tpl->setVariable('SUBMIT_LABEL', $label);
		$tpl->setVariable('SUBMIT_CMD', $this->view_cmd);
		$tpl->parseCurrentBlock();

		//Export-Button

		$tpl->setCurrentBlock('btn_css_class_default');
		$tpl->touchBlock('btn_css_class_default');
		$tpl->parseCurrentBlock();
		

		$tpl->setCurrentBlock('toolbar_commands');
		$label = $lng->txt('ectt_toolbar_export');
		$tpl->setVariable('SUBMIT_LABEL', $label);
		$tpl->setVariable('SUBMIT_CMD', $this->export_cmd);
		$tpl->parseCurrentBlock();

		global $cs;

		$tpl->setCurrentBlock('toolbar');
		$tpl->setVariable('FORM_ACTION', $this->link_target);
		$tpl->setVariable('FORM_METHOD', 'post');
		$tpl->setVariable('DATA_TABLE_STYLE', $table_style);
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}

}

?>