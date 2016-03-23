<?php

require_once('Services/Form/classes/class.ilDateTimeInputGUI.php');
require_once('Services/Form/classes/class.ilCheckboxInputGUI.php');

class ecttDateDurationInputGUI
{
	private $start = null;
	private $end = null;

	public function __construct($title, $postvar)
	{
		global $lng;

		$this->switch = new ilCheckboxInputGUI('', $postvar.'[active]');
		$this->switch->setOptionTitle($lng->txt('ectt_period_filter_label_use'));
		$this->switch->setValue(1);

		$now = new ilDate( time(), IL_CAL_UNIX );

		$this->start = new ilDateTimeInputGUI($title, $postvar.'[from]');
		$this->start->setDate($now);

		$this->end = new ilDateTimeInputGUI($title, $postvar.'[to]');
		$this->end->setDate($now);
	}

	public function setValue($period)
	{
		$this->switch->setChecked((int)$period['active'] );

		if($period['from'] && $period['to'])
		{
			$this->start->setDate(
				new ilDate( date('Y-m-d', $period['from']), IL_CAL_DATE )
			);

			$this->end->setDate(
				new ilDate( date('Y-m-d', $period['to']), IL_CAL_DATE )
			);
		}
	}

	private function render()
	{
		global $lng;

		$tpl = new ilTemplate(
			'tpl.ectt_date_duration_input.html', true, true, PLUGIN_PATH
		);

		$tpl->setVariable('DURATION_SWITCH_LABEL', $lng->txt('ectt_filter_period_label'));
		$tpl->setVariable('DURATION_SWITCH_FIELD', $this->switch->getTableFilterHTML());

		$tpl->setVariable('DURATION_FROM_LABEL', $lng->txt('ectt_filter_period_start'));
		$tpl->setVariable('DURATION_FROM_FIELD', $this->start->render());

		$tpl->setVariable('DURATION_TO_LABEL', $lng->txt('ectt_filter_period_end'));
		$tpl->setVariable('DURATION_TO_FIELD', $this->end->render());

		return $tpl->get();
	}

	public function getToolbarHTML()
	{
		$html = $this->render();
		return $html;
	}
}

?>