<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\MediaCast\BackgroundTasks;

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;

/**
 * Collect files for downloading all media items
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 */
class DownloadAllCollectFilesJob extends AbstractJob
{
    /**
     * @var \ilLogger
     */
    private $logger = null;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var int
     */
    protected $mcst_id;

    /**
     * @var int
     */
    protected $mcst_ref_id;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var string
     */
    protected $target_directory;

    /**
     * @var string
     */
    protected $temp_dir;

    /**
     * @var string
     */
    protected $sanitized_title;

    /**
     * @var \ilObjMediaCast
     */
    protected $media_cast;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('mcst');
        $this->logger = $DIC->logger()->mcst();

        $this->sanitized_title = "images";
    }

    /**
     * @return array
     */
    public function getInputTypes()
    {
        return
            [
                new SingleType(IntegerValue::class),
                new SingleType(IntegerValue::class)
            ];
    }

    /**
     * @return SingleType
     */
    public function getOutputType()
    {
        return new SingleType(StringValue::class);
    }

    /**
     * @inheritdoc
     */
    public function isStateless()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds()
    {
        return 30;
    }

    /**
     * Run job
     *
     * @param array $input
     * @param Observer $observer
     * @return StringValue
     */
    public function run(array $input, Observer $observer)
    {
        $this->user_id = $input[0]->getValue();
        $this->mcst_ref_id = $input[1]->getValue();

        $this->logger->debug("Get Mediacast ".$this->mcst_ref_id);
        $this->media_cast = new \ilObjMediaCast($this->mcst_ref_id);

        $target_dir = $this->createDirectory();

        $this->logger->debug("Collect in ".$target_dir);
        $this->collectMediaFiles($target_dir);
        $this->logger->debug("Finished collecting.");
        
        $out = new StringValue();
        $out->setValue($target_dir);
        return $out;
    }

    /**
     * Create directory
     *
     * @return string
     */
    protected function createDirectory()
    {
        // temp dir
        $this->temp_dir = \ilUtil::ilTempnam();

        // target dir
        $path = $this->temp_dir . DIRECTORY_SEPARATOR;
        $this->target_directory = $path . $this->sanitized_title;
        \ilUtil::makeDirParents($this->target_directory);

        return $this->target_directory;
    }


    /**
     * Collect media files
     */
    public function collectMediaFiles($target_dir)
    {
        $cnt = 0;
        foreach ($this->media_cast->getSortedItemsArray() as $item) {
            $mob = new \ilObjMediaObject($item["mob_id"]);
            $med = $mob->getMediaItem("Standard");

            $cnt++;
            $str_cnt = str_pad($cnt, 4, "0", STR_PAD_LEFT);

            if ($med->getLocationType() === "Reference") {
                $resource = $med->getLocation();
                copy($resource, $target_dir . DIRECTORY_SEPARATOR . $str_cnt . basename($resource));
            } else {
                $path_to_file = \ilObjMediaObject::_getDirectory($mob->getId()) . "/" . $med->getLocation();
                copy($path_to_file, $target_dir . DIRECTORY_SEPARATOR . $str_cnt . $med->getLocation());
            }
        }
    }

}
