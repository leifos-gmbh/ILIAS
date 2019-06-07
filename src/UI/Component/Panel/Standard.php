<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

/**
 * This describes a Standard Panel.
 */
interface Standard extends Panel {

	/**
	 * Add View Controls to panel
	 *
	 * @param array $view_controls Array Of ViewControls
	 * @return \ILIAS\UI\Component\Panel\Standard
	 */
	public function withViewControls(array $view_controls) : Standard;

	/**
	 * Get view controls to be shown in the header of the Secondary panel.
	 *
	 * @return array Array of ViewControls
	 */
	public function getViewControls(): ?array;

}
