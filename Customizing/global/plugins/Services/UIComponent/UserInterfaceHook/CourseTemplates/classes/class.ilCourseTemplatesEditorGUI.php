<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCourseTemplatesEditorGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilCourseTemplatesEditorGUI:
 * @ilCtrl_isCalledBy ilCourseTemplatesEditorGUI: ilUIPluginRouterGUI
 */
class ilCourseTemplatesEditorGUI
{
	protected $plugin; // [ilCourseTemplatesPlugin]
	protected $templates; // [ilCourseTemplates]
	
	public function __construct()
	{		
		$this->plugin = ilPluginAdmin::getPluginObject("Services", "UIComponent", "uihk", "CourseTemplates");
		$this->templates = $this->plugin->getCourseTemplatesInstance();
	}	
	
	public function executeCommand()
	{
		global $ilCtrl, $tpl;
		
		$tpl->getStandardTemplate();
		
		$cmd = $ilCtrl->getCmd();
		
		$this->$cmd();
		
		$tpl->show();
	}
	
	
	//
	// templates
	//
	
	protected function createTemplate(ilPropertyFormGUI $a_form = null)
	{
		global $tpl;
		
		if(!$a_form)
		{
			$a_form = $this->initTemplateForm();
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	protected function initTemplateForm()
	{
		global $lng, $ilCtrl;
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "saveTemplate"));
		$form->setTitle($this->plugin->txt("create_template_form_title"));
		
		$title = new ilTextInputGUI($lng->txt("title"), "title");
		$title->setRequired(true);
		$form->addItem($title);
		
		$desc = new ilTextAreaInputGUI($lng->txt("description"), "desc");
		$form->addItem($desc);
		
		$form->addCommandButton("saveTemplate", $this->plugin->txt("create_template_action"));
		
		return $form;
	}
	
	protected function saveTemplate()
	{
		$form = $this->initTemplateForm();
		if($form->checkInput())
		{
			// :TODO:
		}
		
		$form->setValuesByPost();
		$this->createTemplate($form);
	}
		
	
	//
	// course
	//
	
	protected function createCourse(ilPropertyFormGUI $a_form = null)
	{
		global $tpl;
		
		if(!$a_form)
		{
			$a_form = $this->initCourseForm();
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	protected function initCourseForm()
	{
		global $lng, $ilCtrl;
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "saveCourse"));
		$form->setTitle($this->plugin->txt("create_course_form_title"));
		
		$tmpl = new ilSelectInputGUI($this->plugin->txt("create_course_template"), "tmpl");
		$tmpl->setRequired(true);			
		$tmpl->setOptions(array(""=>$lng->txt("please_select"))+$this->templates->getAvailableTemplates());
		$form->addItem($tmpl);
		
		// :TODO: course target
		
		$title = new ilTextInputGUI($lng->txt("title"), "title");
		$title->setRequired(true);
		$form->addItem($title);
		
		$desc = new ilTextAreaInputGUI($lng->txt("description"), "desc");
		$form->addItem($desc);
		
		$form->addCommandButton("saveCourse", $this->plugin->txt("create_course_action"));
		
		return $form;
	}
	
	protected function saveCourse()
	{
		$form = $this->initCourseForm();
		if($form->checkInput())
		{
			// :TODO:
		}
		
		$form->setValuesByPost();
		$this->createCourse($form);
	}
}

?>