<?php

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
 
/**
 * Course template configuration user interface class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 */
class ilCourseTemplatesConfigGUI extends ilPluginConfigGUI
{
	/**
	* Handles all commmands, default is "configure"
	*/
	function performCommand($cmd)
	{
		switch ($cmd)
		{
			case "configure":
			case "save":
				$this->$cmd();
				break;

		}
	}

	/**
	 * Configure screen
	 */
	function configure()
	{
		global $tpl;

		$form = $this->initConfigurationForm();
		$tpl->setContent($form->getHTML());
	}
	
	//
	// From here on, this is just an example implementation using
	// a standard form (without saving anything)
	//
	
	/**
	 * Init configuration form.
	 *
	 * @return object form object
	 */
	public function initConfigurationForm()
	{
		global $lng, $ilCtrl, $tree;
		
		$pl = $this->getPluginObject();
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTitle($pl->txt("configuration"));
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		$ti = new ilSelectInputGUI($pl->txt("central_category"), "cat");
		$ti->setRequired(true);	
		
		$ct = $pl->getCourseTemplatesInstance();
		$options = $ct->getAvailableRepositoryCategories();
		$current = $ct->getGlobalTemplateCategory();
		
		if($current)
		{
			$ti->setValue($current);
		}
		else
		{
			$options = array(""=>$lng->txt("please_select"))+$options;
		}
		
		$ti->setOptions($options);
		$form->addItem($ti);
		
		$form->addCommandButton("save", $lng->txt("save"));	                
	
		return $form;
	}
	
	/**
	 * Save form input (currently does not save anything to db)
	 *
	 */
	public function save()
	{
		global $tpl, $ilCtrl;
	
		$pl = $this->getPluginObject();
		
		$form = $this->initConfigurationForm();
		if ($form->checkInput())
		{
			$cat_ref_id = $form->getInput("cat");
			
			$ct = $pl->getCourseTemplatesInstance();
			$ct->setGlobalTemplateCategory($cat_ref_id);
						
			ilUtil::sendSuccess($pl->txt("category_set"), true);
			$ilCtrl->redirect($this, "configure");
		}
	
		$form->setValuesByPost();
		$tpl->setContent($form->getHtml());		
	}
}

?>