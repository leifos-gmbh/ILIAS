<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/PDFGeneration/classes/class.ilAbstractHtmlToPdfTransformerGUI.php';

/**
 * Class ilFopHtmlToPdfTransformerGUI
 */
class ilFopHtmlToPdfTransformerGUI extends ilAbstractHtmlToPdfTransformerGUI
{
	protected $is_active;

	/**
	 *
	 */
	public function populateForm()
	{
		$pdf_fop_set		= new ilSetting('pdf_transformer_fop');
		$this->is_active	= $pdf_fop_set->get('is_active');
	}

	/**
	 *
	 */
	public function saveForm()
	{
		$pdf_fop_set = new ilSetting('pdf_transformer_fop');
		$pdf_fop_set->set('is_active',	$this->is_active);
	}

	/**
	 * @return bool
	 */
	public function checkForm()
	{
		$everything_ok	= true;
		$this->is_active	= (int) $_POST['is_active'];
		return $everything_ok;
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function appendForm(ilPropertyFormGUI $form)
	{
		$form->setTitle($this->lng->txt('fop_config'));
		$active = new ilCheckboxInputGUI($this->lng->txt('is_active'), 'is_active');
		if($this->is_active == true || $this->is_active == 1)
		{
			$active->setChecked(true);
		}
		$form->addItem($active);
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function appendHiddenTransformerNameToForm(ilPropertyFormGUI $form)
	{
		$class = new ilHiddenInputGUI('transformer');
		$class->setValue('ilFopHtmlToPdfTransformer');
		$form->addItem($class);
	}

}