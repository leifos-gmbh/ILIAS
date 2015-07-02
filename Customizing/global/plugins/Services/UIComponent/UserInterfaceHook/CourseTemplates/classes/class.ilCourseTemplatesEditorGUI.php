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
		
		if(!$this->plugin->isAccessible())
		{
			return;
		}
		
		$tpl->getStandardTemplate();
		
		$tpl->setTitle($this->plugin->txt("editor_title"));
	
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
	
	protected function listTemplates()
	{
		global $ilToolbar, $ilCtrl, $tpl;
						
		$ilToolbar->addButton(
			$this->plugin->txt("create_new_template"), 
			$ilCtrl->getLinkTarget($this, "createTemplate"));
		
		if($this->templates->getAvailableTemplates())
		{
			$ilToolbar->addButton(
				$this->plugin->txt("create_new_course"), 
				$ilCtrl->getLinkTarget($this, "createCourse"));
		}
		
		$this->plugin->includeClass("class.ilCourseTemplatesTableGUI.php");
		$tbl = new ilCourseTemplatesTableGUI($this, "listTemplates", $this->plugin, $this->templates);
		$tpl->setContent($tbl->getHTML());
	}
	
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
		$form->addCommandButton("listTemplates", $lng->txt("cancel"));
		
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
	
	protected function copyTemplate()
	{
		global $rbacsystem, $ilCtrl, $tree;
		
		$src_ref_id = (int)$_GET["tid"];
		if($src_ref_id &&
			$rbacsystem->checkAccess("copy", $src_ref_id))
		{
			$new_ref_id = $this->createTemplateFromTemplate($src_ref_id);
			
			if(sizeof($tree->getSubTree($tree->getNodeData($src_ref_id))) > 1)
			{
				$ilCtrl->setParameter($this, "src_ref_id", $src_ref_id);
				$ilCtrl->setParameter($this, "tgt_ref_id", $new_ref_id);
				return $this->selectTemplateSubItems($src_ref_id);
			}
			else
			{
				include_once "Services/Link/classes/class.ilLink.php";
				ilUtil::redirect(ilLink::_getLink($new_ref_id, "crs"));
			}
		}
		
		$ilCtrl->redirect($this, "listTemplates");		
	}
	
	protected function createTemplateFromTemplate($a_template_ref_id)
	{
		global $lng;
		
		// (source) course template
		include_once "Modules/Course/classes/class.ilObjCourse.php";
		$src_tmpl = new ilObjCourse($a_template_ref_id);
		
		// (target) create template copy from template
		$new_tmpl = $src_tmpl->cloneObject($this->templates->getGlobalTemplateCategory());
		$this->templates->setCourseTemplateStatus($new_tmpl->getId());
		
		// adopt form title/description
		$new_tmpl->setTitle($new_tmpl->getTitle()." ".$lng->txt("copy_of_suffix"));		
		$new_tmpl->update();
	
		return $new_tmpl->getRefId();
	}
	
	protected function selectTemplateSubItems($a_tmpl_ref_id)
	{
		global $tpl, $lng;
		
		// see ilObjectCopyGUI::showItemSelection()
		
		ilUtil::sendInfo($lng->txt('crs_copy_threads_info'));
		include_once './Services/Object/classes/class.ilObjectCopySelectionTableGUI.php';
		
		$tpl->addJavaScript('./Services/CopyWizard/js/ilContainer.js');
		$tpl->setVariable('BODY_ATTRIBUTES','onload="ilDisableChilds(\'cmd\');"');

		$table = new ilObjectCopySelectionTableGUI($this, 'showItemSelection', 'crs', 'listTemplates');
		$table->setExternalSorting(true); // somehow necessary
		$table->parseSource($a_tmpl_ref_id);
		
		$tpl->setContent($table->getHTML());
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
		
		$this->plugin->includeClass("class.ilJSRepositorySelectorInputGUI.php");
		$tgt = new ilJSRepositorySelectorInputGUI($lng->txt("target"), "tgt");
		$tgt->setRequired(true);
		$form->addItem($tgt);
		
		$title = new ilTextInputGUI($lng->txt("title"), "title");
		$title->setRequired(true);
		$form->addItem($title);
		
		$desc = new ilTextAreaInputGUI($lng->txt("description"), "desc");
		$form->addItem($desc);
		
		$form->addCommandButton("saveCourse", $this->plugin->txt("create_course_action"));
		$form->addCommandButton("listTemplates", $lng->txt("cancel"));
		
		return $form;
	}
	
	protected function saveCourse()
	{
		global $lng, $ilAccess, $ilCtrl, $tree;
		
		$form = $this->initCourseForm();
		if($form->checkInput())
		{			
			$tgt_ref_id = $form->getInput("tgt");
			if($tgt_ref_id == $this->templates->getGlobalTemplateCategory())
			{			
				$form->getItemByPostVar("tgt")->setAlert($this->plugin->txt("cannot_create_course_in_template_category"));
				ilUtil::sendFailure($lng->txt("form_input_not_valid"));
			}
			else if(!$ilAccess->checkAccess("create_crs", "", $tgt_ref_id))
			{
				$form->getItemByPostVar("tgt")->setAlert($lng->txt("permission_denied"));
				ilUtil::sendFailure($lng->txt("form_input_not_valid"));
			}
			else
			{
				$tmpl_ref_id = $form->getInput("tmpl");
				
				$ref_id = $this->createCourseFromTemplate(
					$tmpl_ref_id, 
					$tgt_ref_id,
					$form->getInput("title"), 
					$form->getInput("desc")
				);
				
				if(sizeof($tree->getSubTree($tree->getNodeData($tmpl_ref_id))) > 1)
				{
					$ilCtrl->setParameter($this, "src_ref_id", $tmpl_ref_id);
					$ilCtrl->setParameter($this, "tgt_ref_id", $ref_id);
					return $this->selectTemplateSubItems($tmpl_ref_id);
				}
				else
				{
					include_once "Services/Link/classes/class.ilLink.php";
					ilUtil::redirect(ilLink::_getLink($ref_id, "crs"));
				}
			}
			
		}
		
		$form->setValuesByPost();
		$this->createCourse($form);
	}
	
	protected function copyContainer()
	{
		global $ilCtrl;
		
		$src_ref_id = (int)$_REQUEST["src_ref_id"];
		$tgt_ref_id = (int)$_REQUEST["tgt_ref_id"];
		$options = $_POST["cp_options"];
		
		if($src_ref_id &&
			$tgt_ref_id &&
			sizeof($options))
		{		
			include_once "Modules/Course/classes/class.ilObjCourse.php";
			$tmpl = new ilObjCourse($src_ref_id);

			// crs into crs is possible (see "Adopt Content")
			$tmpl->cloneAllObject(
				$_COOKIE["PHPSESSID"], 
				$_COOKIE["ilClientId"],
				"crs", 
				$tgt_ref_id, 
				$tmpl->getRefId(), 
				$options
			);
			
			include_once "Services/Link/classes/class.ilLink.php";
			ilUtil::redirect(ilLink::_getLink($tgt_ref_id, "crs"));
		}
		
		$ilCtrl->redirect($this, "listTemplates");		
	}
	
	protected function createCourseFromTemplate($a_template_ref_id, $a_target_ref_id, $a_title, $a_desc)
	{		
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
	
		return $new_crs->getRefId();
	}
}

?>