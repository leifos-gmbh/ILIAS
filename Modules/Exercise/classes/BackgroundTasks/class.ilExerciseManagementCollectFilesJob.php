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

	const FBK_DIRECTORY = "Feedback_files";
	const LINK_COLOR = "0,0,255";
	const BG_COLOR = "255,255,255";

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $DIC;
		//TODO instead fo root use exc
		$this->logger = $DIC->logger()->root();
		$this->lng = $DIC->language();
		//$this->logger = $DIC->logger()->exc();
	}

	/**
	 * @return array
	 */
	public function getInputTypes()
	{
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

		//Collect submission files if its upload types.
		if($assignment_type == ilExAssignment::TYPE_UPLOAD || $assignment_type == ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			$this->collectSubmissionFiles($exercise_id);
		}

		if($this->assignment->getPeerReview()) {
			$ass_has_feedback = true;
			//obj to get the reviews in the foreach below.
			$peer_review = new ilExPeerReview($this->assignment);
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
					$this->title_columns[] = 'submission_file';
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
			if($this->criteria_items = $this->assignment->getPeerReviewCriteriaCatalogueItems()){
				$ass_has_criteria = true;
			}

			//Members who sent the submission.
			$participants = $this->assignment->getMemberListData();

			$row = 2;
			$submission_counter = 0;

			foreach($participants as $participant_id => $participant)
			{
				$col = 1;
				$this->excel->setCell($row,$col, $participant['name']);
				$this->excel->setCell($row,++$col, $participant['submission']);
				//TODO why not check for user specific files? There is only one method for get all files..
				//TODO because of this we are trusting in array ids as a sequence...
				$submission_files = ilExSubmission::getAllAssignmentFiles($exercise_id, $assignment_id);

				//Get the submission Text
				if($assignment_type == ilExAssignment::TYPE_TEXT) {
					$this->excel->setCell($row, ++$col, $submission_files[$submission_counter]['atext']);
				} elseif($assignment_type == ilExAssignment::TYPE_UPLOAD) {
					// TODO LINK THE FILE ass type upload
					//Problem I can only add link to the cell not to the text.
					$this->excel->setCell($row, ++$col, $submission_files[$submission_counter]['filetitle']);
					//$excel->addLink($row, $col,ilUtil::getASCIIFilename($this->lng->txt("exc_ass_submission_zip")).DIRECTORY_SEPARATOR.$submission_files[$submission_counter]['filetitle']);
					$this->excel->setColors($this->excel->getCoordByColumnAndRow($col,$row), self::BG_COLOR,self::LINK_COLOR);
				}

				if($ass_has_feedback)
				{
					$review = $peer_review->getPeerReviewsByPeerId($participant_id);

					$feedback_giver = $review[0]['giver_id']; // user who made the review.
					$this->excel->setCell($row, ++$col, ilObjUser::_lookupFullname($feedback_giver));
					//TODO check/ask if one submission can have more than one peer feedback
					$this->excel->setCell($row, ++$col, $review[0]['tstamp']);

				}

				if(isset($feedback_giver) && $ass_has_criteria)
				{
					$this->addCriteriaToExcel($feedback_giver, $participant_id, $row, $col);
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
	//TODO Bugged Only works when criteria belongs to a catalog
	protected function addCriteriaToExcel($feedback_giver,$participant_id, $row, $col)
	{
		$submission = new ilExSubmission($this->assignment,$participant_id);

		$values = $submission->getPeerReview()->getPeerReviewValues($feedback_giver, $participant_id);

		foreach($this->criteria_items as $item)
		{
			//Criteria without catalog doesn't have ID nor TITLE.
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
					} else {
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
					 * I don`t know if the category_id is relevant here.
					 */
					$sub_obj_type = "peer_".$crit_id;
					$rating = ilRating::getRatingForUserAndObject(
						$this->assignment->getId(),
						'ass',
						$participant_id,
						$sub_obj_type,
						$feedback_giver
					);
					$this->excel->setCell($row,++$col, round((int)$rating));
					break;
				case 'text':
					$this->excel->setCell($row,++$col, $values[$crit_id]);
					break;
				case 'file':

					//TODO probably we should move the files first somehow.
					if($crit_id) {
						$crit_file_obj = ilExcCriteriaFile::getInstanceById($crit_id);
					} else {
						$crit_file_obj = ilExcCriteriaFile::getInstanceByType($crit_type);
					}
					$crit_file_obj->setPeerReviewContext($this->assignment, $participant_id, $feedback_giver);
					$files = $crit_file_obj->getFiles();
					$str_files = "";
					//problem here how to link multiple files in one cell.
					foreach($files as $file)
					{
						$this->copyFileToSubDirectory(self::FBK_DIRECTORY,$file);
						$str_files .= $file."\n";
					}
					//$str_files .= "\n FINAL SUBMISSION DIRECTORY => ".$this->target_directory.DIRECTORY_SEPARATOR.self::SUB_DIRECTORY;
					$this->excel->setCell($row,++$col, $str_files);
					//$excel->addLink($row,$col,$this->target_directory.DIRECTORY_SEPARATOR.self::SUB_DIRECTORY);
					break;
			}
		}
	}
}