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

	const FBK_DIRECTORY = "Feedback_files";

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $DIC;
		//TODO instead fo root use exc
		$this->logger = $DIC->logger()->root();
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
	 * //TODO Refactor this method at the end of development.
	 * run the job
	 * @param array $input
	 * @param Observer $observer
	 * @return StringValue
	 */
	public function run(array $input, Observer $observer)
	{
		$exercise_id = $input[0]->getValue();
		$assignment_id = $input[1]->getValue();

		//assignment object
		$this->assignment = new ilExAssignment($assignment_id);
		//TODO sanitize this title.
		$assignment_title = $this->assignment->getTitle();
		$assignment_type = $this->assignment->getType();

		// directories
		$this->createUniqueTempDirectory();
		$this->createTargetDirectory();

		$ass_has_feedback = false;
		$ass_has_criteria = false;

		// PhpSpreadsheet object
		include_once "./Services/Excel/classes/class.ilExcel.php";
		$excel = new ilExcel();

		if($this->assignment->getPeerReview()) {
			$ass_has_feedback = true;
			//obj to get the reviews in the foreach below.
			$peer_review = new ilExPeerReview($this->assignment);
		}

		//TODO Find another method to check the Criteria Catalogue items(It returns items even when the Assignment has no criteria at all)
		//Todo: Remove this as soon as possible.
		if($criteria_items = $this->assignment->getPeerReviewCriteriaCatalogueItems()){
			$ass_has_criteria = true;
		}

		//Excel sheet title
		$excel->addSheet($assignment_title);

		//TODO: they are lang vars
		$title_columns = array(
			'name_of_participants',
			'last_submission'
		);
		switch($assignment_type)
		{
			case ilExAssignment::TYPE_TEXT:
				$title_columns[] = 'submission_text';
				break;
			case ilExAssignment::TYPE_UPLOAD:
				$title_columns[] = 'submission_file';
				break;
			default:
				$title_columns[] = 'submission';
				break;
		}

		if($ass_has_feedback)
		{
			$title_columns[] = 'name_of_feedback_giver';
			$title_columns[] = 'last_feedback';
		}

		$row = 2;
		$submission_counter = 0;
		foreach($this->assignment->getMemberListData() as $participant_id => $participant)
		{
			$col = 1;
			$excel->setCell($row,$col, $participant['name']);
			$excel->setCell($row,++$col, $participant['submission']);
			//TODO why not check for user specific files? There is only one method for get all files..
			//TODO because of this we are trusting in array ids as a sequence...
			$submission_files = ilExSubmission::getAllAssignmentFiles($exercise_id, $assignment_id);

			//Get the submission Text
			if($assignment_type == ilExAssignment::TYPE_TEXT) {
				$excel->setCell($row, ++$col, $submission_files[$submission_counter]['atext']);
			} else {
				// ass type upload
				$excel->setCell($row, ++$col, 'EXTERNAL LINK');
			}
			//$excel->getCell($row, 3)->Hyperlink->setUrl("http://ilias.de");

			if($ass_has_feedback)
			{
				$review = $peer_review->getPeerReviewsByPeerId($participant_id);

				$feedback_giver = $review[0]['giver_id']; // user who made the review.
				$excel->setCell($row, ++$col, ilObjUser::_lookupFullname($feedback_giver));
				//TODO check/ask if one submission can have more than one peer feedback
				$excel->setCell($row, ++$col, $review[0]['tstamp']);

			}
			//TODO Bugged Only works when criteria belongs to a catalog
			if(isset($feedback_giver) && $ass_has_criteria)
			{
				//check if really need this submission
				$submission = new ilExSubmission($this->assignment,$participant_id);

				$values = $submission->getPeerReview()->getPeerReviewValues($feedback_giver, $participant_id);

				foreach($criteria_items as $item)
				{
					//ilLoggerFactory::getRootLogger()->debug("Criteria title => ".$item->getTitle());
					$crit_id = $item->getId();
					$crit_type = $item->getType();

					if(!in_array($item->getTitle(), $title_columns)) {
						//todo multilanguage??
						$title_columns[] = $item->getTitle();
					}
					switch ($crit_type){
						case 'bool':
							//TODO: translate YES NO from  1 0 ????
							$excel->setCell($row,++$col,$values[$crit_id]);
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
								$assignment_id,
								'ass',
								$participant_id,
								$sub_obj_type,
								$feedback_giver
							);
							$excel->setCell($row,++$col, round((int)$rating));
							break;
						case 'text':
							$excel->setCell($row,++$col, $values[$crit_id]);
							break;
						case 'file':
							//Todo move the file to the temp dir as a ZIP.
							$crit_file_obj = ilExcCriteriaFile::getInstanceById($crit_id);
							$crit_file_obj->setPeerReviewContext($this->assignment, $participant_id, $feedback_giver);
							$files = $crit_file_obj->getFiles();
							$str_files = "";
							foreach($files as $file)
							{
								$this->copyFileToTempDirectory($file);
								$str_files .= $file."\n";
							}
							$excel->setCell($row,++$col, $str_files);
							break;
					}
				}
			}
			$submission_counter++;
			$row++;
		}

		//ADD column titles
		$this->addColumnTitles($title_columns, $excel);

		//TODO sanitize this title to avoid problems when file creation.
		$excel->writeToFile($this->target_directory."/$assignment_title");

		$out = new StringValue();
		$out->setValue($this->target_directory);
		return $out;
	}

	/**
	 * TODO use the new filesystem.
	 * @param $a_file string
	 */
	public function copyFileToTempDirectory($a_file)
	{
		if(!is_dir($this->target_directory."/".self::FBK_DIRECTORY))
		{
			ilUtil::createDirectory($this->target_directory."/".self::FBK_DIRECTORY);
		}

		copy($a_file, $this->target_directory."/".self::FBK_DIRECTORY."/".basename($a_file));

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

	protected function addColumnTitles($a_titles, $a_excel_obj)
	{
		$col = 1;
		foreach($a_titles as $title)
		{
			$a_excel_obj->setCell(1, $col, $title);
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
		$this->target_directory = $this->temp_dir."/".$this->assignment->getTitle();
		ilUtil::createDirectory($this->target_directory);
	}
}