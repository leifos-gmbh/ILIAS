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
	protected $blockView;

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

		$this->blockView = ilPDSelectedItemsBlockViewGUI::bySettings($this->viewSettings);

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

		$this->setTitle($this->blockView->getTitle());

		$DIC->database()->useSlave(true);

		// workaround to show details row
		$this->setData(['dummy']);

		ilObjectListGUI::prepareJSLinks('',
			$this->ctrl->getLinkTargetByClass(['ilcommonactiondispatchergui', 'ilnotegui'], '', '', true, false),
			$this->ctrl->getLinkTargetByClass(['ilcommonactiondispatchergui', 'iltagginggui'], '', '', true, false)
		);

		$DIC['ilHelp']->setDefaultScreenId(ilHelpGUI::ID_PART_SCREEN, $this->blockView->getScreenId());

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
			$this->setDataSection($this->blockView->getIntroductionHtml());
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
	protected function getGroupedCommandsForView() : array
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
			return;
		}

		if ($this->blockView->isInManageMode()) {
			return;
		}

		$groupedCommands = $this->getGroupedCommandsForView();
		foreach ($groupedCommands as $group) {
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
	 * @param ilPDSelectedItemsBlockGroup[] $grouped_items
	 * @param bool $showHeader
	 * @return string
	 */
	protected function renderGroupedItems(array $grouped_items, $showHeader = false) : string 
	{
		if (0 === count($grouped_items)) {
			return '';
		}

		$listFactory = new ilPDSelectedItemsBlockListGUIFactory($this, $this->blockView);

		if ($this->viewSettings->isTilePresentation()) {
			$renderer = new ilPDObjectsTileRenderer(
				$this->blockView,
				$this->ui->factory(),
				$this->ui->renderer(),
				$listFactory,
				$this->user,
				$this->lng,
				$this->objectService,
				$this->ctrl
			);
			
			return $renderer->render($grouped_items, $showHeader);
		}

		$renderer = new ilPDObjectsListRenderer(
			$this->blockView,
			$this->ui->factory(),
			$this->ui->renderer(),
			$listFactory,
			$this->user,
			$this->lng,
			$this->objectService,
			$this->ctrl
		);

		return $renderer->render($grouped_items, $showHeader);
	}

	/**
	 * @return string
	 */
	protected function getViewBlockHtml() : string 
	{
		return $this->renderGroupedItems(
			$this->blockView->getItemGroups(),
			($this->getCurrentDetailLevel() >= $this->blockView->getMinimumDetailLevelForSection())
		);
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
		$this->blockView->setIsInManageMode(true);

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
}