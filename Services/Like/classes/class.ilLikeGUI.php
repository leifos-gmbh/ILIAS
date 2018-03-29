<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User interface for like feature
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesLike
 */
class ilLikeGUI
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var int
	 */
	protected $obj_id;

	/**
	 * @var string
	 */
	protected $obj_type;

	/**
	 * @var int
	 */
	protected $sub_obj_id;

	/**
	 * @var string
	 */
	protected $sub_obj_type;

	/**
	 * @var int
	 */
	protected $news_id;

	/**
	 * ilLikeGUI constructor
	 */
	function __construct()
	{
		global $DIC;

		$this->lng = $DIC->language();
		$this->ctrl = $DIC->ctrl();
		$this->user = $DIC->user();
		$this->ui = $DIC->ui();

		$this->lng->loadLanguageModule("like");
	}

	/**
	 * Execute command
	 * @return string
	 */
	function executeCommand()
	{
		$ilCtrl = $this->ctrl;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("getHTML");

		switch($next_class)
		{
			default:
				if (in_array($cmd, array("getHTML", "renderEmoticons", "renderModal")))
				{
					return $this->$cmd();
				}
				break;
		}
		return "";
	}

	/**
	 * Set Object.
	 *
	 * @param	int			$a_obj_id			Object ID
	 * @param	string		$a_obj_type			Object Type
	 * @param	int			$a_sub_obj_id		Subobject ID
	 * @param	string		$a_sub_obj_type		Subobject Type
	 */
	function setObject($a_obj_id, $a_obj_type, $a_sub_obj_id = 0, $a_sub_obj_type = "", $a_news_id = 0)
	{
		if(!trim($a_sub_obj_type))
		{
			$a_sub_obj_type = "-";
		}

		$this->obj_id = $a_obj_id;
		$this->obj_type = $a_obj_type;
		$this->sub_obj_id = $a_sub_obj_id;
		$this->sub_obj_type = $a_sub_obj_type;
		$this->news_id = $a_news_id;
		$this->id = "like_".$this->obj_id."_".$this->obj_type."_".$this->sub_obj_id."_".
			$this->sub_obj_type."_".$this->news_id;
	}

	/**
	 * Get HTML
	 *
	 * @param
	 * @return
	 */
	function getHTML()
	{
		$f = $this->ui->factory();
		$r = $this->ui->renderer();
		$ctrl = $this->ctrl;
		$lng = $this->lng;

		$tpl = new ilTemplate("tpl.like.html", true, true, "Services/Like");

		// modal
		$modal_asyn_url = $ctrl->getLinkTarget($this, "renderModal", "", true, false);
		$modal = $f->modal()->roundtrip('', $f->legacy(""))
			->withAsyncRenderUrl($modal_asyn_url);

		$comps = [];
		$comps[] = $f->glyph()->like()
			->withOnClick($modal->getShowSignal())
			->withCounter($f->counter()->status(5));
		$comps[] = $f->glyph()->wow()
			->withOnClick($modal->getShowSignal())
			->withCounter($f->counter()->status(4));
		$comps[] = $modal;

		$glyphs = $r->render($comps);

		$tpl->setVariable("MODAL_TRIGGER", $glyphs);

		$tpl->setVariable("SEP", $r->render($f->divider()->vertical()));


		// emoticon popover
		$popover = $f->popover()->standard($f->legacy(''))->withTitle('');
		$ctrl->setParameter($this, "repl_sig", $popover->getReplaceContentSignal()->getId());
		$asyn_url = $ctrl->getLinkTarget($this, "renderEmoticons", "", true, false);
		$popover = $popover->withAsyncContentUrl($asyn_url);
		$button = $f->button()->shy($lng->txt("like"), '#')
			->withOnClick($popover->getShowSignal());

		$tpl->setVariable("LIKE", $r->render([$popover, $button]));

		return $tpl->get();
	}

	/**
	 * Render emoticons
	 */
	function renderEmoticons()
	{
		$f = $this->ui->factory();
		$r = $this->ui->renderer();

		$tpl = new ilTemplate("tpl.emoticons.html", true, true, "Services/Like");
		$tpl->setVariable("ID", $this->id);

		$glyphs[] = $f->glyph()->like();
		$glyphs[] = $f->glyph()->dislike();
		$glyphs[] = $f->glyph()->love();
		$glyphs[] = $f->glyph()->laugh();
		$glyphs[] = $f->glyph()->wow();
		$glyphs[] = $f->glyph()->sad();
		$glyphs[] = $f->glyph()->angry();

		$tpl->setVariable("GLYPHS", $r->render($glyphs));

		echo $tpl->get();
		exit;
	}

	/**
	 * Render modal
	 */
	function renderModal()
	{
		$user = $this->user;

		$f = $this->ui->factory();
		$r = $this->ui->renderer();

		$image = $f->image()->responsive(
			ilObjUser::_getPersonalPicturePath($user->getId()),
			"Thumbnail Example");

		$list_item1 = $f->item()->standard("Max Learner")
			->withDescription("👍&nbsp;&nbsp; 11. Feb 2017")
			->withLeadImage($image);

		$list_item2 = $f->item()->standard("Alex Killing")
			->withDescription("👍&nbsp;&nbsp; 10. Feb 2017")
			->withLeadImage($image);

		$list_item3 = $f->item()->standard("Hans Moser")
			->withDescription("😆&nbsp;&nbsp; 8. Feb 2017")
			->withLeadImage($image);

		$list_item4 = $f->item()->standard("Fred Schmidt")
			->withDescription("😆&nbsp;&nbsp; 7. Feb 2017")
			->withLeadImage($image);

		$list_item5 = $f->item()->standard("Gustav Schwan")
			->withDescription("👍&nbsp;&nbsp; 4. Feb 2017")
			->withLeadImage($image);

		$list_item6 = $f->item()->standard("Fridolin Fischer")
			->withDescription("😆&nbsp;&nbsp; 2. Feb 2017")
			->withLeadImage($image);

		$list_item7 = $f->item()->standard("Paul Pastete")
			->withDescription("👍&nbsp;&nbsp; 1. Feb 2017")
			->withLeadImage($image);

		$std_list = $f->panel()->listing()->standard("", array(
			$f->item()->group("", array(
				$list_item1,
				$list_item2,
				$list_item3,
				$list_item4,
				$list_item5,
				$list_item6,
				$list_item7
			))
		));



		$modal = $f->modal()->roundtrip('', $std_list);
		echo $r->render($modal);
		exit;
	}




}

?>