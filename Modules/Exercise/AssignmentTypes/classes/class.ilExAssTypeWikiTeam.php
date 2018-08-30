<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Exercise/AssignmentTypes/classes/interface.ilExAssignmentTypeInterface.php");

/**
 * Team wiki type
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilExAssTypeWikiTeam implements ilExAssignmentTypeInterface
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * Constructor
	 *
	 * @param ilLanguage|null $a_lng
	 */
	public function __construct(ilLanguage $a_lng = null)
	{
		global $DIC;

		$this->lng = ($a_lng)
			? $a_lng
			: $DIC->language();
	}

	/**
	 * @inheritdoc
	 */
	public function isActive()
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function usesTeams()
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function usesFileUpload()
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle()
	{
		$lng = $this->lng;
		$lng->loadLanguageModule("wiki");
		return $lng->txt("wiki_type_wiki_team");
	}

	/**
	 * @inheritdoc
	 */
	public function getSubmissionType()
	{
		return "";
	}

}