<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* BlockGUI class for Selected Items on Personal Desktop
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilPDSelectedItemsBlockGUI: ilColumnGUI
* @ilCtrl_Calls ilPDSelectedItemsBlockGUI: ilCommonActionDispatcherGUI
*/
class ilPDSelectedItemsBlockGUI extends ilBlockGUI implements ilDesktopItemHandling
{
	/** @var ilRbacSystem */
	protected $rbacsystem;

	/** @var ilSetting */
	protected $settings;

	/** @var ilObjectDefinition */
	protected $obj_definition;

	/** @var string */
	static $block_type = 'pditems';

	/** @var ilPDSelectedItemsBlockViewSettings */
	protected $viewSettings;

	/** @var ilPDSelectedItemsBlockViewGUI */
	protected $view;

	/** @var bool */
	protected $manage = false;

	/** @var string */
	protected $content = '';

	/** @var ilLanguage */
	protected $lng;

	/** @var ilCtrl */
	protected $ctrl;

	/** @var ilObjUser */
	protected $user;

	/** @var \ILIAS\DI\UIServices */
	protected $ui;
	
	/** @var \ILIAS\HTTP\GlobalHttpState */
	protected $http;

	/** @var \ilObjectService */
	protected $objectService;

	/**
	 * ilPDSelectedItemsBlockGUI constructor.
	 */
	public function __construct()
	{
		global $DIC;

		$this->rbacsystem = $DIC->rbac()->system();
		$this->settings = $DIC->settings();
		$this->obj_definition = $DIC["objDefinition"];
		$this->access = $DIC->access();
		$this->ui = $DIC->ui();
		$this->http = $DIC->http();
		$this->objectService = $DIC->object();

		parent::__construct();

		$this->lng  = $DIC->language();
		$this->ctrl = $DIC->ctrl();
		$this->user = $DIC->user();

		$this->lng->loadLanguageModule('pd');
		$this->lng->loadLanguageModule('cntr'); // #14158

		$this->setEnableNumInfo(false);
		$this->setLimit(99999);
		$this->setAvailableDetailLevels(3, 1);
		$this->allow_moving = false;

		$this->initViewSettings();
	}

	/**
	 * Evaluates the view settings of this block
	 */
	protected function initViewSettings()
	{
		$view = (int) ($this->http->request()->getQueryParams()['view'] ?? 0);

		$this->viewSettings = new ilPDSelectedItemsBlockViewSettings($this->user, $view);
		$this->viewSettings->parse();

		$this->view = ilPDSelectedItemsBlockViewGUI::bySettings($this->viewSettings);

		$this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
	}

	/**
	 * @return ilPDSelectedItemsBlockViewSettings
	 */
	public function getViewSettings() : ilPDSelectedItemsBlockViewSettings
	{
		return $this->viewSettings;
	}

	/**
	 *
	 */
	public function isManagedView() : bool
	{
		return $this->manage;
	}

	/**
	 * @inheritdoc
	 */
	public function addToDeskObject()
	{
		ilDesktopItemGUI::addToDesktop();
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->setParameterByClass('ilpersonaldesktopgui', 'view', $this->viewSettings->getCurrentView());
		$this->ctrl->redirectByClass('ilpersonaldesktopgui', 'show');
	}

	/**
	 * @inheritdoc
	 */
	public function removeFromDeskObject()
	{
		ilDesktopItemGUI::removeFromDesktop();
		ilUtil::sendSuccess($this->lng->txt("removed_from_desktop"), true);
		$this->ctrl->setParameterByClass('ilpersonaldesktopgui', 'view', $this->viewSettings->getCurrentView());
		$this->ctrl->redirectByClass('ilpersonaldesktopgui', 'show');
	}

	/**
	 * @inheritdoc
	 */
	public function getBlockType(): string 
	{
		return self::$block_type;
	}

