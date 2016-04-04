<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/PDFGeneration/interfaces/interface.ilHtmlToPdfTransformerGUI.php';

/**
 * Class ilHtmlToPdfTransformer
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilAbstractHtmlToPdfTransformerGUI implements ilHtmlToPdfTransformerGUI
{

	protected $lng;
	/**
	 * ilAbstractHtmlToPdfTransformerGUI constructor.
	 */
	public function __construct()
	{
		global $lng;
		$this->lng = $lng;
	}
}