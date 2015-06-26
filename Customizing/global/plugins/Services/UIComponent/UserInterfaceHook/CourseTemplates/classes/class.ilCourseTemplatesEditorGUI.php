<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCourseTemplatesEditorGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilCourseTemplatesEditorGUI: ilPropertyFormGUI
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
		
		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd();
		
		switch($next_class)
		{
			case "ilpropertyformgui":				
				include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
				$form = $this->initCourseForm();
				$ilCtrl->forwardCommand($form);
				break;
			
			default:
				$this->$cmd();
				break;
		}
				
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
			$crs = $this->createCourseInstance(
				$this->templates->getGlobalTemplateCategory(),
				$form->getInput("title"), 
				$form->getInput("desc")
			);
						
			$this->templates->setCourseTemplateStatus($crs->getId());
			
			include_once "Services/Link/classes/class.ilLink.php";
			ilUtil::redirect(ilLink::_getLink($crs->getRefId(), "crs"));
		}
		
		$form->setValuesByPost();
		$this->createTemplate($form);
	}
	
	protected function createCourseInstance($a_target_ref_id, $a_title, $a_desc)
	{
		global $ilUser;
		
		// see ilObjectGUI::saveObject()
		include_once "Modules/Course/classes/class.ilObjCourse.php";
		$crs = new ilObjCourse();
		$crs->setTitle($a_title);
		$crs->setDescription($a_desc);
		$crs->create();
		
		// see ilObjectGUI::putObjectInTree()
		$crs->createReference();
		$crs->putInTree($a_target_ref_id);
		$crs->setPermissions($a_target_ref_id);

		// see ilObjCourseGUI::afterSave()
		/* :TODO: not needed?
		$crs->getMemberObject()->add($ilUser->getId(), IL_CRS_ADMIN);
		$crs->getMemberObject()->updateNotification($ilUser->getId(), 1);
		$crs->update();
		*/
		
		return $crs;
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
		$tmpl->setOptions(
			array(""=>$lng->txt("please_select"))+
			$this->templates->getAvailableTemplates()
		);
		$form->addItem($tmpl);
		
		$this->plugin->initRepositorySelectorInput();
		$tgt = new ilJSRepositorySelectorInputGUI($lng->txt("target"), "tgt");
		$tgt->setRequired(true);
		$form->addItem($tgt);
		
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
		global $lng;
		
		$form = $this->initCourseForm();
		if($form->checkInput())
		{			
			if($form->getInput("tgt") != $this->templates->getGlobalTemplateCategory())
			{			
				$ref_id = $this->createCourseFromTemplate(
					$form->getInput("tmpl"), 
					$form->getInput("tgt"),
					$form->getInput("title"), 
					$form->getInput("desc")
				);

				include_once "Services/Link/classes/class.ilLink.php";
				ilUtil::redirect(ilLink::_getLink($ref_id));
			}
			else
			{
				$form->getItemByPostVar("tgt")->setAlert($this->plugin->txt("cannot_create_course_in_template_category"));
				ilUtil::sendFailure($lng->txt("form_input_not_valid"));
			}
		}
		
		$form->setValuesByPost();
		$this->createCourse($form);
	}
	
	protected function createCourseFromTemplate($a_template_ref_id, $a_target_ref_id, $a_title, $a_desc)
	{
		global $tree;
		
		// (source) course template
		include_once "Modules/Course/classes/class.ilObjCourse.php";
		$tmpl = new ilObjCourse($a_template_ref_id);
		
		// (target) create course from template
		$new_crs = $tmpl->cloneObject($a_target_ref_id);
		$this->templates->setCourseFromTemplateStatus($tmpl->getId(), $new_crs->getId());
		
		// adopt form title/description
		$new_crs->setTitle($a_title);
		if($a_desc)
		{
			$new_crs->setDescription($a_desc);
		}
		$new_crs->update();
	
		// copy all sub-objects
		include_once "Services/CopyWizard/classes/class.ilCopyWizardOptions.php";
		$options = array();
		foreach($tree->getSubTree($tree->getNodeData($a_template_ref_id)) as $sub_item)
		{			
			$options[$sub_item["ref_id"]] = array("type"=>ilCopyWizardOptions::COPY_WIZARD_COPY);
		}
		
		// crs into crs is possible (see "Adopt Content")
		$tmpl->cloneAllObject(
			$_COOKIE["PHPSESSID"], 
			$_COOKIE["ilClientId"],
			"crs", 
			$new_crs->getRefId(), 
			$tmpl->getRefId(), 
			$options
		);
		
		return $new_crs->getRefId();
	}
}

?>