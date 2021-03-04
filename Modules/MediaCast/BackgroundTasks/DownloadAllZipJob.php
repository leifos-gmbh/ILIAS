<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\MediaCast\BackgroundTasks;

use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;

/**
 * Zip media files
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class DownloadAllZipJob extends AbstractJob
{
    private $logger = null;

    /**
     * Construct
     */
    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->mcst();
    }

    /**
     * @inheritDoc
     */
    public function getInputTypes()
    {
        return
            [
                new SingleType(StringValue::class)
            ];
    }

    /**
     * @inheritDoc
     */
    public function getOutputType()
    {
        return new SingleType(StringValue::class);
    }

    /**
     * @inheritDoc
     */
    public function isStateless()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function run(array $input, \ILIAS\BackgroundTasks\Observer $observer)
    {
        $tmpdir = $input[0]->getValue();

        $this->logger->debug("Zip $tmpdir into " . $tmpdir . '.zip');

        \ilUtil::zip($tmpdir, $tmpdir . '.zip');

        // delete temp directory
        \ilUtil::delDir($tmpdir);

        $zip_file_name = new StringValue();
        $zip_file_name->setValue($tmpdir . '.zip');

        $this->logger->debug("Returning " . $tmpdir . '.zip');

        return $zip_file_name;
    }

    /**
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds()
    {
        return 30;
    }
}
