<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\MediaCast\BackgroundTasks;
use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;

/**
 * Download all items
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class DownloadAllBackgroundTask
{
    /**
     * @var int
     */
    protected $mcst_ref_id;

    /**
     * @var int
     */
    protected $mcst_id;

    /**
     * @var int|null
     */
    protected $user_id;

    /**
     * @var \ILIAS\BackgroundTasks\Task\TaskFactory
     */
    protected $task_factory = null;

    /**
     * @var \ILIAS\BackgroundTasks\TaskManager
     */
    protected $task_manager = null;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var
     */
    private $logger = null;

    /**
     * Constructor
     * @param int $a_usr_id
     * @param int $a_mcst_ref_id
     * @param int $a_mcst_id
     */
    public function __construct($a_usr_id, $a_mcst_ref_id, $a_mcst_id)
    {
        global $DIC;

        $this->user_id = $a_usr_id;
        $this->mcst_ref_id = $a_mcst_ref_id;
        $this->mcst_id = $a_mcst_id;

        $this->task_factory = $DIC->backgroundTasks()->taskFactory();
        $this->task_manager = $DIC->backgroundTasks()->taskManager();
        $this->logger = $DIC->logger()->mcst();
    }

    public function run()
    {
        $bucket = new BasicBucket();
        $bucket->setUserId($this->user_id);

        $this->logger->debug("* Create task 'collect_data_job' using the following values:");
        $this->logger->debug("job class = " . DownloadAllCollectFilesJob::class);
        $this->logger->debug("mcst_id = " . $this->mcst_id . ", mcst_ref_id = " . $this->mcst_ref_id . ", user_id = " . (int) $this->user_id);

        $collect_data_job = $this->task_factory->createTask(
            DownloadAllCollectFilesJob::class,
            [
                (int) $this->user_id,
                (int) $this->mcst_ref_id
            ]
        );

        $this->logger->debug("* Create task 'zip job' using the following values:");
        $this->logger->debug("job class = " . DownloadAllZipJob::class);
        $this->logger->debug("sending as input the task called->collect_data_job");

        $zip_job = $this->task_factory->createTask(DownloadAllZipJob::class, [$collect_data_job]);

        $download_name = \ilUtil::getASCIIFilename(\ilObject::_lookupTitle($this->mcst_id));
        $bucket->setTitle($download_name);

        $this->logger->debug("* Create task 'download_interaction' using the following values:");
        $this->logger->debug("job class = " . DownloadAllZipInteraction::class);
        $this->logger->debug("download_name which is the same as bucket title = " . $download_name . " + the zip_job task");
        // see comments here -> https://github.com/leifos-gmbh/ILIAS/commit/df6fc44a4c85da33bd8dd5b391a396349e7fa68f
        $download_interaction = $this->task_factory->createTask(DownloadAllZipInteraction::class, [$zip_job, $download_name]);

        //download name
        $bucket->setTask($download_interaction);
        $this->task_manager->run($bucket);
        return true;
    }
}
