<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesRating Services/Rating
 */

/**
 * Data class for like feature. DB related operations.
 *
 * The like table only holds a record if something has been "liked". After a "dislike" the record disappears.
 * This reduces space and increases performance. But we do not know "when" something has been disliked.
 *
 * Since the subobject_type column is pk it must be not null and does not allow "" due to the abstract DB handling.
 * We internally save "" as "-" here.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesLike
 */
class ilLikeData
{
	const TYPE_LIKE = 0;
	const TYPE_LOVE = 1;
	const TYPE_LAUGH = 2;
	const TYPE_WOW = 3;
	const TYPE_SAD = 4;
	const TYPE_ANGRY = 5;

	/**
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * Constructor
	 * @param ilDB $db
	 */
	function __construct(ilDB $db = null)
	{
		global $DIC;

		$this->db = ($db == null)
			? $DIC->database()
			: $db;
	}
	
	/**
	 * Add like for a user and object
	 *
	 * @param int $a_user_id user id (who is liking)
	 * @param int $a_like_type one of self::TYPE_LIKE to self::TYPE_ANGRY
	 * @param int $a_obj_id object id (must be an repository object id)
	 * @param string $a_obj_type object type (redundant, for performance reasons)
	 * @param int $a_sub_obj_id subobject id (as defined by the module being responsible for main object type)
	 * @param string $a_sub_obj_type subobject type (as defined by the module being responsible for main object type)
	 * @param int $a_news_id news is (optional news id, if like action is dedicated to a news for the object/subobject)
	 */
	public function like($a_user_id, $a_like_type, $a_obj_id, $a_obj_type, $a_sub_obj_id = 0, $a_sub_obj_type = "",
		$a_news_id = 0)
	{
		$ilDB = $this->db;

		if ($a_user_id == ANONYMOUS_USER_ID)
		{
			return;
		}

		if ($a_sub_obj_type == "")
		{
			$a_sub_obj_type = "-";
		}

		$ilDB->replace("like_item",
			array(
				"user_id" => array("integer", (int) $a_user_id),
				"obj_id" => array("integer", (int) $a_obj_id),
				"obj_type" => array("text", (int) $a_obj_type),
				"sub_obj_id" => array("integer", (int) $a_sub_obj_id),
				"sub_obj_type" => array("text", (int) $a_sub_obj_type),
				"news_id" => array("text", (int) $a_news_id),
				"like_type" => array("text", (int) $a_like_type)
				),
			array()
			);
	}

	/**
	 * Remove like for a user and object
	 *
	 * @param int $a_user_id user id (who is liking)
	 * @param int $a_like_type one of self::TYPE_LIKE to self::TYPE_ANGRY
	 * @param int $a_obj_id object id (must be an repository object id)
	 * @param string $a_obj_type object type (redundant, for performance reasons)
	 * @param int $a_sub_obj_id subobject id (as defined by the module being responsible for main object type)
	 * @param string $a_sub_obj_type subobject type (as defined by the module being responsible for main object type)
	 * @param int $a_news_id news is (optional news id, if like action is dedicated to a news for the object/subobject)
	 */
	public function dislike($a_user_id, $a_like_type, $a_obj_id, $a_obj_type, $a_sub_obj_id = 0, $a_sub_obj_type = "",
						 $a_news_id = 0)
	{
		$ilDB = $this->db;

		if ($a_user_id == ANONYMOUS_USER_ID)
		{
			return;
		}

		if ($a_sub_obj_type == "")
		{
			$a_sub_obj_type = "-";
		}

		$ilDB->manipulate("DELETE FROM like_item WHERE ".
			" user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND obj_id = ".$ilDB->quote($a_obj_id, "integer").
			" AND obj_type = ".$ilDB->quote($a_obj_type, "text").
			" AND sub_obj_id = ".$ilDB->quote($a_sub_obj_id, "integer").
			" AND sub_obj_type = ".$ilDB->quote($a_sub_obj_type, "text").
			" AND news_id = ".$ilDB->quote($a_news_id, "integer").
			" AND like_type = ".$ilDB->quote($a_like_type, "integer")
			);
	}

	/**
	 * Load data (for objects)
	 *
	 * @param int[] load data for objects
	 * @return ilLikeData
	 */
	function loadDataForObjects($a_obj_ids = array())
	{
		$ilDB = $this->db;

		$new_obj = clone $this;

		foreach ($a_obj_ids as $id)
		{
			$new_obj->data[$id] = array();
		}

		$set = $ilDB->query("SELECT * FROM like_item ".
			" WHERE ".$ilDB->in("obj_id", $a_obj_ids, false, "integer"));
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$subtype = $rec["subobject_type"] == "-"
				? ""
				: $rec["subobject_type"];
			$new_obj->data[$rec][$rec["subobject_id"]][$subtype][$rec["news_id"]][$rec["user_id"]][$rec["like_type"]] = 1;
		}
		return $new_obj;
	}

}

?>