<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;


/**
 * Download submissions and feedback for exercises.
 * @author Jesús López <lopez@leifos.com>
 */
class ilDownloadSubmissionsBackgroundTask
{
	/**
	 * @var int
	 */
	protected $exc_id;

	/**
	 * @var int|null
	 */
	protected $ass_id;

	/**
	 * @var int|null
	 */
	protected $participant_id;

	/**
	 * @var \ILIAS\BackgroundTasks\Task\TaskFactory
	 */
	protected $task_factory = null;

	/**
	 * Constructor
	 * @param integer $a_usr_id
	 * @param integer $a_exc_id
	 * @param integer $a_ass_id
	 * @param integer $a_participant_id
	 */
	public function __construct($a_usr_id, $a_exc_id, $a_ass_id = null, $a_participant_id = null)
	{
		global $DIC;

		$this->user_id = $a_usr_id;
		$this->exc_id = $a_exc_id;
		$this->ass_id = $a_ass_id;
		$this->participant_id = $a_participant_id;

		$this->task_factory = $DIC->backgroundTasks()->taskFactory();
		$this->task_manager = $DIC->backgroundTasks()->taskManager();
		$this->lng = $DIC->language();
	}

	public function run()
	{
		$bucket = new BasicBucket();
		$bucket->setUserId($this->user_id);
		include_once './Modules/Exercise/classes/BackgroundTasks/class.ilExerciseManagementCollectFilesJob.php';

		if($this->participant_id) {
			$collect_data_job = $this->task_factory->createTask(ilExerciseManagementCollectFilesJob::class,[$this->exc_id, $this->ass_id, $this->participant_id]);
		} else {
			$collect_data_job = $this->task_factory->createTask(ilExerciseManagementCollectFilesJob::class,[$this->exc_id, $this->ass_id]);
		}
		$zip_job = $this->task_factory->createTask(ilCalendarZipJob::class, [$collect_data_job]);

		$bucket->setTitle("THIS IS THE DUMMY BUCKET TITLE");
		$bucket->setTask($zip_job);
		$this->task_manager->run($bucket);
		return true;
	}

}