	/**
	 * @inheritdoc
	 */
	public static function getScreenMode()
	{
		$cmd = $_GET['cmd'];
		if($cmd == 'post')
		{
			$cmd = $_POST['cmd'];
			$cmd = array_shift(array_keys($cmd));
		}

		switch($cmd)
		{
			case 'confirmRemove':
			case 'manage':
				return IL_SCREEN_FULL;

			default:
				return IL_SCREEN_SIDE;
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function isRepositoryObject(): bool 
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function getHTML()
	{
		global $DIC;

		$this->setTitle($this->view->getTitle());

		$DIC->database()->useSlave(true);

		// workaround to show details row
		$this->setData(['dummy']);

		ilObjectListGUI::prepareJSLinks('',
			$this->ctrl->getLinkTargetByClass(['ilcommonactiondispatchergui', 'ilnotegui'], '', '', true, false),
			$this->ctrl->getLinkTargetByClass(['ilcommonactiondispatchergui', 'iltagginggui'], '', '', true, false)
		);

		$DIC['ilHelp']->setDefaultScreenId(ilHelpGUI::ID_PART_SCREEN, $this->view->getScreenId());

		$this->setContent($this->getViewBlockHtml());

		if ('' === $this->getContent()) {
			$this->setEnableDetailRow(false);
		}

		$this->ctrl->clearParameters($this);

		$DIC->database()->useSlave(false);

		return parent::getHTML();
	}

	// Overwritten from ilBlockGUI as there seems to be no other possibility to
	// not show Commands in the HEADER(!!!!) of a block in the VIEW_MY_STUDYPROGRAMME
	// case... Sigh.
	public function getFooterLinks()
	{
		if($this->viewSettings->isStudyProgrammeViewActive())
		{
			return array();
		}

		return parent::getFooterLinks();
	}
	
	/**
	 * 
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();
		$cmd        = $this->ctrl->getCmd('getHTML');

		switch($next_class)
		{
			case 'ilcommonactiondispatchergui':
				include_once('Services/Object/classes/class.ilCommonActionDispatcherGUI.php');
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
				
			default:		
				if(method_exists($this, $cmd))
				{
					return $this->$cmd();
				}
				else
				{
					return $this->{$cmd . 'Object'}();
				}
		}
	}

	/**
	 * @return string
	 */
	protected function getContent() : string 
	{
		return $this->content;
	}

	/**
	 * @param string $a_content
	 */
	protected function setContent(string $a_content)
	{
		$this->content = $a_content;
	}

	/**
	 * @inheritdoc
	 */
	public function fillDataSection()
	{
		if ($this->getContent() == '') {
			$this->setDataSection($this->view->getIntroductionHtml());
		} else {
			$this->tpl->setVariable('BLOCK_ROW', $this->getContent());
		}
	}


	/**
	 * @inheritdoc
	 */
	public function fillFooter()
	{
		$this->setFooterLinks();
		$this->fillFooterLinks();
		$this->tpl->setVariable('FCOLSPAN', $this->getColSpan());
		if ($this->tpl->blockExists('block_footer')) {
			$this->tpl->setCurrentBlock('block_footer');
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	 * @return array
	 */
	protected function getViewCommandGroups() : array
	{
		$commandGroups = [];

		$sortingCommands = [];
		$sortings = $this->viewSettings->getSelectableSortingModes();
		$effectiveSorting = $this->viewSettings->getEffectiveSortingMode();
		foreach ($sortings as $sorting) {
			$this->ctrl->setParameter($this, 'sorting', $sorting);
			$sortingCommands[] = [
				'txt' => $this->lng->txt('pd_sort_by_' . $sorting),
				'url' => $this->ctrl->getLinkTarget($this, 'changePDItemSorting'),
				'asyncUrl' => $this->ctrl->getLinkTarget($this, 'changePDItemSorting', '', true),
				'active' => $sorting === $effectiveSorting,
			];
			$this->ctrl->setParameter($this, 'sorting', null);
		}

		if (count($sortingCommands) > 0) {
			$commandGroups[] = $sortingCommands;
		}

		$presentationCommands = [];
		$presentations = $this->viewSettings->getSelectablePresentationModes();
		$effectivePresentation = $this->viewSettings->getEffectivePresentationMode();
		foreach ($presentations as $presentation) {
			$this->ctrl->setParameter($this, 'presentation', $presentation);
			$presentationCommands[] = [
				'txt' => $this->lng->txt('pd_presentation_mode_' . $presentation),
				'url' => $this->ctrl->getLinkTarget($this, 'changePDItemPresentation'),
				'asyncUrl' => $this->ctrl->getLinkTarget($this, 'changePDItemPresentation', '', true),
				'active' => $presentation === $effectivePresentation,
			];
			$this->ctrl->setParameter($this, 'presentation', null);
		}

		if (count($presentationCommands) > 0) {
			$commandGroups[] = $presentationCommands;
		}

		if (!$this->viewSettings->isTilePresentation()) {
			$commandGroups[] = [
				[
					'txt' => $this->viewSettings->isSelectedItemsViewActive() ?
						$this->lng->txt('pd_remove_multiple') :
						$this->lng->txt('pd_unsubscribe_multiple_memberships'),
					'url' => $this->ctrl->getLinkTarget($this, 'manage'),
					'asyncUrl' => null,
					'active' => false,
				]
			];
		}

		return $commandGroups;
	}
		

	/**
	 *
	 */
	protected function setFooterLinks()
	{
		if ('' === $this->getContent()) {
			$this->setEnableNumInfo(false);
			return '';
		}

		if ($this->manage) {
			return '';
		}

		$commandGroups = $this->getViewCommandGroups();
		foreach ($commandGroups as $group) {
			foreach ($group as $command) {
				$this->addFooterLink(
					$command['txt'],
					$command['url'],
					$command['asyncUrl'],
					'block_' . $this->getBlockType() . '_' . $this->block_id,
					false,
					false,
					$command['active']
				);
			}
		}
	}

	/**
	 * @param ilTemplate $tpl
	 * @param ilPDSelectedItemsBlockGroup[] $grouped_items
	 * @param bool $show_header
	 * @return bool
	 */
	protected function renderGroupedItems(ilTemplate $tpl, array $grouped_items, $show_header = false)
	{
		if(0 == count($grouped_items))
		{
			return false;
		}

		if ($this->viewSettings->isTilePresentation()) {
			$tpl->touchBlock('row_type_2');

			$tpl->setCurrentBlock("container_standard_row");
			$tpl->setVariable("BLOCK_ROW_CONTENT", $this->getTileHTML());
			$tpl->parseCurrentBlock();

			$tpl->touchBlock("container_row");
			return true;
		}

		$output = false;

		require_once 'Services/Object/classes/class.ilObjectActivation.php';
		require_once 'Services/PersonalDesktop/ItemsBlock/classes/class.ilPDSelectedItemsBlockListGUIFactory.php';
		$list_factory = new ilPDSelectedItemsBlockListGUIFactory($this);

		foreach($grouped_items as $group)
		{
			$item_html = array();

			foreach($group->getItems() as $item)
			{
				try
				{
					$item_list_gui = $list_factory->byType($item['type']);
					ilObjectActivation::addListGUIActivationProperty($item_list_gui, $item);

					// #15232
					if($this->manage)
					{
						if($this->view->mayRemoveItem((int)$item['ref_id']))
						{
							$item_list_gui->enableCheckbox(true);
						}
						else
						{
							$item_list_gui->enableCheckbox(false);
						}
					}

					$html = $item_list_gui->getListItemHTML($item['ref_id'], $item['obj_id'], $item['title'], $item['description']);
					if($html != '')
					{
						$item_html[] = array(
							'html'                 => $html,
							'item_ref_id'          => $item['ref_id'],
							'item_obj_id'          => $item['obj_id'],
							'parent_ref'           => $item['parent_ref'],
							'type'                 => $item['type'],
							'item_icon_image_type' => $item_list_gui->getIconImageType()
						);
					}
				}
				catch(ilException $e)
				{
					continue;
				}
			}

			if(0 == count($item_html))
			{
				continue;
			}

			if($show_header)
			{
				$this->addSectionHeader($tpl, $group);
				$this->resetRowType() ;
			}

			foreach($item_html as $item)
			{
				$this->addStandardRow(
					$tpl,$item['html'], $item['item_ref_id'],$item['item_obj_id'],
					$item['item_icon_image_type'],
					'th_' . md5($group->getLabel())
				);
				$output = true;
			}
		}

		return $output;
	}

	/**
	* get selected item block
	*/
	protected function getViewBlockHtml()
	{
		$tpl = $this->newBlockTemplate();

		$this->renderGroupedItems(
			$tpl, $this->view->getItemGroups(),
			($this->getCurrentDetailLevel() >= $this->view->getMinimumDetailLevelForSection())
		);

		if (!$this->viewSettings->isTilePresentation() && $this->manage && $this->view->supportsSelectAll()) {
			// #11355 - see ContainerContentGUI::renderSelectAllBlock()
			$tpl->setCurrentBlock('select_all_row');
			$tpl->setVariable('CHECKBOXNAME', 'ilToolbarSelectAll');
			$tpl->setVariable('SEL_ALL_PARENT', 'ilToolbar');
			$tpl->setVariable('SEL_ALL_CB_NAME', 'id');
			$tpl->setVariable('TXT_SELECT_ALL', $this->lng->txt('select_all'));
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	protected function resetRowType()
	{
		$this->cur_row_type = "";
	}
	
	/**
	* returns a new list block template
	*
	* @access	private
	* @return	object		block template
	*/
	function newBlockTemplate()
	{
		$tpl = new ilTemplate("tpl.pd_list_block.html", true, true, "Services/PersonalDesktop");
		$this->cur_row_type = "";
		return $tpl;
	}

	/**
	 * @param ilTemplate                  $a_tpl
	 * @param ilPDSelectedItemsBlockGroup $group
	 */
	protected function addSectionHeader(ilTemplate $a_tpl, ilPDSelectedItemsBlockGroup $group)
	{
		if($group->hasIcon())
		{
			$a_tpl->setCurrentBlock('container_header_row_image');
			$a_tpl->setVariable('HEADER_IMG', $group->getIconPath());
			$a_tpl->setVariable('HEADER_ALT', $group->getLabel());
		}
		else
		{
			$a_tpl->setCurrentBlock('container_header_row');
		}

		$a_tpl->setVariable('BLOCK_HEADER_CONTENT', $group->getLabel());
		$a_tpl->setVariable('BLOCK_HEADER_ID', 'th_' . md5($group->getLabel()));
		$a_tpl->parseCurrentBlock();

		$a_tpl->touchBlock('container_row');

		$this->resetRowType();
	}

	/**
	* adds a standard row to a block template
	*
	* @param	object		$a_tpl		block template
	* @param	string		$a_html		html code
	* @access	private
	*/
	function addStandardRow(&$a_tpl, $a_html, $a_item_ref_id = "", $a_item_obj_id = "",
		$a_image_type = "", $a_related_header = "")
	{
		$ilSetting = $this->settings;
		
		$this->cur_row_type = ($this->cur_row_type == "row_type_1")
		? "row_type_2"
		: "row_type_1";
		$a_tpl->touchBlock($this->cur_row_type);
		
		if ($a_image_type != "")
		{
			if (!is_array($a_image_type) && !in_array($a_image_type, array("lm", "htlm", "sahs")))
			{
				$icon = ilUtil::getImagePath("icon_".$a_image_type.".svg");
				$title = $this->lng->txt("obj_".$a_image_type);
			}
			else
			{
				$icon = ilUtil::getImagePath("icon_lm.svg");
				$title = $this->lng->txt("learning_module");
			}

			if ($ilSetting->get('custom_icons')) {
				global $DIC;
				/** @var \ilObjectCustomIconFactory  $customIconFactory */
				$customIconFactory = $DIC['object.customicons.factory'];
				$customIcon        = $customIconFactory->getByObjId($a_item_obj_id, $a_image_type);

				if ($customIcon->exists()) {
					$icon = $customIcon->getFullPath();
				}
			}

			$a_tpl->setCurrentBlock("block_row_image");
			$a_tpl->setVariable("ROW_IMG", $icon);
			$a_tpl->setVariable("ROW_ALT", $title);
			$a_tpl->parseCurrentBlock();
		}
		else
		{
			$a_tpl->setVariable("ROW_NBSP", "&nbsp;");
		}
		$a_tpl->setCurrentBlock("container_standard_row");
		$a_tpl->setVariable("BLOCK_ROW_CONTENT", $a_html);
		$rel_headers = ($a_related_header != "")
		? "th_selected_items ".$a_related_header
		: "th_selected_items";
		$a_tpl->setVariable("BLOCK_ROW_HEADERS", $rel_headers);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}

	/**
	 * Called if the user interacted with the provided sorting options
	 */
	public function changePDItemPresentation()
	{
		$this->viewSettings->storeActorPresentationMode(
			\ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['presentation'] ?? ''))
		);
		$this->initAndShow();
	}

	/**
	 * Called if the user interacted with the provided presentation options
	 */
	public function changePDItemSorting()
	{
		$this->viewSettings->storeActorSortingMode(
			\ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['sorting'] ?? ''))
		);

		$this->initAndShow();
	}

	/**
	 * 
	 */
	protected function initAndShow()
	{
		$this->initViewSettings();

		if ($this->ctrl->isAsynch()) {
			echo $this->getHTML();
			exit;
		}

		$this->ctrl->setParameterByClass('ilpersonaldesktopgui', 'view', $this->viewSettings->getCurrentView());
		$this->ctrl->redirectByClass('ilpersonaldesktopgui', 'show');
	}

	function manageObject()
	{
		$this->manage = true;
		$this->setAvailableDetailLevels(1, 1);

		$top_tb = new ilToolbarGUI();
		$top_tb->setFormAction($this->ctrl->getFormAction($this));
		$top_tb->setLeadingImage(ilUtil::getImagePath('arrow_upright.svg'), $this->lng->txt('actions'));

		$button = ilSubmitButton::getInstance();
		if ($this->viewSettings->isSelectedItemsViewActive()) {
			$button->setCaption('remove');
		} else {
			$button->setCaption('pd_unsubscribe_memberships');
		}
		$button->setCommand('confirmRemove');
		$top_tb->addStickyItem($button);

		$button2 = ilSubmitButton::getInstance();
		$button2->setCaption('cancel');
		$button2->setCommand('getHTML');
		$top_tb->addStickyItem($button2);

		$top_tb->setCloseFormTag(false);

		$bot_tb = new ilToolbarGUI();
		$bot_tb->setLeadingImage(ilUtil::getImagePath('arrow_downright.svg'), $this->lng->txt('actions'));
		$bot_tb->addStickyItem($button);
		$bot_tb->addStickyItem($button2);
		$bot_tb->setOpenFormTag(false);

		return $top_tb->getHTML() . $this->getHTML() . $bot_tb->getHTML();
	}
	
	public function confirmRemoveObject()
	{
		$this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());

		$refIds = (array)($this->http->request()->getParsedBody()['id'] ?? []);
		if (0 === count($refIds)) {
			ilUtil::sendFailure($this->lng->txt('select_one'), true);
			$this->ctrl->redirect($this, 'manage');
		}

		if ($this->viewSettings->isSelectedItemsViewActive()) {
			$question = $this->lng->txt('pd_info_delete_sure_remove');
			$cmd = 'confirmedRemove';
		} else {
			$question = $this->lng->txt('pd_info_delete_sure_unsubscribe');
			$cmd = 'confirmedUnsubscribe';
		}

		include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
		$cgui = new ilConfirmationGUI();
		$cgui->setHeaderText($question);

		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setCancel($this->lng->txt('cancel'), 'manage');
		$cgui->setConfirm($this->lng->txt('confirm'), $cmd);

		foreach ($refIds as $ref_id) {
			$obj_id = ilObject::_lookupObjectId((int) $ref_id);
			$title = ilObject::_lookupTitle($obj_id);
			$type = ilObject::_lookupType($obj_id);

			$cgui->addItem('ref_id[]', $ref_id, $title,
				ilObject::_getIcon($obj_id, 'small', $type),
				$this->lng->txt('icon') . ' ' . $this->lng->txt('obj_' . $type)
			);
		}

		return $cgui->getHTML();
	}
		
	public function confirmedRemove()
	{
		$refIds = (array)($this->http->request()->getParsedBody()['ref_id'] ?? []);
		if (0 === count($refIds)) {
			$this->ctrl->redirect($this, 'manage');
		}

		foreach ($refIds as $ref_id) {
			$type = ilObject::_lookupType((int) $ref_id, true);
			ilObjUser::_dropDesktopItem($this->user->getId(), (int) $ref_id, $type);
		}

		// #12909
		ilUtil::sendSuccess($this->lng->txt('pd_remove_multi_confirm'), true);
		$this->ctrl->setParameterByClass('ilpersonaldesktopgui', 'view', $this->viewSettings->getCurrentView());
		$this->ctrl->redirectByClass('ilpersonaldesktopgui', 'show');
	}
	
	public function confirmedUnsubscribe()
	{
		$refIds = (array)($this->http->request()->getParsedBody()['ref_id'] ?? []);
		if (0 === count($refIds)) {
			$this->ctrl->redirect($this, 'manage');
		}

		foreach ($refIds as $ref_id) {
			if ($this->access->checkAccess('leave', '', (int) $ref_id)) {
				switch (ilObject::_lookupType($ref_id, true)) {
					case 'crs':
						// see ilObjCourseGUI:performUnsubscribeObject()
						$members = new ilCourseParticipants(ilObject::_lookupObjId((int) $ref_id));
						$members->delete($this->user->getId());

						$members->sendUnsubscribeNotificationToAdmins($this->user->getId());
						$members->sendNotification(
							$members->NOTIFY_UNSUBSCRIBE,
							$this->user->getId()
						);
						break;

					case 'grp':
						// see ilObjGroupGUI:performUnsubscribeObject()
						$members = new ilGroupParticipants(ilObject::_lookupObjId((int) $ref_id));
						$members->delete($this->user->getId());

						$members->sendNotification(
							ilGroupMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER,
							$this->user->getId()
						);
						$members->sendNotification(
							ilGroupMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE,
							$this->user->getId()
						);
						break;

					default:
						// do nothing
						continue;
				}

				ilForumNotification::checkForumsExistsDelete((int) $ref_id, $this->user->getId());
			}
		}

		ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
		$this->ctrl->setParameterByClass('ilpersonaldesktopgui', 'view', $this->viewSettings->getCurrentView());
		$this->ctrl->redirectByClass('ilpersonaldesktopgui', 'show');
	}


	/**
	 * @return string
	 * @throws ilTemplateException
	 */
	protected function getTileHTML()
	{
		$f = $this->ui->factory();
		$r = $this->ui->renderer();

		$tpl = new ilTemplate('tpl.block_tiles.html', true, true, 'Services/PersonalDesktop');

		$list_factory = new ilPDSelectedItemsBlockListGUIFactory($this);

		$groups = $this->view->getItemGroups();

		foreach ($groups as $group) {
			$items = $group->getItems();
			if (count($items) > 0) {
				$cards = [];
				foreach ($group->getItems() as $item) {
					$cards[] = $this->getCard($item, $list_factory);
				}

				$tpl->setCurrentBlock('head');
				$tpl->setVariable('HEAD', $group->getLabel());
				$tpl->parseCurrentBlock();

				$deck = $f->deck($cards)->withNormalCardsSize();
				$tpl->setCurrentBlock('tiles');
				if ($this->ctrl->isAsynch()) {
					$tpl->setVariable('TILES', $r->renderAsync($deck));
				} else {
					$tpl->setVariable('TILES', $r->render($deck));
				}
				$tpl->parseCurrentBlock();

				$tpl->setCurrentBlock('grouped_tiles');
				$tpl->parseCurrentBlock();
			}
		}

		$html = $tpl->get();
		if ($this->ctrl->isAsynch()) {
			$html .= $tpl->getOnLoadCodeForAsynch();
		}

		return $html;
	}

	/**
	 * Render card
	 * @param array $item
	 * @param ilPDSelectedItemsBlockListGUIFactory $listFactory
	 * @return \ILIAS\UI\Component\Card\Card
	 * @throws ilException
	 */
	protected function getCard(array $item, ilPDSelectedItemsBlockListGUIFactory $listFactory) : \ILIAS\UI\Component\Card\Card
	{
		$itemListGui = $listFactory->byType($item['type']);
		ilObjectActivation::addListGUIActivationProperty($itemListGui, $item);

		$itemListGui->initItem(
			$item['ref_id'],
			$item['obj_id'],
			$item['title'],
			$item['description']
		);

		$itemListGui->insertCommands();
		$actions = [];
		foreach ($itemListGui->current_selection_list->getItems() as $action_item) {
			$actions[] = $this->ui->factory()
				->button()
				->shy($action_item["title"], $action_item["link"]);
		}
		$dropdown = $this->ui->factory()->dropdown()->standard($actions);

		$def_command = $itemListGui->getDefaultCommand();

		$img = $this->objectService->commonSettings()->tileImage()->getByObjId($item['obj_id']);
		if ($img->exists()) {
			$path = $img->getFullPath();
		} else {
			$path = ilUtil::getImagePath('cont_tile/cont_tile_default_' . $item['type'] . '.svg');
			if (!is_file($path)) {
				$path = ilUtil::getImagePath('cont_tile/cont_tile_default.svg');
			}
		}

		$image = $this->ui->factory()
			->image()
			->responsive($path, '');
		if ($def_command['link'] != '')    // #24256
		{
			$image = $image->withAction($def_command['link']);
		}

		$title = $item['title'];

		if ($item['type'] == 'sess' && $item['title'] == '') {
			$app_info = ilSessionAppointment::_lookupAppointment($item['obj_id']);
			$title = ilSessionAppointment::_appointmentToString($app_info['start'], $app_info['end'],
				$app_info['fullday']);
		}

		$icon = $this->ui->factory()
			->icon()
			->standard($item['type'], $this->lng->txt('obj_' . $item['type']))
			->withIsOutlined(true);
		$card = $this->ui->factory()->card()->repositoryObject(
			$title . '<span data-list-item-id="' . $itemListGui->getUniqueItemId(true) . '""></span>',
			$image
		)->withObjectIcon($icon)
			->withActions($dropdown);

		// #24256
		if ($def_command['link']) {
			$card = $card->withTitleAction($def_command['link']);
		}

		$l = [];
		foreach ($itemListGui->determineProperties() as $p) {
			if ($p["property"] != $this->lng->txt("learning_progress")) {
				$l[(string)$p["property"]] = (string)$p["value"];
			}
		}
		if (count($l) > 0) {
			$prop_list = $this->ui->factory()->listing()->descriptive($l);
			$card = $card->withSections([$prop_list]);
		}

		$lp = ilLPStatus::getListGUIStatus($item['obj_id'], false);
		if (is_array($lp) && array_key_exists('status', $lp)) {
			$percentage = (int)ilLPStatus::_lookupPercentage($item['obj_id'], $this->user->getId());
			if ($lp['status'] == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
				$percentage = 100;
			}

			$card = $card->withProgress($this->ui->factory()->chart()->progressMeter()->mini(100, $percentage));
		}

		return $card;
	}
}