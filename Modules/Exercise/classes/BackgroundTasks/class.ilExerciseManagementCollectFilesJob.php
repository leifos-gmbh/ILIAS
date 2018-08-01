<?php
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class
 *
 * @author Jesús López <lopez@leifos.com>
 *
 */
class ilExerciseManagementCollectFilesJob extends AbstractJob
{
	/**
	 * @var ilLogger
	 */
	private $logger = null;

	/**
	 * @var string
	 */
	protected $target_directory;

	protected $assignment;
	protected $temp_dir;
	protected $lng;
	protected $sanitized_title; //sanitized file name/sheet title
	protected $excel; //ilExcel
	protected $criteria_items; //array
	protected $title_columns;
	protected $ass_types_with_files; //TODO will be deprecated when use the new assignment type interface
	protected $participant_id;

	const FBK_DIRECTORY = "Feedback_files";
	const LINK_COLOR = "0,0,255";
	const BG_COLOR = "255,255,255";
	const FIRST_DEFAULT_REVIEW_COLUM = 3;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $DIC;
		//TODO instead fo root use exc
		$this->logger = $DIC->logger()->root();
		$this->lng = $DIC->language();
		//TODO will be deprecated when use the new assignment type interface
		$this->ass_types_with_files = array(
			ilExAssignment::TYPE_UPLOAD,
			ilExAssignment::TYPE_UPLOAD_TEAM,
			ilExAssignment::TYPE_BLOG,
			ilExAssignment::TYPE_PORTFOLIO
		);
		//$this->logger = $DIC->logger()->exc();
	}

	/**
	 * @return array
	 */
	public function getInputTypes()
	{
		if($this->participant_id) {
			return
				[
					new SingleType(StringValue::class),
					new SingleType(StringValue::class),
					new SingleType(StringValue::class)
				];
		}

		return
			[
				new SingleType(StringValue::class),
				new SingleType(StringValue::class)
			];
	}

	/**
	 * @return SingleType
	 */
	public function getOutputType()
	{
		return new SingleType(StringValue::class);
	}

	public function isStateless()
	{
		return true;
	}

	/**
	 * run the job
	 * @param array $input
	 * @param Observer $observer
	 * @return StringValue
	 */
	public function run(array $input, Observer $observer)
	{
		$ass_has_feedback = false;
		$ass_has_criteria = false;

		$exercise_id = $input[0]->getValue();
		$assignment_id = $input[1]->getValue();

		//assignment object
		$this->assignment = new ilExAssignment($assignment_id);
		$assignment_type = $this->assignment->getType();

		//Sanitized title for excel file and target directory.
		$this->sanitized_title = ilUtil::getASCIIFilename($this->assignment->getTitle());

		// directories
		$this->createUniqueTempDirectory();
		$this->createTargetDirectory();

		//Collect submission files if needed by assignment type.
		//TODO check participant id and download only the own files.
		if(in_array($assignment_type,$this->ass_types_with_files)) {
			$this->collectSubmissionFiles($exercise_id);
		}

		if($this->assignment->getPeerReview()) {
			$ass_has_feedback = true;
			//obj to get the reviews in the foreach below.
			$peer_review = new ilExPeerReview($this->assignment);
			//default start column for revisions.
			$first_excel_column_for_review = self::FIRST_DEFAULT_REVIEW_COLUM;
		}

		if($this->isExcelNeeded($assignment_type, $ass_has_feedback))
		{
			// PhpSpreadsheet object
			include_once "./Services/Excel/classes/class.ilExcel.php";
			$this->excel = new ilExcel();

			//Excel sheet title
			$this->excel->addSheet($this->sanitized_title);

			//add common excel Columns
			//TODO: they are lang vars
			$this->title_columns = array(
				'name_of_participants',
				'last_submission'
			);
			switch($assignment_type)
			{
				case ilExAssignment::TYPE_TEXT:
					$this->title_columns[] = 'submission_text';
					break;
				case ilExAssignment::TYPE_UPLOAD:
					$num_columns_submission = $this->getNumOfColumnsForSubmissionFiles($exercise_id,$assignment_id);
					for($i = 1; $i <= $num_columns_submission; $i++)
					{
						$this->title_columns[] = 'submission_file_'.$i;
					}
					$first_excel_column_for_review += $num_columns_submission;
					break;
				default:
					$this->title_columns[] = 'submission';
					break;
			}
			if($ass_has_feedback)
			{
				$this->title_columns[] = 'name_of_feedback_giver';
				$this->title_columns[] = 'last_feedback';
			}

			//criteria
			//Possible TODO -> getPeerReviewCriteriaCatalogueItems can return just an empty instance without data.
			if($this->criteria_items = $this->assignment->getPeerReviewCriteriaCatalogueItems()){
				$ass_has_criteria = true;
			}

			//Members who sent the submission.
			//TODO edit this to get the same array but only with one participant depending on participant_id.
			$participants = $this->assignment->getMemberListData();

			$row = 2;
			$submission_counter = 0;

			foreach($participants as $participant_id => $participant)
			{
				$col = 1;
				$this->excel->setCell($row,$col, $participant['name']);
				$this->excel->setCell($row,++$col, $participant['submission']);

				$submission = new ilExSubmission($this->assignment,$participant_id);
				$submission_files = $submission->getFiles();

				//Get the submission Text
				if(!in_array($assignment_type, $this->ass_types_with_files)) {
					$this->excel->setCell($row, ++$col, $submission_files[$submission_counter]['atext']);
				} else {
					foreach($submission_files as $submission_file)
					{
						++$col;
						$this->excel->setCell($row, $col, $submission_file['filetitle']);
						$this->excel->setColors($this->excel->getCoordByColumnAndRow($col,$row), self::BG_COLOR,self::LINK_COLOR);
						$this->excel->addLink($row, $col, './'.$this->lng->txt("exc_ass_submission_zip").$submission_file['filetitle']);
					}
				}

				if($ass_has_feedback)
				{
					if($col < $first_excel_column_for_review) {

						$col = $first_excel_column_for_review;
					}
					$reviews = $peer_review->getPeerReviewsByPeerId($participant_id);

					//extra lines
					$current_review_row = 0;
					foreach($reviews as $review)
					{
						++$current_review_row;
						if($review['tstamp'])
						{
							if($current_review_row > 1)
							{
								for($i=1;$i<$first_excel_column_for_review;$i++)
								{
									$cell_to_copy = $this->excel->getCell($row,$i);
									$this->excel->setCell($row +1, $i, $cell_to_copy);
									if($i >= self::FIRST_DEFAULT_REVIEW_COLUM){
										$this->excel->setColors($this->excel->getCoordByColumnAndRow($i,$row+1), self::BG_COLOR,self::LINK_COLOR);
									}
								}
								++$row;
							}
							$feedback_giver = $review['giver_id']; // user who made the review.
							$this->excel->setCell($row, $col, ilObjUser::_lookupFullname($feedback_giver));
							$this->excel->setCell($row, $col+1, $review['tstamp']);
							if($ass_has_criteria)
							{
								$this->addCriteriaToExcel($feedback_giver, $participant_id, $row, $col+1);
							}
						}
					}
				}

				$submission_counter++;
				$row++;
			}

			//ADD column titles
			$this->addColumnTitles();

			$this->excel->writeToFile($this->target_directory."/".$this->sanitized_title);

		}

		$out = new StringValue();
		$out->setValue($this->target_directory);
		return $out;
	}

	/**
	 * TODO use the new filesystem.
	 * @param $a_directory string
	 * @param $a_file string
	 */
	public function copyFileToSubDirectory($a_directory, $a_file)
	{
		$dir = $this->target_directory."/".$a_directory;

		if(!is_dir($dir))
		{
			ilUtil::createDirectory($dir);
		}

		copy($a_file, $dir."/".basename($a_file));

		/*global $DIC;
		$fs = $DIC->filesystem();

		$fs->storage()->copy($a_file, $this->temp_dir."/".basename($a_file));*/
	}

	/**
	 * @inheritdoc
	 */
	public function getExpectedTimeOfTaskInSeconds()
	{
		return 30;
	}

	protected function addColumnTitles()
	{
		$col = 1;
		foreach($this->title_columns as $title)
		{
			$this->excel->setCell(1, $col, $title);
			$col++;
		}
	}

	/**
	 * @todo refactor to new file system access
	 * Create unique temp directory
	 * @return string absolute path to new temp directory
	 */
	protected function createUniqueTempDirectory()
	{
		$this->temp_dir = ilUtil::ilTempnam();
		ilUtil::makeDirParents($this->temp_dir);
	}

	protected function createTargetDirectory()
	{
		//todo sanitize this name.
		$this->target_directory = $this->temp_dir."/".$this->sanitized_title;
		ilUtil::createDirectory($this->target_directory);
	}

	/**
	 * TODO -> put the reference of the original code.
	 * @param $a_exercise_id
	 */
	function collectSubmissionFiles($a_exercise_id)
	{
		$members = array();

		$exercise = new ilObjExercise($a_exercise_id, false);

		foreach($exercise->members_obj->getMembers() as $member_id)
		{
			$submission = new ilExSubmission($this->assignment, $member_id);
			$submission->updateTutorDownloadTime();

			// get member object (ilObjUser)
			if (ilObject::_exists($member_id))
			{
				// adding file metadata
				foreach($submission->getFiles() as $file)
				{
					$members[$file["user_id"]]["files"][$file["returned_id"]] = $file;
				}

				$tmp_obj =& ilObjectFactory::getInstanceByObjId($member_id);
				$members[$member_id]["name"] = $tmp_obj->getFirstname() . " " . $tmp_obj->getLastname();
				unset($tmp_obj);
			}
		}

		ilExSubmission::downloadAllAssignmentFiles($this->assignment, $members,$this->target_directory);
	}

	/**
	 * @param $a_ass_type string
	 * @param $a_has_fbk bool
	 */
	protected function isExcelNeeded($a_ass_type, $a_has_fbk)
	{
		if($a_ass_type == ilExAssignment::TYPE_TEXT) {
			return true;
		}elseif($a_has_fbk && $a_ass_type != ilExAssignment::TYPE_UPLOAD_TEAM){
			return true;
		}

		return false;

	}


	protected function addCriteriaToExcel($feedback_giver,$participant_id, $row, $col)
	{
		$submission = new ilExSubmission($this->assignment,$participant_id);

		//Possible TODO: This getPeerReviewValues doesn't return always the same array structure then the client classes have
		//to deal with this. Use only one data structure will avoid this extra work.
		//values can be [19] => "blablablab" or ["text"] => "blablabla"
		$values = $submission->getPeerReview()->getPeerReviewValues($feedback_giver, $participant_id);

		foreach($this->criteria_items as $item)
		{
			//Criteria without catalog doesn't have ID nor TITLE. The criteria instance is given via "type" ilExcCriteria::getInstanceByType
			$crit_id = $item->getId();
			$crit_type = $item->getType();
			$crit_title = $item->getTitle();
			if($crit_title == ""){
				$crit_title = $item->getTranslatedType();
			}

			if(!in_array($crit_title, $this->title_columns)) {
				$this->title_columns[] = $crit_title;
			}
			switch ($crit_type){
				case 'bool':
					if($values[$crit_id] == 1){
						$this->excel->setCell($row,++$col,$this->lng->txt("yes"));
					} elseif($values[$crit_id] == -1){
						$this->excel->setCell($row,++$col,$this->lng->txt("no"));
					}
					break;
				case 'rating':
					/*
					 * Get the rating data from the DB in the current less expensive way.
					 * assignment_id -> used in il_rating.obj_id
					 * object type as string ->  used in il_rating.obj_type
					 * participant id -> il_rating.sub_obj_id
					 * "peer_" + criteria_id -> il_rating.sub_obj_type (peer or e.g. peer_12)
					 * peer id -> il_rating.user_id
					 */
					// Possible TODO: refactor ilExAssignment->getPeerReviewCriteriaCatalogueItems somehow to avoid client
					// classes to deal with ilExCriteria instances with persistence (by id) or instances on the fly (by type)
					$sub_obj_type = "peer";
					if($crit_id) {
						$sub_obj_type .= "_".$crit_id;
					}
					$rating = ilRating::getRatingForUserAndObject(
						$this->assignment->getId(),
						'ass',
						$participant_id,
						$sub_obj_type,
						$feedback_giver
					);
					if($rating_int = round((int)$rating))
					{
						$this->excel->setCell($row,++$col, $rating_int);
					}
					break;
				case 'text':
					//again another check for criteria id (if instantiated via type)
					if($crit_id) {
						$this->excel->setCell($row,++$col, $values[$crit_id]);
					} else {
						$this->excel->setCell($row,++$col, $values['text']);
					}
					break;
				case 'file':  //BUG HERE! the file is not always in the proper row, check when addcriteriatoexcel to avoid it.
					if($crit_id) {
						$crit_file_obj = ilExcCriteriaFile::getInstanceById($crit_id);
					} else {
						$crit_file_obj = ilExcCriteriaFile::getInstanceByType($crit_type);
					}
					$crit_file_obj->setPeerReviewContext($this->assignment, $feedback_giver, $participant_id);
					$files = $crit_file_obj->getFiles();

					$extra_crit_column = 0;
					foreach($files as $file)
					{
						if($extra_crit_column) {
							$this->title_columns[] = $crit_title."_".$extra_crit_column;
						}
						$extra_crit_column++;
						$this->copyFileToSubDirectory(self::FBK_DIRECTORY,$file);
						$this->excel->setCell($row,++$col, "./".self::FBK_DIRECTORY.DIRECTORY_SEPARATOR.basename($file));
						$this->excel->addLink($row, $col, './'.self::FBK_DIRECTORY.DIRECTORY_SEPARATOR.basename($file));
						$this->excel->setColors($this->excel->getCoordByColumnAndRow($col,$row), self::BG_COLOR,self::LINK_COLOR);
					}
					break;
			}
		}
	}

	/**
	 * Get the number of max amount of files submitted by a single user in the assignment.
	 * Used to add columns to the excel.
	 * @param $a_obj_id
	 * @param $a_ass_id
	 * @return mixed
	 */
	public function getNumOfColumnsForSubmissionFiles($a_obj_id, $a_ass_id)
	{
		global $DIC;
		$ilDB = $DIC->database();

		$query = "SELECT MAX(max_num) AS max FROM (SELECT COUNT(user_id) AS max_num FROM exc_returned WHERE obj_id=".$a_obj_id." AND ass_id=".$a_ass_id." AND mimetype IS NOT NULL GROUP BY user_id) AS COUNTS";
		$set = $ilDB->query($query);
		$row = $ilDB->fetchAssoc($set);
		return $row['max'];
	}
}